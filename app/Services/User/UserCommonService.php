<?php

namespace App\Services\User;

use App\Extensions\AdvancedThrottlesLogins;
use App\Models\AccountLog;
use App\Models\BonusType;
use App\Models\ConnectUser;
use App\Models\MerchantsShopInformation;
use App\Models\Sessions;
use App\Models\UserRank;
use App\Models\Users;
use App\Models\UsersLog;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\StrRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Cart\CartCommonService;
use App\Services\Common\AreaService;

class UserCommonService
{
    use AdvancedThrottlesLogins;

    protected $dscRepository;
    protected $userBonusService;
    protected $userRankService;
    protected $connectUserService;
    protected $userOauthService;

    public function __construct(
        DscRepository $dscRepository,
        UserBonusService $userBonusService,
        UserRankService $userRankService,
        ConnectUserService $connectUserService,
        UserOauthService $userOauthService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->userBonusService = $userBonusService;
        $this->userRankService = $userRankService;
        $this->connectUserService = $connectUserService;
        $this->userOauthService = $userOauthService;
    }

    /**
     * 获得用户等级与折扣
     *
     * @param int $uid
     * @return array
     * @throws \Exception
     */
    public function getUserRankByUid($uid = 0)
    {
        $user_rank = [];
        if ($uid) {
            $user = Users::where('user_id', $uid)->select('user_id', 'user_rank', 'rank_points');
            $user = BaseRepository::getToArrayFirst($user);

            $rank_id = $user['user_rank'] ?? 0;
            $rank_points = $user['rank_points'] ?? 0;

            // 查询用户是否特殊会员等级
            $special_rank = 0;
            if ($rank_id > 0) {
                $user_rank_info = UserRank::where('rank_id', $rank_id);
                $user_rank = BaseRepository::getToArrayFirst($user_rank_info);

                $special_rank = $user_rank['special_rank'] ?? 0;
            }

            if (empty($special_rank)) {
                //用户是一般会员

                //是否开启成长值清零
                $open_user_rank_set = config('shop.open_user_rank_set', 0);
                if ($open_user_rank_set == 1) {
                    $clear_time = config('shop.clear_rank_point', 365);
                    $clear_time = (!isset($clear_time) || $clear_time <= 0) ? 365 : intval($clear_time);//默认365天
                    $clear_start_time = TimeRepository::getLocalStrtoTime('-' . $clear_time . ' days');
                    $rank_points = AccountLog::where('user_id', $uid)
                        ->where('change_time', '>=', $clear_start_time)
                        ->sum('rank_points');
                }

                $rank_points = $rank_points ? $rank_points : 0;

                //1.4.3 会员等级修改（成长值只有下限）
                $user_rank = $this->userRankService->getUserRankByPoint($rank_points);
            }
        }

        return [
            'rank_id' => $user_rank['rank_id'] ?? 0,
            'rank_name' => $user_rank['rank_name'] ?? '',
            'discount' => $user_rank['discount'] ?? 100,
        ];
    }

    /**
     * 根据用户名/电子邮箱/手机号返回用户信息
     * @param string $username
     * @return mixed
     */
    public function getUserByName($username = '')
    {
        $condition = ['field' => 'user_name', 'value' => $username];
        if (CommonRepository::getMatchEmail($username)) {
            $condition['field'] = 'email';
        } elseif (is_phone_number($username)) {
            $condition['field'] = 'mobile_phone';
        }

        return Users::where('user_name', $username)->orWhere($condition['field'], $condition['value'])->first();
    }

    /**
     * 生成ecjiaHash
     * @param array $user
     * @return string
     */
    public function ecjiaHash($user = [])
    {
        $time = TimeRepository::getGmTime();

        // 生成refresh token
        $refresh_token = md5($time . rand(100000, 999999));

        // 生成access token
        $access_token = md5($refresh_token);

        // 唯一标识
        $open_id = md5($user['user_id']);
        $connect_code = 'app'; // ecjia 原生APP类型

        $data = [
            'connect_code' => $connect_code,
            'user_type' => 'user',
            'user_id' => $user['user_id'],
            'open_id' => $open_id,
            'refresh_token' => $refresh_token,
            'access_token' => $access_token,
            'create_at' => $time
        ];

        // 兼容处理旧数据 clear connect_user
        ConnectUser::where('connect_code', '')
            ->where('user_type', 'user')
            ->where('user_id', $user['user_id'])
            ->delete();

        $model = ConnectUser::query()->where('connect_code', $connect_code)
            ->where('user_type', 'user')
            ->where('user_id', $user['user_id'])
            ->where('open_id', $open_id)
            ->first();

        if (empty($model)) {
            // insert connect_user
            ConnectUser::insert($data);
        } else {
            $model->update($data);
        }

        // 生成签名
        $data = [
            'gmtime' => $data['create_at'],
            'openid' => $data['open_id'],
            'origin' => $connect_code,
            'token' => $data['access_token'],
            'usertype' => 'user', // 默认为会员类型
        ];

        ksort($data);
        $data['sign'] = hash_hmac('md5', http_build_query($data, '', '&'), $refresh_token);

        // 返回 base64 数据
        return base64_encode(json_encode($data));
    }

    /**
     * 更新会员信息
     *
     * @param int $uid 默认会员ID
     * @param string $type [pc|mobile]使用端
     * @return mixed
     */
    public function updateUserInfo($uid = 0, $type = 'pc')
    {
        if (session()->has('user_id') && session('user_id')) {
            $user_id = session('user_id', 0);
        } else {
            $user_id = $uid;
        }

        $time = TimeRepository::getGmTime();

        if ($user_id > 0) {
            /* 查询会员信息 */
            $row = Users::where('user_id', $user_id);

            $row = $row->with([
                'getUserBonus' => function ($query) use ($time) {
                    $query = $query->select('user_id', 'bonus_type_id');

                    $query = $query->where('used_time', 0);

                    $query->whereHasIn('getBonusType', function ($query) use ($time) {
                        $query->where('use_start_date', '<=', $time)
                            ->where('use_end_date', '>=', $time);
                    });
                }
            ]);

            $row = BaseRepository::getToArrayFirst($row);

            $bonus_type_id = $row && $row['get_user_bonus'] ? $row['get_user_bonus']['bonus_type_id'] : 0;

            if ($bonus_type_id > 0) {
                $type_money = BonusType::where('type_id', $bonus_type_id)->value('type_money');
                $type_money = $type_money ? $type_money : 0;
                $row['type_money'] = $type_money;
            }

            if ($row) {
                if ($type === 'pc') {
                    session([
                        'user_name' => stripslashes($row['user_name']),
                        'nick_name' => stripslashes($row['nick_name']),
                        'last_time' => $row['last_login'],
                        'last_ip' => $row['last_ip'],
                        'login_fail' => 0,
                        'email' => $row['email']
                    ]);
                }

                /*判断是否是特殊等级，可能后台把特殊会员组更改普通会员组*/
                if ($row['user_rank'] > 0) {
                    $special_rank = UserRank::where('rank_id', $row['user_rank'])->value('special_rank');

                    if (!$special_rank) {
                        Users::where('user_id', $user_id)->update(['user_rank' => 0]);
                        $row['user_rank'] = 0;
                    }
                }

                //是否开启会员等级时间段清零
                if (config('shop.open_user_rank_set')) {
                    $clert_time = (config('shop.clear_rank_point') <= 0) ? 365 : intval(config('shop.clear_rank_point'));
                    $clear_start_time = TimeRepository::timePeriod(9, '-', $clert_time);

                    //规定时间范围内的rank_points之和
                    $rank_points = AccountLog::where('user_id', $user_id)->where('change_time', '>=', $clear_start_time)->sum('rank_points');
                    if ($row['user_rank'] == 0) {
                        //1.4.3 会员等级修改（成长值只有下限）
                        $rank_row = $this->userRankService->getUserRankByPoint($rank_points);

                        if ($rank_row) {
                            $userRank = [
                                'user_rank' => $rank_row['rank_id'],
                                'discount' => $rank_row['discount'] / 100.00
                            ];
                        } else {
                            $userRank = [
                                'user_rank' => 0,
                                'discount' => 1
                            ];
                        }

                        if ($type === 'pc') {
                            session($userRank);
                        }
                    } else {
                        // 特殊等级
                        $rank_row = UserRank::select('rank_id', 'discount')->where('rank_id', $row['user_rank']);
                        $rank_row = BaseRepository::getToArrayFirst($rank_row);

                        if ($rank_row) {
                            $userRank = [
                                'user_rank' => $rank_row['rank_id'],
                                'discount' => $rank_row['discount'] / 100.00
                            ];
                        } else {
                            //1.4.3 会员等级修改（成长值只有下限）
                            $rank_row = $this->userRankService->getUserRankByPoint($rank_points);

                            if ($rank_row) {
                                $userRank = [
                                    'user_rank' => $rank_row['rank_id'],
                                    'discount' => $rank_row['discount'] / 100.00
                                ];
                            } else {
                                $userRank = [
                                    'user_rank' => 0,
                                    'discount' => 1
                                ];
                            }
                        }

                        if ($type === 'pc') {
                            session($userRank);
                        }
                    }
                } else {
                    /* 取得用户等级和折扣 */
                    if ($row['user_rank'] == 0) {
                        // 非特殊等级，根据成长值计算用户等级（注意：不包括特殊等级）
                        //1.4.3 会员等级修改（成长值只有下限）
                        $rank_row = $this->userRankService->getUserRankByPoint(intval($row['rank_points']));

                        if ($rank_row) {
                            $userRank = [
                                'user_rank' => $rank_row['rank_id'],
                                'discount' => $rank_row['discount'] / 100.00
                            ];
                        } else {
                            $userRank = [
                                'user_rank' => 0,
                                'discount' => 1
                            ];
                        }

                        if ($type === 'pc') {
                            session($userRank);
                        }
                    } else {
                        // 特殊等级
                        $rank_row = UserRank::select('rank_id', 'discount')->where('rank_id', $row['user_rank']);
                        $rank_row = BaseRepository::getToArrayFirst($rank_row);

                        if ($rank_row) {
                            $userRank = [
                                'user_rank' => $rank_row['rank_id'],
                                'discount' => $rank_row['discount'] / 100.00
                            ];
                        } else {
                            //1.4.3 会员等级修改（成长值只有下限）
                            $rank_row = $this->userRankService->getUserRankByPoint(intval($row['rank_points']));

                            if ($rank_row) {
                                $userRank = [
                                    'user_rank' => $rank_row['rank_id'],
                                    'discount' => $rank_row['discount'] / 100.00
                                ];
                            } else {
                                $userRank = [
                                    'user_rank' => 0,
                                    'discount' => 1
                                ];
                            }
                        }

                        if ($type === 'pc') {
                            session($userRank);
                        }
                    }
                }
            }

            if (config('session.driver') === 'database') {
                Sessions::where('userid', $row['user_id'])->where('adminid', 0)->where('sesskey', '<>', SESS_ID)->delete();
            }

            /* 更新登录时间，登录次数及登录ip */
            $other = [
                'last_ip' => request()->getClientIp(),
                'user_rank' => $userRank['user_rank'] ?? 0,
                'last_login' => TimeRepository::getGmTime()
            ];
            Users::where('user_id', $user_id)->increment('visit_count', 1, $other);

            return $row;
        } else {
            return [];
        }
    }

    /**
     * 更新用户昵称与头像信息
     *
     * @param array $users
     */
    public function updateUsers($users = [])
    {
        $nick_name = StrRepository::filterSpecialCharacters($users['nickname'] ?? '');
        if ($users) {
            $data = [
                'nick_name' => $nick_name,
                'user_picture' => $users['headimgurl'] ?? '',
                'sex' => $users['sex'] ?? 0,
            ];
            Users::where('user_id', $users['user_id'])->update($data);
        }
    }

    /**
     * 不需要登录的操作或自己验证是否登录（如ajax处理）的act
     *
     * @param string $actionType
     * @return array
     */
    public function notLoginArr($actionType = 'default')
    {
        $list = [
            'order' => [
            ],
            'activity' => [
            ],
            'address' => [
            ],
            'baitiao' => [
            ],
            'collect' => [
                'collect'
            ],
            'crowdfund' => [
            ],
            'message' => [
            ],
            'wholesale' => [
            ],
            'default' => [
                'login', 'act_login', 'register', 'act_register', 'act_edit_password', 'get_password', 'send_pwd_email', 'get_pwd_mobile', 'password', 'signin', 'add_tag',
                'logout', 'email_list', 'validate_email', 'send_hash_mail', 'order_query', 'is_registered', 'check_email', 'clear_history',
                'qpassword_name', 'get_passwd_question', 'check_answer', 'oath', 'oath_login', 'other_login', 'is_mobile_phone', 'check_phone', 'captchas', 'phone_captcha',
                'code_notice', 'captchas_pass', 'oath_register', 'is_user', 'is_login_captcha', 'is_register_captcha', 'is_mobile_code', 'oath_remove', 'oath_weixin_login',
                'user_email_verify', 'user_email_send', 'add_value_card', 'email_send_succeed', 'pay_pwd', 'checkd_email_send_code', 'checkorder'
            ]
        ];

        return $list[$actionType];
    }

    /**
     * 显示页面的action列表
     *
     * @param string $actionType
     * @return array
     */
    public function uiArr($actionType = 'default')
    {
        $list = [
            'order' => [
                'order_list', 'order_detail', 'order_recycle', 'auction_order_detail', 'order_delete_restore', 'order_to_query', 'cancel_order', 'complaint_list',
                'complaint_apply', 'complaint_submit', 'arbitration', 'trade', 'goods_order', 'service_detail', 'apply_info', 'submit_return', 'batch_applied', 'submit_batch_return',
                'return_list', 'cancel_return', 'return_delivery', 'activation_return_order', 'return_detail', 'edit_express', 'ajax_select_cause', 'return_order_status', 'affirm_received',
                'return_to_cart', 'order_delete_return', 'apply_return'
            ],
            'activity' => [
                'snatch_list', 'snatch_to_query', 'bonus', 'auction_list', 'auction_to_query', 'coupons', 'auction', 'auction_order_recycle'
            ],
            'address' => [
                'address_list', 'address', 'ajax_del_address', 'ajax_update_address', 'ajax_add_address', 'ajax_make_address', 'act_edit_address', 'drop_consignee'
            ],
            'baitiao' => [
                'baitiao', 'baitiao_pay_log', 'repay_bt'
            ],
            'collect' => [
                'collection_list', 'store_list', 'focus_brand', 'delete_collection', 'add_to_attention', 'del_attention', 'collect'
            ],
            'crowdfund' => [
                'crowdfunding', 'delete_zc_focus'
            ],
            'message' => [
                'message_list', 'comment_list', 'commented_view', 'del_msg', 'notification'
            ],
            'wholesale' => [
                'purchase_info', 'purchase_edit', 'purchase_delete', 'wholesale_batch_applied', 'wholesale_return_list', 'wholesale_return_detail', 'wholesale_goods_order',
                'wholesale_apply_return', 'wholesale_affirm_received', 'wholesale_buy', 'wholesale_purchase', 'wholesale_return', 'delete_wholesale_order', 'wholesale_order_to_query',
                'wholesale_submit_batch_return', 'wholesale_cancel_return', 'wholesale_return_delivery', 'wholesale_activation_return_order', 'wholesale_edit_express', 'wholesale_submit_return',
                'wholesale_order_delete_return', 'purchase', 'want_buy'
            ],
            'default' => [
                'register', 'act_register', 'login', 'profile',
                'account_safe', 'account_bind',
                'tag_list', 'get_password', 'get_pwd_mobile', 'reset_password', 'booking_list', 'add_booking', 'account_raply', 'to_paid',
                'apply_suppliers',
                'account_deposit', 'account_log', 'account_detail', 'act_account', 'pay', 'default', 'value_card', 'value_card_info', 'group_buy', 'group_buy_detail', 'affiliate', 'drp_affiliate', 'sales_reward_detail', 'user_drp_order', 'user_drp_card_order',
                'validate_email', 'track_packages', 'transform_points', 'qpassword_name', 'get_passwd_question', 'check_answer', 'account_paypoints', 'account_rankpoints',
                'apply_return', 'return_shipping', 'face', 'check_comm', 'single_sun',
                'single_sun_insert', 'single_list', 'user_picture', 'vat_insert', 'vat_update', 'vat_remove', 'account_complaint', 'account_complaint_insert',
                'ajax_BatchCancelFollow', 'take_list', 'merchants_upgrade', 'grade_load', 'application_grade', 'application_grade_edit',
                'merchants_upgrade_log', 'confirm_inventory', 'update_submit', 'complaint_info', 'purchase', 'want_buy',
                'invoice', 'illegal_report', 'vat_invoice_info', 'vat_consignee', 'users_log', 'apply_delivery', 'query_express'
            ]
        ];

        return $list[$actionType];
    }

    /**
     * 过滤处理会员中心[未登录处理]
     *
     * @param int $user_id
     * @param string $action
     * @param array $not_login_arr
     * @param array $ui_arr
     * @return array
     */
    public function requireLogin($user_id = 0, $action = '', $not_login_arr = [], $ui_arr = [])
    {
        $is_back_act = 0;
        $require_login = 0;
        if (empty($user_id)) {
            if (!in_array($action, $not_login_arr)) {
                if (in_array($action, $ui_arr)) {
                    if (request()->server('REQUEST_URI')) {
                        $is_back_act = 1;
                    }
                    $action = 'login';
                } else {
                    if ($action != 'act_add_bonus') {
                        //未登录提交数据。非正常途径提交数据！
                        $require_login = 1;
                    }
                }
            }
        }

        return [
            'action' => $action,
            'is_back_act' => $is_back_act,
            'require_login' => $require_login
        ];
    }

    /**
     * 区分登录注册底部样式
     *
     * @return array
     */
    public function userFooter()
    {
        return [
            'login', 'act_login', 'register', 'act_register',
            'act_edit_password', 'get_password', 'send_pwd_email',
            'get_pwd_mobile', 'password'
        ];
    }

    /**
     * 店铺是否已审核通过
     *
     * @param int $user_id
     * @return int
     */
    public function merchantsIsApply($user_id = 0)
    {
        $is_apply = MerchantsShopInformation::where('user_id', $user_id)->where('merchants_audit', '<>', 2)->count();
        $is_apply = $is_apply ? $is_apply : 0;

        return $is_apply;
    }

    /**
     * 获取用户中心默认页面所需的数据
     *
     * @param int $user_id
     * @return array
     * @throws \Exception
     */
    public function getUserDefault($user_id = 0)
    {
        $user_bonus = $this->userBonusService->getUserBonus($user_id);

        $row = Users::select('user_name', 'pay_points', 'user_money', 'email', 'mobile_phone', 'is_validated', 'credit_line', 'nick_name', 'user_picture', 'last_login')
            ->where('user_id', $user_id);
        $row = BaseRepository::getToArrayFirst($row);

        $info = [];
        if ($row) {
            /* 会员等级 */
            $rank_info = $this->userRankService->getUsersRankInfo($user_id);
            if ($rank_info && $rank_info['rank_id']) {
                $info['rank_name'] = $rank_info['rank_name'];
                $info['special_rank'] = $rank_info['special_rank'];
                $info['rank_sort'] = $this->userRankService->getUserRankSort($rank_info['rank_id']);
            } else {
                $info['rank_name'] = lang('common.undifine_rank');
            }
            $info['username'] = $row['user_name'];
            $info['shop_name'] = config('shop.shop_name');
            $info['integral'] = $row['pay_points'];

            /* 增加是否开启会员邮件验证开关 */
            $info['is_validate'] = (config('shop.member_email_validate') && !$row['is_validated']) ? 0 : 1;
            $info['credit_line'] = $row['credit_line'];
            $info['formated_credit_line'] = $this->dscRepository->getPriceFormat($info['credit_line'], false);
            $info['nick_name'] = !empty($row['nick_name']) ? $row['nick_name'] : $info['username'];
            $info['user_picture'] = $this->dscRepository->getImagePath($row['user_picture']);
            $info['is_validated'] = $row['is_validated'];

            //如果session中时间无效说明用户是第一次登录。取当前登录时间。
            $last_time = !session()->has('last_time') ? $row['last_login'] : session('last_time');

            if ($last_time == 0) {
                $last_time = TimeRepository::getGmTime();
                session([
                    'last_time' => $last_time
                ]);
            }

            $info['last_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $last_time);
            $info['surplus'] = $this->dscRepository->getPriceFormat($row['user_money'], false);
            $info['bonus'] = sprintf(lang('user.user_bonus_info'), $user_bonus['bonus_count'], $this->dscRepository->getPriceFormat($user_bonus['bonus_value'], false));

            $info['bonus_count'] = $user_bonus['bonus_count'];
            $info['bonus_value'] = $this->dscRepository->getPriceFormat($user_bonus['bonus_value']);
            $info['pay_points'] = ($row['pay_points'] > 0) ? $row['pay_points'] : 0;

            $info['email'] = $info['is_validate'] == 1 ? $row['email'] : '';
            $info['mobile_phone'] = $row['mobile_phone'];
            $info['user_money'] = ($row['user_money'] > 0) ? $row['user_money'] : 0;
        }

        return $info;
    }

    /**
     * 第三方登录列表
     * @return array
     */
    public function getWebsiteList()
    {
        $modules = $this->dscRepository->readModules(plugin_path('Connect'));

        for ($i = 0; $i < count($modules); $i++) {
            $type = StrRepository::studly($modules[$i]['type']);

            $this->dscRepository->helpersLang($modules[$i]['type'], 'Connect/' . $type . '/Languages/' . config('shop.lang'), 1);

            $modules[$i]['name'] = $GLOBALS['_LANG'][$modules[$i]['type']];
            $modules[$i]['desc'] = $GLOBALS['_LANG'][$modules[$i]['desc']];

            /* 检查该插件是否已经安装 */
            $installed = $this->userOauthService->oauthStatus($modules[$i]['type']);

            if ($installed == true) {
                /* 插件已经安装了 */
                $modules[$i]['install'] = 1;
            } else {
                $modules[$i]['install'] = 0;
            }
        }
        return $modules;
    }

    /**
     * 获取用户绑定信息
     * @param int $user_id
     * @param string $type
     * @return array|bool
     */
    public function getConnectUser($user_id = 0, $type = '')
    {
        return $this->connectUserService->getUserInfo($user_id, $type);
    }

    /**
     * 获取用户表单个字段的值
     * @param array $where
     * @param string $field
     * @return string
     */
    public function getUserField($where = [], $field = 'id')
    {
        if (empty($where)) {
            return '';
        }
        return Users::where($where)->value($field);
    }

    /**
     * 获取当前会员成长值 进度区间
     *
     * @param int $rank_points
     * @return array
     */
    public function userRankProgress($rank_points = 0)
    {
        $min_points = UserRank::query()->where('special_rank', 0)
            ->where('min_points', '>', $rank_points)
            ->whereHasIn('userRankRights')
            ->pluck('min_points');

        $next_rank_point = 0;
        $percentage = 0;
        $progress_format = '';
        if ($min_points) {
            $next_rank_point = collect($min_points)->min();
            $next_rank_point = $next_rank_point ?? 0;

            // 百分比值 例如 50 即 50% 用于前端进度条展示
            $percentage = empty($next_rank_point) ? 100 : (round($rank_points / $next_rank_point, 2)) * 100;
            $progress_format = empty($next_rank_point) ? $rank_points : $rank_points . '/' . $next_rank_point;
        }

        return [
            'next_rank_point' => $next_rank_point,
            'percentage' => $percentage,
            'progress_format' => $progress_format
        ];
    }

    /**
     * 会员登录
     *
     * @return array
     * @throws \Exception
     */
    public function actLogin()
    {
        $_POST = get_request_filter($_POST, 1);

        $username = addslashes(request()->input('username', ''));

        $password = addslashes(request()->input('pwcode', ''));
        $password = base64_decode($password);

        $back_act = addslashes(request()->input('back_act', ''));
        $back_act = str_replace(["|", "&amp;"], '&', $back_act);

        $is_jsonp = $this->dscRepository->isJsonp($back_act, '&');

        if ($is_jsonp == 1 && strpos($back_act, 'merchants_store') !== false) {
            $seller_id = $this->dscRepository->isJsonp($back_act, '&', 'merchant_id');
            $back_act = $this->dscRepository->sellerUrl(intval($seller_id));
        } elseif (strpos($back_act, 'is_jsonp=') !== false) {
            $back_act = str_replace(["/&is_jsonp=1", "/&is_jsonp=0"], '/', $back_act);
        }

        $result = ['error' => 0, 'message' => '', 'url' => ''];

        $captcha = intval(config('shop.captcha'));

        if (($captcha & CAPTCHA_LOGIN) && gd_version() > 0) {
            if ((!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && session('login_fail') > 2))) {
                $post_captcha = trim(request()->input('captcha', ''));
                if (empty($post_captcha)) {
                    $result['error'] = 1;
                    $result['message'] = lang('common.invalid_captcha');
                }

                $verify = app(\App\Libraries\CaptchaVerify::class);
                $captcha_code = $verify->check($post_captcha, 'captcha_login');

                if (!$captcha_code) {
                    $result['error'] = 1;
                    $result['message'] = lang('common.invalid_captcha');
                }
            }
            if ((!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && session('login_fail') >= 2))) {
                //验证码
                $GLOBALS['smarty']->assign('enabled_captcha', 1);
                $GLOBALS['smarty']->assign('rand', mt_rand());
                $result['captcha'] = $GLOBALS['smarty']->fetch('library/captcha.lbi');
            }
        }

        if ($result['error'] == 0) {
            $user = $this->getUserByName($username);

            if (is_null($user)) {
                $result['error'] = 1;
                $result['message'] = lang('user.user_not_exist');
                return $result;
            }

            // 登录锁定1: 判断登录失败次数超过限制则返回true 触发锁定操作, 返回false 不会触发
            request()->offsetSet('user_id', $user->user_id);
            if ($this->hasTooManyLoginAttempts(request())) {
                $respond = $this->sendLockoutResponse(request());

                $result['error'] = 1;
                $result['message'] = $respond;
                return $result;
            }

            if ($GLOBALS['user']->login($username, $password, false)) {

                $user_id = session('user_id');

                // 登录锁定3: 在用户通过身份验证后 清除计数器
                $this->clearLoginAttempts(request());

                $this->updateUserInfo();
                app(CartCommonService::class)->recalculatePriceCart();

                $info = Users::select('nick_name', 'is_validated', 'password')->where('user_id', $user_id)->first();
                $info = $info ? $info->toArray() : [];

                if (empty($info['nick_name'])) {
                    $nick_name = rand(1, 99999999) . "-" . rand(1, 999999);
                    $update_data['nick_name'] = $nick_name;

                    Users::where('user_id', $user_id)->update($update_data);
                }

                // 兼容原md5密码
                if (isset($info['password']) && strlen($info['password']) == 32) {
                    // 通过验证 生成hash密码
                    $GLOBALS['user']->edit_user([
                        'user_id' => $user_id,
                        'username' => $username,
                        'password' => $password
                    ]);
                }

                $ucdata = isset($GLOBALS['user']->ucdata) ? $GLOBALS['user']->ucdata : '';

                //by wang
                $back_act = !empty($back_act) ? $back_act : 'index.php';
                $result['url'] = $back_act;
                $result['ucdata'] = $ucdata;
                if (config('shop.user_login_register') == 1) {
                    $result['is_validated'] = $info['is_validated']; //验证邮箱
                } else {
                    $result['is_validated'] = 1; //验证邮箱
                    $result['url'] = 'user.php';
                }

                //记录会员操作日志
                $this->usersLogChange($user_id, USER_LOGIN);
            } else {
                session()->increment('login_fail');
                $result['error'] = 1;
                $result['message'] = lang('user.login_failure');

                // 登录锁定2: 如果登录尝试不成功，增加尝试次数
                $respond = $this->incrementLoginAttempts(request());
                if ($respond) {
                    $result['message'] = $respond;
                }
            }
        }

        return $result;
    }

    /**
     * 记录会员操作日志
     *
     * @param int $user_id 用户id
     * @param int $change_type 变动类型：参见常量文件
     * @param string $from 来源：PC|手机
     */
    public function usersLogChange($user_id = 0, $change_type = USER_LOGIN, $from = PC_USER)
    {
        $ip = $this->dscRepository->dscIp();
        $areaService = app(AreaService::class);
        $ipAreaName = $areaService->ipAreaName($ip);
        $ipAreaName = $ipAreaName ? implode(', ', $ipAreaName) : '-';

        $admin_id = 0;
        if (session()->exists('admin_id') && session('admin_id') > 0) {
            $admin_id = session('admin_id');
        }

        /* 插入操作记录 */
        $users_log = [
            'user_id' => $user_id,
            'change_time' => TimeRepository::getGmTime(),
            'change_type' => $change_type,
            'ip_address' => $ip,
            'change_city' => $ipAreaName,
            'admin_id' => $admin_id,
            'logon_service' => $from
        ];

        UsersLog::insert($users_log);
    }

    /**
     * 判断管理员修改内容，划分修改类
     *
     * @param array $old_user
     * @param array $other
     * @param int $user_id
     */
    public function usersLogChangeType($old_user = [], $other = [], $user_id = 0)
    {
        //修改邮箱
        if ($old_user['old_email'] != $other['email']) {
            $this->usersLogChange($user_id, USER_EMAIL);
        }
        //修改信用额度
        if ($old_user['old_credit_line'] != $other['credit_line']) {
            $this->usersLogChange($user_id, USER_LINE);
        }
        //修改密码
        if ($old_user['password']) {
            $this->usersLogChange($user_id, USER_LPASS);
        }
        //修改手机
        if (!empty($old_user['old_mobile_phone']) && $old_user['old_mobile_phone'] != $other['mobile_phone']) {
            $this->usersLogChange($user_id, USER_PHONE);
        }
        //其他会员信息
        if ($old_user['old_user_rank'] != $other['user_rank'] || $old_user['old_sex'] != $other['sex']
            || $old_user['old_birthday'] != $other['birthday'] || $old_user['old_msn'] != $other['msn']
            || $old_user['old_qq'] != $other['qq'] || $old_user['old_office_phone'] != $other['office_phone']
            || $old_user['old_home_phone'] != $other['home_phone'] || $old_user['old_passwd_answer'] != $other['passwd_answer']
            || (isset($old_user['old_sel_question']) && isset($other['sel_question']) && $old_user['old_sel_question'] != $other['sel_question'])
        ) {
            $this->usersLogChange($user_id, USER_INFO);
        }
    }
}

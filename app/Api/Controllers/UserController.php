<?php

namespace App\Api\Controllers;

use App\Api\Foundation\Controllers\Controller;
use App\Api\Transformers\UserTransformer;
use App\Models\AffiliateLog;
use App\Models\UserOrderNum;
use App\Models\Users;
use App\Models\UsersReal;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Rules\PasswordRule;
use App\Rules\PhoneNumber;
use App\Services\Article\ArticleCommonService;
use App\Services\Navigator\TouchNavService;
use App\Services\User\UserAffiliateService;
use App\Services\User\UserCommonService;
use App\Services\User\UserRankService;
use App\Services\User\UserService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Class UserController
 * @package App\Api\Controllers
 */
class UserController extends Controller
{
    protected $userCommonService;
    protected $userTransformer;
    protected $dscRepository;
    protected $userService;

    public function __construct(
        UserCommonService $userCommonService,
        UserTransformer $userTransformer,
        DscRepository $dscRepository,
        UserService $userService
    )
    {
        $this->userCommonService = $userCommonService;
        $this->userTransformer = $userTransformer;
        $this->dscRepository = $dscRepository;
        $this->userService = $userService;
    }

    /**
     * 返回用户资料
     *
     * @param Request $request
     * @param UserAffiliateService $userAffiliateService
     * @return JsonResponse
     * @throws Exception
     */
    public function profile(Request $request, UserAffiliateService $userAffiliateService)
    {
        $time = TimeRepository::getGmTime();

        $user_id = $this->uid;

        $user = Users::query()->where('user_id', $user_id);

        // 用户不存在返回
        if (!$user->count()) {
            return $this->setErrorCode(102)->failed(lang('user.user_not_exist'));
        }

        $user = $user->with([
            'getUserBonusList' => function ($query) use ($time) {
                $query->select('user_id', 'bonus_id')
                    ->where('bonus_type_id', '>', 0)
                    ->where('used_time', '=', 0)
                    ->whereHasIn('getBonusType', function ($query) use ($time) {
                        $query->where('use_start_date', '<', $time)->where('use_end_date', '>', $time);
                    });
            },
            'getCouponsUserList' => function ($query) use ($time) {
                $query->select('user_id', 'uc_id')
                    ->where('cou_id', '>', 0)
                    ->where('is_use', '=', 0)
                    ->where('is_use_time', '=', 0)
                    ->whereHasIn('getCoupons', function ($query) use ($time) {
                        $query->whereRaw("IF(valid_type > 1, receive_start_time <= '$time' and receive_end_time >= '$time', cou_start_time <= '$time' and cou_end_time >= '$time')")
                            ->where('cou_type', '<>', VOUCHER_GROUPBUY)
                            ->where('status', COUPON_STATUS_EFFECTIVE);
                    });
            },
            'getValueCard' => function ($query) use ($time) {
                $query->select('user_id', 'vid')
                    ->where('bind_time', '>', 0)
                    ->where('end_time', '>', $time);
            }
        ]);

        $user = BaseRepository::getToArrayFirst($user);

        // 统计订单数量
        $orderNum = UserOrderNum::where('user_id', $user_id)->first();
        $orderNum = $orderNum ? $orderNum->toArray() : [];

        $orderCount = [
            'all' => $orderNum['order_all_num'] ?? 0, //订单数量
            'nopay' => $orderNum['order_nopay'] ?? 0, //待付款订单数量
            'nogoods' => $orderNum['order_nogoods'] ?? 0, //待收货订单数量
            'isfinished' => $orderNum['order_isfinished'] ?? 0, //已完成订单数量
            'isdelete' => $orderNum['order_isdelete'] ?? 0, //回收站订单数量
            'team_num' => $orderNum['order_team_num'] ?? 0, //拼团订单数量
            'not_comment' => $orderNum['order_not_comment'] ?? 0,  //待评价订单数量
            'return_count' => $orderNum['order_return_count'] ?? 0 //待同意状态退换货申请数量
        ];

        // 会员等级
        $user_rank = $this->userCommonService->getUserRankByUid($user_id);
        $user['user_rank'] = $user_rank['rank_name'];

        $res = $this->userTransformer->transform(array_merge($user, $orderCount));
        $res['avatar'] = $this->dscRepository->getImagePath($res['avatar']);
        $res['use_value_card'] = config('shop.use_value_card') ?? '';

        // 返回是否实名认证
        $res['user_real'] = UsersReal::where('user_id', $user_id)
            ->where('user_type', 0)
            ->count();

        //微信浏览器判断
        $res['is_wechat_browser'] = is_wechat_browser() ? 1 : 0;

        //是否显示我的微店
        $res['is_drp'] = file_exists(MOBILE_DRP) ? 1 : 0;
        $res['drp_shop'] = 0;
        if (file_exists(MOBILE_DRP)) {
            $distributeService = app(\App\Modules\Drp\Services\Distribute\DistributeService::class);
            // 是否已申请分销商
            $drp_shop = $distributeService->drp_shop_info($user_id, ['audit', 'status', 'apply_channel', 'membership_card_id', 'membership_status']);
            $res['drp_shop'] = empty($drp_shop) ? 0 : $drp_shop;

            if (!empty($drp_shop)) {
                // VIP会员 我的推广
                $res['drp_affiliate'] = [
                    'user_child_num' => $userAffiliateService->getUserChildNum($user_id), // 下级会员数量（含普通会员+高级会员）
                    'register_affiliate_money' => 0, // 分销商推荐注册奖励 TODO
                    'total_drp_log_money' => $distributeService->get_drp_money(3, $user_id), // 累计销售佣金（购买商品、购买会员卡）奖励
                ];
            }
        }

        // 普通会员 我的推广
        $res['user_affiliate'] = [
            'user_child_num' => $userAffiliateService->getUserChildNum($user_id), // 下线会员
            'register_affiliate_money' => $userAffiliateService->getUserParentAffiliate($user_id), //注册分成奖励
            'order_affiliate_money' => $userAffiliateService->getUserOrderAffiliate($user_id), //订单分成奖励
        ];

        // 仅普通会员显示 成长值区间
        $res['user_rank_progress'] = [];
        if (isset($res['drp_shop']) && $res['drp_shop'] == 0) {
            $res['user_rank_progress'] = $this->userCommonService->userRankProgress($user['rank_points']);
        }

        //是否显示待拼团
        $res['is_team'] = file_exists(MOBILE_TEAM) ? 1 : 0;

        //是否显示我的砍价
        $res['is_bargain'] = file_exists(MOBILE_BARGAIN) ? 1 : 0;

        // 是否显示供应链
        if (file_exists(SUPPLIERS) && config('shop.wholesale_user_rank') != 0) {
            $res['is_suppliers'] = 1;
        } else {
            $res['is_suppliers'] = 0;
        }

        //是否显示推荐分成
        $affiliate = config('shop.affiliate') ?? '';
        $share = empty($affiliate) ? '' : unserialize($affiliate);
        $res['is_share'] = ($share && $share['on'] == 1) ? 1 : 0;

        // 整站可评论 则展示待评论
        $res['shop_can_comment'] = config('shop.shop_can_comment') == 1 ? 1 : 0;

        // 收藏
        $res['collect_goods_num'] = $this->userService->collectGoodsNum($user_id);
        // 足迹
        $res['history_goods_num'] = $this->userService->historyGoodsNum($user_id);
        // 店铺
        $res['collect_store_num'] = $this->userService->collectStoreNum($user_id);

        return $this->succeed($res);
    }

    /**
     * 返回用户脱敏数据
     * @param Request $request
     * @return JsonResponse
     */
    public function basicProfileByMobile(Request $request)
    {
        $name = $request->get('name');

        $user = $this->userCommonService->getUserByName($name);

        if (is_null($user)) {
            return $this->setErrorCode(102)->failed(lang('user.user_not_exist'));
        }

        $user = $this->userTransformer->transform($user);

        $user['avatar'] = $user['avatar'] ? $user['avatar'] : asset('img/user_default.png');

        $res = [
            'username' => $user['username'],
            'avatar' => $this->dscRepository->getImagePath($user['avatar'])
        ];

        return $this->succeed($res);
    }

    /**
     * 保存用户资料
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function update(Request $request)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            'name' => 'filled|string|min:2|max:20',
            'sex' => 'filled|integer',
            'birthday' => 'filled|string',
        ]);

        // 返回错误
        if ($validator->fails()) {
            return $this->setErrorCode(422)->failed($validator->errors()->first());
        }

        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $user = Users::find($user_id);

        $nick_name = $request->get('name', '');
        if ($nick_name) {
            $user->nick_name = $nick_name;
        }

        $sex = $request->get('sex', 0);
        if ($sex) {
            $user->sex = $sex;
        }

        $birthday = $request->get('birthday', '');
        if ($birthday) {
            $user->birthday = $birthday;
        }

        $result = $user->save();

        if ($result) {
            // 记录操作日志
            $this->userCommonService->usersLogChange($user_id, USER_INFO, MOBILE_USER);
        }

        $res = $this->userTransformer->transform($user);

        return $this->succeed($res);
    }

    /**
     * 设置头像
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function avatar(Request $request)
    {
        $this->validate($request, [
            'pic' => 'required|string',
        ]);

        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $user_picture = $request->get('pic', '');

        $user = Users::find($user_id);

        if ($user_picture) {
            $file_arr = $this->dscRepository->transformOssFile(['user_picture' => $user_picture]);
            $user->user_picture = $file_arr['user_picture'];
            $result = $user->save();
            if ($result) {
                $this->userCommonService->usersLogChange($user_id, USER_PICT, MOBILE_USER);
            }
        }

        $res = $this->userTransformer->transform($user);
        $res['avatar'] = $this->dscRepository->getImagePath($res['avatar']);
        $res['user_picture'] = $this->dscRepository->getImagePath($res['user_picture']);

        return $this->succeed($res);
    }

    /**
     * 返回ECJia Hash
     * @return JsonResponse
     * @throws Exception
     */
    public function ecjiaHash()
    {
        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $user = Users::where('user_id', $user_id);
        $user = BaseRepository::getToArrayFirst($user);

        if (empty($user)) {
            return $this->setErrorCode(102)->failed(lang('user.user_not_exist'));
        }

        $res = $this->userCommonService->ecjiaHash($user);

        return $this->succeed($res);
    }

    /**
     * 帮助中心
     * @param Request $request
     * @param ArticleCommonService $articleCommonService
     * @return JsonResponse
     * @throws Exception
     */
    public function help(Request $request, ArticleCommonService $articleCommonService)
    {
        $user_id = $this->authorization();

        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $res = $articleCommonService->helpinfo();

        return $this->succeed($res);
    }

    /**
     * 返回会员id 手机号
     *
     * @return JsonResponse
     * @throws Exception
     */
    public function getUserId()
    {
        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        // 已登录且存在返回会员id,手机号
        $user = Users::select('user_id', 'mobile_phone')->where('user_id', $user_id)->first();
        if (!empty($user)) {
            $mobile = $user->mobile_phone ?? '';
            return $this->succeed(['id' => $user_id, 'mobile_phone' => $mobile]);
        }

        return $this->setErrorCode(102)->failed(lang('user.user_not_exist'));
    }

    /**
     * 推广中心
     *
     * @param Request $request
     * @param UserRankService $userRankService
     * @param UserAffiliateService $userAffiliateService
     * @return JsonResponse
     * @throws Exception
     */
    public function affiliateInfo(Request $request, UserRankService $userRankService, UserAffiliateService $userAffiliateService)
    {
        $user_id = $this->authorization();

        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $user = Users::query()->where('user_id', $user_id);

        // 用户不存在返回
        if (!$user->count()) {
            return $this->setErrorCode(102)->failed(lang('user.user_not_exist'));
        }

        $user = BaseRepository::getToArrayFirst($user);

        $res = [];
        if (!empty($user)) {
            $res = $this->userTransformer->transformInfo($user);

            $res['avatar'] = $this->dscRepository->getImagePath($res['avatar']);

            // 会员等级
            $user_rank = $this->userCommonService->getUserRankByUid($user_id);
            $res['user_rank_name'] = $user_rank['rank_name'];

            // 会员等级权益列表
            $res['user_rank_rights_list'] = $userRankService->rankRightsList($user_rank['rank_id']);

            // 我的资产
            $res['user_money'] = $this->dscRepository->getPriceFormat($res['user_money'], true, false); // 当前会员总余额
            $res['user_total_affiliate_money'] = $userAffiliateService->getUserTotalAffiliate($user_id);//注册分成奖励 + 订单分成奖励
            $res['user_today_affiliate_money'] = $userAffiliateService->getUserTotalAffiliate($user_id, 'today'); // 今日总分成奖励
            $res['user_total_order_amount'] = $userAffiliateService->getUserTotalOrderAmount($user_id); // 总分成销售额
        }

        $res['is_drp'] = file_exists(MOBILE_DRP) ? 1 : 0;

        return $this->succeed($res);
    }

    /**
     * 会员等级权益列表
     *
     * @param Request $request
     * @param UserRankService $userRankService
     * @return JsonResponse
     */
    public function rankRightsList(Request $request, UserRankService $userRankService)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            'rank_id' => 'required|integer',
        ]);

        // 返回错误
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return $this->setErrorCode(422)->failed($error);
        }

        //返回用户ID
        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $rank_id = $request->input('rank_id', 0); // 会员等级id

        $result['list'] = $userRankService->rankRightsList($rank_id);

        return $this->succeed($result);
    }

    /**
     * 下级会员列表
     *
     * @param Request $request
     * @param UserAffiliateService $userAffiliateService
     * @return JsonResponse
     */
    public function childList(Request $request, UserAffiliateService $userAffiliateService)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            'page' => 'filled|integer',
            'size' => 'filled|integer'
        ]);

        // 返回错误
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return $this->setErrorCode(422)->failed($error);
        }

        $page = $request->get('page', 1);
        $size = $request->get('size', 10);

        $user_id = $this->authorization();

        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $list = $userAffiliateService->userChildList($user_id, $page, $size);

        if ($list) {
            foreach ($list as $key => $value) {
                $list[$key]['user_id'] = $value['user_id'];
                $list[$key]['reg_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $value['reg_time']);
                //会员信息
                $list[$key]['user_name'] = !empty($value['nick_name']) ? $value['nick_name'] : $value['user_name'];
                $list[$key]['user_picture'] = $this->dscRepository->getImagePath($value['user_picture']);
            }
        }

        return $this->succeed($list);
    }

    /**
     * 注册分成、订单分成奖励列表
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function affiliateList(Request $request)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            'page' => 'filled|integer',
            'size' => 'filled|integer'
        ]);

        // 返回错误
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return $this->setErrorCode(422)->failed($error);
        }

        $page = $request->get('page', 1);
        $size = $request->get('size', 10);

        $user_id = $this->authorization();

        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $affiliate = config('shop.affiliate') ? unserialize(config('shop.affiliate')) : [];

        if (empty($affiliate['config']['separate_by'])) {
            //推荐注册分成
            $affdb = [];
            $num = count($affiliate['item']);
            $up_uid = $user_id;
            $all_uid = $user_id;
            for ($i = 1; $i <= $num; $i++) {
                $count = 0;
                if ($up_uid) {
                    $up_uid_str = !is_array($up_uid) ? explode(",", $up_uid) : $up_uid;
                    $res = Users::whereIn('parent_id', $up_uid_str);
                    $res = $res->select('user_id')->get();

                    $res = $res ? $res->toArray() : [];

                    $up_uid = '';

                    foreach ($res as $rt) {
                        $up_uid .= $up_uid ? ",$rt[user_id]" : "$rt[user_id]";
                        if ($i < $num) {
                            $all_uid .= ",$rt[user_id]";
                        }
                        $count++;
                    }
                }
                $affdb[$i]['num'] = $count;
                $affdb[$i]['point'] = $affiliate['item'][$i - 1]['level_point'];
                $affdb[$i]['money'] = $affiliate['item'][$i - 1]['level_money'];
            }

            $where = [
                'all_uid' => $all_uid,
                'user_id' => $user_id
            ];

            $result = $this->userService->getUserParentOrderAffiliateList($where, $page, $size);

            $res = $result['res'];
        } else {
            //推荐订单分成
            $where = [
                'all_uid' => $user_id,
                'user_id' => $user_id
            ];
            $result = $this->userService->getUserOrderAffiliateList($where, $page, $size);

            $res = $result['res'] ?? [];
        }

        $list = [];
        if ($res) {
            foreach ($res as $rt) {
                $rt['up'] = Users::where('user_id', $rt['user_id'])->value('parent_id');

                $log = AffiliateLog::select('log_id', 'user_id as suid', 'user_name as auser', 'money', 'point', 'separate_type', 'time')
                    ->where('order_id', $rt['order_id'])->first();
                $log = $log ? $log->toArray() : [];

                $rt = $log ? array_merge($rt, $log) : $rt;

                if (!empty($rt['suid'])) {
                    //在affiliate_log有记录
                    if ($rt['separate_type'] == -1 || $rt['separate_type'] == -2) {
                        //已被撤销
                        $rt['is_separate'] = 3;
                    }
                }

                // 推荐下单会员
                $arr['user_name'] = !empty($rt['get_users']['nick_name']) ? $rt['get_users']['nick_name'] : ($rt['get_users']['user_name'] ?? '');
                $arr['user_picture'] = $rt['get_users']['user_picture'] ?? '';
                $arr['user_picture'] = $this->dscRepository->getImagePath($arr['user_picture']);
                $arr['reg_time'] = !empty($rt['get_users']['reg_time']) ? TimeRepository::getLocalDate(config('shop.time_format'), $rt['get_users']['reg_time']) : '';

                // 订单
                $arr['order_sn'] = substr($rt['order_sn'], 0, strlen($rt['order_sn']) - 5) . "***" . substr($rt['order_sn'], -2, 2);
                $arr['order_id'] = $rt['order_id'] ?? 0;
                $arr['is_separate'] = $rt['is_separate'] ?? 0; // 是否已分成
                $arr['separate_type'] = $log['separate_type'] ?? 0; // 注册分成 0、订单分成 1
                $arr['time'] = !empty($log['time']) ? TimeRepository::getLocalDate(config('shop.time_format'), $log['time']) : '';
                $arr['money'] = !empty($log['money']) ? $this->dscRepository->getPriceFormat($log['money']) : 0;
                $list[] = $arr;
            }
        }

        return $this->succeed($list);
    }

    /**
     * 操作日志
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function userLog(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page' => 'filled|integer',
            'size' => 'filled|integer'
        ]);

        // 返回错误
        if ($validator->fails()) {
            $error = $validator->errors()->first();
            return $this->setErrorCode(422)->failed($error);
        }

        $user_id = $this->authorization();
        if (!$user_id) {
            return $this->setErrorCode(12)->failed(lang('user.not_login'));
        }

        $page = $request->get('page', 1);
        $size = $request->get('size', 10);

        $list = $this->userService->getUserLog($user_id, $page, $size);

        return $this->succeed($list);
    }

    /**
     * 自定义工具栏
     * @param TouchNavService $touchNavService
     * @param Request $request
     * @return JsonResponse
     */
    public function touch_nav(Request $request, TouchNavService $touchNavService)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            'device' => ['required', 'string', Rule::in(['h5', 'wxapp', 'app'])],
        ]);

        // 返回错误
        if ($validator->fails()) {
            return $this->setErrorCode(422)->failed($validator->errors()->first());
        }

        $device = $request->input('device', 'h5'); // 客户端：h5 wxapp app
        $page_flag = $request->input('page_flag', 'user'); // 页面标识：user 会员中心
        $top_id = $request->input('top_id', 0); // 工具栏分类id

        $list = $touchNavService->getTouchNav($device, $page_flag, $top_id);

        return $this->succeed($list);
    }

    /**
     * 找回密码
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function forget(Request $request)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            'captcha' => 'required|string',
            'user_name' => 'required|string',
        ]);

        // 返回错误
        if ($validator->fails()) {
            return $this->setErrorCode(422)->failed($validator->errors()->first());
        }

        $captcha = $request->get('captcha', '');
        $username = $request->get('user_name', '');
        $client_id = $request->get('client');

        // 校验图片验证码
        if (Cache::get($client_id) != $captcha) {
            return $this->setErrorCode(422)->failed(lang('user.bind_captcha_error'));
        }

        $user = $this->userCommonService->getUserByName($username);
        if (empty($user)) {
            return $this->setErrorCode(102)->failed(lang('user.user_not_exist'));
        }

        $user = collect($user)->toArray();

        $res = [
            'user_name' => $user['user_name'],
            'user_name_sign' => CommonRepository::getMatchPhone($user['user_name'])
                ? app(DscRepository::class)->stringToStar($user['user_name'], 3, 4) : $user['user_name'],
            'mobile_phone' => $user['mobile_phone'],
            'is_mobile_phone' => CommonRepository::getMatchPhone($username) || CommonRepository::getMatchPhone($user['mobile_phone']) ? 1 : 0,
            'is_email' => CommonRepository::getMatchEmail($user['email']) ? 1 : 0,
            'mobile_phone_sign' => app(DscRepository::class)->stringToStar($user['mobile_phone'], 3, 4),
            'email' => $user['email'],
            'email_sign' => app(DscRepository::class)->stringToStar($user['email'], 4, 4),
        ];

        return $this->succeed($res);
    }

    /**
     * 重置密码发送邮件
     * @param Request $request
     * @return JsonResponse
     */
    public function resetEmail(Request $request)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            'email' => 'required|string'
        ]);

        // 返回错误
        if ($validator->fails()) {
            return $this->failed($validator->errors()->first());
        }

        $email = $request->get('email', '');

        $res = $this->userService->resetEmail($email);

        if ($res['error_code'] > 0) {
            return $this->failed($res['msg']);
        }

        return $this->succeed($res);
    }

    /**
     * 验证邮箱发送的验证码
     * @param Request $request
     * @return JsonResponse
     */
    public function verificationEmail(Request $request)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'code' => 'required|string'
        ]);

        // 返回错误
        if ($validator->fails()) {
            return $this->failed($validator->errors()->first());
        }
        $email = $request->get('email', '');
        $code = $request->get('code', '');
        $res = $this->userService->verificationEmail($email, $code);

        if ($res['error_code'] > 0) {
            return $this->failed($res['msg']);
        }

        return $this->succeed($res);
    }

    /**
     * 重置用户密码
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function resetPassword(Request $request)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            'user_name' => 'required|string',
            'new_password' => ['required', 'different:user_name', new PasswordRule()], // 密码
        ], [
            'new_password.required' => lang('user.user_pass_empty'),
            'new_password.different' => lang('user.user_pass_same')
        ]);

        // 返回错误
        if ($validator->fails()) {
            return $this->failed($validator->errors()->first());
        }

        $user_name = $request->get('user_name', '');
        $new_password = $request->get('new_password', '');

        // 查找用户信息
        $user = $this->userCommonService->getUserByName($user_name);
        if (is_null($user)) {
            return $this->setErrorCode(102)->failed(lang('user.user_not_exist'));
        }

        // 重置用户新密码
        $GLOBALS['user']->edit_user([
            'user_id' => collect($user)->get('user_id'),
            'username' => collect($user)->get('user_name'),
            'password' => $new_password
        ], 1);

        return $this->succeed(['error_code' => 0, 'msg' => 'success']);
    }

    /**
     * 验证短信
     * @param Request $request
     * @return JsonResponse
     */
    public function verificationSms(Request $request)
    {
        // 数据验证
        $validator = Validator::make($request->all(), [
            'mobile_phone' => ['required', new PhoneNumber()],
            'code' => 'required|string'
        ]);

        // 返回错误
        if ($validator->fails()) {
            return $this->failed($validator->errors()->first());
        }

        $mobile_phone = $request->get('mobile_phone', '');
        $code = $request->get('code', '');

        $res = $this->userService->verificationSms($mobile_phone, $code);

        if ($res['error_code'] > 0) {
            return $this->failed($res['msg']);
        }

        return $this->succeed($res);
    }

}

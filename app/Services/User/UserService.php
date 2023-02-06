<?php

namespace App\Services\User;

use App\Libraries\Image;
use App\Libraries\Pager;
use App\Libraries\Template;
use App\Models\AccountLog;
use App\Models\Article;
use App\Models\Category;
use App\Models\CollectGoods;
use App\Models\CollectStore;
use App\Models\ComplaintImg;
use App\Models\ComplaintTalk;
use App\Models\CouponsUser;
use App\Models\GoodsHistory;
use App\Models\GoodsReport;
use App\Models\GoodsReportImg;
use App\Models\GoodsReportTitle;
use App\Models\GoodsReportType;
use App\Models\MerchantsStepsFields;
use App\Models\OrderInfo;
use App\Models\Payment;
use App\Models\PresaleActivity;
use App\Models\RegFields;
use App\Models\Region;
use App\Models\SeckillGoods;
use App\Models\SellerShopinfo;
use App\Models\UserAccount;
use App\Models\UserAddress;
use App\Models\UserBonus;
use App\Models\Users;
use App\Models\UsersLog;
use App\Models\UsersPaypwd;
use App\Models\UsersReal;
use App\Models\UsersVatInvoicesInfo;
use App\Models\ValueCard;
use App\Models\ValueCardType;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Activity\BonusService;
use App\Services\Bonus\BonusDataHandleService;
use App\Services\Coupon\CouponDataHandleService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Order\OrderDataHandleService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * 商城会员
 * Class UserService
 * @package App\Services\User
 */
class UserService
{
    protected $dscRepository;
    protected $image;
    protected $merchantCommonService;
    protected $template;

    public function __construct(
        DscRepository $dscRepository,
        Image $image,
        MerchantCommonService $merchantCommonService,
        Template $template
    )
    {
        $this->dscRepository = $dscRepository;
        $this->image = $image;
        $this->merchantCommonService = $merchantCommonService;
        $this->template = $template;
    }

    /**
     * 取得用户信息
     *
     * @param array $where
     * @return array
     */
    public function userInfo($where = [])
    {
        $user = Users::whereRaw(1);

        if (isset($where['user_id'])) {
            $user = $user->where('user_id', $where['user_id']);
        }

        $user = $user->first();

        $user = $user ? $user->toArray() : [];

        /* 格式化帐户余额 */
        if ($user) {
            unset($user['question']);
            unset($user['answer']);

            if (config('shop.show_mobile') == 0) {
                $user['mobile_phone'] = $this->dscRepository->stringToStar($user['mobile_phone']);
                $user['user_name'] = $this->dscRepository->stringToStar($user['user_name']);
                $user['email'] = $this->dscRepository->stringToStar($user['email']);
            }

            $user['formated_user_money'] = $this->dscRepository->getPriceFormat($user['user_money'], false);
            $user['formated_frozen_money'] = $this->dscRepository->getPriceFormat($user['frozen_money'], false);
        }

        return $user;
    }

    // 判断有没有开通手机验证、邮箱验证、支付密码
    public function GetValidateInfo($user_id)
    {
        $res = Users::where('user_id', $user_id)
            ->with([
                'getUsersPaypwd' => function ($query) {
                    $query->select('user_id', 'paypwd_id', 'pay_password');
                },
                'getUsersReal' => function ($query) {
                    $query->select('user_id', 'bank_mobile', 'real_name', 'bank_card', 'bank_name', 'review_status')->where('user_type', 0);
                }
            ]);

        $res = $res->first();

        $res = $res ? $res->toArray() : [];

        $res = $res && $res['get_users_paypwd'] ? array_merge($res, $res['get_users_paypwd']) : $res;
        $res = $res && $res['get_users_real'] ? array_merge($res, $res['get_users_real']) : $res;

        return $res;
    }

    // 用户中心 安�    �评级 qin
    public function SecurityRating()
    {
        $user_id = session('user_id', 0);

        $count = 2;
        $count_info = '';
        $Percentage = 0;

        $res = Users::where('user_id', $user_id);

        $res = $res->with([
            'getUsersPaypwd' => function ($query) {
                $query->select('user_id', 'paypwd_id', 'pay_password');
            },
            'getUsersReal' => function ($query) {
                $query->select('user_id', 'real_id', 'bank_mobile', 'real_name', 'bank_card', 'bank_name', 'review_status')
                    ->where('user_type', 0);
            }
        ]);

        $res = BaseRepository::getToArrayFirst($res);

        $res = $res && $res['get_users_paypwd'] ? array_merge($res, $res['get_users_paypwd']) : $res;
        $res = $res && $res['get_users_real'] ? array_merge($res, $res['get_users_real']) : $res;

        if ($res) {
            if (isset($res['is_validated']) && $res['is_validated']) {
                // 邮箱
                $count++;
            }

            if (isset($res['mobile_phone']) && $res['mobile_phone']) {
                // 手机
                $count++;
            }

            if (isset($res['pay_password']) && $res['pay_password']) {
                // 支付密码
                $count++;
            }

            if (isset($res['real_id']) && $res['real_id']) {
                // 实名认证
                $count++;
            }

            switch ($count) {
                case 1:
                    $count_info = lang('user.Risk_rating.0');
                    $Percentage = 15;
                    break;
                case 2:
                    $count_info = lang('user.Risk_rating.1');
                    $Percentage = 30;
                    break;
                case 3:
                    $count_info = lang('user.Risk_rating.2');
                    $Percentage = 45;
                    break;
                case 4:
                    $count_info = lang('user.Risk_rating.3');
                    $Percentage = 60;
                    break;
                case 5:
                    $count_info = lang('user.Risk_rating.4');
                    $Percentage = 80;
                    break;
                case 6:
                    $count_info = lang('user.Risk_rating.5');
                    $Percentage = 100;
                    break;

                default:
                    break;
            }
        }

        return ['count' => $count, 'count_info' => $count_info, 'Percentage' => $Percentage];
    }

    /**
     * 保存申请时的上传图片
     *
     * @param array $image_files 上传图片数组
     * @param array $file_id 图片对应的id数组
     * @param array $url
     * @return array|bool
     */
    public function UploadApplyFile($image_files = [], $file_id = [], $url = [])
    {
        /* 是否成功上传 */
        foreach ($file_id as $v) {
            $flag = false;
            if (isset($image_files['error'])) {
                if ($image_files['error'][$v] == 0) {
                    $flag = true;
                }
            } else {
                if ($image_files['tmp_name'][$v] != 'none' && $image_files['tmp_name'][$v]) {
                    $flag = true;
                }
            }
            if ($flag) {
                /*生成上传信息的数组*/
                $upload = [
                    'name' => $image_files['name'][$v],
                    'type' => $image_files['type'][$v],
                    'tmp_name' => $image_files['tmp_name'][$v],
                    'size' => $image_files['size'][$v],
                ];
                if (isset($image_files['error'])) {
                    $upload['error'] = $image_files['error'][$v];
                }

                $img_original = $this->image->upload_image($upload);
                if ($img_original === false) {
                    return $this->image->error_msg();
                }
                $img_url[$v] = $img_original;
                /*删除原文件*/
                if (!empty($url[$v])) {
                    @unlink(storage_public($url[$v]));
                    unset($url[$v]);
                }
            }
        }
        $return_file = [];
        if ($url) {
            foreach ($url as $k => $v) {
                if ($v == '') {
                    unset($url[$k]);
                }
            }
        }
        if (!empty($url) && !empty($img_url)) {
            $return_file = $url + $img_url;
        } elseif (!empty($url)) {
            $return_file = $url;
        } elseif (!empty($img_url)) {
            $return_file = $img_url;
        }
        if (!empty($return_file)) {
            return $return_file;
        } else {
            return false;
        }
    }

    public function CreatePassword($pw_length = 8)
    {
        $randpwd = '';
        for ($i = 0; $i < $pw_length; $i++) {
            $randpwd .= chr(mt_rand(33, 126));
        }

        return $randpwd;
    }

    /*
    * 判断预售商品是否处在尾款结算状态 liu
    */
    public function PresaleSettleStatus($extension_id)
    {
        $time = TimeRepository::getGmTime();
        $row = PresaleActivity::where('act_id', $extension_id)
            ->where('review_status', 3)
            ->first();

        $row = $row ? $row->toArray() : [];

        $result = [];
        $result['info'] = [];
        if ($row) {
            $result['info'] = $row;

            if ($row['pay_start_time'] <= $time && $row['pay_end_time'] >= $time) {
                $result['start_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $row['pay_start_time']);
                $result['end_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $row['pay_end_time']);
                $result['settle_status'] = 1; //在支付尾款时间段内
            } elseif ($row['pay_end_time'] < $time) {
                $result['start_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $row['pay_start_time']);
                $result['end_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $row['pay_end_time']);
                $result['settle_status'] = -1; //超出支付尾款时间
            } else {
                $result['start_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $row['pay_start_time']);
                $result['end_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $row['pay_end_time']);
                $result['settle_status'] = 0; //未到付款时间
            }
        }

        return $result;
    }

    /**
     * 取得储值卡使用限制说明
     *
     * @param $vid
     * @return array
     * @throws \Exception
     */
    public function GetExplain($vid)
    {
        $rz_shopName = [];
        $arr = [];
        $row = ValueCardType::whereHasIn('getValueCard', function ($query) use ($vid) {
            $query->where('vid', $vid);
        });

        $row = BaseRepository::getToArrayFirst($row);

        if ($row['use_condition'] == 0) {
            $explain = $GLOBALS['_LANG']['all_goods'];
        } elseif ($row['use_condition'] == 1) {
            $res = [];
            if ($row['spec_cat']) {
                $spec_cat = !is_array($row['spec_cat']) ? explode(",", $row['spec_cat']) : $row['spec_cat'];
                $res = Category::whereIn('cat_id', $spec_cat)->get();
                $res = $res ? $res->toArray() : [];
            }

            $explain = str_replace('%', $this->cat_format($res), $GLOBALS['_LANG']['spec_cat_explain']);
        } elseif ($row['use_condition'] == 2) {
            $explain['explain'] = str_replace('%', $row['spec_goods'], $GLOBALS['_LANG']['spec_goods_explain']);
            $explain['goods_ids'] = $row['spec_goods'];
        } else {
            $explain = '';
        }
        $other_explain = '';
        if ($row['use_merchants'] == 'all') {
            $other_explain = ' | ' . $GLOBALS['_LANG']['all_merchants'];
        } elseif ($row['use_merchants'] == 'self') {
            $other_explain = ' | ' . $GLOBALS['_LANG']['self_merchants'];
        } elseif (!empty($row['use_merchants'])) {
            $ru_ids = explode(',', $row['use_merchants']);
            if (!empty($ru_ids)) {

                $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_ids);

                foreach ($ru_ids as $k => $v) {
                    $shop_name = [];
                    $shop_name['shop_name'] = $merchantList[$v]['shop_name'] ?? '';
                    $build_uri = [
                        'urid' => $v,
                        'append' => $shop_name['shop_name']
                    ];

                    $domain_url = $this->merchantCommonService->getSellerDomainUrl($v, $build_uri);
                    $shop_name['shop_url'] = $domain_url['domain_name'];
                    $rz_shopName[] = $shop_name;
                }
            }
            $other_explain = ' | ' . $GLOBALS['_LANG']['assign_merchants'];
        }
        $arr['rz_shopNames'] = $rz_shopName;
        if ($other_explain) {
            $arr['explain'] = $explain; //. $other_explain;
            $arr['other_explain'] = $other_explain;
        } else {
            $arr['explain'] = $explain;
        }

        return $arr;
    }

    /**
     * 获取举报列表
     *
     * @param int $num 列表最大数量
     * @param int $start 列表起始位置
     * @return array
     * @throws \Exception
     */
    public function getGoodsReportList($num = 10, $start = 0)
    {
        $user_id = session('user_id', 0);

        $row = GoodsReport::where('user_id', $user_id)
            ->where('report_state', '<', 3);

        $row = $row->orderBy('add_time', 'desc');

        if ($start > 0) {
            $row = $row->skip($start);
        }

        if ($num > 0) {
            $row = $row->take($num);
        }

        $row = BaseRepository::getToArrayGet($row);

        if ($row) {

            $goods_id = BaseRepository::getKeyPluck($row, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goods_id);

            $ru_id = BaseRepository::getKeyPluck($goodsList, 'user_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($row as $k => $v) {

                $v['get_goods'] = $goodsList[$v['goods_id']] ?? [];
                $row[$k]['get_goods'] = $v['get_goods'];

                if ($v['title_id'] > 0) {
                    $row[$k]['title_name'] = GoodsReportTitle::where('title_id', $v['title_id'])->value('title_name');
                }

                if ($v['type_id'] > 0) {
                    $row[$k]['type_name'] = GoodsReportType::where('type_id', $v['type_id'])->value('type_name');
                }
                if ($v['add_time'] > 0) {
                    $row[$k]['add_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $v['add_time']);
                }

                $row[$k]['url'] = $this->dscRepository->buildUri('goods', ['gid' => $v['goods_id']], $v['goods_name']);

                $ru_id = $v['get_goods']['user_id'] ?? 0;
                $row[$k]['shop_name'] = $merchantList[$ru_id]['shop_name'] ?? '';

                $row[$k]['goods_thumb'] = $this->dscRepository->getImagePath($v['goods_image']);
            }
        }

        return $row;
    }

    /**
     * 获取订单投诉数量
     *
     * @param int $user_id
     * @param int $dealy_time
     * @param int $is_complaint
     * @param string $keyword
     * @return mixed
     */
    public function getComplaintCount($user_id = 0, $dealy_time = 0, $is_complaint = 0, $keyword = '')
    {
        $time = TimeRepository::getGmTime();

        $record_count = OrderInfo::where('main_count', 0)
            ->where('user_id', $user_id)
            ->where('is_delete', 0);

        if (!empty($keyword)) {
            $record_count = $record_count->where('order_sn', 'like', '%' . $keyword . '%');
        }

        $record_count = $record_count->where('is_zc_order', 0);

        $record_count = $record_count->where(function ($query) use ($is_complaint) {
            $query->complaintCount($is_complaint);
        });

        if ($is_complaint == 0) {

            //获取已确认，已分单，部分分单，已付款，已发货或者已确认收货15天内的订单
            $record_count = $record_count->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART]);

            $where_confirmed = "IF(pay_status = " . PS_PAYED . ", IF(shipping_status = " . SS_RECEIVED . ", shipping_status = '" . SS_RECEIVED . "' AND ('$time'- confirm_take_time) < '$dealy_time', shipping_status <> " . SS_UNSHIPPED . ")";
            $where_confirmed .= " AND pay_status = " . PS_PAYED . ", IF(shipping_status = " . SS_RECEIVED . ", shipping_status = " . SS_RECEIVED . " AND ('$time'- confirm_take_time) < '$dealy_time', shipping_status <> " . SS_UNSHIPPED . "))";

            $record_count = $record_count->whereRaw($where_confirmed);
        }

        $record_count = $record_count->count();

        return $record_count;
    }

    /**
     * 获取订单投诉列表
     *
     * @param int $user_id
     * @param int $dealy_time
     * @param int $num
     * @param int $start
     * @param int $is_complaint
     * @param string $keyword
     * @return array
     * @throws \Exception
     */
    public function getComplaintList($user_id = 0, $dealy_time = 0, $num = 10, $start = 0, $is_complaint = 0, $keyword = '')
    {
        $time = TimeRepository::getGmTime();

        $res = OrderInfo::selectRaw("*, (goods_amount + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee + tax - discount) AS total_fee")
            ->where('main_count', 0)
            ->where('user_id', $user_id)->where('is_delete', 0);

        if (!empty($keyword)) {
            $res = $res->where('order_sn', 'like', '%' . $keyword . '%');
        }

        $res = $res->where('is_zc_order', 0);

        $res = $res->where(function ($query) use ($is_complaint) {
            $query->complaintCount($is_complaint);
        });

        if ($is_complaint == 0) {
            //获取已确认，已分单，部分分单，已付款，已发货或者已确认收货15天内的订单
            $res = $res->whereIn('order_status', [OS_CONFIRMED, OS_SPLITED, OS_SPLITING_PART]);
            $where_confirmed = "IF(pay_status = " . PS_PAYED . ", IF(shipping_status = " . SS_RECEIVED . ", shipping_status = '" . SS_RECEIVED . "' AND ('$time'- confirm_take_time) < '$dealy_time', shipping_status <> " . SS_UNSHIPPED . ")";
            $where_confirmed .= " AND pay_status = " . PS_PAYED . ", IF(shipping_status = " . SS_RECEIVED . ", shipping_status = " . SS_RECEIVED . " AND ('$time'- confirm_take_time) < '$dealy_time', shipping_status <> " . SS_UNSHIPPED . "))";

            $res = $res->whereRaw($where_confirmed);
        }

        $res = $res->with([
            'getComplaint' => function ($query) {
                $query->selectRaw("order_id, IFNULL(complaint_id, 0) AS is_complaint, complaint_state, complaint_active");
            },
            'getOrderGoods' => function ($query) {
                $query->select('ru_id', 'goods_id');
            }
        ]);

        $res = $res->orderBy('add_time', 'desc');

        if ($start > 0) {
            $res = $res->skip($start);
        }

        if ($num > 0) {
            $res = $res->take($num);
        }

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {

            $ru_id = BaseRepository::getKeyPluck($res, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($res as $row) {
                $row = BaseRepository::getArrayMerge($row, $row['get_complaint']);

                $shop_information = $merchantList[$row['ru_id']] ?? [];

                $order_goods = $row['get_order_goods'];
                $row['ru_id'] = $order_goods ? $order_goods['ru_id'] : 0;
                $row['goods_id'] = $order_goods ? $order_goods['goods_id'] : 0;
                $row['order_goods'] = get_order_goods_toInfo($row['order_id']);
                $row['shop_name'] = $shop_information['shop_name'] ?? '';
                $row['shop_ru_id'] = $row['ru_id'];

                $build_uri = [
                    'urid' => $row['ru_id'],
                    'append' => $row['shop_name']
                ];

                $domain_url = $this->merchantCommonService->getSellerDomainUrl($row['ru_id'], $build_uri);
                $row['shop_url'] = $domain_url['domain_name'];

                $chat = $this->dscRepository->chatQq($shop_information);

                //IM or 客服
                if (config('shop.customer_service') == 0) {
                    $ru_id = 0;
                } else {
                    $ru_id = $row['ru_id'];
                }

                //判断当前商家是平台,还是入驻商家 bylu
                if ($ru_id == 0) {
                    //判断平台是否开启了IM在线客服
                    $kf_im_switch = SellerShopinfo::where('ru_id', 0)->value('kf_im_switch');
                    if ($kf_im_switch) {
                        $row['is_dsc'] = true;
                    } else {
                        $row['is_dsc'] = false;
                    }
                } else {
                    $row['is_dsc'] = false;
                }

                $row['has_talk'] = 0;

                //获取是否存在未读信息
                if (isset($row['complaint_state']) && $row['complaint_state'] > 1) {
                    $talk_list = ComplaintTalk::where('complaint_id', $row['is_complaint'])
                        ->orderBy('talk_time', 'desc')
                        ->get();

                    $talk_list = $talk_list ? $talk_list->toArray() : [];

                    if ($talk_list) {
                        foreach ($talk_list as $k => $v) {
                            if ($v['view_state']) {
                                $view_state = explode(',', $v['view_state']);
                                if (!in_array('user', $view_state)) {
                                    $row['has_talk'] = 1;
                                    break;
                                }
                            }
                        }
                    }
                }

                $arr[] = ['order_id' => $row['order_id'],
                    'order_sn' => $row['order_sn'],
                    'order_time' => TimeRepository::getLocalDate(config('shop.time_format'), $row['add_time']),
                    'is_im' => $shop_information['is_im'], //平台是否允许商家使用"在线客服";
                    'is_dsc' => $row['is_dsc'],
                    'ru_id' => $row['ru_id'],
                    'shop_name' => $row['shop_name'], //店铺名称	,
                    'shop_url' => $row['shop_url'], //店铺名称	,
                    'order_goods' => $row['order_goods'],
                    'no_picture' => config('shop.no_picture'),
                    'kf_type' => $chat['kf_type'],
                    'kf_ww' => $chat['kf_ww'],
                    'kf_qq' => $chat['kf_qq'],
                    'total_fee' => isset($row['total_fee']) ? $this->dscRepository->getPriceFormat($row['total_fee'], false) : 0,
                    'is_complaint' => isset($row['is_complaint']) ? $row['is_complaint'] : 0,
                    'complaint_state' => isset($row['complaint_state']) ? $row['complaint_state'] : 0,
                    'complaint_active' => isset($row['complaint_active']) ? $row['complaint_active'] : '',
                    'has_talk' => $row['has_talk']
                ];
            }
        }

        return $arr;
    }

    /**
     * 违规举报图片
     */
    public function ReportImagesList($where = [])
    {
        $img_list = $this->getGoodsReportImgList($where);

        if ($img_list) {
            foreach ($img_list as $key => $row) {
                $img_list[$key]['comment_img'] = $this->dscRepository->getImagePath($row['comment_img']);
            }
        }

        return $img_list;
    }

    /**
     * 获取会员操作日志列表
     *
     * @param int $user_id
     * @param int $num
     * @param int $start
     * @return array
     * @throws \Exception
     */
    public function GetUsersLogList($user_id = 0, $num = 10, $start = 0)
    {
        $row = UsersLog::where('change_type', '<>', 9)
            ->where('user_id', $user_id)
            ->where('admin_id', 0);

        $row = $row->with([
            'getAdminUser'
        ]);

        if ($start > 0) {
            $row = $row->skip($start);
        }

        if ($num > 0) {
            $row = $row->take($num);
        }

        $row = $row->orderByDesc('log_id')->get();

        $row = $row ? $row->toArray() : [];

        if ($row) {
            foreach ($row as $k => $v) {
                if ($v['change_time'] > 0) {
                    $row[$k]['change_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $v['change_time']);
                }

                if ($v['admin_id'] > 0) {
                    $user_name = $v['get_admin_user']['user_name'] ?? '';
                    $row[$k]['admin_name'] = sprintf(lang('user.admin_user'), $user_name);
                }
            }
        }

        return $row;
    }

    /**
     * 判断秒杀活动是否失效
     *
     * @access  public
     * @param int $goods_id 秒杀商品ID
     * @return
     */
    public function is_invalid($goods_id = 0)
    {
        $row = SeckillGoods::select('sec_id')->where('id', $goods_id);
        $row = $row->with([
            'getSeckill' => function ($query) {
                $query->select('sec_id', 'is_putaway', 'acti_time');
            }
        ]);
        $row = $row->first();

        $row = $row ? $row->toArray() : [];

        $row = $row && $row['get_seckill'] ? array_merge($row, $row['get_seckill']) : $row;

        $time = TimeRepository::getGmTime();
        if ($row && ($row['is_putaway'] == 0 || $row['acti_time'] < $time)) {
            return true; //失效
        } else {
            return false; //有效
        }
    }

    /* 用户注册 计算用户名长度 */

    public function DealJsStrlen($str)
    {
        $strlen = strlen($str);

        //汉字长度
        $zhcn_len = 0;
        $pattern = '/[^\x00-\x80]+/';
        if (preg_match_all($pattern, $str, $matches)) {
            $words = $matches[0];
            foreach ($words as $word) {
                $zhcn_len += strlen($word);
            }
        }
        //剩余长度
        $left_len = $strlen - $zhcn_len;
        //转换长度
        $deal_len = $left_len + $zhcn_len / 3 * 2;
        return $deal_len;
    }

    //获取余额记录总数
    public function GetUserAccountLogCount($user_id = 0, $account_type = '')
    {
        /* 获取记录条数 */
        $record_count = AccountLog::where('user_id', $user_id)
            ->where($account_type, '<>', 0)
            ->count();

        return $record_count;
    }

    //获取余额记录
    public function GetUserAccountLogList($user_id = 0, $account_type = '', $pager = [])
    {
        $res = AccountLog::where('user_id', $user_id)
            ->where($account_type, '<>', 0);

        $res = $res->orderBy('log_id', 'desc');

        if ($pager) {
            if ($pager['start'] > 0) {
                $res = $res->skip($pager['start']);
            }

            if ($pager['size'] > 0) {
                $res = $res->take($pager['size']);
            }
        }

        $res = $res->get();

        $res = $res ? $res->toArray() : [];
        $account_log = [];
        if ($res) {
            foreach ($res as $row) {
                $row['change_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['change_time']);
                $row['type'] = $row[$account_type] > 0 ? lang('user.account_inc') : lang('user.account_dec');
                $row['user_money'] = $this->dscRepository->getPriceFormat(abs($row['user_money']), false);
                $row['frozen_money'] = $this->dscRepository->getPriceFormat(abs($row['frozen_money']), false);
                $row['rank_points'] = abs($row['rank_points']);
                $row['pay_points'] = abs($row['pay_points']);
                $row['short_change_desc'] = $this->dscRepository->subStr($row['change_desc'], 60);
                $row['amount'] = $row[$account_type];
                $account_log[] = $row;
            }
        }

        return $account_log;
    }

    /**
     *  获取上传凭证图片列表
     *
     * @access  public
     * @param array $where
     * @return  bool
     */
    public function getGoodsReportImgList($where = [])
    {
        $img_list = GoodsReportImg::selectRaw('*, img_file as comment_img, img_id as id');

        if (isset($where['user_id'])) {
            $img_list = $img_list->where('user_id', $where['user_id']);
        }

        if (isset($where['goods_id'])) {
            $img_list = $img_list->where('goods_id', $where['goods_id']);
        }

        if (isset($where['report_id'])) {
            $img_list = $img_list->where('report_id', $where['report_id']);
        }

        $img_list = $img_list->orderBy('id', 'desc');

        $img_list = $img_list->get();

        $img_list = $img_list ? $img_list->toArray() : [];

        if ($img_list) {
            foreach ($img_list as $key => $row) {
                $img_list[$key]['comment_img'] = $this->dscRepository->getImagePath($row['img_file']);
            }
        }

        return $img_list;
    }

    /**
     *  获取上传投诉图片列表
     *
     * @access  public
     * @param array $where
     * @return  bool
     */
    public function getComplaintImgList($where = [])
    {
        $img_list = ComplaintImg::selectRaw('*, img_id as id ,img_file as comment_img');

        if (isset($where['user_id'])) {
            $img_list = $img_list->where('user_id', $where['user_id']);
        }

        if (isset($where['order_id'])) {
            $img_list = $img_list->where('order_id', $where['order_id']);
        }

        if (isset($where['complaint_id'])) {
            $img_list = $img_list->where('complaint_id', $where['complaint_id']);
        }

        $img_list = $img_list->orderBy('id', 'desc');

        $img_list = $img_list->get();

        $img_list = $img_list ? $img_list->toArray() : [];

        if ($img_list) {
            foreach ($img_list as $key => $row) {
                $img_list[$key]['img_file'] = $this->dscRepository->getImagePath($row['img_file']);
                $img_list[$key]['comment_img'] = $this->dscRepository->getImagePath($row['comment_img']);
            }
        }

        return $img_list;
    }

    /**
     *  获取会员优惠券数量与金额
     *
     * @access  public
     * @param array $where
     * @return  array
     */
    public function getUserCouMoney($where = [])
    {
        $cou = CouponsUser::where('is_delete', 0)->where('user_id', $where['user_id'])->where('is_use', 0)->where('is_use_time', 0);

        $time = TimeRepository::getGmTime();

        $cou = $cou->whereHasIn('getCoupons', function ($query) use ($time) {
            $query->whereRaw("IF(valid_type > 1, receive_start_time <= '$time' and receive_end_time >= '$time', cou_start_time <= '$time' and cou_end_time >= '$time')")
                ->where('cou_type', '<>', VOUCHER_GROUPBUY)
                ->where('status', COUPON_STATUS_EFFECTIVE);
        });

        $cou = $cou->with([
            'getCoupons'
        ]);

        $coupon_num = $cou->count();

        $cou = $cou->get();

        $cou = $cou ? $cou->toArray() : [];

        $money = 0;
        if ($cou) {
            foreach ($cou as $key => $row) {
                $cou_money = $row && $row['get_coupons'] ? $row['get_coupons']['cou_money'] : 0;
                $money += $cou_money;
            }
        }

        return ['num' => $coupon_num, 'money' => $money];
    }

    /**
     *  获取会员储值卡信息
     *
     * @access  public
     * @param array $where
     * @return  bool
     */
    public function getValueCardInfo($where = [])
    {
        if (isset($where['user_id'])) {
            $res = ValueCard::selectRaw("COUNT(*) AS num, SUM(card_money) AS money")
                ->whereRaw(1);
        } else {
            $res = ValueCard::whereRaw(1);
        }


        if (isset($where['user_id'])) {
            $res = $res->where('user_id', $where['user_id']);
        }

        $res = $res->first();

        $res = $res ? $res->toArray() : [];

        return $res;
    }

    /**
     *  获取会员注册字段列表
     *
     * @access  public
     * @param array $where
     * @return  bool
     */
    public function getRegFieldsList($where = [])
    {
        $res = RegFields::whereRaw(1);

        if (isset($where['type'])) {
            if (is_array($where['type'])) {
                $res = $res->where('type', $where['type'][0], $where['type'][1]);
            } else {
                $res = $res->where('type', $where['type']);
            }
        }

        if (isset($where['display'])) {
            $res = $res->where('display', $where['display']);
        }

        if (isset($where['sort']) && isset($where['order'])) {
            if (is_array($where['sort'])) {
                $where['sort'] = implode(",", $where['sort']);
                $res = $res->orderByRaw($where['sort'] . " " . $where['order']);
            } else {
                $res = $res->orderBy($where['sort'], $where['order']);
            }
        }

        $res = $res->get();

        $res = $res->toArray();

        return $res;
    }

    /**
     *  获取会员增值发票信息
     *
     * @access  public
     * @param array $where
     * @return  bool
     */
    public function getUsersVatInvoicesInfo($where = [])
    {
        $res = UsersVatInvoicesInfo::where('user_id', $where['user_id']);

        if (isset($where['id'])) {
            $res = $res->where('id', $where['id']);
        }

        $res = $res->with([
            'getRegionProvince' => function ($query) {
                $query->select('region_id', 'region_name');
            },
            'getRegionCity' => function ($query) {
                $query->select('region_id', 'region_name');
            },
            'getRegionDistrict' => function ($query) {
                $query->select('region_id', 'region_name');
            }
        ]);

        $res = $res->first();

        $res = $res ? $res->toArray() : [];

        if (!empty($res)) {
            $res['province_name'] = $res['get_region_province']['region_name'] ?? '';
            $res['city_name'] = $res['get_region_city']['region_name'] ?? '';
            $res['district_name'] = $res['get_region_district']['region_name'] ?? '';

            $res['vat_region'] = $res['province_name'] . ' ' . $res['city_name'] . ' ' . $res['district_name'];
        }

        return $res;
    }

    /**
     *  获取会员优惠券总数和金额
     *
     * @access  public
     * @param array $where
     * @return  bool
     */
    public function getCouponsUserTotal($user_id = 0)
    {
        $time = TimeRepository::getGmTime();
        $user = Users::where('user_id', $user_id);
        $user = $user->with([
            'getCouponsUserList' => function ($query) use ($time) {
                $query->select('user_id', 'uc_id', 'cou_money')
                    ->where('cou_id', '>', 0)
                    ->where('is_use', 0)
                    ->where('is_use_time', 0)
                    ->whereHasIn('getCoupons', function ($query) use ($time) {
                        $query->whereRaw("IF(valid_type > 1, receive_start_time <= '$time' and receive_end_time >= '$time', cou_start_time <= '$time' and cou_end_time >= '$time')")
                            ->where('status', COUPON_STATUS_EFFECTIVE);
                    });
            }
        ]);

        $user = BaseRepository::getToArrayFirst($user);

        $num = 0;
        $money = 0;
        if ($user) {
            foreach ($user['get_coupons_user_list'] as $row) {
                $num += 1;
                $money += $row['cou_money'] ?? 0;
            }
        }

        $cou = [
            'num' => $num,
            'money' => $money
        ];

        return $cou;
    }

    /**
     * 推荐注册分成
     *
     * @param array $where
     * @param int $page
     * @param int $size
     * @return array
     */
    public function getUserParentOrderAffiliateList($where = [], $page = 1, $size = 10)
    {
        /*
          SQL解释：

          订单、用户、分成记录关联
          一个订单可能有多个分成记录

          1、订单有效 o.user_id > 0
          2、满足以下之一：
          a.直接下线的未分成订单 u.parent_id IN ($all_uid) AND o.is_separate = 0
          其中$all_uid为该ID及其下线(不包含最后一层下线)
          b.全部已分成订单 a.user_id = '$user_id' AND o.is_separate > 0

         */

        $res = OrderInfo::where('main_count', 0);
        $res = CommonRepository::constantMaxId($res, 'user_id');
        $res = $res->where('parent_id', 0)
            ->where(function ($query) use ($where) {
                $query = $query->where(function ($query) use ($where) {
                    $query->whereHasIn('getUsers', function ($query) use ($where) {
                        $where['all_uid'] = !is_array($where['all_uid']) ? explode(",", $where['all_uid']) : $where['all_uid'];
                        $query->whereIn('parent_id', $where['all_uid'])->where('is_separate', 0);
                    });
                });

                $query->orWhere(function ($query) use ($where) {
                    $query->whereHasIn('getAffiliateLog', function ($query) use ($where) {
                        $query->where('user_id', $where['user_id'])->where('is_separate', '>', 0);
                    });
                });
            });

        if (file_exists(MOBILE_DRP)) {
            $res = $res->whereDoesntHaveIn('getDrpLog');
        }

        $res = $res->where(function ($query) {
            $query->whereHasIn('getOrderGoods', function ($query) {
                $query->sellerCount();
            });
        });

        $res = $res->with([
            'getUsers' => function ($query) {
                $query->select('user_id', 'user_name', 'nick_name', 'reg_time', 'user_picture');
            }
        ]);

        $sqlcount = $res->count();

        $max_page = ($sqlcount > 0) ? ceil($sqlcount / $size) : 1;
        if ($page > $max_page) {
            $page = $max_page;
        }

        $res = $res->orderBy('order_id', 'desc');

        $start = ($page - 1) * $size;

        if ($start > 0) {
            $res = $res->skip($start);
        }

        if ($size > 0) {
            $res = $res->take($size);
        }

        $res = $res->get();

        $res = $res ? $res->toArray() : [];

        return ['res' => $res, 'sqlcount' => $sqlcount, 'max_page' => $max_page, 'page' => $page];
    }

    /**
     * 推荐订单分成
     *
     * @param array $where
     * @param int $page
     * @param int $size
     * @return array
     */
    public function getUserOrderAffiliateList($where = [], $page = 1, $size = 10)
    {
        /*
          SQL解释：

          订单、用户、分成记录关联
          一个订单可能有多个分成记录

          1、订单有效 o.user_id > 0
          2、满足以下之一：
          a.订单下线的未分成订单 o.parent_id = '$user_id' AND o.is_separate = 0
          b.全部已分成订单 a.user_id = '$user_id' AND o.is_separate > 0

         */
        $res = OrderInfo::where('main_count', 0);
        $res = CommonRepository::constantMaxId($res, 'user_id');
        $res = $res->where(function ($query) use ($where) {
            $query->where('parent_id', $where['all_uid'])->where('is_separate', 0);
            $query->orWhere(function ($query) use ($where) {
                $query->whereHasIn('getAffiliateLog', function ($query) use ($where) {
                    $query->where('user_id', $where['user_id']);
                });
            });
        });

        if (file_exists(MOBILE_DRP)) {
            $res = $res->whereDoesntHaveIn('getDrpLog');
        }

        $res = $res->where(function ($query) {
            $query->whereHasIn('getOrderGoods', function ($query) {
                $query->sellerCount();
            });
        });

        $res = $res->with([
            'getUsers' => function ($query) {
                $query->select('user_id', 'user_name', 'nick_name', 'reg_time', 'user_picture');
            }
        ]);

        $sqlcount = $res->count();

        $max_page = ($sqlcount > 0) ? ceil($sqlcount / $size) : 1;
        if ($page > $max_page) {
            $page = $max_page;
        }

        $res = $res->orderBy('order_id', 'desc');

        $start = ($page - 1) * $size;

        if ($start > 0) {
            $res = $res->skip($start);
        }

        if ($size > 0) {
            $res = $res->take($size);
        }

        $res = $res->get();

        $res = $res ? $res->toArray() : [];

        return ['res' => $res, 'sqlcount' => $sqlcount, 'max_page' => $max_page, 'page' => $page];
    }

    /**
     *  获取会员跟踪包裹数量
     *
     * @access  public
     * @param array $where
     * @return  bool
     */
    public function getTrackPackagesCount($user_id = 0)
    {
        $record_count = OrderInfo::where('main_count', 0)
            ->where('user_id', $user_id)
            ->whereIn('shipping_status', [SS_SHIPPED, SS_RECEIVED]);

        $record_count = $record_count->count();

        return $record_count;
    }

    /**
     *  获取会员跟踪包裹列表
     *
     * @access  public
     * @param array $where
     * @return  bool
     */
    public function getTrackPackagesList($user_id = 0, $page = 1, $size = 10)
    {
        $res = OrderInfo::where('main_count', 0)
            ->where('user_id', $user_id)
            ->whereIn('shipping_status', [SS_SHIPPED, SS_RECEIVED]);

        $res = $res->orderBy('order_id', 'desc');

        $start = ($page - 1) * $size;
        if ($start > 0) {
            $res = $res->skip($start);
        }

        if ($size > 0) {
            $res = $res->take($size);
        }

        $res = $res->get();

        $res = $res ? $res->toArray() : [];

        return $res;
    }

    protected function cat_format($res)
    {
        if ($res) {
            $result = '';
            foreach ($res as $v) {
                $result .= '<a href="category.php?id=' . $v['cat_id'] . '" style="color:red;">' . $v['cat_name'] . '</a>' . '，';
            }
            $result = rtrim($result, '，');
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 查询会员余额的操作记录
     *
     * @access  public
     * @param int $user_id 会员ID
     * @param int $size 每页显示数量
     * @param int $start 开始显示的条数
     * @return  array
     */
    public function getAccountLog($user_id = 0, $size = 10, $start = 0)
    {
        if (empty($user_id)) {
            return [];
        }

        $res = UserAccount::where('user_id', $user_id)
            ->whereIn('process_type', [SURPLUS_SAVE, SURPLUS_RETURN]);

        $res = $res->orderBy('add_time', 'desc');

        if ($start > 0) {
            $res = $res->skip($start);
        }

        if ($size > 0) {
            $res = $res->take($size);
        }

        $res = BaseRepository::getToArrayGet($res);

        $account_log = [];
        if ($res) {
            foreach ($res as $rows) {
                $rows['add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $rows['add_time']);
                $rows['admin_note'] = nl2br(htmlspecialchars($rows['admin_note']));
                $rows['short_admin_note'] = ($rows['admin_note'] > '') ? $this->dscRepository->subStr($rows['admin_note'], 30) : 'N/A';
                $rows['user_note'] = nl2br(htmlspecialchars($rows['user_note']));
                $rows['short_user_note'] = ($rows['user_note'] > '') ? $this->dscRepository->subStr($rows['user_note'], 30) : 'N/A';
                $rows['pay_status'] = ($rows['is_paid'] == 0) ? $GLOBALS['_LANG']['un_confirmed'] : $GLOBALS['_LANG']['is_confirmed'];
                $rows['amount'] = $this->dscRepository->getPriceFormat(abs($rows['amount']), false);

                /* 会员的操作类型： 冲值，提现 */
                if ($rows['process_type'] == 0) {
                    $rows['type'] = $GLOBALS['_LANG']['surplus_type_0'];
                } else {
                    $rows['type'] = $GLOBALS['_LANG']['surplus_type_1'];
                }

                /* 支付方式的ID */
                if ($rows['pay_id'] > 0) {
                    $pid = $rows['pay_id'];
                } else {
                    $pid = Payment::where('pay_name', $rows['payment'])->where('enabled')->value('pay_id');
                }

                /* 如果是预付款而且还没有付款, 允许付款 */
                if (($rows['is_paid'] == 0) && ($rows['process_type'] == 0)) {
                    $rows['handle'] = '<a href="user.php?act=pay&id=' . $rows['id'] . '&pid=' . $pid . '" class="ftx-01">' . $GLOBALS['_LANG']['pay'] . '</a>';
                }

                $account_log[] = $rows;
            }
        }

        return $account_log;
    }

    /**
     * 获取用户消费积分记录
     *
     * @param int $user_id
     * @param int $page
     * @param int $size
     * @param string $type
     * @return array
     */
    public function getUserPayPoints($user_id = 0, $page = 1, $size = 10, $type = '')
    {
        $res = AccountLog::where('user_id', $user_id)
            ->where($type, '<>', 0)
            ->orderBy('log_id', 'desc')
            ->offset(($page - 1) * $size)
            ->limit($size)
            ->get();
        $res = $res ? $res->toArray() : [];
        $account_log = [];
        if ($res) {
            foreach ($res as $k => $row) {
                $row['change_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['change_time']);
                $row['type'] = $row[$type] > 0 ? '+' : '-';
                $row['user_money'] = $this->dscRepository->getPriceFormat(abs($row['user_money']), false);
                $row['frozen_money_format'] = $this->dscRepository->getPriceFormat(abs($row['frozen_money']), false);
                $row['rank_points'] = abs($row['rank_points']);
                $row['pay_points'] = abs($row['pay_points']);
                $row['short_change_desc'] = $this->dscRepository->subStr($row['change_desc'], 60);
                $temp = explode(',', $row['short_change_desc']);
                if (count($temp) == 2) {
                    $row['short_change_desc_part1'] = $temp[0];
                    $row['short_change_desc_part2'] = $temp[1];
                }
                $row['amount'] = $row[$type];
                $account_log[] = $row;
            }
        }
        return $account_log;
    }


    /**
     * 查询用户是否实名认证
     *
     * @param int $user_id
     * @param int $user_type 0 会员实名认证 1 ，商家实名认证
     * @return int
     */
    public function userReal($user_id = 0, $user_type = 0)
    {
        $count = UsersReal::where('user_id', $user_id)->where('user_type', $user_type)->count();

        return empty($count) ? 0 : 1;
    }

    /**
     * 当前会员是否是商家
     *
     * @param int $user_id
     * @return int
     */
    public function isSeller($user_id = 0)
    {
        $is_jurisdiction = 0;
        if ($user_id > 0) {
            // 判断是否是商家
            $seller_count = SellerShopinfo::where('ru_id', $user_id)->count();

            $is_jurisdiction = $seller_count > 0 ? 1 : 0;

            //判断是否是厂商
            $is_chang_count = MerchantsStepsFields::where('user_id', $user_id)->where('company_type', '厂商')->count();

            if ($is_chang_count > 0) {
                $is_jurisdiction = 0;
            }
        }
        return $is_jurisdiction;
    }

    /**
     * 用户支付密码
     * @param int $user_id
     * @return array
     */
    public function getPaypwd($user_id = 0)
    {
        $result = UsersPaypwd::where('user_id', $user_id)->first();

        return $result ? $result->toArray() : [];
    }

    public function getUserHelpart()
    {
        $article_id = config('shop.user_helpart');
        $arr = [];

        $new_article = substr($article_id, -1);
        if ($new_article == ',') {
            $article_id = substr($article_id, 0, -1);
        }

        if (!empty($article_id)) {
            $article_id = !is_array($article_id) ? explode(",", $article_id) : $article_id;

            $res = Article::whereIn('article_id', $article_id)
                ->orderBy('article_id', 'desc');

            $res = BaseRepository::getToArrayGet($res);

            if ($res) {
                foreach ($res as $key => $row) {
                    $arr[$key]['article_id'] = $row['article_id'];
                    $arr[$key]['title'] = $row['title'];
                    $arr[$key]['url'] = $this->dscRepository->buildUri('article', ['aid' => $row['article_id']], $row['title']);
                }
            }
        }

        return $arr;
    }

    /**获取地区名称
     * @param $regionId
     * @return string
     */
    public function getRegionName($regionId)
    {
        $regionName = Region::where('region_id', $regionId)
            ->pluck('region_name')
            ->toArray();
        if (empty($regionName)) {
            return '';
        }

        return $regionName[0];
    }


    /**
     * 判断会员是否有虚拟资产
     * @param int $user_id 用户id
     * @return array          虚拟资产信息
     */
    public function getUserAccount($user_id)
    {
        $result = [
            'type' => false, //是否有资产
            'data' => [
                'money' => ['text' => $GLOBALS['_LANG']['virtual_assets'][0], 'num' => 0], //会员余额
                'frozen' => ['text' => $GLOBALS['_LANG']['virtual_assets'][1], 'num' => 0], //冻结金额
                'point' => ['text' => $GLOBALS['_LANG']['virtual_assets'][2], 'num' => 0], //消费积分
                'coupons' => ['text' => $GLOBALS['_LANG']['virtual_assets'][3], 'num' => 0], //优惠券数量
                'bonus' => ['text' => $GLOBALS['_LANG']['virtual_assets'][4], 'num' => 0], //红包数量
                'card' => ['text' => $GLOBALS['_LANG']['virtual_assets'][5], 'num' => 0] //储值卡余额
            ]
        ];

        $time = TimeRepository::getGmTime();

        //查询出 会员余额 、 消费积分 、 冻结余额
        $user = Users::select('user_money', 'frozen_money', 'pay_points')->where('user_id', $user_id);
        $user = BaseRepository::getToArrayFirst($user);
        $result['data']['money']['num'] = $user['user_money'];
        $result['data']['frozen']['num'] = $user['frozen_money'];
        $result['data']['point']['num'] = $user['pay_points'];
        if ($user['user_money'] != 0 || $user['frozen_money'] > 0 || $user['pay_points'] > 0) {
            $result['type'] = true;
        }

        //查询出会员优惠券数量
        $coupons_user = CouponsUser::select('cou_money')
            ->where('order_id', 0)
            ->where('user_id', $user_id)
            ->where('is_use', 0);

        $coupons_user = $coupons_user->whereHasIn('getCoupons', function ($query) use ($time) {
            $query->where('review_status', 3)
                ->whereRaw("IF(valid_type > 1, receive_start_time <= '$time' and receive_end_time >= '$time', cou_start_time <= '$time' and cou_end_time >= '$time')")
                ->where('status', COUPON_STATUS_EFFECTIVE);
        });

        $coupons_user = $coupons_user->with(['getCoupons']);
        $coupons_user = $coupons_user->groupBy('uc_id');
        $coupons_user = BaseRepository::getToArrayGet($coupons_user);
        $result['data']['coupons']['num'] = count($coupons_user);
        if ($result['data']['coupons']['num'] > 0) {
            $result['type'] = true;
        }

        //查询出会员红包数量
        $result['data']['bonus']['num'] = app(BonusService::class)->getUserBounsNewCount($user_id, 0);
        if ($result['data']['bonus']['num'] > 0) {
            $result['type'] = true;
        }

        //查询出会员储值卡数量
        $result['data']['card']['num'] = ValueCard::where('user_id', $user_id)
            ->where('end_time', '>', $time)
            ->where('card_money', '>', 0)
            ->sum('card_money');
        if ($result['data']['card']['num'] > 0) {
            $result['type'] = true;
        }

        return $result;
    }

    /**
     * 销售奖励
     * @param int $user_id
     * @param int $page
     * @param int $size
     * @return array
     */
    public function getUserDrpOrder($user_id = 0, $page = 1, $size = 10)
    {
        if (!file_exists(MOBILE_DRP)) {
            return [];
        }

        $offset = [
            'start' => ($page - 1) * $size,
            'limit' => $size
        ];
        $res = app(\App\Modules\Drp\Services\Drp\DrpLogService::class)->drp_log_list($user_id, 2, $offset, 'order');

        $list = $res['list'] ?? [];
        $record_count = $res['total'] ?? 0;

        $page_count = ($record_count > 0) ? ceil($record_count / $size) : 1;

        $config = ['header' => $GLOBALS['_LANG']['pager_2'], "prev" => "<i><<</i>" . $GLOBALS['_LANG']['page_prev'], "next" => "" . $GLOBALS['_LANG']['page_next'] . "<i>>></i>", "first" => $GLOBALS['_LANG']['page_first'], "last" => $GLOBALS['_LANG']['page_last']];

        $pagerParams = [
            'total' => $record_count,
            'listRows' => $size,
            'page' => $page,
            'funName' => 'user_drp_orderPage',
            'pageType' => 1,
            'config_zn' => $config
        ];
        $user_snatch = new Pager($pagerParams);
        $pager = $user_snatch->fpage([0, 4, 5, 6, 9]);

        return ['list' => $list, 'pager' => $pager, 'record_count' => $record_count, 'page_count' => $page_count];
    }

    /**
     * 推荐办卡奖励
     * @param int $user_id
     * @param int $page
     * @param int $size
     * @return array
     */
    public function getUserDrpCardOrder($user_id = 0, $page = 1, $size = 10)
    {
        if (!file_exists(MOBILE_DRP)) {
            return [];
        }

        $offset = [
            'start' => ($page - 1) * $size,
            'limit' => $size
        ];
        $res = app(\App\Modules\Drp\Services\Drp\DrpLogService::class)->drp_log_list($user_id, 2, $offset, 'card');

        $list = $res['list'] ?? [];
        $record_count = $res['total'] ?? 0;

        $page_count = ($record_count > 0) ? ceil($record_count / $size) : 1;
        $config = ['header' => $GLOBALS['_LANG']['pager_2'], "prev" => "<i><<</i>" . $GLOBALS['_LANG']['page_prev'], "next" => "" . $GLOBALS['_LANG']['page_next'] . "<i>>></i>", "first" => $GLOBALS['_LANG']['page_first'], "last" => $GLOBALS['_LANG']['page_last']];

        $pagerParams = [
            'total' => $record_count,
            'listRows' => $size,
            'page' => $page,
            'funName' => 'user_drp_card_orderPage',
            'pageType' => 1,
            'config_zn' => $config
        ];
        $user_snatch = new Pager($pagerParams);
        $pager = $user_snatch->fpage([0, 4, 5, 6, 9]);

        return ['list' => $list, 'pager' => $pager, 'record_count' => $record_count, 'page_count' => $page_count];
    }

    /**
     * 分销商统计
     * @param  $user_id
     * @param $status
     * @return  bool
     */
    public function getUserDrpCount($user_id = 0, $status = 0)
    {
        if (!file_exists(MOBILE_DRP)) {
            return 0;
        }

        // 已邀请好友数量
        if ($status == 0) {
            $model = Users::query()->where('parent_id', $user_id);
            return $model->count('user_id');
        }

        // 今日收入
        if ($status == 1) {
            return app(\App\Modules\Drp\Services\Drp\DrpLogService::class)->get_drp_money(1, $user_id);
        }

        // 总销售额
        if ($status == 2) {
            return app(\App\Modules\Drp\Services\Drp\DrpLogService::class)->get_drp_money(2, $user_id);
        }

        // 累计佣金
        if ($status == 3) {
            return app(\App\Modules\Drp\Services\Drp\DrpLogService::class)->get_drp_money(3, $user_id);
        }

        // 分销商信息 - 可提现佣金
        if ($status == 5) {
            return app(\App\Modules\Drp\Repositories\DrpShopRepository::class)->shop_info($user_id, ['shop_money', 'membership_card_id']);
        }

        return 0;
    }

    /**
     * 会员详情信息
     *
     * @param int $user_id
     * @return array
     * @throws \Exception
     */
    public function getUserDetail($user_id = 0)
    {
        $user = $this->getUserinfoDetail($user_id);

        if (empty($user)) {
            return [];
        }

        $rank_info = $this->getMembershipCard($user_id);

        $data_user = [
            'user_name' => $user['user_name'] ?? '',
            'sex' => $user['sex'] ?? 0,
            'nick_name' => $user['nick_name'] ?? '',
            'mobile_phone' => !empty($user['mobile_phone']) ? $user['mobile_phone'] : '',
            'mobile_phone_sign' => !empty($user['mobile_phone']) ? $this->formatNum($user['mobile_phone'], 3, 6) : '',
            'bind_phone' => !empty($user['mobile_phone']) ? 1 : 0,
            'user_picture' => $this->dscRepository->getImagePath($user['user_picture'] ?? ''),
            'email' => !empty($user['email']) ? $user['email'] : $GLOBALS['_LANG']['unbinding'],
            'bind_email' => !empty($user['email']) ? 1 : 0,
            'birthday' => !empty($user['birthday']) ? $user['birthday'] : $GLOBALS['_LANG']['unsetting'],
            'reg_time' => !empty($user['reg_time']) ? TimeRepository::getLocalDate(config('shop.time_format'), $user['reg_time']) : $GLOBALS['_LANG']['no_wu'],
            'last_login' => !empty($user['last_login']) ? TimeRepository::getLocalDate(config('shop.time_format'), $user['last_login']) : $GLOBALS['_LANG']['no_wu'],
            'bind_real' => !empty($user['self_num']) && !empty($user['review_status']) ? 1 : 0,
            'rank_name' => $rank_info['rank_name'] ?? '',
            'rank_rights' => $rank_info['rank_rights'] ?? [],
            'rank_end_time' => $rank_info['rank_end_time'] ?? '',
            'rank_id' => $rank_info['rank_id'] ?? 0,
        ];
        if ($user['parent_id'] > 0) {
            $parent_user_name = Users::where('user_id', $user['parent_id'])->value('user_name');
        }

        $affiliate = config('shop.affiliate') ? unserialize(config('shop.affiliate')) : [];
        if ($affiliate) {
            if (empty($affiliate['config']['separate_by'])) {
                //推荐注册分成
                $affdb = [];
                $num = $affiliate['item'] ? count($affiliate['item']) : 0;
                $up_uid[] = $user_id;

                if ($num) {
                    for ($i = 1; $i <= $num; $i++) {
                        $count = 0;
                        if ($up_uid) {
                            $sql = Users::query()->whereIn('parent_id', $up_uid);
                            $query = BaseRepository::getToArrayGet($sql);
                            $up_uid = [];
                            foreach ($query as $rt) {
                                $up_uid[] = $rt['user_id'];
                                $count++;
                            }
                        }
                        if ($count > 0) {
                            $affdb[$i]['num'] = $count;
                        }
                    }
                }
            }
        }

        $data_aff = [
            'reg_time' => $data_user['reg_time'],
            'parent_username' => !empty($parent_user_name) ? $parent_user_name : $GLOBALS['_LANG']['no_wu'],
            'parent_user_id' => $user['parent_id'],
            'bind_affiliate' => !empty($user['parent_id']) ? 1 : 0,
            'affdb' => $affdb ?? []
        ];

        $data_real = [
            'review_status' => $user['review_status'] ?? 0,
            'real_name' => $user['real_name'] ?? '',
            'bank_mobile' => !empty($user['bank_mobile']) ? $user['bank_mobile'] : '',
            'bank_mobile_sign' => !empty($user['bank_mobile']) ? $this->formatNum($user['bank_mobile'], 3, 6) : '',
            'self_num_sign' => !empty($user['self_num']) ? $this->formatNum($user['self_num'], 4, 8) : '',
            'self_num' => !empty($user['self_num']) ? $user['self_num'] : '',
            'z_user_pic' => !empty($user['front_of_id_card']) ? $this->dscRepository->getImagePath($user['front_of_id_card']) : '',
            'f_user_pic' => !empty($user['reverse_of_id_card']) ? $this->dscRepository->getImagePath($user['reverse_of_id_card']) : '',
            'real_id' => $user['real_id'] ?? 0,
        ];

        $data_account = [
            'formated_frozen_money' => $this->dscRepository->getPriceFormat($user['frozen_money'], false),
            'formated_user_money' => $this->dscRepository->getPriceFormat($user['user_money'], false),
            'pay_points' => $user['pay_points'] ?? 0,
            'rank_points' => $user['rank_points'] ?? 0,
            'formated_credit_line' => $this->dscRepository->getPriceFormat($user['credit_line'] ?? 0, false),
        ];

        $data_activity = [
            'value_card_num' => count($user['get_value_card']),
            'coupon_num' => count($user['get_coupons_user_list']),
            'bonus_num' => count($user['get_user_bonus_list']),
        ];

        $data_address = [
            'buyer' => '',
            'buyer_mobile' => '',
            'region' => '',
            'address' => '',
            'address_id' => 0
        ];
        if (!empty($user['address_id'])) {
            $address = $this->getDefaultAddress($user_id, $user['address_id']);
            $data_address = [
                'buyer' => $address['consignee'] ?? '',
                'buyer_mobile' => $address['mobile'] ?? '',
                'region' => ($address['get_region_province']['region_name'] ?? '') . ($address['get_region_city']['region_name'] ?? '') . ($address['get_region_district']['region_name'] ?? '') . ($address['get_region_street']['region_name'] ?? ''),
                'address' => $address['address'] ?? '',
                'address_id' => $user['address_id']
            ];
        }

        $data_order = $this->getUserOrderInfo($user_id);

        return ['user' => $data_user, 'aff' => $data_aff, 'real' => $data_real, 'account' => $data_account, 'activity' => $data_activity, 'address' => $data_address, 'order' => $data_order, 'uid' => $user['user_id']];
    }

    /**
     * 会员等级信息
     * @param int $user_id
     * @return array
     * @throws \Exception
     */
    private function getMembershipCard($user_id = 0)
    {
        $rank = app(UserCommonService::class)->getUserRankByUid($user_id);
        $ranklist = app(UserRankService::class)->getUserRankList(['rank_ids' => $rank['rank_id'] ?? 0, 'membership_card_display' => 'hide']);

        $mer_rank = [];
        if (!empty($ranklist[0])) {
            $mer_rank = [
                'rank_name' => $ranklist[0]['rank_name'] ?? '',
                'rank_rights' => $ranklist[0]['user_rank_rights'] ?? [],
                'rank_end_time' => $GLOBALS['_LANG']['expiry_type_forever'],
                'rank_id' => $rank['rank_id']
            ];
        }

        if (file_exists(MOBILE_DRP) && empty($mer_rank)) {
            $res = app(\App\Modules\Drp\Services\Drp\DrpShopService::class)->get_drp_shop_by_user($user_id);

            $membership_card_id = $res['membership_card_id'] ?? 0;
            $membership_card_info = app(\App\Modules\Drp\Services\RightsCardService::class)->cardInfo($membership_card_id); // 会员权益卡信息
            $mer_rank = [
                'rank_name' => $membership_card_info['name'] ?? '',
                'rank_rights' => $membership_card_info['user_membership_card_rights_list'] ?? [],
                'rank_end_time' => $membership_card_info['expiry_type_format'] ?? '',
                'rank_id' => $rank['rank_id']
            ];
        }

        return $mer_rank;
    }

    /**
     * 格式化手机和身份证
     * @param $str
     * @param int $sub_num
     * @param int $num
     * @return string
     */
    private function formatNum($str, $sub_num = 4, $num = 8)
    {
        return substr($str, 0, $sub_num) . ($num == 6 ? '******' : '********') . substr($str, -$sub_num);
    }

    /**
     * 会员信息
     * @param $user_id
     * @return array
     */
    private function getUserinfoDetail($user_id)
    {
        $time = TimeRepository::getGmTime();
        $res = Users::where('user_id', $user_id);

        $res = $res->with([
            'getUsersPaypwd' => function ($query) {
                $query->select('user_id', 'paypwd_id', 'pay_password');
            },
            'getUsersReal' => function ($query) {
                $query->select('real_id', 'user_id', 'bank_mobile', 'real_name', 'bank_card', 'bank_name', 'review_status', 'self_num', 'reverse_of_id_card', 'front_of_id_card')->where('user_type', 0);
            },
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

        $res = BaseRepository::getToArrayFirst($res);

        $res = $res && $res['get_users_paypwd'] ? array_merge($res, $res['get_users_paypwd']) : $res;
        $res = $res && $res['get_users_real'] ? array_merge($res, $res['get_users_real']) : $res;

        return $res;
    }

    /**
     * 会员默认地址
     * @param $user_id
     * @param $add_id
     * @return mixed
     */
    private function getDefaultAddress($user_id, $add_id)
    {
        $res = UserAddress::where('address_id', $add_id)->where('user_id', $user_id);
        $res = $res->with(['getRegionProvince', 'getRegionCity', 'getRegionDistrict', 'getRegionStreet']);
        return BaseRepository::getToArrayFirst($res);
    }

    /**
     * 获取指定的订单列表
     * @param $user_id
     * @return array
     * @throws \Exception
     */
    private function getUserOrderInfo($user_id)
    {
        $order = OrderInfo::where('user_id', $user_id)
            ->with(['getRegionCountry', 'getRegionProvince', 'getRegionCity', 'getRegionDistrict', 'getRegionStreet'])
            ->orderBy('order_id', 'desc')
            ->limit(2);
        $order = BaseRepository::getToArrayGet($order);

        $data = [];
        if ($order) {

            $ru_id = BaseRepository::getKeyPluck($order, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($order as $row) {
                $arr['order_sn'] = $row['order_sn'] ?? '';

                $arr['shop_name'] = $merchantList[$row['ru_id']]['shop_name'] ?? '';
                $arr['add_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['add_time']);
                $arr['address'] = $row['get_region_country']['region_name'] . '&nbsp;&nbsp;' . $row['get_region_province']['region_name'] . '&nbsp;&nbsp;' . $row['get_region_city']['region_name'] . '&nbsp;&nbsp;' . $row['get_region_district']['region_name'] . '&nbsp;&nbsp;' . $row['get_region_street']['region_name'] . '&nbsp;&nbsp;' . $row['address'] ?? '';
                $arr['mobile'] = !empty($row['mobile']) ? $this->formatNum($row['mobile'], 3, 6) : '';
                $arr['buyer'] = $row['consignee'] ?? '';
                $arr['total_fee_order'] = $row['goods_amount'] + $row['shipping_fee'] + $row['insure_fee'] + $row['pay_fee'] + $row['pack_fee'] + $row['card_fee'] - $row['discount'] - $row['vc_dis_money'];
                $arr['formated_total_fee_order'] = $this->dscRepository->getPriceFormat($arr['total_fee_order'], false);
                $arr['order_status'] = $row['order_status'] ?? 0;
                $arr['pay_status'] = $row['pay_status'] ?? 0;
                $arr['shipping_status'] = $row['shipping_status'] ?? 0;
                $data[] = $arr;
            }
        }
        return $data;
    }

    /**
     * 储值卡列表
     * @return array
     */
    public function getUserValueCardList()
    {
        $filter['user_id'] = $_REQUEST['user_id'] ?? 0;;
        $filter['use_type'] = $_REQUEST['use_type'] ?? 0;
        $filter['sort_by'] = 'id';
        $filter['sort_order'] = 'DESC';
        $filter['page'] = $_REQUEST['page'] ?? 0;
        $filter['start'] = $_REQUEST['start'] ?? 0;
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);

        $now = TimeRepository::getGmTime();
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }

        $res = ValueCard::where('user_id', $filter['user_id'])->whereHasIn('getValueCardType', function ($query) use ($filter) {
            if (!empty($filter['keywords'])) {

                $query->where('name', 'like', '%' . $filter['keywords'] . '%');
            }
        });

        $res = $filter['use_type'] == 1 ? $res->where('end_time', '<', $now) : $res->where('end_time', '>', $now);

        $res = $res->with([
            'getValueCardType'
        ]);

        $filter['record_count'] = $res->count();
        $filter = page_and_size($filter);

        $res = $res->orderBy('vid', 'desc')
            ->offset(($filter['page'] - 1) * $filter['page_size'])
            ->limit($filter['page_size']);

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            foreach ($res as $key => $row) {
                $row = $row['get_value_card_type'] ? array_merge($row, $row['get_value_card_type']) : $row;
                unset($row['get_value_card_type']);
                $res[$key] = $row;
                if ($now > $row['end_time']) {
                    $res[$key]['status'] = false;
                } else {
                    $res[$key]['status'] = true;
                }
                /* 先判断是否被使用，然后判断是否开始或过期 */
                $res[$key]['vc_value'] = $this->dscRepository->getPriceFormat($row['vc_value']);
                $res[$key]['use_condition'] = condition_format($row['use_condition']);
                $res[$key]['card_money'] = $this->dscRepository->getPriceFormat($row['card_money']);
                $res[$key]['bind_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['bind_time']);
                $res[$key]['end_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $row['end_time']);
            }
        }

        return ['user_list' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }

    /**
     * 优惠券列表
     * @return array
     * @throws \Exception
     */
    public function getUserCouponList()
    {
        $filter['user_id'] = $_REQUEST['user_id'] ?? 0;;
        $filter['use_type'] = $_REQUEST['use_type'] ?? 0;
        $filter['sort_by'] = 'id';
        $filter['sort_order'] = 'DESC';
        $filter['page'] = $_REQUEST['page'] ?? 0;
        $filter['start'] = $_REQUEST['start'] ?? 0;
        $filter['ru_id'] = $_REQUEST['ru_id'] ?? 0;
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        $filter['now_time'] = TimeRepository::getGmTime();

        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }

        $res = CouponsUser::where('is_delete', 0)->where('user_id', $filter['user_id']);
        //可使用
        if ($filter['use_type'] == 0) {
            $res = $res->where('is_use', 0)->where('order_id', 0);
        }

        //已使用
        if ($filter['use_type'] == 1) {
            $res = $res->where('is_use', 1)->where('order_id', '>', 0);
        }

        //已过期
        if ($filter['use_type'] == 2) {
            $res = $res->where('is_use', 0)->where('order_id', 0);
        }

        $res = $res->whereHasIn('getCoupons', function ($query) use ($filter) {

            $query = $query->where('status', COUPON_STATUS_EFFECTIVE);

            //可使用
            if ($filter['use_type'] == 0) {
                $whereTime = $filter['now_time'];
                $query = $query->whereRaw("IF(valid_type > 1, receive_end_time >= '$whereTime', cou_end_time >= '$whereTime')");
            }

            //已过期
            if ($filter['use_type'] == 2) {
                $whereTime = $filter['now_time'];
                $query = $query->whereRaw("IF(valid_type > 1, receive_end_time < '$whereTime', cou_end_time < '$whereTime')");
            }

            //关键字
            if ($filter['keywords']) {
                $query = $query->where('cou_name', 'like', '%' . $filter['keywords'] . '%');
            }

            //商家
            if ($filter['ru_id'] > 0) {
                $query->where('ru_id', $filter['ru_id']);
            }
        });

        $filter['record_count'] = $res->count();
        $filter = page_and_size($filter);

        $res = $res->orderBy('uc_id', 'desc')
            ->offset(($filter['page'] - 1) * $filter['page_size'])
            ->limit($filter['page_size']);

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            /* 加载语言 */
            $common = lang('common');
            $coupons = lang('coupons');
            $lang = array_merge($common, $coupons);

            $cou_id = BaseRepository::getKeyPluck($res, 'cou_id');
            $cou_id = BaseRepository::getArrayUnique($cou_id);
            $couponList = CouponDataHandleService::getCouponsDataList($cou_id, ['*', 'cou_money as cou_money']);

            $ru_id = BaseRepository::getKeyPluck($couponList, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            $order_id = BaseRepository::getKeyPluck($res, 'order_id');
            $orderList = OrderDataHandleService::orderDataList($order_id, ['order_id', 'order_sn']);

            foreach ($res as $k => $v) {

                $v['get_coupons'] = $couponList[$v['cou_id']] ?? [];
                $v['get_order'] = $orderList[$v['order_id']] ?? [];

                $res[$k] = $v = array_merge($v, $v['get_coupons']);
                $res[$k]['order_id'] = $orderList[$v['order_id']]['order_id'] ?? '';
                $res[$k]['order_sn'] = $orderList[$v['order_id']]['order_sn'] ?? '';
                $res[$k]['cou_start_time'] = TimeRepository::getLocalDate("Y-m-d", $v['cou_start_time']);
                $res[$k]['cou_end_time'] = TimeRepository::getLocalDate("Y-m-d", $v['cou_end_time']);
                $res[$k]['cou_man'] = $this->dscRepository->getPriceFormat($v['cou_man']);
                $res[$k]['cou_money'] = $this->dscRepository->getPriceFormat($v['cou_money']);
                $res[$k]['is_use_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $v['is_use_time']);

                //可使用的店铺;
                $shop_name = $merchantList[$v['ru_id']]['shop_name'] ?? '';
                $res[$k]['store_name_coupons'] = sprintf($lang['use_limit'], $shop_name);
                $res[$k]['store_name'] = $shop_name;
                $res[$k]['cou_type_name'] = \App\Services\Coupon\CouponService::cou_type_name($v['cou_type']);
                // 是否过期
                $res[$k]['is_overdue'] = $v['cou_end_time'] < TimeRepository::getGmTime() ? 1 : 0;
            }
        }

        return ['user_list' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }

    /**
     * 移除用户优惠券
     * @param $uc_id
     * @param $user_id
     * @return bool
     */
    public function removeUserCoupon($uc_id, $user_id)
    {
        $count = CouponsUser::where('is_delete', 0)->where('uc_id', $uc_id)->where('user_id', $user_id)->count();
        if ($count) {
            CouponsUser::where('is_delete', 0)->where('uc_id', $uc_id)->delete();
            return true;
        }
        return false;
    }

    /**
     * 红包列表
     *
     * @return array
     * @throws \Exception
     */
    public function getUserBonusList()
    {
        $filter['user_id'] = $_REQUEST['user_id'] ?? 0;;
        $filter['use_type'] = $_REQUEST['use_type'] ?? 0;
        $filter['sort_by'] = 'id';
        $filter['sort_order'] = 'DESC';
        $filter['page'] = $_REQUEST['page'] ?? 0;
        $filter['start'] = $_REQUEST['start'] ?? 0;
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        $filter['ru_id'] = $_REQUEST['ru_id'] ?? 0;

        $now = TimeRepository::getGmTime();
        if (isset($_REQUEST['is_ajax']) && $_REQUEST['is_ajax'] == 1) {
            $filter['keywords'] = json_str_iconv($filter['keywords']);
        }

        $res = UserBonus::where('user_id', $filter['user_id'])->whereHasIn('getBonusType', function ($query) use ($filter) {
            if (!empty($filter['keywords'])) {
                $query->where('type_name', 'like', '%' . $filter['keywords'] . '%');
            }

            //商家
            if ($filter['ru_id'] > 0) {
                $query->where('user_id', $filter['ru_id']);
            }
        });

        //可使用
        if ($filter['use_type'] == 0) {
            $res = $res->where('used_time', 0)->where('order_id', 0)->where('start_time', '<', $now)->where('end_time', '>', $now);
        }

        //已使用
        if ($filter['use_type'] == 1) {
            $res = $res->where('used_time', '>', 0)->where('order_id', '>', 0);
        }

        //已过期
        if ($filter['use_type'] == 2) {
            $res = $res->where('used_time', 0)->where('order_id', 0)->where('end_time', '<', $now);
        }

        $filter['record_count'] = $res->count();
        $filter = page_and_size($filter);

        $res = $res->orderBy('bonus_id', 'desc')
            ->offset(($filter['page'] - 1) * $filter['page_size'])
            ->limit($filter['page_size']);

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {

            $bonus_type_id = BaseRepository::getKeyPluck($res, 'bonus_type_id');
            $bonusTypeList = BonusDataHandleService::getBonusTypeDataList($bonus_type_id);

            $ru_id = BaseRepository::getKeyPluck($bonusTypeList, 'user_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            $order_id = BaseRepository::getKeyPluck($res, 'order_id');
            $orderList = OrderDataHandleService::orderDataList($order_id, ['order_id', 'order_sn']);

            foreach ($res as $k => $v) {

                $v['get_bonus_type'] = $bonusTypeList[$v['bonus_type_id']] ?? [];

                $res[$k] = $v = array_merge($v, $v['get_bonus_type']);
                $res[$k]['get_bonus_type'] = $v = array_merge($v, $v['get_bonus_type']);
                $res[$k]['used_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $v['used_time']);
                $res[$k]['order_sn'] = $orderList[$v['order_id']]['order_sn'] ?? '';
                $res[$k]['type_money'] = $this->dscRepository->getPriceFormat($v['type_money'], true);
                $res[$k]['min_goods_amount'] = $this->dscRepository->getPriceFormat($v['min_goods_amount'], true);
                $res[$k]['send_by'] = $GLOBALS['_LANG']['send_by'][$v['send_type']];

                // 使用范围
                if ($v['usebonus_type'] == 0) {
                    $res[$k]['shop_name'] = $merchantList[$v['user_id']]['shop_name'] ?? '';
                } else {
                    $res[$k]['shop_name'] = lang('user.general_audience'); // 全场通用
                }
            }
        }

        return ['user_list' => $res, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }

    /**
     * 移除用户红包
     * @param int $bonus_id
     * @param int $user_id
     * @return bool
     */
    public function removeUserBonus($bonus_id = 0, $user_id = 0)
    {
        $count = UserBonus::where('bonus_id', $bonus_id)->where('user_id', $user_id)->where('order_id', 0)->count();
        if ($count) {
            UserBonus::where('bonus_id', $bonus_id)->delete();
            return true;
        }
        return false;
    }

    /**
     * 统计我的收藏
     *
     * @param int $user_id
     * @return bool
     */
    public function collectGoodsNum($user_id = 0)
    {
        if (empty($user_id)) {
            return 0;
        }

        $res = CollectGoods::where('user_id', $user_id);

        $res = $res->whereHasIn('getGoods', function ($query) {
            $query = $query->where('is_on_sale', 1)
                ->where('is_alone_sale', 1)
                ->where('is_delete', 0)
                ->where('is_show', 1);

            if (config('shop.review_goods') == 1) {
                $query->whereIn('review_status', [3, 4, 5]);
            }
        });

        return $res->count();

    }

    /**
     * 统计我的足迹
     *
     * @param int $user_id
     * @return bool
     */
    public function historyGoodsNum($user_id = 0)
    {
        if (empty($user_id)) {
            return 0;
        }

        // 仅显示70天内的浏览记录
        $time = TimeRepository::getLocalStrtoTime('-70 days');

        $res = GoodsHistory::where('user_id', $user_id)->where('add_time', '>=', $time);

        $res = $res->whereHasIn('getGoods', function ($query) {
            $query = $query->where('is_on_sale', 1)
                ->where('is_alone_sale', 1)
                ->where('is_delete', 0)
                ->where('is_show', 1);

            if (config('shop.review_goods') == 1) {
                $query->whereIn('review_status', [3, 4, 5]);
            }
        });

        return $res->count();
    }

    /**
     * 统计我的店铺
     *
     * @param int $user_id
     * @return bool
     */
    public function collectStoreNum($user_id = 0)
    {
        if (empty($user_id)) {
            return 0;
        }

        $res = CollectStore::where('user_id', $user_id)
            ->whereHasIn('getSellerShopinfo');

        return $res->count();
    }

    /**
     * 余额明细
     * @param $user_id
     * @param int $page
     * @param int $size
     * @return array
     */
    public function userMoneyAccountLogList($user_id, $page = 1, $size = 10, $order_sn, $month = '')
    {
        $res = AccountLog::where('user_id', $user_id)
            ->where('user_money', '<>', 0);

        if (!empty($order_sn)) {
            $res = $res->where('change_desc', 'like', '%' . $order_sn . '%');
        }

        if (!empty($month)) {
            $mon_first_day = $month . '-01';
            $mon_first_time = TimeRepository::getLocalStrtoTime($mon_first_day);
            $mon_last_time = TimeRepository::getLocalStrtoTime("$mon_first_day +1 month -1 day");
            $res = $res->where('change_time', '>=', $mon_first_time)->where('change_time', '<=', $mon_last_time);
        }

        $res = $res->orderBy('log_id', 'desc');

        $start = ($page - 1) * $size;

        if ($start > 0) {
            $res = $res->skip($start);
        }

        if ($size > 0) {
            $res = $res->take($size);
        }

        $res = BaseRepository::getToArrayGet($res);

        $account_log = [];
        if ($res) {
            $account_log = $this->setDateToMonth($res, $page, $size);
        }

        return $account_log;
    }

    /**
     * 按日期分组
     * 月份
     * @pram array
     * @return array
     * */
    private function setDateToMonth($arr = [], $page = 1, $size = 0)
    {
        $dy = TimeRepository::getLocalDate('Y', TimeRepository::getGmTime());
        $res = [];

        foreach ($arr as $key => $value) {
            $y = TimeRepository::getLocalDate('Y', $value['change_time']);
            $m = TimeRepository::getLocalDate('m', $value['change_time']);

            $value['change_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $value['change_time']);
            $value['short_change_desc'] = $this->dscRepository->subStr($value['change_desc'], 60);
            $value['amount'] = $value['user_money'];

            if ($dy == $y) {
                $res[intval($m) . lang('admin/user_rank.month')][] = $value;
            } else {
                $res[$y . lang('admin/common.nian') . intval($m) . lang('admin/user_rank.month')][] = $value;
            }
        }
        $array = [];
        $i = 0;

        foreach ($res as $k => $v) {
            $array[$i]['ymd'] = $k;
            $array[$i]['page'] = intval($page);
            $array[$i]['size'] = intval($size);
            $array[$i]['source_data'] = $v;
            $i++;
        }

        return $array;
    }

    /**
     * 获取操作日志
     * @param $user_id
     * @param $page
     * @param $size
     * @return array
     * @throws \Exception
     */
    public function getUserLog($user_id, $page, $size)
    {
        $res = UsersLog::where('user_id', $user_id);
        $res = $res->orderBy('log_id', 'desc');

        $start = ($page - 1) * $size;
        if ($start > 0) {
            $res = $res->skip($start);
        }

        if ($size > 0) {
            $res = $res->take($size);
        }

        $res = BaseRepository::getToArrayGet($res);

        $list = [];
        if ($res) {
            foreach ($res as $row) {
                $data['from'] = $row['logon_service'] ?? '';
                $data['change_city'] = $row['change_city'] ?? '';
                $data['login_type'] = lang('user.change_type_user.' . $row['change_type'] ?? 1);
                $data['change_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['change_time']);
                $data['ip'] = $row['ip_address'] ?? '';
                $list[] = $data;
            }
        }
        return $list;
    }

    /**
     * 重置密码发送邮件
     *
     * @param $email
     * @return array
     * @throws \Exception
     */
    public function resetEmail($email)
    {
        $users = Users::select('user_id', 'user_name')->where('email', $email);
        $users = BaseRepository::getToArrayFirst($users);
        if (empty($users)) {
            return ['error_code' => 1, 'msg' => 'faild'];
        }

        // 获取验证码
        $sms_code = rand(100000, 999999);

        Cache::put($email . 'dsc' . $users['user_id'], $sms_code, Carbon::now()->addMinutes(10));
        $tpl = get_mail_template('reset_password');

        $this->template->assign('user_name', $users['user_name']);
        $this->template->assign('code', $sms_code);
        $content = $this->template->fetch('str:' . $tpl['template_content']);

        $res = CommonRepository::sendEmail($users['user_name'], $email, $tpl['template_subject'], $content, $tpl['is_html']);
        return $res === true ? ['error_code' => 0, 'msg' => lang('user.validate_mail_ok')] : ['error_code' => 1, 'msg' => lang('common.sendemail_false')];
    }

    /**
     * 验证邮箱发送的验证码
     * @param $email
     * @param $code
     * @return array
     */
    public function verificationEmail($email, $code)
    {
        $result = ['error_code' => 1, 'msg' => 'faild'];
        $users = Users::select('user_id', 'user_name')->where('email', $email);
        $users = BaseRepository::getToArrayFirst($users);
        if (empty($users)) {
            return $result;
        }

        $key = $email . 'dsc' . $users['user_id'];

        if (Cache::has($key)) {
            if (Cache::get($key) == $code) {
                Cache::forget($key);
                return ['error_code' => 0, 'msg' => 'success'];
            }
        }
        return $result;
    }

    /**
     * 验证短信
     * @param $mobile
     * @param $code
     * @return bool
     */
    public function verificationSms($mobile, $code)
    {
        $client_id = '';

        $label = $client_id . $mobile;

        // 记录错误次数
        $errorNum = Cache::get($label . 'error', 0);

        // 错误验证码且超过3次，直接返回错误
        if ((Cache::get($label) != $code) || $errorNum > 3) {
            Cache::put($label . 'error', $errorNum + 1, Carbon::now()->addMinutes(1));
            return ['error_code' => 1, 'msg' => lang('user.bind_mobile_code_error')];
        } else {
            return ['error_code' => 0, 'msg' => 'success'];
        }
    }
}

<?php

use App\Libraries\Pager;
use App\Models\BonusType;
use App\Models\OrderGoods;
use App\Models\OrderInfo;
use App\Models\PackageGoods;
use App\Models\PayCard;
use App\Models\PayCardType;
use App\Models\SellerShopinfo;
use App\Models\Shipping;
use App\Models\StoreOrder;
use App\Models\UserAddress;
use App\Models\UserBonus;
use App\Models\UserOrderNum;
use App\Models\UserRank;
use App\Models\Users;
use App\Models\UsersVatInvoicesInfo;
use App\Models\ValueCard;
use App\Models\ValueCardRecord;
use App\Models\ValueCardType;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Flow\FlowUserService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Order\OrderService;
use App\Services\Merchant\MerchantDataHandleService;

/**
 * 修改个人资料（Email, 性别，生日)
 *
 * @access  public
 * @param array $profile array_keys(user_id int, email string, sex int, birthday string);
 *
 * @return  boolen      $bool
 */
function edit_profile($profile = [])
{
    if (empty($profile) || empty($profile['user_id'])) {
        $GLOBALS['err']->add($GLOBALS['_LANG']['not_login']);
        return false;
    }

    $cfg = [];
    $cfg['user_id'] = $profile['user_id'];

    $row = Users::select('user_name', 'mobile_phone')
        ->where('user_id', $profile['user_id']);
    $row = BaseRepository::getToArrayFirst($row);

    $cfg['username'] = $row ? $row['user_name'] : '';

    if (isset($profile['sex'])) {
        $cfg['gender'] = intval($profile['sex']);
    }

    if (!empty($profile['email'])) {
        if (!CommonRepository::getMatchEmail($profile['email'])) {
            $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['email_invalid'], $profile['email']));

            return false;
        }
        $cfg['email'] = $profile['email'];
    }

    //手机号码
    if (!empty($profile['mobile_phone'])) {
        $mobile = $row ? $row['mobile_phone'] : '';

        if ($mobile != $profile['mobile_phone'] && $GLOBALS['_CFG']['sms_signin'] == 1) {
            if (!empty($profile['mobile_code'])) {
                if ($profile['mobile_phone'] != session('sms_mobile') || $profile['mobile_code'] != session('sms_mobile_code')) {
                    $GLOBALS['err']->add($GLOBALS['_LANG']['phone_check_code']);
                    return false;
                }
            } else {
                $profile['mobile_phone'] = $mobile;
            }
        }
        $cfg['mobile_phone'] = $profile['mobile_phone'];
    }

    if (!empty($profile['birthday'])) {
        $cfg['bday'] = $profile['birthday'];
    }

    if (!$GLOBALS['user']->edit_user($cfg)) {
        if ($GLOBALS['user']->error == ERR_EMAIL_EXISTS) {
            $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['email_exist'], $profile['email']));
        } elseif ($GLOBALS['user']->error == ERR_PHONE_EXISTS) {
            $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['phone_exist'], $profile['mobile_phone']));
        } else {
            $GLOBALS['err']->add('DB ERROR!');
        }

        return false;
    }

    /* 过滤非法的键值 */
    $other_key_array = ['msn', 'qq', 'office_phone', 'home_phone'];
    foreach ($profile['other'] as $key => $val) {
        //删除非法key值
        if (!in_array($key, $other_key_array)) {
            unset($profile['other'][$key]);
        } else {
            $profile['other'][$key] = htmlspecialchars(trim($val)); //防止用户输入javascript代码
        }
    }

    /* 修改在其他资料 */
    if (!empty($profile['other'])) {
        Users::where('user_id', $profile['user_id'])->update($profile['other']);
    }

    return true;
}

/**
 * 获取用户帐号信息
 *
 * @param $user_id
 * @return array
 */
function get_profile($user_id)
{

    /* 会员帐号信息 */
    $info = [];
    $infos = Users::where('user_id', $user_id);
    $infos = BaseRepository::getToArrayFirst($infos);

    if (empty($infos)) {
        return [];
    }

    $infos['user_name'] = addslashes($infos['user_name']);

    $row = $GLOBALS['user']->get_profile_by_name($infos['user_name']); //获取用户帐号信息

    session([
        'email' => $row['email']
    ]);

    /* 会员等级 */
    if ($infos['user_rank'] > 0) {
        $row = UserRank::where('rank_id', $infos['user_rank']);
    } else {
        $row = UserRank::where('min_points', '<=', intval($infos['rank_points']))
            ->orderBy('min_points', 'desc');
    }

    $row = BaseRepository::getToArrayFirst($row);

    if ($row) {
        $info['rank_name'] = $row['rank_name'];
    } else {
        $info['rank_name'] = $GLOBALS['_LANG']['undifine_rank'];
    }

    $time = TimeRepository::getGmTime();
    $cur_date = TimeRepository::getLocalDate('Y-m-d H:i:s', $time);

    /* 会员红包 */
    $bonus = UserBonus::select('bonus_type_id')
        ->where('user_id', $user_id)
        ->where('order_id', 0);

    $where = [
        'cur_date' => $cur_date
    ];
    $bonus = $bonus->whereHasIn('getBonusType', function ($query) use ($where) {
        $query->where('use_start_date', '<=', $where['cur_date'])
            ->where('use_end_date', '>', $where['cur_date']);
    });

    $bonus = $bonus->with('getBonusType');

    $bonus = BaseRepository::getToArrayGet($bonus);

    if ($bonus) {
        foreach ($bonus as $key => $row) {
            $row = BaseRepository::getArrayMerge($row, $row['get_bonus_type']);

            $row['type_money'] = isset($row['type_money']) ? $row['type_money'] : 0;

            $row['type_money'] = app(DscRepository::class)->getPriceFormat($row['type_money'], false);

            $bonus[$key] = $row;
        }
    }

    $info['discount'] = session('discount', 1) * 100 . "%";
    $info['email'] = session('email', '');
    $info['user_name'] = $infos['user_name'];
    $info['rank_points'] = isset($infos['rank_points']) ? $infos['rank_points'] : '';
    $info['pay_points'] = isset($infos['pay_points']) ? $infos['pay_points'] : 0;
    $info['user_money'] = isset($infos['user_money']) ? $infos['user_money'] : 0;
    $info['sex'] = isset($infos['sex']) ? $infos['sex'] : 0;
    $info['birthday'] = isset($infos['birthday']) ? $infos['birthday'] : '';
    $info['question'] = isset($infos['question']) ? htmlspecialchars($infos['question']) : '';

    $info['user_money'] = app(DscRepository::class)->getPriceFormat($info['user_money'], false);
    $info['pay_points'] = $info['pay_points'] . $GLOBALS['_CFG']['integral_name'];
    $info['bonus'] = $bonus;
    $info['qq'] = $infos['qq'];
    $info['msn'] = $infos['msn'];
    $info['office_phone'] = $infos['office_phone'];
    $info['home_phone'] = $infos['home_phone'];
    $info['mobile_phone'] = $infos['mobile_phone'];
    $info['passwd_question'] = $infos['passwd_question'];
    $info['passwd_answer'] = $infos['passwd_answer'];
    $info['is_validate'] = $infos['is_validated'];
    $info['nick_name'] = !empty($infos['nick_name']) ? $infos['nick_name'] : $infos['user_name'];
    $info['user_picture'] = app(DscRepository::class)->getImagePath($infos['user_picture']);

    return $info;
}

/**
 * 用户收货地址信息
 *
 * @param int $user_id
 * @return array
 */
function get_new_consignee_list($user_id = 0)
{
    if (empty($user_id)) {
        return [];
    }

    $res = UserAddress::where('user_id', $user_id);

    $res = $res->with([
        'getRegionProvince',
        'getRegionCity',
        'getRegionDistrict',
        'getRegionStreet'
    ]);

    $res = $res->take(config('app.address_count', 50))->orderBy('address_id', 'DESC');

    $res = BaseRepository::getToArrayGet($res);

    $arr = [];
    if ($res) {
        foreach ($res as $key => $row) {
            $arr[$key]['address_id'] = $row['address_id'];
            $arr[$key]['consignee'] = $row['consignee'];

            $arr[$key]['address'] = $row['address'];
            $arr[$key]['email'] = $row['email'];
            $arr[$key]['mobile'] = $row['mobile'];
            $arr[$key]['tel'] = $row['tel'];
            $arr[$key]['zipcode'] = $row['zipcode'];
            $arr[$key]['sign_building'] = $row['sign_building'];
            $arr[$key]['best_time'] = $row['best_time'];

            $arr[$key]['province_id'] = $row['province'];
            $arr[$key]['city_id'] = $row['city'];
            $arr[$key]['district_id'] = $row['district'];
            $arr[$key]['street_id'] = $row['street'] ?? 0;

            $province = $row['get_region_province'] ?? [];
            $city = $row['get_region_city'] ?? [];
            $district = $row['get_region_district'] ?? [];
            $street = $row['get_region_street'] ?? [];

            $arr[$key]['province_name'] = $province['region_name'] ?? '';
            $arr[$key]['city_name'] = $city['region_name'] ?? '';
            $arr[$key]['district_name'] = $district['region_name'] ?? '';
            $arr[$key]['street_name'] = $street['region_name'] ?? '';

            // 四个直辖市
            if ($arr[$key]['province_name'] == $arr[$key]['city_name'] || in_array($row['province'], ['110000', '120000', '310000', '500000'])) {
                $region = $arr[$key]['city_name'] . ' ' . $arr[$key]['district_name'] . ' ' . $arr[$key]['street_name'] ?? '';
            } else {
                $region = $arr[$key]['province_name'] . ' ' . $arr[$key]['city_name'] . ' ' . $arr[$key]['district_name'] . ' ' . $arr[$key]['street_name'] ?? '';
            }
            $arr[$key]['region'] = $region . ' ' . $row['address'];

            // 简短地址展示 仅显示在PC商品详情
            $arr[$key]['region_simple'] = $region;
        }
    }

    return $arr;
}

function get_user_address_info($address_id, $user_id)
{
    /* 取默认地址 */
    $res = UserAddress::where('address_id', $address_id)->where('user_id', $user_id);
    $res = BaseRepository::getToArrayFirst($res);

    return $res;
}

//ecmoban模板堂 --zhuo end

/**
 *  给指定用户添加一个指定红包
 *
 * @access  public
 * @param int $user_id 用户ID
 * @param string $bouns_sn 红包序列号
 *
 * @return  boolen      $result
 */
function add_bonus($user_id, $bouns_sn, $password)
{
    /* 查询红包序列号是否已经存在 */
    $row = UserBonus::where('bonus_sn', $bouns_sn)
        ->where('bonus_password', $password);

    $row = $row->with([
        'getBonusType'
    ]);

    $row = BaseRepository::getToArrayFirst($row);

    if ($row) {
        if ($row['user_id'] == 0) {

            //红包没有被使用
            $bonus = BonusType::select('send_end_date', 'use_end_date')
                ->where('type_id', $row['bonus_type_id'])
                ->where('review_status', 3);

            $bonus = BaseRepository::getToArrayFirst($bonus);

            $now = gmtime();
            if ($bonus && $now > $bonus['use_end_date']) {
                $GLOBALS['err']->add($GLOBALS['_LANG']['bonus_use_expire']);
                return false;
            }

            $bonus_info = $row['get_bonus_type'] ?? [];

            if (empty($bonus_info)) {
                $bonus_info = [
                    'date_type' => 0,
                    'valid_period' => 0,
                    'use_start_date' => '',
                    'use_end_date' => '',
                ];
            }

            $other = [
                'user_id' => $user_id,
                'bind_time' => gmtime()
            ];
            if ($bonus_info['valid_period'] > 0) {
                $other['start_time'] = $other['bind_time'];
                $other['end_time'] = $other['bind_time'] + $bonus_info['valid_period'] * 3600 * 24;
            } else {
                $other['start_time'] = $bonus_info['use_start_date'];
                $other['end_time'] = $bonus_info['use_end_date'];
            }
            $result = UserBonus::where('bonus_id', $row['bonus_id'])
                ->update($other);

            if ($result) {
                return true;
            } else {
                return $GLOBALS['db']->errorMsg();
            }
        } else {
            if ($row['user_id'] == $user_id) {
                //红包已经添加过了。
                $GLOBALS['err']->add($GLOBALS['_LANG']['bonus_is_used']);
            } else {
                //红包被其他人使用过了。
                $GLOBALS['err']->add($GLOBALS['_LANG']['bonus_is_used_by_other']);
            }

            return false;
        }
    } else {
        //红包不存在
        $GLOBALS['err']->add($GLOBALS['_LANG']['bonus_not_exist']);
        return false;
    }
}

/**
 * 给指定用户添加一张储值卡
 *
 * @param $user_id 用户ID
 * @param $value_card 储值卡序列号
 * @param $password
 * @return int
 */
function add_value_card($user_id, $value_card, $password)
{
    /* 查询储值卡序列号是否已经存在 */
    $row = ValueCard::where('value_card_sn', $value_card)
        ->where('value_card_password', $password);
    $row = BaseRepository::getToArrayFirst($row);

    if ($row) {

        if ($row['use_status'] == 0) {
            return 6;
        }

        if ($row['user_id'] == 0) {

            $time = TimeRepository::getGmTime();

            //储值卡未被绑定
            $vc_type = ValueCardType::where('id', $row['tid']);
            $vc_type = BaseRepository::getToArrayFirst($vc_type);

            $other = [
                'user_id' => $user_id,
                'bind_time' => $time
            ];

            if ($row['end_time']) {
                if ($time > $row['end_time']) {
                    $GLOBALS['err']->add($GLOBALS['_LANG']['vc_use_expire']);
                    return 1;
                }
            } else {
                $other['end_time'] = TimeRepository::getLocalStrtoTime("+" . $vc_type['vc_indate'] . " months ");
            }

            $limit = ValueCard::where('user_id', $user_id)
                ->where('tid', $row['tid'])
                ->count();

            if ($limit >= $vc_type['vc_limit']) {
                $GLOBALS['err']->add($GLOBALS['_LANG']['vc_limit_expire']);
                return 5;
            }

            $result = ValueCard::where('vid', $row['vid'])
                ->update($other);

            if ($result) {
                return 0;
            } else {
                return $GLOBALS['db']->errorMsg();
            }
        } else {
            if ($row['user_id'] == $user_id) {
                //储值卡已添加。
                $GLOBALS['err']->add($GLOBALS['_LANG']['vc_is_used']);
                return 2;
            } else {
                //储值卡已被绑定。
                $GLOBALS['err']->add($GLOBALS['_LANG']['vc_is_used_by_other']);
                return 3;
            }
        }
    } else {
        //储值卡不存在
        return 4;
    }
}

/**
 *  使用一张充值卡
 *
 * @access  public
 * @param int $user_id 用户ID
 * @param string $value_card 储值卡序列号
 *
 * @return  boolen      $result
 */
function use_pay_card($user_id, $vid, $pay_card, $password)
{
    /* 查询储值卡序列号是否已经存在 */
    $row = PayCard::where('card_number', $pay_card)
        ->where('card_psd', $password);

    $row = $row->with([
        'getPayCardType' => function ($query) {
            $query->select('type_id', 'type_money');
        }
    ]);

    $row = BaseRepository::getToArrayFirst($row);

    if ($row) {
        $row = BaseRepository::getArrayMerge($row, $row['get_pay_card_type']);
    }

    $valueCardType = ValueCardType::select('is_rec', 'vc_dis')
        ->whereHasIn('getValueCard', function ($query) use ($vid) {
            $query->where('vid', $vid);
        });

    $valueCardType = BaseRepository::getToArrayFirst($valueCardType);

    $is_rec = $valueCardType ? $valueCardType['is_rec'] : 0;
    $vc_dis = $valueCardType ? $valueCardType['vc_dis'] : 0;

    if ($row) {
        if ($row['user_id'] == 0 && $is_rec) {

            //储值卡未被绑定
            $use_end_date = PayCardType::where('type_id', $row['c_id'])->value('use_end_date');

            $now = gmtime();
            if ($now > $use_end_date) {
                $GLOBALS['err']->add($GLOBALS['_LANG']['vc_use_expire']);
                return false;
            }

            $other = [
                'user_id' => $user_id,
                'used_time' => gmtime()
            ];
            $result = PayCard::where('id', $row['id'])
                ->update($other);

            if ($result) {
                $res = ValueCard::where('vid', $vid)->increment('card_money', $row['type_money']);

                if ($res) {
                    $other = [
                        'vc_id' => $vid,
                        'add_val' => $row['type_money'],
                        'vc_dis' => $vc_dis,
                        'record_time' => gmtime(),
                        'change_desc' => sprintf(lang('user.label_pay_sn'), $row['card_number'])
                    ];
                    ValueCardRecord::insert($other);

                    return true;
                } else {
                    $other = [
                        'user_id' => 0,
                        'used_time' => 0
                    ];
                    PayCard::where('id', $row['id'])
                        ->update($other);

                    return $GLOBALS['db']->errorMsg();
                }
            } else {
                return $GLOBALS['db']->errorMsg();
            }
        } else {
            //充值卡已使用或改储值卡无法被充值
            $GLOBALS['err']->add($GLOBALS['_LANG']['pc_is_used']);

            return false;
        }
    } else {
        //储值卡不存在
        return false;
    }
}

/**
 * 取消一个用户订单
 *
 * @param int $order_id 订单ID
 * @param int $user_id 用户ID
 *
 * @return bool
 */
function cancel_order($order_id = 0, $user_id = 0)
{
    if (empty($order_id)) {
        return false;
    }

    /* 查询订单信息，检查状态 */
    $order = OrderInfo::where('order_id', $order_id);
    $order = BaseRepository::getToArrayFirst($order);

    if (empty($order)) {
        $GLOBALS['err']->add(lang('user.order_exist'));
        return false;
    }

    // 如果用户ID大于0，检查订单是否属于该用户
    if ($user_id > 0 && $order['user_id'] != $user_id) {
        $GLOBALS['err']->add(lang('user.no_priv'));

        return false;
    }

    // 订单状态只能是“未确认”或“已确认”
    if ($order['order_status'] != OS_UNCONFIRMED && $order['order_status'] != OS_CONFIRMED) {
        $GLOBALS['err']->add(lang('user.current_os_not_unconfirmed'));

        return false;
    }

    // 发货状态只能是“未发货”
    if ($order['shipping_status'] != SS_UNSHIPPED) {
        $GLOBALS['err']->add(lang('user.current_ss_not_cancel'));

        return false;
    }

    // 如果付款状态是“已付款”、“付款中”，不允许取消，要取消和商家联系
    if ($order['pay_status'] != PS_UNPAYED) {
        $GLOBALS['err']->add(lang('user.current_ps_not_cancel'));

        return false;
    }

    //将用户订单设置为取消
    $res = OrderInfo::where('order_id', $order_id)->where('pay_status', '<>', PS_PAYED)
        ->update(['order_status' => OS_CANCELED]);

    if ($res) {

        /* 记录log */
        order_action($order['order_sn'], OS_CANCELED, $order['shipping_status'], PS_UNPAYED, lang('user.buyer_cancel'), lang('common.buyer'));

        if ($order['main_count'] > 0) {
            $childOrder = OrderInfo::where('main_order_id', $order_id)->select('order_id', 'order_sn', 'shipping_status');
            $childOrder = BaseRepository::getToArrayGet($childOrder);

            if ($childOrder) {
                foreach ($childOrder as $k => $v) {
                    $childArr = [
                        'bonus_id' => 0,
                        'bonus' => 0,
                        'integral' => 0,
                        'integral_money' => 0,
                        'surplus' => 0,
                        'order_status' => OS_CANCELED
                    ];

                    $child_up = OrderInfo::where('order_id', $v['order_id'])->where('pay_status', '<>', PS_PAYED)
                        ->update($childArr);

                    if ($child_up) {
                        /* 记录log */
                        order_action($v['order_sn'], OS_CANCELED, $v['shipping_status'], PS_UNPAYED, lang('user.buyer_cancel'), lang('common.buyer'));
                    }
                }
            }
        }

        /* 退回订单消费储值卡金额 */
        return_card_money($order_id);

        /* 退货用户余额、积分、红包、优惠券 */
        if ($order['user_id'] > 0 && $order['surplus'] > 0) {
            $change_desc = sprintf(lang('user.return_surplus_on_cancel'), $order['order_sn']);
            log_account_change($order['user_id'], $order['surplus'], 0, 0, 0, $change_desc);
        }
        if ($order['user_id'] > 0 && $order['integral'] > 0) {
            $change_desc = sprintf(lang('user.return_integral_on_cancel'), $order['order_sn']);
            log_account_change($order['user_id'], 0, 0, 0, $order['integral'], $change_desc);
        }
        if ($order['user_id'] > 0 && $order['bonus_id'] > 0) {
            change_user_bonus($order['bonus_id'], $order['order_id'], false);
        }
        if ($order['user_id'] > 0 && $order['uc_id'] > 0) {
            unuse_coupons($order['order_id'], $order['uc_id']);
        }

        /* 如果使用库存，且下订单时减库存，则增加库存 */
        if ($GLOBALS['_CFG']['use_storage'] == '1' && $GLOBALS['_CFG']['stock_dec_time'] == SDT_PLACE) {
            change_order_goods_storage($order['order_id'], false, 1, 3);
        }

        /* 修改订单 */
        $arr = [
            'bonus_id' => 0,
            'bonus' => 0,
            'uc_id' => 0,
            'coupons' => 0,
            'integral' => 0,
            'integral_money' => 0,
            'surplus' => 0
        ];
        update_order($order['order_id'], $arr);

        $order_nopay = UserOrderNum::where('user_id', $user_id)->value('order_nopay');
        $order_nopay = $order_nopay ? intval($order_nopay) : 0;

        /* 更新会员订单信息 */
        if ($order_nopay > 0) {
            $dbRaw = [
                'order_nopay' => "order_nopay - 1",
            ];
            $dbRaw = BaseRepository::getDbRaw($dbRaw);
            UserOrderNum::where('user_id', $user_id)->where('order_nopay', '>', 0)->update($dbRaw);
        }

        return true;
    } else {
        return $GLOBALS['db']->errorMsg();
    }
}

/**
 * 获取指订单的详情
 *
 * @param $order_id
 * @param int $user_id
 * @return array
 */
function get_order_detail($order_id, $user_id = 0)
{
    load_helper('order');

    $order_id = intval($order_id);
    if ($order_id <= 0) {
        $GLOBALS['err']->add($GLOBALS['_LANG']['invalid_order_id']);

        return false;
    }
    $order = order_info($order_id);

    $time = TimeRepository::getGmTime();

    // 延时收货
    $order['allow_order_delay'] = 0;
    if ($order['pay_status'] == PS_PAYED && $order['shipping_status'] == SS_SHIPPED) {

        $auto_delivery_time = config('shop.auto_delivery_time') ?? 0;
        $auto_delivery_time = $auto_delivery_time > 0 && $auto_delivery_time > $order['auto_delivery_time'] ? $auto_delivery_time : $order['auto_delivery_time'];
        $auto_delivery_time = $auto_delivery_time * 24 * 3600;

        $auto_delivery_time = $order['shipping_time'] + $auto_delivery_time; // 延迟收货截止天数
        $order_delay_day = isset($GLOBALS['_CFG']['order_delay_day']) && $GLOBALS['_CFG']['order_delay_day'] > 0 ? intval($GLOBALS['_CFG']['order_delay_day']) : 3;

        // 用户已申请延时收货次数
        $order['user_order_delay_num'] = \App\Models\OrderDelayed::where('order_id', $order['order_id'])->where('review_status', '<>', 1)->count();
        $order_delay_num = $GLOBALS['_CFG']['order_delay_num'] ?? 3;
        if (($auto_delivery_time - $time) / 86400 < $order_delay_day && $order['user_order_delay_num'] < $order_delay_num) {
            $order['allow_order_delay'] = 1;
        }
        $order['auto_delivery_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $auto_delivery_time);
    }
    //检查订单是否属于该用户
    if ($user_id > 0 && $user_id != $order['user_id']) {
        $GLOBALS['err']->add($GLOBALS['_LANG']['no_priv']);

        return false;
    }

    /* 对发货号处理 */
    if (!empty($order['invoice_no'])) {
        $shipping_code = Shipping::where('shipping_id', $order['shipping_id'])->value('shipping_code');

        $shippingObject = CommonRepository::shippingInstance($shipping_code);

        $order['shipping_code_name'] = !is_null($shippingObject) ? $shippingObject->get_code_name() : '';
    }

    /* 只有未确认才允许用户修改订单地址 */
    if ($order['order_status'] == OS_UNCONFIRMED) {
        $order['allow_update_address'] = 1; //允许修改收货地址
    } else {
        $order['allow_update_address'] = 0;
    }

    /* 获取订单中实体商品数量 */
    $order['exist_real_goods'] = app(FlowUserService::class)->existRealGoods($order_id);

    $order['pay_online'] = '';
    /* 如果是未付款状态，生成支付按钮 */
    if (($order['pay_status'] == PS_PAYED_PART) || ($order['pay_status'] == PS_UNPAYED && ($order['order_status'] == OS_UNCONFIRMED || $order['order_status'] == OS_CONFIRMED))
    ) {
        /*
         * 在线支付按钮
         */
        //支付方式信息
        $payment_info = payment_info($order['pay_id']);

        //无效支付方式
        if (!$payment_info) {
            $order['pay_online'] = '';
        } else {
            //ecmoban模板堂 --will改 start
            //pc端如果使用的是app的支付方式，也不生成支付按钮
            if (substr($payment_info['pay_code'], 0, 4) == 'pay_') {
                $order['pay_online'] = '';
            } else {
                if ($payment_info) {
                    //取得支付信息，生成支付代码
                    $payment = unserialize_config($payment_info['pay_config']);

                    //获取需要支付的log_id
                    $order['log_id'] = get_paylog_id($order['order_id'], $pay_type = PAY_ORDER);
                    $order['user_name'] = Users::where('user_id', $order['user_id'])->value('user_name');
                    $order['pay_desc'] = $payment_info['pay_desc'];

                    if (strpos($payment_info['pay_code'], 'pay_') === false) {
                        $payObject = CommonRepository::paymentInstance($payment_info['pay_code']);

                        /* 取得在线支付方式的支付按钮 */
                        if (!is_null($payObject)) {
                            $order['pay_online'] = $payObject->get_code($order, $payment);
                        }
                    }
                }
            }
        }
    }

    /* 无配送时的处理 */
    $order['shipping_id'] == -1 and $order['shipping_name'] = $GLOBALS['_LANG']['shipping_not_need'];

    /* 其他信息初始化 */
    $order['how_oos_name'] = $order['how_oos'];
    $order['how_surplus_name'] = $order['how_surplus'];

    /* 虚拟商品付款后处理 */
    if ($order['pay_status'] != PS_UNPAYED) {
        /* 取得已发货的虚拟商品信息 */
        $virtual_goods = get_virtual_goods($order_id, true);
        $virtual_card = [];
        foreach ($virtual_goods as $code => $goods_list) {
            /* 只处理虚拟卡 */
            if ($code == 'virtual_card') {
                foreach ($goods_list as $goods) {
                    if ($info = virtual_card_result($order['order_sn'], $goods)) {
                        $virtual_card[] = ['goods_id' => $goods['goods_id'], 'goods_name' => $goods['goods_name'], 'info' => $info];
                    }
                }
            }
            /* 处理超值礼包里面的虚拟卡 */
            if ($code == 'package_buy') {
                foreach ($goods_list as $goods) {
                    $packageGoodsList = PackageGoods::select('goods_id')
                        ->where('package_id', $goods['goods_id']);

                    $packageGoodsList = BaseRepository::getToArrayGet($packageGoodsList);

                    if ($packageGoodsList) {

                        $goods_id = BaseRepository::getKeyPluck($packageGoodsList, 'goods_id');
                        $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, 'goods_id', 'goods_name');

                        $sql = [
                            'where' => [
                                [
                                    'name' => 'extension_code',
                                    'value' => 'virtual_card'
                                ]
                            ]
                        ];
                        $goodsList = BaseRepository::getArraySqlGet($goodsList, $sql, 1);
                        $goods_id = BaseRepository::getKeyPluck($goodsList, 'goods_id');

                        $sql = [
                            'whereIn' => [
                                [
                                    'name' => 'goods_id',
                                    'value' => $goods_id
                                ]
                            ]
                        ];
                        $packageGoodsList = BaseRepository::getArraySqlGet($packageGoodsList, $sql);

                        if ($packageGoodsList) {
                            foreach ($packageGoodsList as $val) {

                                $goods = $goodsList[$val['goods_id']] ?? [];

                                $val = BaseRepository::getArrayMerge($val, $goods);

                                if ($info = virtual_card_result($order['order_sn'], $val)) {
                                    $virtual_card[] = ['goods_id' => $goods['goods_id'], 'goods_name' => $goods['goods_name'], 'info' => $info];
                                }
                            }
                        }
                    }
                }
            }
        }
        $var_card = deleteRepeat($virtual_card);
        $GLOBALS['smarty']->assign('virtual_card', $var_card);
    }

    /* 确认时间 支付时间 发货时间 */
    if ($order['confirm_time'] > 0 && ($order['order_status'] == OS_CONFIRMED || $order['order_status'] == OS_SPLITED || $order['order_status'] == OS_SPLITING_PART)) {
        $order['confirm_time'] = sprintf($GLOBALS['_LANG']['confirm_time'], TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $order['confirm_time']));
    } else {
        $order['confirm_time'] = '';
    }
    if ($order['pay_time'] > 0 && $order['pay_status'] != PS_UNPAYED) {
        $order['pay_time'] = sprintf($GLOBALS['_LANG']['pay_time'], TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $order['pay_time']));
    } else {
        $order['pay_time'] = '';
    }
    if ($order['shipping_time'] > 0 && in_array($order['shipping_status'], [SS_SHIPPED, SS_RECEIVED])) {
        $order['shipping_time'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $order['shipping_time']);
    } else {
        $order['shipping_time'] = '';
    }

    if (!empty($order['confirm_take_time'])) {
        $order['confirm_take_time'] = sprintf($GLOBALS['_LANG']['confirm_time'], TimeRepository::getLocalDate($GLOBALS['_CFG']['time_format'], $order['confirm_take_time']));
    } else {
        $order['confirm_take_time'] = '';
    }

    //IM or 客服
    if ($GLOBALS['_CFG']['customer_service'] == 0) {
        $ru_id = 0;
    } else {
        $ru_id = $order['ru_id'];
    }

    // 店铺名称
    $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);
    $shop_information = $merchantList[$order['ru_id']] ?? [];
    $order['is_im'] = $shop_information['is_im'] ?? 0; //平台是否允许商家使用"在线客服";
    $order['cross_warehouse_name'] = $shop_information['cross_warehouse_name'] ?? '';

    $order['shop_information'] = $shop_information;

    if ($ru_id == 0) {
        //判断平台是否开启了IM在线客服
        $kf_im_switch = $shop_information['kf_im_switch'] ?? 0;

        $order['is_dsc'] = $kf_im_switch ? true : false;
    } else {
        $order['is_dsc'] = false;
    }

    $chat = app(DscRepository::class)->chatQq($shop_information);

    $order['kf_type'] = $chat['kf_type'];
    $order['kf_ww'] = $chat['kf_ww'];
    $order['kf_qq'] = $chat['kf_qq'];

    /* 取得区域名 */
    $order['region'] = app(OrderService::class)->getOrderUserRegion($order_id);

    $order['store_id'] = StoreOrder::where('order_id', $order_id)->value('store_id');

    if (isset($order['pay_code']) && $order['pay_code'] == 'bank') {
        // 上传支付凭证
        $bank_transfer = \Illuminate\Support\Facades\DB::table('order_info_bank_transfer')->where('order_id', $order['order_id'])->first();
        $order['pay_document'] = !empty($bank_transfer->pay_document) ? app(DscRepository::class)->getImagePath($bank_transfer->pay_document) : '';
    }

    return $order;
}

/**
 * 获得退换货订单信息
 *
 * @param $ret_id
 * @return array
 */
function get_return_detail($ret_id = 0)
{
    load_helper('order');

    $ret_id = intval($ret_id);
    if (empty($ret_id)) {
        return [];
    }

    $order = return_order_info($ret_id);

    return $order;
}

/**
 *  合并指定用户订单
 *
 * @access  public
 * @param string $from_order 合并的从订单号
 * @param string $to_order 合并的主订单号
 *
 * @return  boolen      $bool
 */
function merge_user_order($from_order, $to_order, $user_id = 0)
{
    if ($user_id > 0) {
        /* 检查订单是否属于指定用户 */
        if (strlen($to_order) > 0) {
            $order_user = OrderInfo::where('order_sn', $to_order)->value('user_id');

            if ($order_user != $user_id) {
                $GLOBALS['err']->add($GLOBALS['_LANG']['no_priv']);
            }
        } else {
            $GLOBALS['err']->add($GLOBALS['_LANG']['order_sn_empty']);
            return false;
        }
    }

    $result = merge_order($from_order, $to_order);
    if ($result === true) {
        return true;
    } else {
        $GLOBALS['err']->add($result);
        return false;
    }
}

/**
 *  保存用户收货地址
 *
 * @access  public
 * @param array $address array_keys(consignee string, email string, address string, zipcode string, tel string, mobile stirng, sign_building string, best_time string, order_id int)
 * @param int $user_id 用户ID
 *
 * @return  boolen  $bool
 */
function save_order_address($address = [], $user_id = 0)
{
    $GLOBALS['err']->clean();
    /* 数据验证 */
    empty($address['consignee']) and $GLOBALS['err']->add($GLOBALS['_LANG']['consigness_empty']);
    empty($address['address']) and $GLOBALS['err']->add($GLOBALS['_LANG']['address_empty']);
    $address['order_id'] == 0 and $GLOBALS['err']->add($GLOBALS['_LANG']['order_id_empty']);
    if (empty($address['email'])) {
        $GLOBALS['err']->add($GLOBALS['email_empty']);
    } else {
        if (!CommonRepository::getMatchEmail($address['email'])) {
            $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['email_invalid'], $address['email']));
        }
    }
    if ($GLOBALS['err']->error_no > 0) {
        return false;
    }

    /* 检查订单状态 */
    $OrderRep = app(OrderService::class);

    $where = [
        'order_id' => $address['order_id'],
        'user_id' => $user_id
    ];
    $row = $OrderRep->getOrderInfo($where);

    if ($row) {
        if ($row['order_status'] != OS_UNCONFIRMED) {
            $GLOBALS['err']->add($GLOBALS['_LANG']['require_unconfirmed']);
            return false;
        }

        OrderInfo::where('order_id', $address['order_id'])->update($address);

        return true;
    } else {
        /* 订单不存在 */
        $GLOBALS['err']->add($GLOBALS['_LANG']['order_exist']);
        return false;
    }
}

/**
 *
 * @access  public
 * @param int $user_id 用户ID
 * @param int $num 列表显示条数
 * @param int $start 显示起始位置
 *
 * @return  array       $arr             红保列表
 */
function get_user_bouns_list($user_id, $num = 10, $start = 0)
{
    $res = UserBonus::select('bonus_type_id', 'bonus_sn', 'order_id')
        ->where('user_id', $user_id);

    $res = $res->with([
        'getBonusType' => function ($query) {
            $query->select('type_id', 'type_name', 'type_money', 'min_goods_amount', 'use_start_date', 'use_end_date');
        }
    ]);

    if ($start > 0) {
        $res = $res->skip($start);
    }

    if ($num > 0) {
        $res = $res->take($num);
    }

    $res = BaseRepository::getToArrayGet($res);

    $arr = [];

    $day = getdate();
    $cur_date = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);

    if ($res) {
        foreach ($res as $row) {
            $row = BaseRepository::getArrayMerge($row, $row['get_bonus_type']);

            /* 先判断是否被使用，然后判断是否开始或过期 */
            if (empty($row['order_id'])) {
                /* 没有被使用 */
                if ($row['use_start_date'] > $cur_date) {
                    $row['status'] = $GLOBALS['_LANG']['not_start'];
                } elseif ($row['use_end_date'] < $cur_date) {
                    $row['status'] = $GLOBALS['_LANG']['overdue'];
                } else {
                    $row['status'] = $GLOBALS['_LANG']['not_use'];
                }
            } else {
                $row['status'] = '<a href="user_order.php?act=order_detail&order_id=' . $row['order_id'] . '" >' . $GLOBALS['_LANG']['had_use'] . '</a>';
            }

            $row['use_startdate'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['date_format'], $row['use_start_date']);
            $row['use_enddate'] = TimeRepository::getLocalDate($GLOBALS['_CFG']['date_format'], $row['use_end_date']);

            $arr[] = $row;
        }
    }

    return $arr;
}

/**
 * 去除虚拟卡中重复数据
 *
 *
 */
function deleteRepeat($array)
{
    $_card_sn_record = [];
    foreach ($array as $_k => $_v) {
        foreach ($_v['info'] as $__k => $__v) {
            if (in_array($__v['card_sn'], $_card_sn_record)) {
                unset($array[$_k]['info'][$__k]);
            } else {
                array_push($_card_sn_record, $__v['card_sn']);
            }
        }
    }
    return $array;
}

/*
* 我的发票列表
* @param 	int		$user_id	用户ID
* @return 	array	$list		列表
*/
function invoice_list($user_id = 0, $record_count, $page, $pagesize = 10)
{
    $config = ['header' => $GLOBALS['_LANG']['pager_2'], "prev" => "<i><<</i>" . $GLOBALS['_LANG']['page_prev'], "next" => "" . $GLOBALS['_LANG']['page_next'] . "<i>>></i>", "first" => $GLOBALS['_LANG']['page_first'], "last" => $GLOBALS['_LANG']['page_last']];

    $pagerParams = [
        'total' => $record_count,
        'listRows' => $pagesize,
        'id' => $user_id,
        'page' => $page,
        'funName' => 'user_inv_gotoPage',
        'pageType' => 1,
        'config_zn' => $config
    ];
    $user_order = new Pager($pagerParams);
    $pager = $user_order->fpage([0, 4, 5, 6, 9]);

    $res = OrderInfo::where('main_count', 0)->where('is_delete', 0)->where('user_id', $user_id);

    $res = $res->with([
        'goods' => function ($query) {
            $query = $query->select('goods_id', 'order_id', 'goods_name', 'goods_number', 'goods_price', 'goods_attr');
            $query->with([
                'getGoods' => function ($query) {
                    $query->select('goods_id', 'goods_name', 'goods_thumb');
                }
            ]);
        }
    ]);

    $res = $res->orderBy('order_id', 'desc');

    $start = ($page - 1) * $pagesize;
    if ($start > 0) {
        $res = $res->skip($start);
    }

    if ($pagesize > 0) {
        $res = $res->take($pagesize);
    }

    $res = BaseRepository::getToArrayGet($res);

    $arr = [];
    if ($res) {

        $ruIdList = BaseRepository::getKeyPluck($res, 'ru_id');
        $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ruIdList);

        foreach ($res as $k => $val) {
            $arr[$k]['ru_id'] = $val['ru_id'];
            $arr[$k]['add_time'] = TimeRepository::getLocalDate('Y-m-d H:i:s', $val['add_time']);

            if ($val['invoice_type'] == 1) {
                $vat = UsersVatInvoicesInfo::where('user_id', $user_id);
                $vat = BaseRepository::getToArrayFirst($vat);
                $arr[$k]['vat_info'] = $vat;
            }

            $arr[$k]['invoice_type'] = $val['invoice_type'] ? lang('invoice.vat_invoice') : lang('invoice.plain_invoice');

            if (empty($val['inv_content']) && $val['invoice_type'] == 0) {
                $arr[$k]['inv_status'] = lang('invoice.not_open_invoice');
                $arr[$k]['invoice_type'] = '';
            } else {
                $arr[$k]['inv_status'] = $arr[$k]['invoice_type'];
            }

            $arr[$k]['inv_content'] = $val['inv_content'] ?? '';
            $arr[$k]['inv_payee'] = $val['inv_payee'] ?? '';
            $arr[$k]['order_sn'] = $val['order_sn'] ?? '';

            $arr[$k]['mobile'] = $val['mobile'];
            $arr[$k]['shop_name'] = $merchantList[$val['ru_id']]['shop_name'] ?? '';

            $shop_information = $merchantList[$val['ru_id']] ?? []; //通过ru_id获取到店铺信息;
            $arr[$k]['is_im'] = isset($shop_information['is_im']) ? $shop_information['is_im'] : ''; //平台是否允许商家使用"在线客服";
            $basic_info = $shop_information;

            $chat = app(DscRepository::class)->chatQq($basic_info);

            if ($GLOBALS['_CFG']['customer_service'] == 0) {
                $ru_id = 0;
            } else {
                $ru_id = $val['ru_id'];
            }

            //判断当前商家是平台,还是入驻商家
            if ($ru_id == 0) {
                //判断平台是否开启了IM在线客服
                $kf_im_switch = SellerShopinfo::where('ru_id', 0)->value('kf_im_switch');
                if ($kf_im_switch) {
                    $arr[$k]['is_dsc'] = true;
                } else {
                    $arr[$k]['is_dsc'] = false;
                }
            } else {
                $arr[$k]['is_dsc'] = false;
            }
            $arr[$k]['kf_type'] = isset($basic_info['kf_type']) ? $basic_info['kf_type'] : '';
            $arr[$k]['kf_ww'] = $chat['kf_ww'];
            $arr[$k]['kf_qq'] = $chat['kf_qq'];
            if (!empty($val['goods'])) {
                foreach ($val['goods'] as $key => $res) {
                    $res['goods_thumb'] = app(DscRepository::class)->getImagePath($res['get_goods']['goods_thumb'] ?? '');
                    $val['goods'][$key] = $res;
                }
            }

            $arr[$k]['order_goods'] = $val['goods'];

            $arr[$k]['order_goods_count'] = count($arr[$k]['order_goods']);
        }
    }

    $order_list = ['order_list' => $arr, 'pager' => $pager, 'record_count' => $record_count];
    return $order_list;
}

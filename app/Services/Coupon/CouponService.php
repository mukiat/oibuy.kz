<?php

namespace App\Services\Coupon;

use App\Models\CollectStore;
use App\Models\Coupons;
use App\Models\CouponsUser;
use App\Models\Goods;
use App\Models\Users;
use App\Repositories\Common\ArrRepository;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Activity\CouponsService;
use App\Services\Goods\GoodsDataHandleService;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;
use App\Services\Order\OrderDataHandleService;
use App\Services\User\UserCommonService;

/**
 * 优惠券
 *
 * Class CouponService
 * @package App\Services\Coupon
 */
class CouponService
{
    protected $dscRepository;
    protected $merchantCommonService;
    protected $userCommonService;
    private $lang;

    public function __construct(
        UserCommonService $userCommonService,
        DscRepository $dscRepository,
        MerchantCommonService $merchantCommonService
    )
    {
        $this->userCommonService = $userCommonService;
        $this->dscRepository = $dscRepository;
        $this->merchantCommonService = $merchantCommonService;

        /* 加载语言 */
        $common = lang('common');
        $coupons = lang('coupons');
        $this->lang = array_merge($common, $coupons);
    }

    /**
     * 优惠券列表 全场券、会员券、免邮券
     *
     * @param int $user_id
     * @param int $status
     * @param int $page
     * @param int $size
     * @param int $cou_id
     * @return array
     * @throws \Exception
     */
    public function listCoupon($user_id = 0, $status = 0, $page = 1, $size = 10, $cou_id = 0)
    {
        $time = TimeRepository::getGmTime();
        $begin = ($page - 1) * $size;

        $cou_type = [
            VOUCHER_ALL, VOUCHER_USER, VOUCHER_SHIPPING, VOUCHER_SHOP_CONLLENT
        ];
        $res = Coupons::where('review_status', 3)->whereIn('cou_type', $cou_type)
            ->whereRaw("IF(valid_type > 1, receive_start_time <= '$time' and receive_end_time >= '$time', cou_start_time <= '$time' and cou_end_time >= '$time')")
            ->where('status', COUPON_STATUS_EFFECTIVE);

        if ($status >= 0) {
            //取出所有优惠券(注册送、购物送除外)
            if ($status == 0) {
                $res->where('cou_type', VOUCHER_ALL); // 全场券
            } elseif ($status == 1) {
                $res->where('cou_type', VOUCHER_USER);// 会员券
            } elseif ($status == 2) {
                $res->where('cou_type', VOUCHER_SHIPPING);// 免邮券
            }
        }

        if ($cou_id > 0) {
            $res->where('cou_id', $cou_id);
        }

        $res = $res->orderBy('cou_id', 'desc')
            ->offset($begin)
            ->limit($size);

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {

            $cou_id = BaseRepository::getKeyPluck($res, 'cou_id');
            $couponsUserList = CouponDataHandleService::getCouponsUserDataList([], $cou_id, $user_id, ['uc_id', 'cou_id', 'user_id', 'is_use', 'is_delete']);

            $ru_id = BaseRepository::getKeyPluck($res, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($res as $k => $v) {
                $res[$k]['begintime'] = TimeRepository::getLocalDate("Y-m-d", $v['cou_start_time']);
                $res[$k]['endtime'] = TimeRepository::getLocalDate("Y-m-d", $v['cou_end_time']);
                $res[$k]['img'] = asset('images/coupons_default.png');

                $merchant = $merchantList[$v['ru_id']] ?? [];

                //可使用的店铺;
                $res[$k]['store_name'] = sprintf($this->lang['use_limit'], $merchant['shop_name'] ?? '');

                $res[$k]['cou_type'] = $v['cou_type'];
                $res[$k]['cou_type_name'] = self::cou_type_name($v['cou_type']);

                // 是否使用
                if ($user_id > 0) {

                    $sql = [
                        'where' => [
                            [
                                'name' => 'is_delete',
                                'value' => 0,
                            ],
                            [
                                'name' => 'cou_id',
                                'value' => $v['cou_id'],
                            ],
                            [
                                'name' => 'user_id',
                                'value' => $user_id,
                            ]
                        ]
                    ];

                    $couponsUser = BaseRepository::getArraySqlFirst($couponsUserList, $sql);
                }

                $is_use = $couponsUser['is_use'] ?? 0;
                $res[$k]['is_use'] = empty($is_use) ? 0 : $is_use; //好券集市(用户登入了的话,重新获取用户优惠券的使用情况)

                // 是否过期
                $res[$k]['is_overdue'] = $v['cou_end_time'] < TimeRepository::getGmTime() ? 1 : 0;

                //是否已经领取过了
                if ($user_id > 0) {

                    $sql = [
                        'where' => [
                            [
                                'name' => 'is_delete',
                                'value' => 0,
                            ],
                            [
                                'name' => 'cou_id',
                                'value' => $v['cou_id'],
                            ],
                            [
                                'name' => 'user_id',
                                'value' => $user_id,
                            ]
                        ]
                    ];

                    $user_num = BaseRepository::getArraySqlGet($couponsUserList, $sql);
                    $user_num = BaseRepository::getArrayCount($user_num);

                    if ($user_num > 0 && $v['cou_user_num'] <= $user_num) {
                        $res[$k]['cou_is_receive'] = 1;
                    } else {
                        $res[$k]['cou_is_receive'] = 0;
                    }
                }

                // 能否领取 优惠劵总张数 1 不能 0 可以领取
                $sql = [
                    'where' => [
                        [
                            'name' => 'is_delete',
                            'value' => 0,
                        ],
                        [
                            'name' => 'cou_id',
                            'value' => $v['cou_id'],
                        ]
                    ]
                ];

                $cou_num = BaseRepository::getArraySqlGet($couponsUserList, $sql);
                $cou_num = BaseRepository::getArrayCount($cou_num);

                $res[$k]['enable_ling'] = (!empty($cou_num) && $cou_num >= $v['cou_total']) ? 1 : 0;
            }

            return $res;
        }
    }


    /**
     * 任务集市- 购物券
     *
     * @param int $user_id
     * @param int $page
     * @param int $size
     * @return array
     * @throws \Exception
     */
    public function get_coupons_goods_list($user_id = 0, $page = 1, $size = 10)
    {
        $time = TimeRepository::getGmTime();

        $start = ($page - 1) * $size;

        $model = Coupons::where('review_status', 3)
            ->where('cou_type', VOUCHER_SHOPING)
            ->whereRaw("IF(valid_type > 1, receive_start_time <= '$time' and receive_end_time >= '$time', cou_start_time <= '$time' and cou_end_time >= '$time')")
            ->where('status', COUPON_STATUS_EFFECTIVE);

        $cou_goods = $count = $model;

        $cou_goods = $cou_goods->orderBy('cou_id', 'DESC')
            ->offset($start)
            ->limit($size);

        $cou_goods = BaseRepository::getToArrayGet($cou_goods);

        if ($cou_goods) {

            $ru_id = BaseRepository::getKeyPluck($cou_goods, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            $cou_ok_goods = BaseRepository::getKeyPluck($cou_goods, 'cou_ok_goods');
            $cou_ok_goods = BaseRepository::getImplode($cou_ok_goods);
            $cou_ok_goods = BaseRepository::getExplode($cou_ok_goods);
            $cou_ok_goods = BaseRepository::getArrayUnique($cou_ok_goods);
            $cou_ok_goods = ArrRepository::getArrayUnset($cou_ok_goods);

            $goodsList = $cou_ok_goods ? GoodsDataHandleService::GoodsDataList($cou_ok_goods, ['goods_id', 'goods_name', 'goods_thumb', 'is_alone_sale', 'is_delete', 'is_on_sale']) : [];

            $cou_id = BaseRepository::getKeyPluck($cou_goods, 'cou_id');
            $couponsUserList = CouponDataHandleService::getCouponsUserDataList([], $cou_id, $user_id, ['uc_id', 'cou_id', 'user_id', 'is_use', 'is_delete']);

            foreach ($cou_goods as $k => $v) {
                $cou_goods[$k]['begintime'] = TimeRepository::getLocalDate("Y-m-d", $v['cou_start_time']);
                $cou_goods[$k]['endtime'] = TimeRepository::getLocalDate("Y-m-d", $v['cou_end_time']);

                $merchant = $merchantList[$v['ru_id']] ?? [];

                //可使用的店铺;
                $cou_goods[$k]['store_name'] = sprintf($this->lang['use_limit'], $merchant['shop_name'] ?? '');

                $cou_goods[$k]['cou_type_name'] = $v['cou_type'] == VOUCHER_SHOPING ? $this->lang['vouchers_shoping'] : '';

                //商品图片(没有指定商品时为默认图片)
                if ($v['cou_ok_goods']) {
                    $cou_ok_goods = BaseRepository::getExplode($v['cou_ok_goods']);
                    $cou_ok_goods = ArrRepository::getArrayUnset($cou_ok_goods);

                    $sql = [
                        'where' => [
                            [
                                'name' => 'is_alone_sale',
                                'value' => 1
                            ],
                            [
                                'name' => 'is_delete',
                                'value' => 0
                            ],
                            [
                                'name' => 'is_on_sale',
                                'value' => 1
                            ]
                        ],
                        'whereIn' => [
                            [
                                'name' => 'goods_id',
                                'value' => $cou_ok_goods
                            ]
                        ]
                    ];
                    $goods = BaseRepository::getArraySqlGet($goodsList, $sql);

                    if ($goods) {
                        foreach ($goods as $key => $value) {
                            $goods[$key]['goods_thumb'] = $this->dscRepository->getImagePath($value['goods_thumb']);
                        }
                    }

                    $cou_goods[$k]['cou_ok_goods_list'] = $goods;
                } else {
                    $cou_goods[$k]['cou_ok_goods_list'][0]['goods_thumb'] = asset('images/coupons_default.png');
                }

                // 是否过期
                $cou_goods[$k]['is_overdue'] = $v['cou_end_time'] < $time ? 1 : 0;

                //是否已经领取过了
                if ($user_id) {
                    $sql = [
                        'where' => [
                            [
                                'name' => 'is_delete',
                                'value' => 0,
                            ],
                            [
                                'name' => 'cou_id',
                                'value' => $v['cou_id'],
                            ],
                            [
                                'name' => 'user_id',
                                'value' => $user_id,
                            ]
                        ]
                    ];

                    $user_num = BaseRepository::getArraySqlGet($couponsUserList, $sql);
                    $user_num = BaseRepository::getArrayCount($user_num);

                    if ($user_num > 0 && $v['cou_user_num'] <= $user_num) {
                        $cou_goods[$k]['cou_is_receive'] = 1;
                    } else {
                        $cou_goods[$k]['cou_is_receive'] = 0;
                    }
                }

                // 能否领取 优惠劵总张数 1 不能 0 可以领取
                $sql = [
                    'where' => [
                        [
                            'name' => 'is_delete',
                            'value' => 0,
                        ],
                        [
                            'name' => 'cou_id',
                            'value' => $v['cou_id'],
                        ]
                    ]
                ];

                $cou_num = BaseRepository::getArraySqlGet($couponsUserList, $sql);
                $cou_num = BaseRepository::getArrayCount($cou_num);

                $cou_goods[$k]['enable_ling'] = (!empty($cou_num) && $cou_num >= $v['cou_total']) ? 1 : 0;
            }
        }

        return $cou_goods;
    }

    /**
     * 领取优惠券
     *
     * @param int $user_id
     * @param int $cou_id
     * @return array
     * @throws \Exception
     */
    public function receiveCoupon($user_id = 0, $cou_id = 0)
    {
        if ($user_id > 0) {
            //会员等级
            $user_rank = Users::where('user_id', $user_id)->value('user_rank');

            $rest = Coupons::select('cou_type', 'cou_ok_user', 'valid_type', 'cou_start_time', 'cou_end_time', 'receive_start_time', 'receive_end_time')
                ->where('cou_id', $cou_id);
            $rest = BaseRepository::getToArrayFirst($rest);

            $time = TimeRepository::getGmTime();
            if (($rest['valid_type'] == 1 && $time < $rest['cou_start_time']) || ($rest['valid_type'] == 2 && $time < $rest['receive_start_time'])) {
                $result = ['error' => 0, 'msg' => sprintf(lang('coupons.receive_time'), TimeRepository::getLocalDate("Y-m-d H:i:s", $rest['receive_start_time']))];
                return $result;
            } elseif (($rest['valid_type'] == 1 && $rest['cou_end_time'] < $time) || ($rest['valid_type'] == 2 && $rest['receive_end_time'] < $time)) {
                $result = ['error' => 0, 'msg' => lang('coupons.receive_overdue')];
                return $result;
            }

            $type = $rest['cou_type'];      //优惠券类型
            $cou_rank = $rest['cou_ok_user'];  //可以使用优惠券的rank
            $ranks = BaseRepository::getExplode($cou_rank);

            if ($type == 2 || $type == 4 && $ranks != 0) {
                if (in_array($user_rank, $ranks)) {
                    $result = $this->getCoups($cou_id, $user_id);
                } else {
                    $result = [
                        'error' => 0,
                        'msg' => lang('coupons.notuser_notget'), //没有优惠券不能领取
                    ];
                }
            } else {
                $result = $this->getCoups($cou_id, $user_id);
            }
        } else {
            $result = [
                'error' => 0,
                'msg' => $this->lang['not_login'],
            ];
        }
        return $result;
    }

    /**
     * 获取优惠券
     *
     * @param int $cou_id
     * @param int $user_id
     * @return array
     */
    public function getCoups($cou_id = 0, $user_id = 0)
    {
        $result = [
            'error' => 0,
            'msg' => $this->lang['Coupon_redemption_failure'],
        ];
        
        $time = TimeRepository::getGmTime();

        $res = Coupons::where('review_status', 3)->where('cou_id', $cou_id)
            ->whereRaw("IF(valid_type > 1, receive_start_time <= '$time' and receive_end_time >= '$time', cou_start_time <= '$time' and cou_end_time >= '$time')")
            ->where('status', COUPON_STATUS_EFFECTIVE);
        $res = BaseRepository::getToArrayFirst($res);

        if (!empty($res)) {

            $couUserCount = CouponsUser::where('cou_id', $cou_id)->where('is_delete', 0)->count('cou_id');
            if ($couUserCount >= $res['cou_total']) {
                return $result;
            }

            $couponsUserList = CouponDataHandleService::getCouponsUserDataList([], $cou_id, $user_id, ['uc_id', 'cou_id', 'user_id', 'is_use', 'is_delete']);

            $sql = [
                'where' => [
                    [
                        'name' => 'is_delete',
                        'value' => 0
                    ],
                    [
                        'name' => 'cou_id',
                        'value' => $cou_id
                    ],
                    [
                        'name' => 'user_id',
                        'value' => $user_id
                    ]
                ]
            ];
            $couponsUserNum = BaseRepository::getArraySqlGet($couponsUserList, $sql);

            $num = BaseRepository::getArrayCount($couponsUserNum);

            $sql = [
                'where' => [
                    [
                        'name' => 'is_delete',
                        'value' => 0
                    ],
                    [
                        'name' => 'cou_id',
                        'value' => $cou_id
                    ]
                ]
            ];
            $couponsUserReceived = BaseRepository::getArraySqlGet($couponsUserList, $sql);
            $is_received = BaseRepository::getArrayCount($couponsUserReceived);

            //判断是否已经领取了,并且还没有使用(根据创建优惠券时设定的每人可以领取的总张数为准,防止超额领取)
            if ($res && $res['cou_user_num'] > $num) {

                //判断优惠券是否已经被领完了
                $cou_surplus = $res['cou_total'] - $is_received;
                if ($cou_surplus <= 0) {
                    return [
                        'error' => 0,
                        'msg' => $this->lang['lang_coupons_receive_failure'],
                    ];
                }

                if ($res['cou_type'] == VOUCHER_SHOP_CONLLENT) {
                    $is_collect = CollectStore::where('user_id', $user_id)->where('ru_id', $res['ru_id'])->count();
                    //添加关注
                    if ($is_collect < 1) {
                        $other = [
                            'user_id' => $user_id,
                            'ru_id' => $res['ru_id'],
                            'add_time' => $time,
                            'is_attention' => 1
                        ];
                        CollectStore::insert($other);
                    }
                }

                // 领取有效时间
                $valid_day_num = empty($res['valid_day_num']) ? 1 : $res['valid_day_num'];
                $valid_time = $valid_day_num * 24 * 3600;
                $valid_time = $time + $valid_time;

                //领取优惠券
                $data = [
                    'user_id' => $user_id,
                    'cou_id' => $cou_id,
                    'cou_money' => $res['cou_money'],
                    'uc_sn' => CommonRepository::couponSn(),
                    'valid_time' => $valid_time,
                    'add_time' => $time
                ];
                $insertGetId = CouponsUser::insertGetId($data);

                if ($insertGetId > 0) {
                    $result = [
                        'error' => 1,
                        'msg' => $this->lang['receive_success'],
                        'uc_id' => $insertGetId,
                    ]; //领取成功！感谢您的参与，祝您购物愉快
                }
            } else {
                $result = [
                    'error' => 0,
                    'msg' => sprintf($this->lang['Coupon_redemption_limit'], $num),
                ];
            }
        }

        return $result;
    }

    /**
     * 会员中心优惠券列表
     *
     * @param int $user_id
     * @param int $page
     * @param int $size
     * @param int $type
     * @return array
     * @throws \Exception
     */
    public function userCoupons($user_id = 0, $page = 1, $size = 10, $type = 0)
    {
        $begin = ($page - 1) * $size;
        $time = TimeRepository::getGmTime();

        $res = CouponsUser::select('*', 'cou_money as uc_money')->where('user_id', $user_id);

        if ($type == 0) {
            //领取的优惠券未使用
            $res->where('is_use', 0);

            $res = $res->whereHasIn('getCoupons', function ($query) use ($time) {
                $query->where('review_status', 3)
                    ->whereIn('cou_type', [VOUCHER_LOGIN, VOUCHER_SHOPING, VOUCHER_ALL, VOUCHER_USER, VOUCHER_SHIPPING, VOUCHER_SHOP_CONLLENT])
                    ->whereRaw("IF(valid_type = 1, cou_end_time >= '$time', 1)")
                    ->where('status', COUPON_STATUS_EFFECTIVE);
            });

        } elseif ($type == 1) {
            //已使用的
            $res->where('is_use', 1)->where('order_id', '>', 0);
        } elseif ($type == 2) {
            //已过期
            $res = $res->where('is_use', 0);

            $res = $res->whereHasIn('getCoupons', function ($query) use ($time) {
                $query->where('review_status', 3)
                    ->whereIn('cou_type', [VOUCHER_LOGIN, VOUCHER_SHOPING, VOUCHER_ALL, VOUCHER_USER, VOUCHER_SHIPPING, VOUCHER_SHOP_CONLLENT])
                    ->whereRaw("IF(valid_type = 1, cou_end_time < '$time', 1)")
                    ->where('status', COUPON_STATUS_EFFECTIVE);
            });
        }

        if ($type != 0 || $type != 2) {
            $res = $res->whereHasIn('getCoupons', function ($query) {
                $query->where('review_status', 3)
                    ->whereIn('cou_type', [VOUCHER_LOGIN, VOUCHER_SHOPING, VOUCHER_ALL, VOUCHER_USER, VOUCHER_SHIPPING, VOUCHER_SHOP_CONLLENT])
                    ->where('status', COUPON_STATUS_EFFECTIVE);
            });
        }

        $res = $res->offset($begin)
            ->limit($size);
        $res = BaseRepository::getToArrayGet($res);

        if ($res) {

            $cou_id = BaseRepository::getKeyPluck($res, 'cou_id');
            $cou_id = BaseRepository::getArrayUnique($cou_id);
            $couponList = CouponDataHandleService::getCouponsDataList($cou_id, ['*', 'cou_money as cou_money']);

            $ru_id = BaseRepository::getKeyPluck($couponList, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            $order_id = BaseRepository::getKeyPluck($res, 'order_id');
            $orderList = OrderDataHandleService::orderDataList($order_id, ['order_id', 'order_sn', 'add_time', 'coupons as order_coupons']);

            $cou_goods = BaseRepository::getKeyPluck($res, 'cou_goods');
            $cou_goods = BaseRepository::getImplode($cou_goods);
            $cou_goods = BaseRepository::getExplode($cou_goods);
            $cou_goods = BaseRepository::getArrayUnique($cou_goods);
            $cou_goods = ArrRepository::getArrayUnset($cou_goods);
            $goodsList = GoodsDataHandleService::GoodsDataList($cou_goods, ['goods_id', 'goods_name']);

            foreach ($res as $k => $v) {

                $add_time = $v['add_time'];
                $coupon = $couponList[$v['cou_id']] ?? [];
                $v = BaseRepository::getArrayMerge($v, $coupon);

                $cou_start_time = $v['cou_start_time'];
                $cou_end_time = $v['cou_end_time'];

                $is_valid = 1;
                if ($v['valid_type'] == 2) {
                    $cou_start_time = $add_time;
                    $cou_end_time = $v['valid_time'];
                    $is_valid = $time > $cou_end_time ? 0 : $is_valid;
                }

                $order = $orderList[$v['order_id']] ?? [];
                $v = BaseRepository::getArrayMerge($v, $order);

                $res[$k]['begintime'] = TimeRepository::getLocalDate("Y-m-d", $cou_start_time);
                $res[$k]['endtime'] = TimeRepository::getLocalDate("Y-m-d", $cou_end_time);
                $res[$k]['img'] = asset('images/coupons_default.png');

                $res[$k]['cou_man'] = $v['cou_man'] ?? 0;
                $res[$k]['order_sn'] = $v['order_sn'] ?? '';
                $res[$k]['order_coupons'] = $v['order_coupons'] ?? '';
                $res[$k]['cou_title'] = $v['cou_title'] ?? '';
                $res[$k]['add_time'] = TimeRepository::getLocalDate('Y-m-d', $v['add_time']); //订单生成时间即算优惠券使用时间

                //如果指定了使用的优惠券的商品,取出允许使用优惠券的商品
                if (!empty($v['cou_goods'])) {
                    $goods_ids = BaseRepository::getExplode($v['cou_goods']);

                    $sql = [
                        'whereIn' => [
                            [
                                'name' => 'goods_id',
                                'value' => $goods_ids
                            ]
                        ]
                    ];
                    $goods = BaseRepository::getArraySqlGet($goodsList, $sql);

                    $res[$k]['goods_list'] = $goods;
                }

                $merchant = $merchantList[$v['ru_id']] ?? [];

                //获取店铺名称区分平台和店铺(平台发的全平台用,商家发的商家店铺用)
                $res[$k]['store_name'] = sprintf($this->lang['use_limit'], $merchant['shop_name'] ?? '');

                $res[$k]['cou_type'] = $v['cou_type'] ?? 0;
                //格式化类型名称
                $res[$k]['cou_type_name'] = self::cou_type_name($v['cou_type']);

                // 是否过期
                $res[$k]['is_overdue'] = $v['cou_end_time'] < $time ? 1 : 0;

                /**
                 * 排除未过期
                 */
                if ($type == 2 && $is_valid == 1) {
                    unset($res[$k]);
                }
            }

            $res = array_values($res);
        }

        return $res;
    }

    /**
     * 优惠券类型名称
     * @param int $cou_type
     * @return string
     */
    public static function cou_type_name(int $cou_type = 0)
    {
        switch ($cou_type) {
            case VOUCHER_LOGIN:
                return trans('coupons.vouchers_login');
                break;
            case VOUCHER_SHOPING:
                return trans('coupons.vouchers_shoping');
                break;
            case VOUCHER_ALL:
                return trans('coupons.vouchers_all');
                break;
            case VOUCHER_USER:
                return trans('coupons.vouchers_user');
                break;
            case VOUCHER_SHIPPING:
                return trans('coupons.vouchers_shipping');
                break;
            case VOUCHER_SHOP_CONLLENT:
                return trans('coupons.vouchers_shop_conllent');
                break;
            default:
                return trans('coupons.unknown');
                break;
        }
    }

    /**
     * 商品详情优惠券列表
     *
     * @param int $user_id
     * @param int $goods_id
     * @param int $ru_id
     * @param int $size
     * @return array
     * @throws \Exception
     */
    public function goodsCoupons($user_id = 0, $goods_id = 0, $ru_id = 0, $size = 10)
    {
        //店铺优惠券 by wanglu
        $time = TimeRepository::getGmTime();

        $row = Coupons::where('review_status', 3)
            ->where('ru_id', $ru_id)
            ->whereRaw("IF(valid_type > 1, receive_start_time <= '$time' and receive_end_time >= '$time', cou_start_time <= '$time' and cou_end_time >= '$time')")
            ->where(function ($query) {
                $query->orWhere('cou_type', VOUCHER_ALL)
                    ->orWhere('cou_type', VOUCHER_USER);
            })
            ->whereRaw("((instr(cou_goods, $goods_id)  or (cou_goods = 0)))")
            ->where('status', COUPON_STATUS_EFFECTIVE);

        //获取会员等级id
        $user_rank = $this->userCommonService->getUserRankByUid($user_id);

        $user_rank = $user_rank['rank_id'] ?? 0;
        if ($user_rank > 0) {
            //获取符合会员等级的优惠券
            $row = $row->whereraw("CONCAT(',', cou_ok_user, ',') LIKE '%" . $user_rank . "%'");
        }

        $res = $row->orderBy('cou_id', 'DESC')
            ->limit($size);

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {
            $cou_id = BaseRepository::getKeyPluck($res, 'cou_id');
            $couponsUserList = CouponDataHandleService::getCouponsUserDataList([], $cou_id, $user_id, ['uc_id', 'cou_id', 'user_id', 'is_use', 'is_delete']);

            $seller_id = BaseRepository::getKeyPluck($res, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($seller_id);

            if ($goods_id > 0) {
                $cat_id = Goods::query()->where('goods_id', $goods_id)->value('cat_id');
            }
            $cat_id = $cat_id ?? 0;

            foreach ($res as $key => $value) {

                $couponsUser = [];
                if ($couponsUserList) {
                    $sql = [
                        'where' => [
                            [
                                'name' => 'cou_id',
                                'value' => $value['cou_id']
                            ]
                        ]
                    ];
                    $couponsUser = BaseRepository::getArraySqlGet($couponsUserList, $sql);
                }

                $res[$key]['get_coupons_user_list'] = $couponsUser;

                if ($value['use_type'] == 0) {
                    if ($value['ru_id'] != $ru_id) {
                        unset($res[$key]);
                        continue;
                    }
                }

                if (!empty($value['cou_goods'])) {
                    $spec_goods = BaseRepository::getExplode($value['cou_goods']);
                    if (!in_array($goods_id, $spec_goods)) {
                        unset($res[$key]);
                        continue;
                    }
                }

                if (!empty($value['spec_cat'])) {

                    $spec_cat = app(CouponsService::class)->getCouChildren($value['spec_cat']);
                    $spec_cat = BaseRepository::getExplode($spec_cat);

                    if (!in_array($cat_id, $spec_cat)) {
                        unset($res[$key]);
                        continue;
                    }
                }

                $res[$key]['cou_end_time'] = TimeRepository::getLocalDate('Y.m.d', $value['cou_end_time']);
                $res[$key]['cou_start_time'] = TimeRepository::getLocalDate('Y.m.d', $value['cou_start_time']);

                // 能否领取 优惠劵总张数 1 不能 0 可以领取
                $cou_num = [];
                if (!empty($res[$key]['get_coupons_user_list'])) {
                    $sql = [
                        'where' => [
                            [
                                'name' => 'is_delete',
                                'value' => 0,
                            ]
                        ]
                    ];

                    $cou_num = BaseRepository::getArraySqlGet($res[$key]['get_coupons_user_list'], $sql);
                }

                $res[$key]['enable_ling'] = (!empty($cou_num) && $cou_num >= $value['cou_total']) ? 1 : 0;

                $merchant = $merchantList[$value['ru_id']] ?? [];

                $res[$key]['shop_name'] = $merchant['shop_name'] ?? '';
                if (!empty($value['spec_cat'])) {
                    $res[$key]['cou_goods_name'] = lang('common.lang_goods_coupons.is_cate');
                } elseif (!empty($value['cou_goods'])) {
                    $res[$key]['cou_goods_name'] = lang('common.lang_goods_coupons.is_goods');
                } else {
                    $res[$key]['cou_goods_name'] = lang('common.lang_goods_coupons.is_all');
                }

                // 是否领取
                if ($user_id > 0) {

                    $user_num = 0;
                    if (!empty($res[$key]['get_coupons_user_list'])) {
                        $sql = [
                            'where' => [
                                [
                                    'name' => 'is_delete',
                                    'value' => 0,
                                ],
                                [
                                    'name' => 'user_id',
                                    'value' => $user_id,
                                ]
                            ]
                        ];
                        $user_num = BaseRepository::getArraySqlGet($res[$key]['get_coupons_user_list'], $sql);
                        $user_num = BaseRepository::getArrayCount($user_num);
                    }

                    if ($user_num > 0 && $value['cou_user_num'] <= $user_num) {
                        $res[$key]['cou_is_receive'] = 1;
                        unset($res[$key]);
                    } else {
                        $res[$key]['cou_is_receive'] = 0;
                    }
                }
            }
            $res = collect($res)->values()->all();
        }

        return ['res' => $res, 'total' => count($res)];
    }

    /**
     * 获取优惠券详情
     *
     * @param $cou_id
     * @return array
     */
    public function getDetail($cou_id)
    {
        $result = Coupons::where('review_status', 3)
            ->where('cou_id', $cou_id)
            ->where('status', COUPON_STATUS_EFFECTIVE);

        $result = BaseRepository::getToArrayFirst($result);

        return $result;
    }

    /**
     * 购物车领取优惠券列表
     *
     * @param $user_id
     * @param int $ru_id
     * @param int $size
     * @return array
     * @throws \Exception
     */
    public function getCouponsList($user_id, $ru_id = 0, $size = 10)
    {
        //店铺优惠券 by wanglu
        $time = TimeRepository::getGmTime();

        $user_rank = $this->userCommonService->getUserRankByUid($user_id);
        $user_rank = $user_rank['rank_id'] ?? 0;

        $res = Coupons::where('review_status', 3)
            ->where('ru_id', $ru_id)
            ->whereRaw("IF(valid_type > 1, receive_start_time <= '$time' and receive_end_time >= '$time', cou_start_time <= '$time' and cou_end_time >= '$time')")
            ->where('status', COUPON_STATUS_EFFECTIVE)
            ->where(function ($query) {
                $query->orWhere('cou_type', VOUCHER_ALL)
                    ->orWhere('cou_type', VOUCHER_USER);
            })
            ->whereRaw("((instr(cou_ok_user, $user_rank)  or (cou_goods = 0)))");

        $res = $res->orderBy('cou_id', 'DESC')
            ->limit($size);
        $res = BaseRepository::getToArrayGet($res);

        if ($res) {

            $cou_id = BaseRepository::getKeyPluck($res, 'cou_id');
            $couponsUserList = CouponDataHandleService::getCouponsUserDataList([], $cou_id, $user_id, ['uc_id', 'cou_id', 'user_id', 'is_use', 'is_delete']);

            foreach ($res as $key => $value) {
                $res[$key]['cou_end_time'] = TimeRepository::getLocalDate('Y.m.d', $value['cou_end_time']);
                $res[$key]['cou_start_time'] = TimeRepository::getLocalDate('Y.m.d', $value['cou_start_time']);
                // 是否领取
                if ($user_id > 0) {

                    $sql = [
                        'where' => [
                            [
                                'name' => 'is_delete',
                                'value' => 0,
                            ],
                            [
                                'name' => 'cou_id',
                                'value' => $value['cou_id'],
                            ],
                            [
                                'name' => 'user_id',
                                'value' => $user_id
                            ]
                        ]
                    ];
                    $user_num = BaseRepository::getArraySqlGet($couponsUserList, $sql);
                    $user_num = BaseRepository::getArrayCount($user_num);

                    if ($user_num > 0 && $value['cou_user_num'] <= $user_num) {
                        $res[$key]['cou_is_receive'] = 1;
                    } else {
                        $res[$key]['cou_is_receive'] = 0;
                    }
                }

                // 能否领取 优惠劵总张数 1 不能 0 可以领取
                $sql = [
                    'where' => [
                        [
                            'name' => 'is_delete',
                            'value' => 0,
                        ],
                        [
                            'name' => 'cou_id',
                            'value' => $value['cou_id'],
                        ]
                    ]
                ];

                $cou_num = BaseRepository::getArraySqlGet($couponsUserList, $sql);
                $cou_num = BaseRepository::getArrayCount($cou_num);

                $res[$key]['enable_ling'] = (!empty($cou_num) && $cou_num >= $value['cou_total']) ? 1 : 0;
            }
        }

        return $res;
    }
}

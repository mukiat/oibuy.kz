<?php

namespace App\Services\ValueCard;

use App\Exceptions\HttpException;
use App\Models\Cart;
use App\Models\PayCard;
use App\Models\PayCardType;
use App\Models\ValueCard;
use App\Models\ValueCardRecord;
use App\Models\ValueCardType;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\CommonRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\SessionRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Goods\GoodsDataHandleService;

class ValueCardService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 绑定储值卡
     *
     * @param $user_id
     * @param string $vc_num
     * @param string $vc_password
     * @return array
     * @throws \Exception
     */
    public function addCard($user_id, $vc_num = '', $vc_password = '')
    {
        /* 查询储值卡序列号是否已经存在 */
        $row = ValueCard::where('value_card_sn', $vc_num)
            ->where('value_card_password', $vc_password);
        $row = BaseRepository::getToArrayFirst($row);
        $result = [];
        if ($row) {
            $now = TimeRepository::getGmTime();
            if ($row['user_id'] == 0) {

                if ($row['use_status'] == 0) {
                    $result['error'] = 1;
                    $result['msg'] = lang('user.vc_use_invalid');
                    return $result;
                }

                //储值卡未被绑定
                $vc_type = ValueCardType::where('id', $row['tid']);
                $vc_type = BaseRepository::getToArrayFirst($vc_type);

                $other = [
                    'user_id' => $user_id,
                    'bind_time' => $now
                ];

                if ($row['end_time']) {
                    if ($now > $row['end_time']) {
                        $result['error'] = 1;
                        $result['msg'] = lang('user.vc_use_expire');
                        return $result;
                    }
                } else {
                    $other['end_time'] = TimeRepository::getLocalStrtoTime("+" . $vc_type['vc_indate'] . " months ");
                }

                $limit = ValueCard::where('user_id', $user_id)
                    ->where('tid', $row['tid'])
                    ->count();

                if ($limit >= $vc_type['vc_limit']) {
                    $result['error'] = 1;
                    $result['msg'] = lang('user.vc_limit_expire');
                    return $result;
                }

                $res = ValueCard::where('vid', $row['vid'])
                    ->update($other);

                if ($res) {
                    $result['error'] = 0;
                    $result['msg'] = lang('user.add_value_card_sucess');
                } else {
                    $result['error'] = 1;
                    $result['msg'] = lang('user.unknow_error');
                }
            } else {
                if ($row['user_id'] == $user_id) {
                    //储值卡已添加。
                    $result['error'] = 1;
                    $result['msg'] = lang('user.vc_is_used');
                } else {
                    //储值卡已被绑定。
                    $result['error'] = 1;
                    $result['msg'] = lang('user.vc_is_used_by_other');
                }
            }
        } else {
            //储值卡不存在
            $result['error'] = 1;
            $result['msg'] = lang('user.not_exist');
        }

        return $result;
    }

    /**
     * 详情
     *
     * @param int $user_id 会员ID
     * @param int $vc_id 储值卡详情
     * @return array
     */
    public function cardDetail($user_id = 0, $vc_id = 0)
    {
        $arr = [];
        if ($vc_id) {
            $res = ValueCardRecord::where('vc_id', $vc_id);

            $res = $res->with([
                'getOrder' => function ($query) use ($user_id) {
                    $query->select('order_id', 'order_sn')
                        ->where('user_id', $user_id)
                        ->where('main_count', 0);
                }
            ]);

            $res = $res->orderBy('rid', 'desc');
            $res = BaseRepository::getToArrayGet($res);

            if ($res) {
                foreach ($res as $key => $row) {
                    if (!empty($row['get_order']) || $row['order_id'] == 0) {
                        if ($row['use_val'] > 0 && $row['add_val'] > 0) {
                            $row['add_val'] = 0;
                            $arr[$key]['use_val'] = $row['use_val'] > 0 ? '+' . $this->dscRepository->getPriceFormat($row['use_val']) : $this->dscRepository->getPriceFormat($row['use_val']);
                        } else {
                            $arr[$key]['use_val'] = $row['use_val'] > 0 ? '-' . $this->dscRepository->getPriceFormat($row['use_val']) : $this->dscRepository->getPriceFormat($row['use_val']);
                        }

                        $arr[$key]['add_val'] = $row['add_val'] > 0 ? '+' . $this->dscRepository->getPriceFormat($row['add_val']) : $this->dscRepository->getPriceFormat($row['add_val']);

                        $arr[$key]['rid'] = $row['rid'];
                        $arr[$key]['order_sn'] = isset($row['get_order']['order_sn']) ? $row['get_order']['order_sn'] : '';
                        $arr[$key]['record_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['record_time']);
                    }
                }
            }
        }

        $arr = $arr ? array_values($arr) : [];

        return $arr;
    }

    /**
     * 充值绑定储值卡
     *
     * @param int $user_id
     * @param int $vid
     * @param int $pay_card
     * @param string $password
     * @return array
     * @throws HttpException
     */
    public function deposit($user_id = 0, $vid = 0, $pay_card = 0, $password = '')
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

        if (empty($row)) {
            throw new HttpException(trans('user.vc_money_not_exist'), 1);
        }

        $row = BaseRepository::getArrayMerge($row, $row['get_pay_card_type']);

        $model = ValueCardType::select('is_rec', 'vc_dis', 'id')
            ->whereHasIn('getValueCard', function ($query) use ($vid) {
                $query->where('vid', $vid);
            });

        $model = $model->with([
            'getValueCard' => function ($query) use ($vid) {
                $query->where('vid', $vid)->select('tid', 'user_id');
            }
        ]);

        $valueCardType = BaseRepository::getToArrayFirst($model);

        $is_rec = $valueCardType ? $valueCardType['is_rec'] : 0;
        $vc_dis = $valueCardType ? $valueCardType['vc_dis'] : 0;

        $result = [];

        if ($is_rec == 0) {
            throw new HttpException(trans('user.vc_add_error'), 1);
        }

        $valueCard = $valueCardType['get_value_card'] ?? [];
        if ($valueCard) {
            if ($user_id > 0 && $valueCard['user_id'] != $user_id) {
                throw new HttpException(trans('user.unauthorized_access'), 1);
            }
        }

        if ($row['user_id'] == 0 && $is_rec) {
            //储值卡未被绑定
            $use_end_date = PayCardType::where('type_id', $row['c_id'])->value('use_end_date');
            $now = TimeRepository::getGmTime();

            if ($now > $use_end_date) {
                throw new HttpException(trans('user.vc_money_expire'), 1);
            }

            $other = [
                'user_id' => $user_id,
                'used_time' => $now
            ];

            $pay = PayCard::where('id', $row['id'])
                ->update($other);

            if ($pay) {
                $res = ValueCard::where('vid', $vid)->increment('card_money', $row['type_money']);

                if ($res) {
                    $other = [
                        'vc_id' => $vid,
                        'add_val' => $row['type_money'],
                        'vc_dis' => $vc_dis,
                        'record_time' => $now,
                        'change_desc' => sprintf(lang('user.label_pay_sn'), $row['card_number'])
                    ];

                    ValueCardRecord::insert($other);
                    $result['error'] = 0;
                    $result['msg'] = lang('user.vc_money_use_success');
                    return $result;
                } else {
                    $other = [
                        'user_id' => 0,
                        'used_time' => 0
                    ];
                    PayCard::where('id', $row['id'])->update($other);

                    throw new HttpException(trans('user.unknow_error'), 1);
                }
            } else {
                throw new HttpException(trans('user.unknow_error'), 1);
            }
        } else {
            //充值卡已使用或改储值卡无法被充值
            throw new HttpException(trans('user.vc_money_is_used'), 1);
        }
    }

    /**
     * 储值卡列表
     *
     * @param int $user_id
     * @param int $page
     * @param int $size
     * @param array $rec_id
     * @return array
     * @throws \Exception
     */
    public function getUserValueCardList($user_id = 0, $page = 1, $size = 10, $rec_id = [])
    {
        $vidList = [];
        if (!empty($rec_id)) {
            $cart_goods = Cart::select('rec_id', 'goods_number', 'goods_price', 'ru_id', 'goods_id')
                ->whereIn('rec_id', $rec_id);

            if (!empty($user_id)) {
                $cart_goods = $cart_goods->where('user_id', $user_id);
            } else {
                $session_id = app(SessionRepository::class)->realCartMacIp();
                $cart_goods = $cart_goods->where('session_id', $session_id);
            }

            $cart_goods = BaseRepository::getToArrayGet($cart_goods);

            $goods_id = BaseRepository::getKeyPluck($cart_goods, 'goods_id');
            $goodsList = GoodsDataHandleService::GoodsDataList($goods_id, ['goods_id', 'brand_id', 'cat_id']);
            foreach ($cart_goods as $key => $row) {
                $goods = $goodsList[$row['goods_id']] ?? [];
                $cart_goods[$key]['brand_id'] = $goods['brand_id'] ?? 0;
                $cart_goods[$key]['cat_id'] = $goods['cat_id'] ?? 0;
            }

            $cartGoodsCard = app(\App\Services\Activity\ValueCardService::class)->getUserValueCard($user_id, $cart_goods);

            $vidList = BaseRepository::getKeyPluck($cartGoodsCard, 'vid');
        }

        $res = ValueCard::where('user_id', $user_id);
        $res = $res->with([
            'getValueCardType' => function ($query) {
                $query->select('id', 'name', 'use_condition', 'is_rec', 'vc_dis');
            }
        ]);

        $res = $res->orderBy('end_time', 'desc');

        $res = BaseRepository::getToArrayGet($res);

        $time = TimeRepository::getGmTime();
        if ($res) {
            foreach ($res as $key => $row) {
                $row = $row['get_value_card_type'] ? array_merge($row, $row['get_value_card_type']) : $row;

                $res[$key] = $row;

                if ($time > $row['end_time']) {
                    $res[$key]['use_status'] = 2; //已过期
                }

                if ($row['card_money'] == 0) {
                    $res[$key]['use_status'] = 3; //已用完
                }

                $res[$key]['vc_value_money'] = $row['vc_value']; //面值
                $res[$key]['use_card_money'] = $row['card_money']; //可用余额

                $res[$key]['is_rec'] = $row['is_rec'];

                $res[$key]['vc_dis_format'] = $row['vc_dis'] == 1 ? lang('common.wu') : $row['vc_dis'] * 10 . lang('user.percent');

                /* 先判断是否被使用，然后判断是否开始或过期 */
                $res[$key]['vc_value'] = $this->dscRepository->getPriceFormat($row['vc_value']);
                $res[$key]['use_condition'] = CommonRepository::conditionFormat($row['use_condition']);
                $res[$key]['card_money'] = $this->dscRepository->getPriceFormat($row['card_money']);
                $res[$key]['bind_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['bind_time']);
                $res[$key]['local_end_time'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['end_time']);
            }
        }

        /* 可用储值卡 start */
        $sql = [
            'where' => [
                [
                    'name' => 'use_card_money',
                    'value' => 0,
                    'condition' => '>' //条件查询
                ],
                [
                    'name' => 'use_status',
                    'value' => 1
                ]
            ]
        ];
        $use_card_list = BaseRepository::getArraySqlGet($res, $sql);

        $sql = [
            'where' => [
                [
                    'name' => 'is_rec',
                    'value' => 1
                ]
            ],
            'whereIn' => [
                [
                    'name' => 'use_status',
                    'value' => [1, 3]
                ]
            ]
        ];
        $use_card_rec_list = BaseRepository::getArraySqlGet($res, $sql);

        $use_card_list = BaseRepository::getArrayMerge($use_card_list, $use_card_rec_list);
        $use_card_list = BaseRepository::getArrayUnique($use_card_list, 'vid');
        /* 可用储值卡 end */

        if (!empty($rec_id)) {

            /* 购物流程 */

            $res = $use_card_list;

            if (!empty($vidList)) {
                $sql = [
                    'whereNotIn' => [
                        [
                            'name' => 'vid',
                            'value' => $vidList
                        ]
                    ]
                ];
                $not_use_card_list = BaseRepository::getArraySqlGet($res, $sql);

                $sql = [
                    'whereIn' => [
                        [
                            'name' => 'vid',
                            'value' => $vidList
                        ]
                    ]
                ];
                $use_card_list = BaseRepository::getArraySqlGet($res, $sql);
            } else {
                $not_use_card_list = $res;
                $use_card_list = [];
            }

            if ($not_use_card_list) {
                foreach ($not_use_card_list as $nkey => $nrow) {
                    $not_use_card_list[$nkey]['use_status'] = 0;
                }
            }

            $sql = [
                'where' => [
                    [
                        'name' => 'end_time',
                        'value' => $time,
                        'condition' => '<' //条件查询
                    ]
                ]
            ];
            $use_card_end_time_list = BaseRepository::getArraySqlGet($res, $sql);
        } else {

            /* 会员中心储值卡 */

            /* 不可用储值卡 start */
            $sql = [
                'whereIn' => [
                    [
                        'name' => 'use_status',
                        'value' => [0, 2]
                    ]
                ]
            ];
            $status_card_list = BaseRepository::getArraySqlGet($res, $sql);

            $sql = [
                'where' => [
                    [
                        'name' => 'use_card_money',
                        'value' => 0
                    ],
                    [
                        'name' => 'is_rec',
                        'value' => 0
                    ]
                ]
            ];
            $money_card_list = BaseRepository::getArraySqlGet($res, $sql);

            $not_use_card_list = BaseRepository::getArrayMerge($status_card_list, $money_card_list);

            $sql = [
                'where' => [
                    [
                        'name' => 'end_time',
                        'value' => $time,
                        'condition' => '<' //条件查询
                    ]
                ]
            ];
            $use_card_end_time_list = BaseRepository::getArraySqlGet($res, $sql);

            $not_use_card_list = BaseRepository::getArrayMerge($not_use_card_list, $use_card_end_time_list);
            $not_use_card_list = BaseRepository::getArrayUnique($not_use_card_list, 'vid');
            $not_use_card_list = BaseRepository::getSortBy($not_use_card_list, 'end_time');
            /* 不可用储值卡 end */
        }

        /* 已用完储值卡金额列表 start */
        $sql = [
            'where' => [
                [
                    'name' => 'use_card_money',
                    'value' => 0
                ]
            ]
        ];
        $use_money_card_null_list = BaseRepository::getArraySqlGet($res, $sql);
        /* 已用完储值卡金额列表 end */

        /* 已失效储值卡列表 start */
        $sql = [
            'where' => [
                [
                    'name' => 'use_status',
                    'value' => 0
                ]
            ]
        ];
        $use_card_invalid_list = BaseRepository::getArraySqlGet($res, $sql);
        /* 已失效储值卡列表 end */

        $use_card_page_list = BaseRepository::getPaginate($use_card_list, $size, ['path' => asset('/user.php?act=value_card&use_type=1')]);
        $not_use_card_page_list = BaseRepository::getPaginate($not_use_card_list, $size, ['path' => asset('/user.php?act=value_card&use_type=0')]);

        $arr = [
            'card_list' => BaseRepository::getArrayMerge($use_card_list, $not_use_card_list),
            'use_card_count' => BaseRepository::getArrayCount($use_card_list),
            'not_use_card_count' => BaseRepository::getArrayCount($not_use_card_list),
            'use_card_page_list' => $use_card_page_list,
            'not_use_card_page_list' => $not_use_card_page_list,
            'card_total' => BaseRepository::getArraySum($use_card_list, 'use_card_money'),
            'use_card_null' => BaseRepository::getArrayCount($use_money_card_null_list),
            'card_invalid_count' => BaseRepository::getArrayCount($use_card_invalid_list),
            'card_end_time_count' => BaseRepository::getArrayCount($use_card_end_time_list)
        ];

        return $arr;
    }
}

<?php

namespace App\Services\Bonus;

use App\Models\BonusType;
use App\Models\UserBonus;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Services\Merchant\MerchantCommonService;
use App\Services\Merchant\MerchantDataHandleService;

/**
 * 红包
 * Class BonusService
 * @package App\Services\Bonus
 */
class BonusService
{
    protected $dscRepository;
    protected $merchantCommonService;

    public function __construct(
        DscRepository $dscRepository,
        MerchantCommonService $merchantCommonService
    )
    {
        $this->dscRepository = $dscRepository;
        $this->merchantCommonService = $merchantCommonService;
    }

    /**
     * 红包列表
     *
     * @param int $user_id
     * @param int $status
     * @param int $page
     * @param int $size
     * @param int $id
     * @return array
     * @throws \Exception
     */
    public function listBonus($user_id = 0, $status = 4, $page = 1, $size = 10, $id = 0)
    {
        $time = TimeRepository::getGmTime();
        $begin = ($page - 1) * $size;
        $res = BonusType::where('send_type', $status)
            ->where('send_end_date', '>', $time)
            ->where('review_status', 3)
            ->where('send_start_date', '<', $time);

        if ($id > 0) {
            $res = $res->where('type_id', $id);
        }

        $res = $res->orderBy('type_id', 'desc');

        if ($begin > 0) {
            $res = $res->skip($begin);
        }

        if ($size > 0) {
            $res = $res->take($size);
        };

        $res = BaseRepository::getToArrayGet($res);

        if ($res) {

            $ru_id = BaseRepository::getKeyPluck($res, 'user_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            $bonus_type_id = BaseRepository::getKeyPluck($res, 'type_id');
            $userBonusList = BonusDataHandleService::getUserBonusDataList($bonus_type_id, ['bonus_id', 'bonus_type_id', 'user_id']);

            foreach ($res as $k => $v) {
                $res[$k]['begintime'] = TimeRepository::getLocalDate("Y-m-d ", $v['send_start_date']);
                $res[$k]['endtime'] = TimeRepository::getLocalDate("Y-m-d ", $v['send_end_date']);
                $res[$k]['min_goods_amount'] = $v['min_goods_amount'];
                $res[$k]['type_money'] = $this->dscRepository->getPriceFormat($v['type_money'], true);

                // 使用范围
                if ($v['usebonus_type'] == 0) {
                    $res[$k]['shop_name'] = sprintf(lang('user.use_limit'), $merchantList[$v['user_id']]['shop_name'] ?? '');
                } else {
                    $res[$k]['shop_name'] = lang('user.general_audience'); // 全场通用
                }

                $res[$k]['lang_receive'] = lang('bonus.receive');
                $res[$k]['lang_valid_period_lost'] = lang('bonus.valid_period_lost');

                // 是否已领取
                if ($user_id > 0) {

                    $sql = [
                        'where' => [
                            [
                                'name' => 'bonus_type_id',
                                'value' => $v['type_id']
                            ],
                            [
                                'name' => 'user_id',
                                'value' => $user_id
                            ]
                        ]
                    ];
                    $exist = BaseRepository::getArraySqlGet($userBonusList, $sql);
                    $exist = BaseRepository::getArrayCount($exist);

                    $res[$k]['is_receive'] = $exist > 0 ? 1 : 0;
                }

                // 发放剩余数量
                $sql = [
                    'where' => [
                        [
                            'name' => 'bonus_type_id',
                            'value' => $v['type_id']
                        ],
                        [
                            'name' => 'user_id',
                            'value' => 0
                        ]
                    ]
                ];
                $left_num = BaseRepository::getArraySqlGet($userBonusList, $sql);
                $left_num = BaseRepository::getArrayCount($left_num);

                $res[$k]['is_left'] = $left_num > 0 ? 1 : 0;
            }
        }

        return $res;
    }

    /**
     * 领取红包
     *
     * @param int $user_id
     * @param int $type_id
     * @return array
     * @throws \Exception
     */
    public function receiveBonus($user_id = 0, $type_id = 0)
    {
        $res = UserBonus::select('bonus_id')
            ->where('bonus_type_id', $type_id)
            ->where('user_id', $user_id);

        $res = BaseRepository::getToArrayFirst($res);

        if (!empty($res)) {
            $result = [
                'code' => 1,
                'msg' => lang('bonus.already_got')
            ];
        } else {
            $res = UserBonus::select('bonus_id')
                ->where('bonus_type_id', $type_id)
                ->where('user_id', 0);
            $res = BaseRepository::getToArrayFirst($res);

            if (empty($res)) {
                $result = [
                    'code' => 1,
                    'msg' => lang('bonus.no_bonus'),
                ];
            } else {
                $now = TimeRepository::getGmTime();

                $data = [
                    'user_id' => $user_id,
                    'bind_time' => $now,
                ];

                // 更新红包使用有效期
                $bonus_info = BonusType::select('send_end_date', 'use_start_date', 'use_end_date', 'valid_period')
                    ->where('type_id', $type_id);
                $bonus_info = BaseRepository::getToArrayFirst($bonus_info);

                if (isset($bonus_info['use_end_date']) && $now > $bonus_info['use_end_date']) {
                    return ['code' => 1, 'msg' => lang('user.bonus_use_expire')];
                }

                if (isset($bonus_info['valid_period']) && $bonus_info['valid_period'] > 0) {
                    $data['start_time'] = $data['bind_time'];
                    $data['end_time'] = $data['bind_time'] + $bonus_info['valid_period'] * 3600 * 24;
                } else {
                    $data['start_time'] = $bonus_info['use_start_date'] ?? 0;
                    $data['end_time'] = $bonus_info['use_end_date'] ?? 0;
                }

                UserBonus::where('bonus_id', $res['bonus_id'])->update($data);

                $result = [
                    'code' => 0,
                    'msg' => lang('bonus.get_success'),
                ];
            }
        }

        return $result;
    }

    /**
     * 会员添加红包
     *
     * @param array $res
     * @return array
     * @throws \Exception
     */
    public function addBonus($res = [])
    {
        if (empty($res)) {
            return [];
        }

        $user_id = $res['user_id'];

        /* 查询红包序列号是否已经存在 */
        $row = UserBonus::where('bonus_sn', $res['bonus_sn'])
            ->where('bonus_password', $res['bonus_password']);

        $row = BaseRepository::getToArrayFirst($row);

        if ($row) {
            if ($row['user_id'] == 0) {
                //红包没有被使用
                $bonus_info = BonusType::select('send_end_date', 'use_start_date', 'use_end_date', 'valid_period')
                    ->where('type_id', $row['bonus_type_id'])
                    ->where('review_status', 3)
                    ->first();
                $bonus_info = $bonus_info ? $bonus_info->toArray() : [];

                $now = TimeRepository::getGmTime();

                if ($bonus_info && $now > $bonus_info['use_end_date']) {
                    $result = [
                        'code' => 1,
                        'mesg' => lang('user.bonus_use_expire'),
                    ];
                } else {
                    $data = [
                        'user_id' => $user_id,
                        'bind_time' => $now
                    ];

                    if (isset($bonus_info['valid_period']) && $bonus_info['valid_period'] > 0) {
                        $data['start_time'] = $data['bind_time'];
                        $data['end_time'] = $data['bind_time'] + $bonus_info['valid_period'] * 3600 * 24;
                    } else {
                        $data['start_time'] = $bonus_info['use_start_date'] ?? 0;
                        $data['end_time'] = $bonus_info['use_end_date'] ?? 0;
                    }

                    UserBonus::where('bonus_id', $row['bonus_id'])
                        ->update($data);

                    $result = [
                        'code' => 0,
                        'mesg' => lang('user.add_bonus_sucess'),
                    ];
                }
            } else {
                if ($row['user_id'] == $user_id) {
                    //红包已经添加过了。
                    $result = [
                        'code' => 1,
                        'mesg' => lang('user.bonus_is_used'),
                    ];
                } else {
                    //红包被其他人使用过了。
                    $result = [
                        'code' => 1,
                        'mesg' => lang('user.bonus_is_used_by_other'),
                    ];
                }
            }
        } else {
            //红包不存在
            $result = [
                'code' => 1,
                'mesg' => lang('user.bonus_not_exist'),
            ];
        }
        return $result;
    }

    /**
     * 会员中心红包列表
     *
     * @param $user_id
     * @param int $type
     * @param int $page
     * @param int $size
     * @return array
     * @throws \Exception
     */
    public function userBonus($user_id, $type = 0, $page = 1, $size = 10)
    {
        $begin = ($page - 1) * $size;
        $time = TimeRepository::getGmTime();
        //获取表前缀
        $prefix = config('database.connections.mysql.prefix');

        $res = UserBonus::from('user_bonus as u')
            ->select('u.bonus_sn', 'u.order_id', 'b.user_id as ru_id', 'b.type_name', 'b.type_money', 'b.min_goods_amount', 'b.use_start_date', 'b.use_end_date', 'u.start_time', 'u.end_time', 'b.usebonus_type', 'b.valid_period', 'u.bind_time', 'b.date_type')
            ->leftjoin('bonus_type as b', 'u.bonus_type_id', '=', 'b.type_id')
            ->where('u.user_id', $user_id);

        if ($type == 0) {
            //如果时间类型:红包有效期
            //就加上条件:绑定用户的时间大于|小于,当前时间戳减去红包有效期的时间戳
            $res = $res->where(function ($query) use ($time, $prefix) {
                $query = $query->where('b.use_end_date', '>', $time);
                $query->whereRaw("if(" . $prefix . "b . date_type = 1," . $prefix . "u . bind_time > $time - (60 * 60 * 24 * " . $prefix . "b . valid_period),1=1)");
            });
            $res = $res->where('u.used_time', '');
        } elseif ($type == 1) {
            $res = $res->where('u.order_id', '>', 0);
        } elseif ($type == 2) {
            $res = $res->where(function ($query) use ($time, $prefix) {
                $query = $query->where('b.use_end_date', '<', $time);
                $query->orWhereRaw("if(" . $prefix . "b . date_type = 1," . $prefix . "u . bind_time < $time - (60 * 60 * 24 * " . $prefix . "b . valid_period),'')");
            });
        }

        $res = $res->orderBy('bonus_id', 'DESC');

        $res = $res->offset($begin)
            ->limit($size);

        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {

            $ru_id = BaseRepository::getKeyPluck($res, 'ru_id');
            $merchantList = MerchantDataHandleService::getMerchantInfoDataList($ru_id);

            foreach ($res as $row) {
                if ($row['valid_period'] > 0) {
                    $add_time = $row['valid_period'] * 60 * 60 * 24;
                    $row['use_start_date'] = $row['bind_time'];
                    $row['use_end_date'] = $row['bind_time'] + $add_time;
                }
                /* 先判断是否被使用，然后判断是否开始或过期 */
                if (empty($row['order_id'])) {
                    /* 没有被使用 */
                    if ($row['use_start_date'] > $time) {
                        $row['status'] = lang('user.not_start');
                        if ($row['use_start_date'] - $time < 60 * 60 * 24 * 2) {
                            $row['near_time'] = 1;
                        }
                        $row['bonus_status'] = 2;
                    } elseif ($row['use_end_date'] < $time) {
                        $row['status'] = lang('user.overdue'); // 已过期
                        $row['bonus_status'] = 3;
                    } else {
                        $row['status'] = lang('user.not_use'); // 未使用
                        $row['bonus_status'] = 0;
                    }
                } else {
                    $row['status'] = lang('user.had_use');  // 已使用
                    $row['bonus_status'] = 1;
                }

                $row['use_start_date'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['use_start_date']);
                $row['use_end_date'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['use_end_date']);

                // 使用范围
                if ($row['usebonus_type'] == 0) {
                    $row['shop_name'] = sprintf(lang('user.use_limit'), $merchantList[$row['ru_id']]['shop_name'] ?? '');
                } else {
                    $row['shop_name'] = lang('user.general_audience'); //全场通用
                }

                $arr[] = $row;
            }
        }

        return $arr;
    }
}

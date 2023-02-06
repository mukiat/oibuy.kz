<?php

namespace App\Services\Lottery;

use App\Models\LotteryPrize;

/**
 * Class LotteryPrizeService
 * @package App\Services\Lottery
 */
class LotteryPrizeService
{
    /**
     * 获取活动奖品
     * @param $lottery_id 活动ID
     * @param $ru_id 店铺ID
     * @return array
     */
    public function all($lottery_id, $ru_id)
    {
        $list = $this->model()->where('lottery_id', $lottery_id)->where('ru_id', $ru_id)->get();

        return collect($list)->toArray();
    }

    /**
     * 增加活动奖品记录
     * @param $data 奖品数据
     * @return mixed
     */
    public function create($data)
    {
        return $this->model()->insert($data);
    }

    /**
     * 查看活动奖品
     * @param $id 奖品
     * @param $ru_id 店铺ID
     * @return array
     */
    public function show($id, $ru_id)
    {
        $data = $this->model()->where('id', $id)->where('ru_id', $ru_id)->first();

        return collect($data)->toArray();
    }

    /**
     * 更新活动奖品
     * @param $data 奖品数据
     * @param $ru_id 店铺ID
     * @return mixed
     */
    public function update($data, $ru_id)
    {
        return $this->model()->where('ru_id', $ru_id)->update($data);
    }

    /**
     * 移除活动奖品记录
     * @param $id 奖品ID
     * @param $ru_id 店铺ID
     * @return mixed
     */
    public function delete($id, $ru_id)
    {
        return $this->model()->where('id', $id)->where('ru_id', $ru_id)->delete();
    }

    /**
     * 获取活动奖品模型
     * @return \App\Models\LotteryPrize
     */
    private function model()
    {
        return new LotteryPrize();
    }
}

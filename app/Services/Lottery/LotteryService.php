<?php

namespace App\Services\Lottery;

use App\Models\Lottery;

/**
 * Class LotteryService
 * @package App\Services\Lottery
 */
class LotteryService
{
    /**
     * 初始化配置
     * @param int $ru_id 店铺ID
     * @return mixed
     */
    public function init($ru_id = 0)
    {
        $config = [
            'ru_id' => $ru_id,
            'active_state' => '',
            'start_time' => '',
            'end_time' => '',
            'active_desc' => '',
            'participant' => '',
            'single_amount' => 0,
            'participate_number' => 1,
        ];

        $config['id'] = $this->model()->insertGetId($config);

        return $config;
    }

    /**
     * 获取活动配置
     * @param $ru_id 店铺ID
     * @return array
     */
    public function get($ru_id)
    {
        $config = $this->model()->where('ru_id', $ru_id)->first();

        return collect($config)->toArray();
    }

    /**
     * 更新配置
     * @param array $config 活动配置
     * @param int $ru_id 店铺ID
     * @return mixed
     */
    public function update($config = [], $ru_id = 0)
    {
        $id = $config['id'];
        unset($config['id']);
        return $this->model()->where('id', $id)->where('ru_id', $ru_id)->update($config);
    }

    /**
     * 活动模型
     * @return \App\Models\Lottery
     */
    private function model()
    {
        return new Lottery();
    }
}

<?php

namespace App\Services\Lottery;

use App\Models\LotteryRecord;

/**
 * Class LotteryRecordService
 * @package App\Services\Lottery
 */
class LotteryRecordService
{
    /**
     * @param $filter
     * @return array
     */
    public function all($filter)
    {
        $model = $this->model();

        // 按照类型查询
        if (isset($filter['type'])) {
            $model = $model->where('prize_type', intval($filter['type']));
        }

        // 按照关键词查询
        if (isset($filter['keyword'])) {
            $model = $model->where('user_name', 'like', '%' . e($filter['keyword']) . '%');
        }

        $data = $model->where('lottery_id', $filter['lottery_id'])->paginate($filter['page_size']);

        return collect($data)->toArray();
    }

    /**
     * 会员参与抽奖活动记录
     * @param $data
     * @return mixed
     */
    public function create($data)
    {
        return $this->model()->insert($data);
    }

    /**
     * 活动参与记录模型
     * @return \App\Models\LotteryRecord
     */
    private function model()
    {
        return new LotteryRecord();
    }
}

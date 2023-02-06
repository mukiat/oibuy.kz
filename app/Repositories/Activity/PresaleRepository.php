<?php

namespace App\Repositories\Activity;

use App\Models\OrderGoods;
use App\Models\PresaleActivity;
use App\Repositories\Common\BaseRepository;


/**
 * Class PresaleRepository
 * @package App\Repositories\Activity
 */
class PresaleRepository
{
    /**
     * 更新预售活动商品数量
     *
     * @param int $order_id
     * @return bool
     */
    public static function increment_presale_num($order_id = 0)
    {
        if (empty($order_id)) {
            return false;
        }

        $res = OrderGoods::where('order_id', $order_id)->select('goods_id');
        $res = BaseRepository::getToArrayFirst($res);

        if ($res) {
            return PresaleActivity::where('goods_id', $res['goods_id'])->increment('pre_num', 1);
        }

        return false;
    }

}
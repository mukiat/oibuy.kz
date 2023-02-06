<?php

namespace App\Services\Goods;

use App\Models\GoodsActivity;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;

class GoodsActivityService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 查询商品活动
     * @param int $act_id
     * @param int $act_type
     * @return array
     */
    public function getGoodsActivity($act_id = 0, $act_type = GAT_PACKAGE)
    {
        $activity = $this->goodsActivityDataList($act_id, $act_type);
        $activity = BaseRepository::getArraySqlFirst($activity);

        return $activity;
    }

    /**
     * 查询商品活动
     *
     * @param $act_id
     * @param int $act_type
     * @param array $data
     * @return array
     */
    public static function goodsActivityDataList($act_id, $act_type = GAT_PACKAGE, $data = [])
    {
        $act_id = BaseRepository::getExplode($act_id);

        if (empty($act_id)) {
            return [];
        }

        $act_id = array_unique($act_id);

        $data = empty($data) ? "*" : $data;

        $res = GoodsActivity::select($data)->whereIn('act_id', $act_id)
            ->where('act_type', $act_type);

        $res = BaseRepository::getToArrayGet($res);

        return self::goodsActivityThumb($res);
    }

    /**
     * 处理图片
     *
     * @param $res
     * @return array
     */
    public static function goodsActivityThumb($res)
    {
        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {

                $row['activity_thumb'] = !empty($row['activity_thumb']) ? app(DscRepository::class)->getImagePath($row['activity_thumb']) : app(DscRepository::class)->dscUrl('themes/ecmoban_dsc2017/images/17184624079016pa.jpg');

                $arr[$row['act_id']] = $row;
            }
        }

        return $arr;
    }
}

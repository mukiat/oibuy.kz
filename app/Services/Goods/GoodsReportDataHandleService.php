<?php

namespace App\Services\Goods;

use App\Models\GoodsReportImg;
use App\Repositories\Common\BaseRepository;

class GoodsReportDataHandleService
{
    /**
     * 商品举报图片列表
     *
     * @param array $report_id
     * @param array $data
     * @return array
     */
    public static function getGoodsReportImgReportIdDataList($report_id = [], $data = [])
    {
        $report_id = BaseRepository::getExplode($report_id);

        if (empty($report_id)) {
            return [];
        }

        $report_id = $report_id ? array_unique($report_id) : [];

        $data = $data ? $data : '*';

        $res = GoodsReportImg::select($data)->whereIn('report_id', $report_id)
            ->orderBy('img_id', 'DESC');
        $res = BaseRepository::getToArrayGet($res);

        $arr = [];
        if ($res) {
            foreach ($res as $key => $row) {
                $arr[$row['img_id']] = $row;
            }
        }

        return $arr;
    }
}
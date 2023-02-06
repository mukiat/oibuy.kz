<?php

namespace App\Repositories\Goods;

use App\Models\GoodsServicesLabel;
use App\Models\GoodsUseServicesLabel;
use App\Repositories\Common\BaseRepository;
use Illuminate\Support\Facades\DB;

/**
 * Class GoodsServicesLabelRepository
 * @package App\Repositories\Goods
 */
class GoodsServicesLabelRepository
{
    /**
     * 商品活动标签
     *
     * @param int $goods_id
     * @param array $columns
     * @param array $label_use_id
     * @return array
     */
    public static function getGoodsServicesLabel($goods_id = 0, $columns = ['*'], $label_use_id = [])
    {
        if (empty($goods_id) && empty($label_use_id)) {
            return [];
        }

        $res = GoodsUseServicesLabel::query();
        $res = $goods_id > 0 ? $res->where('goods_id', $goods_id) : $res->whereIn('id', $label_use_id);

        $res = $res->with([
            'getGoodsServicesLabel' => function ($query) use ($columns) {
                $query->select($columns)->where('label_code', '<>', 'no_reason_return');
            }
        ]);

        $res = $res->limit(6);

        return BaseRepository::getToArrayGet($res);
    }

    /**
     * 商品标签（含通用、悬浮标签） for前端显示
     * @param int $goods_id
     * @param int $self_run
     * @return array
     */
    public static function getAllGoodsServicesLabel($goods_id = 0, $self_run = 0)
    {
        if (empty($goods_id)) {
            return [];
        }

        $res = GoodsUseServicesLabel::where('goods_id', $goods_id);

        $res = $res->whereHasIn('getGoodsServicesLabel', function ($query) use ($self_run) {
            $query = $query->where('status', 1)->where('label_code', '<>', 'no_reason_return');
            if ($self_run == 0) {
                $query = $query->where('merchant_use', 1); // 如果不是自营的 只展示商家可用的
            }
        });

        $res = $res->with([
            'getGoodsServicesLabel' => function ($query) {
                $query->select('id', 'label_image', 'sort', 'label_url', 'start_time', 'end_time')->where('label_code', '<>', 'no_reason_return');
            }
        ]);

        $res = $res->limit(6);

        return BaseRepository::getToArrayGet($res);
    }

    /**
     * 搜索
     * @param string $keyword
     * @param int $type
     * @return mixed
     */
    public static function searchGoodsServicesLabel($keyword = '', $type = 0)
    {
        $res = GoodsServicesLabel::select('id', 'label_name', 'label_image')->where('status', 1)->where('label_code', '<>', 'no_reason_return');

        if (!empty($keyword)) {
            $res = $res->where('label_name', 'like', '%' . $keyword . '%');
        }

        return BaseRepository::getToArrayGet($res);
    }

    /**
     * 获取商品标签列表（通用、平台后台）
     * @param int $goods_id
     * @param array $label_use_id
     * @return array
     */
    public static function getGoodsServicesLabelForAdmin($goods_id = 0, $label_use_id = [])
    {
        if (empty($goods_id) && empty($label_use_id)) {
            return [];
        }

        $labels = GoodsUseServicesLabel::query();
        $labels = $goods_id > 0 ? $labels->where('goods_id', $goods_id) : $labels->whereIn('id', $label_use_id);

        $labels = $labels->pluck('label_id');

        $options = GoodsServicesLabel::select('id', 'label_name', 'label_image')->where('status', 1)->where('label_code', '<>', 'no_reason_return')->whereIn('id', $labels);

        return BaseRepository::getToArrayGet($options);
    }

    /**
     * 获取商品标签列表（通用、商家后台）
     * @param int $goods_id
     * @param array $label_use_id
     * @return array
     */
    public static function getGoodsServicesLabelForSeller($goods_id = 0, $label_use_id = [])
    {

        if (empty($goods_id) && empty($label_use_id)) {
            return [];
        }

        $labels = GoodsUseServicesLabel::query();
        $labels = $goods_id > 0 ? $labels->where('goods_id', $goods_id) : $labels->whereIn('id', $label_use_id);

        $labels = $labels->pluck('label_id');
        $options = GoodsServicesLabel::select('id', 'label_name', 'label_image')->where('status', 1)->where('label_code', '<>', 'no_reason_return')->where('merchant_use', 1)->whereIn('id', $labels);

        return BaseRepository::getToArrayGet($options);
    }

    /**
     * 更新绑定标签商品数量
     * @param int $label_id
     * @return bool
     */
    public static function update_label_bind_goods_number($label_id = 0)
    {
        if (empty($label_id)) {
            return false;
        }

        $bind_goods_number = DB::table('goods_use_services_label')->where('label_id', $label_id)->count('goods_id');
        $bind_goods_number = $bind_goods_number ?? 0;

        DB::table('goods_services_label')->where('id', $label_id)->update(['bind_goods_number' => $bind_goods_number]);

        return true;
    }
}

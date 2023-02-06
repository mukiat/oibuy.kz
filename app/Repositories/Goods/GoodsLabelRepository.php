<?php

namespace App\Repositories\Goods;

use App\Models\GoodsLabel;
use App\Models\GoodsServicesLabel;
use App\Models\GoodsUseLabel;
use App\Models\GoodsUseServicesLabel;
use App\Repositories\Common\BaseRepository;
use Illuminate\Support\Facades\DB;

/**
 * Class GoodsLabelRepository
 * @package App\Repositories\Goods
 */
class GoodsLabelRepository
{
    /**
     * 商品活动标签
     *
     * @param int $goods_id
     * @param int $type
     * @param array $columns
     * @param array $label_use_id
     * @return array
     */
    public static function getGoodsLabel($goods_id = 0, $type = 0, $columns = ['*'], $label_use_id = [])
    {
        if (empty($goods_id) && empty($label_use_id)) {
            return [];
        }

        $res = GoodsUseLabel::query();
        $res = $goods_id > 0 ? $res->where('goods_id', $goods_id) : $res->whereIn('id', $label_use_id);

        $res = $res->whereHasIn('getGoodsLabel', function ($query) use ($type) {
            $query->where('type', $type);
        });

        $res = $res->with([
            'getGoodsLabel' => function ($query) use ($type, $columns) {
                $query->where('type', $type)->select($columns);
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
    public static function getAllGoodsLabel($goods_id = 0, $self_run = 0)
    {
        if (empty($goods_id)) {
            return [];
        }

        $res = GoodsUseLabel::where('goods_id', $goods_id);

        $res = $res->whereHasIn('getGoodsLabel', function ($query) use ($self_run) {
            $query = $query->where('status', 1);
            if ($self_run == 0) {
                $query = $query->where('merchant_use', 1); // 如果不是自营的 只展示商家可用的
            }
        });

        $res = $res->with([
            'getGoodsLabel' => function ($query) {
                $query->select('id', 'label_image', 'sort', 'type', 'label_url', 'start_time', 'end_time');
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
    public static function searchGoodsLabel($keyword = '', $type = 0)
    {
        $res = GoodsLabel::select('id', 'label_name', 'label_image')->where('status', 1)->where('type', $type);

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
    public static function getGoodsLabelForAdmin($goods_id = 0, $label_use_id = [])
    {
        if (empty($goods_id) && empty($label_use_id)) {
            return [];
        }

        $labels = GoodsUseLabel::query();
        $labels = $goods_id > 0 ? $labels->where('goods_id', $goods_id) : $labels->whereIn('id', $label_use_id);

        $labels = $labels->pluck('label_id');

        $options = GoodsLabel::select('id', 'label_name', 'label_image')->where('status', 1)->where('type', 0)->whereIn('id', $labels);

        return BaseRepository::getToArrayGet($options);
    }

    /**
     * 获取商品标签列表（通用、商家后台）
     * @param int $goods_id
     * @param array $label_use_id
     * @return array
     */
    public static function getGoodsLabelForSeller($goods_id = 0, $label_use_id = [])
    {

        if (empty($goods_id) && empty($label_use_id)) {
            return [];
        }

        $labels = GoodsUseLabel::query();
        $labels = $goods_id > 0 ? $labels->where('goods_id', $goods_id) : $labels->whereIn('id', $label_use_id);

        $labels = $labels->pluck('label_id');
        $options = GoodsLabel::select('id', 'label_name', 'label_image')->where('status', 1)->where('type', 0)->where('merchant_use', 1)->whereIn('id', $labels);

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

        $bind_goods_number = DB::table('goods_use_label')->where('label_id', $label_id)->count('goods_id');
        $bind_goods_number = $bind_goods_number ?? 0;

        DB::table('goods_label')->where('id', $label_id)->update(['bind_goods_number' => $bind_goods_number]);

        return true;
    }

    /**
     * 商品服务标签
     *
     * @param int $goods_id
     * @param array $columns
     * @param array $services_label_use_id
     * @return array
     */
    public static function getGoodsServicesLabel($goods_id = 0, $columns = ['*'], $services_label_use_id = [])
    {
        if (empty($goods_id) && empty($services_label_use_id)) {
            return [];
        }

        $res = GoodsUseServicesLabel::query();
        $res = $goods_id > 0 ? $res->where('goods_id', $goods_id) : $res->whereIn('id', $services_label_use_id);

        $res = $res->with([
            'getGoodsServicesLabel' => function ($query) use ($columns) {
                $query->where('label_code', '<>', 'no_reason_return')->select($columns);
            }
        ]);

        $res = $res->limit(6);

        return BaseRepository::getToArrayGet($res);
    }

    /**
     * 获取商品标签列表（通用、平台后台）
     * @param int $goods_id
     * @param array $services_label_use_id
     * @return array
     */
    public static function getGoodsServicesLabelForAdmin($goods_id = 0, $services_label_use_id = [])
    {
        if (empty($goods_id) && empty($services_label_use_id)) {
            return [];
        }

        $labels = GoodsUseServicesLabel::query();
        $labels = $goods_id > 0 ? $labels->where('goods_id', $goods_id) : $labels->whereIn('id', $services_label_use_id);

        $labels = $labels->pluck('label_id');

        $options = GoodsServicesLabel::query()->select('id', 'label_name', 'label_image')->where('status', 1)->where('label_code', '<>', 'no_reason_return')->whereIn('id', $labels);

        return BaseRepository::getToArrayGet($options);
    }

    /**
     * 获取商品标签列表（通用、商家后台）
     * @param int $goods_id
     * @param array $services_label_use_id
     * @return array
     */
    public static function getGoodsServicesLabelForSeller($goods_id = 0, $services_label_use_id = [])
    {
        if (empty($goods_id) && empty($services_label_use_id)) {
            return [];
        }

        $labels = GoodsUseServicesLabel::query();
        $labels = $goods_id > 0 ? $labels->where('goods_id', $goods_id) : $labels->whereIn('id', $services_label_use_id);

        $labels = $labels->pluck('label_id');
        $options = GoodsServicesLabel::query()->select('id', 'label_name', 'label_image')->where('status', 1)->where('label_code', '<>', 'no_reason_return')->where('merchant_use', 1)->whereIn('id', $labels);

        return BaseRepository::getToArrayGet($options);
    }

    /**
     * 更新绑定标签商品数量
     * @param int $label_id
     * @return bool
     */
    public static function update_services_label_bind_goods_number($label_id = 0)
    {
        if (empty($label_id)) {
            return false;
        }

        $bind_goods_number = DB::table('goods_use_services_label')->where('label_id', $label_id)->count('goods_id');
        $bind_goods_number = $bind_goods_number ?? 0;

        DB::table('goods_services_label')->where('id', $label_id)->update(['bind_goods_number' => $bind_goods_number]);

        return true;
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

        $res = GoodsUseServicesLabel::query()->where('goods_id', $goods_id);

        $res = $res->whereHasIn('getGoodsServicesLabel', function ($query) use ($self_run) {
            $query = $query->where('status', 1);
            if ($self_run == 0) {
                $query = $query->where('merchant_use', 1); // 如果不是自营的 只展示商家可用的
            }
        });

        $res = $res->with([
            'getGoodsServicesLabel' => function ($query) {
                $query->select('id', 'label_image', 'label_name', 'label_code', 'label_explain', 'sort');
            }
        ]);

        $res = $res->limit(6);

        $goods_res = BaseRepository::getToArrayGet($res);

        $default_res = GoodsServicesLabel::query()->select('id', 'label_image', 'label_name', 'label_code', 'label_explain', 'sort')->where('label_code', 'no_reason_return');
        $default_res = BaseRepository::getToArrayGet($default_res);

        $goods_res = BaseRepository::getArrayMerge($goods_res, $default_res);

        return $goods_res;
    }
}

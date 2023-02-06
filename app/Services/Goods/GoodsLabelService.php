<?php

namespace App\Services\Goods;

use App\Models\Goods;
use App\Models\GoodsLabel;
use App\Models\GoodsUseLabel;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;
use App\Repositories\Goods\GoodsLabelRepository;
use App\Repositories\Seller\SellerShopinfoRepository;
use App\Services\Category\CategoryService;
use Illuminate\Support\Facades\DB;

/**
 * Class GoodsLabelService
 * @package App\Services\Goods
 */
class GoodsLabelService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * @param array $data
     * @param int $id
     * @return bool
     */
    public function labelUpdateStatus($data = [], $id = 0)
    {
        if (empty($data)) {
            return false;
        }

        return GoodsLabel::where('id', $id)->update($data);
    }

    /**
     * 活动标签列表
     *
     * @param array $filter
     * @param array $offset
     * @return array
     */
    public function getLabelList($filter = [], $offset = [])
    {
        $type = $filter['type'] ?? 0;
        $list = GoodsLabel::query()->where('type', $type);

        if (isset($filter['keywords']) && !empty($filter['keywords'])) {
            $list = $list->where('label_name', 'like', "%" . $filter['keywords'] . "%");
        }

        // 商家id
        $ru_id = $filter['ru_id'] ?? 0;
        if (!empty($ru_id)) {
            if (isset($filter['status']) && $filter['status'] >= 0) {
                $list = $list->where('status', $filter['status']);
            }

            if (isset($filter['merchant_use']) && $filter['merchant_use'] > 0) {
                $list = $list->where('merchant_use', 1);
            }
        }

        $total = $list->count('id');

        if (!empty($offset['start'])) {
            $list = $list->offset($offset['start']);
        }
        if (!empty($offset['limit'])) {
            $list = $list->limit($offset['limit']);
        }

        $list = $list->orderBy('sort')->orderBy('id', 'DESC');

        $list = BaseRepository::getToArrayGet($list);

        if (!empty($list)) {
            foreach ($list as $k => $row) {
                // 平台后台 直接读数据库保存字段值
                if (!empty($row['bind_goods_number']) && $ru_id == 0) {
                    $bind_goods_number = $row['bind_goods_number'];
                } else {
                    // 商家id
                    $bind_goods_number = GoodsUseLabel::where('label_id', $row['id'])->whereHasIn('getGoods', function ($query) use ($ru_id) {
                        $query->where('user_id', $ru_id);
                    });
                    $bind_goods_number = $bind_goods_number->count('goods_id');
                }

                $list[$k]['bind_goods_number'] = $bind_goods_number ?? 0;
                $list[$k]['label_image'] = $this->dscRepository->getImagePath($row['label_image']);

                if ($row['type'] == 1) {
                    $list[$k]['start_time_formated'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['start_time']);
                    $list[$k]['end_time_formated'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['end_time']);
                }
            }
        }

        return ['list' => $list, 'total' => $total];
    }

    /**
     * @param int $id
     * @return array
     */
    public function getLabelInfo($id = 0)
    {
        if (empty($id)) {
            return [];
        }

        $row = GoodsLabel::where('id', $id);
        $row = BaseRepository::getToArrayFirst($row);

        if ($row) {
            $row['fromated_label_image'] = $this->dscRepository->getImagePath($row['label_image']);
            if ($row['type'] == 1) {
                $row['start_time_formated'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['start_time']);
                $row['end_time_formated'] = TimeRepository::getLocalDate(config('shop.time_format'), $row['end_time']);
            }
        }

        return $row;
    }

    /**
     * 添加标签
     * @param array $data
     * @return bool
     */
    public function goodsLabelInstall($data = [])
    {
        if (empty($data)) {
            return false;
        }

        /* 过滤表字段 */
        $label = BaseRepository::getArrayfilterTable($data, 'goods_label');

        $label['add_time'] = TimeRepository::getGmTime();

        return GoodsLabel::insert($label);
    }

    /**
     * 商品标签更新
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function goodsLabelUpdate($id = 0, $data = [])
    {
        if (empty($id) || empty($data)) {
            return false;
        }

        /* 过滤表字段 */
        $label = BaseRepository::getArrayfilterTable($data, 'goods_label');

        GoodsLabel::where('id', $id)->update($label);

        return true;
    }

    /*
    * 检测活动标签是否存在
    */
    public function labelExists($label = '', $id = 0)
    {
        $count = GoodsLabel::where('label_name', $label);

        if ($id > 0) {
            $count = $count->where('id', '<>', $id);
        }

        $count = $count->count();

        return ($count > 0) ? true : false;
    }

    /**
     * 删除活动标签
     *
     * @param int $id
     * @return bool
     */
    public function labelDrop($id = 0)
    {
        if ($id > 0) {
            GoodsLabel::where('id', $id)->delete(); // 删除活动标签
            GoodsUseLabel::where('label_id', $id)->delete();// 同步商品删除标签
            return true;
        }
        return false;
    }

    /*
    * 商品标签批量更新
    */
    public function batchUpdate($id_list = [], $update = [])
    {
        if (empty($id_list)) {
            return false;
        }

        GoodsLabel::whereIn('id', $id_list)->update($update);

        return true;
    }

    /*
    * 商品标签批量删除
    */
    public function batchDrop($id_list = [])
    {
        if (empty($id_list)) {
            return false;
        }

        GoodsLabel::whereIn('id', $id_list)->delete(); // 删除活动标签
        GoodsUseLabel::whereIn('label_id', $id_list)->delete();// 同步商品删除标签

        return true;
    }

    /**
     * 搜索商品
     * @param string $keywords
     * @param int $cat_id
     * @param int $brand_id
     * @param array $offset
     * @param array $filter
     * @return array
     */
    public function goodsListSearch($keywords = '', $cat_id = 0, $brand_id = 0, $offset = [], $filter = [])
    {
        $model = Goods::query()->where('is_alone_sale', 1)
            ->where('is_delete', 0)
            ->whereIn('review_status', [3, 4, 5]);

        if ($cat_id > 0) {

            /**
             * 当前分类下的所有子分类
             * 返回一维数组
             */
            $cat_keys = app(CategoryService::class)->getCatListChildren($cat_id);

            $model = $model->whereIn('cat_id', $cat_keys);
        }
        if ($brand_id > 0) {
            $model = $model->where('brand_id', $brand_id);
        }

        // 平台、商家商品
        $ru_id = $filter['ru_id'] ?? 0;
        $model = $model->where('user_id', $ru_id);

        // 搜索
        if ($keywords) {
            $model = $model->where(function ($query) use ($keywords) {
                $query->where('goods_name', 'like', '%' . $keywords . '%')
                    ->orWhere('goods_sn', 'like', '%' . $keywords . '%')
                    ->orWhere('keywords', 'like', '%' . $keywords . '%');
            });
        }

        // 当前标签id
        $select_label_id = $filter['label_id'] ?? 0;
        $model = $model->whereDoesntHaveIn('goodsUseLabel', function ($query) use ($select_label_id) {
            $query->where('label_id', $select_label_id);
        });

        $total = $model->count('goods_id');

        if (!empty($offset)) {
            $model = $model->offset($offset['start'])->limit($offset['limit']);
        }

        $list = $model->select('goods_id', 'goods_name', 'goods_thumb')
            ->orderBy('sort_order', 'ASC')
            ->orderBy('goods_id', 'DESC')
            ->get();
        $list = $list ? $list->toArray() : [];

        if ($list) {
            foreach ($list as $k => $value) {
                $list[$k]['goods_thumb'] = empty($value['goods_thumb']) ? '' : $this->dscRepository->getImagePath($value['goods_thumb']);
            }
        }

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 绑定标签商品
     * @param int $label_id
     * @param array $filter
     * @param array $offset
     * @return array
     */
    public function bindGoodsList($label_id= 0, $filter = [], $offset = [])
    {
        if (empty($label_id)) {
            return [];
        }

        $model = GoodsUseLabel::query()->where('label_id', $label_id);

        if (!empty($filter)) {

            // 搜索商品名称
            $goods_keywords = $filter['goods_keywords'] ?? '';
            if (!empty($goods_keywords)) {
                $model = $model->whereHasIn('getGoods', function ($query) use ($goods_keywords) {
                    $query = $query->where('goods_name', 'like', "%" . $goods_keywords . "%");
                });
            }

            // 搜索商家名称
            $ru_id = $filter['ru_id'] ?? -1;
            if ($ru_id >= 0) {
                $model = $model->whereHasIn('getGoods', function ($query) use ($ru_id) {
                    $query = $query->where('user_id', $ru_id);
                });
            }
        }

        $model = $model->with('getGoods:goods_id,goods_name,goods_img,user_id');

        $total = $model->count();

        if (!empty($offset)) {
            $model = $model->offset($offset['start'])->limit($offset['limit']);
        }

        if (!empty($columns)) {
            $model = $model->select($columns);
        }

        $model = $model->orderBy('id', 'DESC')->get();

        $list = $model ? $model->toArray() : [];

        if (!empty($list)) {
            foreach ($list as $k => $value) {
                $list[$k] = collect($value)->merge($value['get_goods'])->except('get_goods')->all(); // 合并且移除

                $list[$k]['goods_img'] = $this->dscRepository->getImagePath($list[$k]['goods_img']);
                $list[$k]['shop_name'] = SellerShopinfoRepository::getShopName($list[$k]['user_id']);
            }
        }

        return ['list' => $list, 'total' => $total];
    }

    /**
     * 商家列表(名称)
     * @param int $limit
     * @return mixed
     */
    public function seller_list($limit = 100)
    {
        $result = cache()->remember('seller_list', config('shop.cache_time', 3600), function () use ($limit) {
            return SellerShopinfoRepository::getShopList($limit);
        });

        return $result;
    }

    /**
     * 标签绑定添加商品
     * @param int $label_id
     * @param array $goods_id_arr
     * @return bool
     */
    public function bind_goods_to_label($label_id = 0, $goods_id_arr = [])
    {
        if (empty($label_id) || empty($goods_id_arr)) {
            return false;
        }

        foreach ($goods_id_arr as $goods_id) {
            $where = [
                'label_id' => $label_id,
                'goods_id' => $goods_id
            ];
            $values = [
                'add_time' => TimeRepository::getGmTime()
            ];
            GoodsUseLabel::query()->updateOrInsert($where, $values);
        }

        GoodsLabelRepository::update_label_bind_goods_number($label_id);

        return true;
    }


    /**
     * 标签解除绑定商品
     * @param int $label_id
     * @param array $goods_id_arr
     * @return bool
     */
    public function unbind_goods_to_label($label_id = 0, $goods_id_arr = [])
    {
        if (empty($label_id) || empty($goods_id_arr)) {
            return false;
        }

        DB::table('goods_use_label')->where('label_id', $label_id)->whereIn('goods_id', $goods_id_arr)->delete();

        GoodsLabelRepository::update_label_bind_goods_number($label_id);

        return true;
    }



}

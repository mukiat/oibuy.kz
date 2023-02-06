<?php

namespace App\Services\VirtualCard;

use App\Models\Goods;
use App\Models\VirtualCard;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\Common\TimeRepository;

/**
 * 虚拟卡后台
 * Class VirtualCardManageService
 * @package App\Services\VirtualCard
 */
class VirtualCardManageService
{
    protected $dscRepository;

    public function __construct(
        DscRepository $dscRepository
    )
    {
        $this->dscRepository = $dscRepository;
    }

    /**
     * 虚拟卡信息
     * @param int $card_id
     * @return array|string
     */
    public static function virtual_card_info($card_id = 0)
    {
        if (blank($card_id)) {
            return '';
        }

        $model = VirtualCard::where('card_id', $card_id);

        $model = $model->with([
            'getGoods' => function ($query) {
                $query->select('goods_id', 'goods_name');
            }
        ]);

        $model = $model->select('card_id', 'goods_id', 'card_sn', 'card_password', 'end_date', 'crc32')->first();

        $card = $model ? $model->toArray() : [];

        return $card;
    }

    /**
     * 返回补货列表
     *
     * @return array
     */
    public function get_replenish_list()
    {
        // 如果存在最后一次过滤条件并且使用 重置 REQUEST
        $param_str = 'get_replenish_list';
        $get_filter = $this->dscRepository->getSessionFilter($param_str);

        $request = !empty($get_filter) ? BaseRepository::getArrayMerge(request()->all(), $get_filter) : request()->all();

        /* 查询条件 */
        $filter['goods_id'] = empty($request['goods_id']) ? 0 : intval($request['goods_id']);
        $filter['search_type'] = empty($request['searchType']) ? 0 : trim($request['searchType']);
        $filter['order_sn'] = empty($request['order_sn']) ? 0 : trim($request['order_sn']);
        $filter['keyword'] = empty($request['keyword']) ? 0 : trim($request['keyword']);
        if (isset($request['is_ajax']) && $request['is_ajax'] == 1) {
            $filter['keyword'] = json_str_iconv($filter['keyword']);
        }
        $filter['sort_by'] = empty($request['sort_by']) ? 'card_id' : trim($request['sort_by']);
        $filter['sort_order'] = empty($request['sort_order']) ? 'DESC' : trim($request['sort_order']);
        $filter['is_saled'] = isset($request['is_saled']) && $request['is_saled'] >= 0 ? intval($request['is_saled']) : '';

        $model = VirtualCard::query();

        if (!empty($filter['goods_id'])) {
            $model = $model->where('goods_id', $filter['goods_id']);
        }
        if (!empty($filter['order_sn'])) {
            $model = $model->where('order_sn', 'like', '%' . $filter['order_sn'] . '%');
        }

        if(!is_string($filter['is_saled'])) {
            $filter['is_saled'] = $filter['is_saled'] > 0 ? 1 : 0;
            $model = $model->where('is_saled', $filter['is_saled']);
        }

        if (!empty($filter['keyword'])) {
            if ($filter['search_type'] == 'card_sn') {
                $model = $model->where('card_sn', 'like', '%' . $filter['keyword'] . '%');
            } elseif ($filter['search_type'] == 'card_password') {
                $model = $model->where('card_password', dsc_encrypt($filter['keyword']));
            } else {
                $model = $model->where('order_sn', 'like', '%' . $filter['keyword'] . '%');
            }
        }

        $record_count = $model->count();

        $filter['record_count'] = $record_count;

        /* 分页大小 */
        $filter = page_and_size($filter);
        $start = ($filter['page'] - 1) * $filter['page_size'];

        // 存储最后一次过滤条件
        $this->dscRepository->setSessionFilter($filter, $param_str);

        $model = $model->offset($start)->limit($filter['page_size']);

        if (!empty($filter['sort_by'])) {
            $model = $model->orderBy($filter['sort_by'], $filter['sort_order']);
        }

        $model = $model->with([
            'getGoods' => function ($query) {
                $query->select('goods_id', 'goods_name');
            }
        ]);

        $model = $model->select('card_id', 'goods_id', 'card_sn', 'card_password', 'end_date', 'is_saled', 'order_sn', 'crc32')->get();

        $all = $model ? $model->toArray() : [];

        $arr = [];
        foreach ($all as $key => $row) {
            if ($row['crc32'] == 0 || $row['crc32'] == crc32(AUTH_KEY)) {
                $row['card_password'] = dsc_decrypt($row['card_password']);
            } elseif ($row['crc32'] == crc32(OLD_AUTH_KEY)) {
                $row['card_password'] = dsc_decrypt($row['card_password'], OLD_AUTH_KEY);
            } else {
                $row['card_sn'] = '***';
                $row['card_password'] = '***';
            }

            $row['end_date'] = $row['end_date'] == 0 ? '' : TimeRepository::getLocalDate(config('shop.date_format'), $row['end_date']);

            $row['goods_name'] = $row['get_goods']['goods_name'] ?? '';
            $row['is_saled_formated'] = $row['is_saled'] > 0 ? __('admin::common.yes') : __('admin::common.no');
            $arr[] = $row;
        }

        return ['item' => $arr, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']];
    }

    /**
     *
     * @param int $goods_id
     * @param string $card_sn
     * @return string
     */
    public static function card_sn_count($goods_id = 0, $card_sn = '')
    {
        if (blank($goods_id) || blank($card_sn)) {
            return '';
        }

        $count = VirtualCard::where('goods_id', $goods_id)->where('card_sn', $card_sn)->count();

        return $count;
    }

    /**
     * 添加
     * @param array $data
     * @return bool
     */
    public static function create_virtual_card($data = [])
    {
        if (blank($data)) {
            return false;
        }

        $data = BaseRepository::getArrayfilterTable($data, 'virtual_card');

        return VirtualCard::insert($data);
    }

    /**
     * 更新
     * @param int $card_id
     * @param array $data
     * @return bool
     */
    public static function update_virtual_card($card_id = 0, $data = [])
    {
        if (blank($card_id) || blank($data)) {
            return false;
        }

        $data = BaseRepository::getArrayfilterTable($data, 'virtual_card');

        return VirtualCard::where('card_id', $card_id)->update($data);
    }

    /**
     * 批量删除
     * @param array $card_id_arr
     * @return bool
     */
    public static function batch_delete_virtual_card($card_id_arr = [])
    {
        if (blank($card_id_arr)) {
            return false;
        }

        return VirtualCard::whereIn('card_id', $card_id_arr)->delete();
    }

    /**
     * 更新虚拟商品的商品数量
     *
     * @access  public
     * @param int $goods_id
     *
     * @return bool
     */
    public static function update_goods_number($goods_id = 0)
    {
        if (blank($goods_id)) {
            return false;
        }

        $goods_number = VirtualCard::where('goods_id', $goods_id)->where('is_saled', 0)->count();
        $goods_number = $goods_number ?? 0;

        return Goods::where('goods_id', $goods_id)->where('extension_code', 'virtual_card')->update(['goods_number' => $goods_number]);
    }

    /**
     * 查看密码
     * @param int $card_id
     * @return string
     */
    public static function getPassword($card_id = 0)
    {
        if (blank($card_id)) {
            return '';
        }

        $row = VirtualCard::where('card_id', $card_id)->select('card_password', 'crc32')->first();
        $row = $row ? $row->toArray() : [];

        $password = '';
        if (!empty($row)) {
            $password = dsc_decrypt($row['card_password']);
        }

        return $password;
    }

    /**
     * @param string $old_crc32
     * @param int $num
     * @return array
     */
    public static function get_virtual_card_by_crc32($old_crc32 = '', $num = 0)
    {
        $row = VirtualCard::where('crc32', $old_crc32)->select('card_id', 'card_password', 'card_sn')->get();

        if ($num > 0) {
            $row = $row->limit($num);
        }

        $row = $row ? $row->toArray() : [];

        return $row;
    }
}
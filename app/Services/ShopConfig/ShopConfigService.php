<?php

namespace App\Services\ShopConfig;

use App\Models\ShopConfig;
use App\Repositories\Common\BaseRepository;
use App\Repositories\Common\DscRepository;
use App\Repositories\ShopConfig\ShopConfigRepository;

/**
 * Class ShippingService
 * @package App\Services\Shipping
 */
class ShopConfigService
{
    protected $dscRepository;
    protected $shopConfigRepository;

    public function __construct(
        DscRepository $dscRepository,
        ShopConfigRepository $shopConfigRepository
    )
    {
        $this->dscRepository = $dscRepository;
        $this->shopConfigRepository = $shopConfigRepository;
    }

    /**
     * 获取所有的相关配置信息
     * @param string $code
     * @return array
     */
    public function get_all_lower_config($code = '')
    {
        if (empty($code)) {
            return [];
        }
        $parent_config = $this->shopConfigRepository->find_config_mess($code);
        if (empty($parent_config)) {
            return [];
        }
        $all_config = $this->shopConfigRepository->get_lower_config($parent_config['id']);
        if (empty($all_config)) {
            return [];
        }
        foreach ($all_config as $key => $val) {
            $all_config[$key]['img_path'] = '';
            if (!empty($val['value'])) {
                $all_config[$key]['img_path'] = $this->dscRepository->getImagePath('assets/' . $val['value']);
            }
        }
        return $all_config;
    }

    /**
     * 更新配置信息
     * @param string $code
     * @param array $date
     * @return bool
     */
    public function update_config($code = '', $date = [])
    {
        if (empty($code) || empty($date)) {
            return false;
        }
        return $this->shopConfigRepository->update_config($code, $date);
    }

    /**获取指定分组的配置
     * @param $groups
     * @return mixed
     */
    public function getShopConfig($groups = '')
    {
        /* 取出全部数据：分组和变量 */
        $item_list = ShopConfig::where('parent_id', '>', 0)
            ->where('type', '<>', 'hidden');

        if (!empty($groups)) {
            $item_list = $item_list->where('shop_group', $groups);
        }

        $item_list = $item_list->orderByRaw("sort_order, parent_id, id asc");

        return BaseRepository::getToArrayGet($item_list);
    }

    /**保存设置
     * @param array $value
     * @return bool
     */
    public function updateConfig($value = [])
    {
        if (!empty($value)) {
            foreach ($value as $key => $v) {
                ShopConfig::where('id', $key)->update(['value' => $v]);
            }
            return true;
        }
    }


}

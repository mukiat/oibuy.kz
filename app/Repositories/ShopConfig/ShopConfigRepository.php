<?php

namespace App\Repositories\ShopConfig;

use App\Models\ShopConfig;

class ShopConfigRepository
{
    /**
     * 获取配置信息
     * @param string $code
     * @return array
     */
    public function find_config_mess($code = '')
    {
        if (empty($code)) {
            return [];
        }
        $model = ShopConfig::where('code', $code);
        $result = $model->first();
        return $result ? $result->toArray() : [];
    }

    /**
     * 获取下级的所有配置信息
     * @param int $parent_id
     * @return array
     */
    public function get_lower_config($parent_id = 0){
        if(empty($parent_id)){
            return [];
        }
        $model = ShopConfig::where('parent_id', $parent_id);
        $result = $model->get();
        return $result ? $result->toArray() : [];
    }

    /**
     * 更新配置信息
     * @param string $code
     * @param array $date
     * @return bool
     */
    public function update_config($code = '', $date = [])
    {
        if(empty($code) || empty($date)){
            return false;
        }
        return ShopConfig::where('code', $code)->update($date);
    }
}

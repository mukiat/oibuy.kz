<?php

namespace App\Services\ECJia;

use App\Models\ShopConfig;
use App\Repositories\Common\BaseRepository;


/**
 * Class EcjiaConfigManageService
 * @package App\Services\ECJia
 */
class EcjiaConfigManageService
{
    // ecjia config
    public function ecjiaConfig($code)
    {
        $value = ShopConfig::where('code', $code)->value('value');
        $value = $value ?? '';
        return $value;
    }

    // ecjia config
    public function updateConfig($code, $value)
    {
        $data = ['value' => $value];
        ShopConfig::where('code', $code)->update($data);
    }
}

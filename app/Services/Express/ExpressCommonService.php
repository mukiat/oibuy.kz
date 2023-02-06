<?php

namespace App\Services\Express;

use App\Services\Common\PluginManageService;
use Illuminate\Support\Facades\DB;

/**
 *
 * Class ExpressCommonService
 * @package App\Services\Express
 */
class ExpressCommonService
{

    public static function oldConfig()
    {
        $count = DB::table('express')->count();
        if (!empty($count)) {
            return false;
        }

        $kdniao_client_id = DB::table('shop_config')->where('code', 'kdniao_client_id')->value('value');
        $kdniao_appkey = DB::table('shop_config')->where('code', 'kdniao_appkey')->value('value');

        $kuaidiniao_config = [
            'customer' => $kdniao_client_id ?? '',
            'key'=> $kdniao_appkey ?? ''
        ];

        $kuaidi100_config = config('services.kd100');

        $kuaidi100 = [
            'customer' => $kuaidi100_config['customer'] ?? '',
            'key'=> $kuaidi100_config['key'] ?? ''
        ];

        if (!empty($kuaidi100['customer']) && !empty($kuaidi100['key'])) {
            $install_code = 'kuaidi100';
        } else {
            $install_code = 'kuaidiniao';
        }

        $list = [
            // 快递100
            'kuaidi100' => [
                'customer' => $kuaidi100['customer'] ?? '',
                'key' => $kuaidi100['key'] ?? '',
            ],
            //快递鸟
            'kuaidiniao' => [
                'customer' => $kuaidiniao_config['customer'] ?? '',
                'key' => $kuaidiniao_config['key'] ?? '',
            ],
        ];

        $default_kadi_item = $list[$install_code];

        // 插件配置
        $cfg = app(PluginManageService::class)->getPluginConfig($install_code, 'Express');

        $data = [
            'name'           => $cfg['name'] ?? '',
            'description'   => $cfg['description'] ?? '',
            'website'       => $cfg['website'] ?? '',
            'sort'          => $cfg['sort'] ?? '',
            'enable'        => 1,
            'default'       => 1
        ];
        $express_configure = [];
        if (!empty($cfg['express_configure'])) {
            foreach ($cfg['express_configure'] as $value) {
                $value['value'] = $default_kadi_item[$value['name']] ?? '';

                $express_configure[] = $value;
            }
        }
        $data['express_configure'] = empty($express_configure) ? '' : \Opis\Closure\serialize($express_configure);

        return app(ExpressService::class)->create($install_code, $data);

}
}

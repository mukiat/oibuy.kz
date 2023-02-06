<?php

namespace App\Services\PersonalMerchants;

/**
 *
 * Class PersonalMerchantsService
 * @package App\Services\PersonalMerchants
 */
class PersonalMerchantsService
{
    /**
     * 个人入驻类是否存在 存在则实例化
     * @return \Illuminate\Contracts\Foundation\Application|mixed|null
     */
    public static function permerExists()
    {
        $permer = 'App\\Modules\\Seller\\Services\\PermerService';

        if (class_exists($permer)) {
            return app($permer);
        } else {
            return null;
        }
    }

}

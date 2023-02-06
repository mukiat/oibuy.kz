<?php

namespace App\Services\Cgroup;

/**
 * 社区团购外部服务类 实例类
 *
 * Class CgroupService
 * @package App\Services\Cgroup
 */
class CgroupService
{
    /*
    * 社区团购外部服务类是否存在 存在则实例化
    */
    public function postExists()
    {
        $post = 'App\\Modules\\Cgroup\\Services\\Post\\PostOutsideService';

        if (class_exists($post)) {
            return app($post);
        } else {
            return null;
        }
    }
}

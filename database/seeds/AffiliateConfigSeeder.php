<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AffiliateConfigSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 推荐注册赠送优惠券 配置
        $config_count = DB::table('shop_config')->where('code', 'affiliate_coupons')->count();
        if (empty($config_count)) {
            $parent_id = DB::table('shop_config')->where('code', 'hidden')->value('id');
            $parent_id = $parent_id ?? 0;

            $value = [
                'give_parent' => 1, // 上级是否可获得优惠券 0 否，1 是
                'give_register' => 0, // 注册人是否赠送优惠券 0 否，1 是
                'give_coupons_id' => 0, // 选择可赠送的优惠券id
            ];
            $row = [
                'parent_id' => $parent_id,
                'code' => 'affiliate_coupons',
                'type' => 'hidden',
                'value' => json_encode($value),
            ];
            DB::table('shop_config')->insert($row);
        }


        // 推荐注册赠送优惠券 开发菜单权限 code
        $count = DB::table('admin_action')->where('action_code', 'affiliate_coupons')->count();
        if (empty($count)) {
            // 父级菜单id  同 推荐分成父级菜单
            $parent_id = DB::table('admin_action')->where('action_code', 'sys_manage')->value('action_id');
            $parent_id = $parent_id ? $parent_id : 0;

            DB::table('admin_action')->insert([
                'parent_id' => $parent_id,
                'action_code' => 'affiliate_coupons',
                'seller_show' => 0, // 是否控制商家分配权限 0 否 1 是
            ]);
        }

    }
}

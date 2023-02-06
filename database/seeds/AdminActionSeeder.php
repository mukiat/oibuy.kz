<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminActionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->update();
        $this->addAction();
        $this->deleteAction();
    }

    /**
     * 权限配置
     */
    private function update()
    {

        $count = DB::table('admin_action')->where('action_code', 'cos_configure')->count();
        if ($count <= 0) {
            $parent_id = DB::table('admin_action')->where('action_code', 'third_party_service')->value('action_id');
            $parent_id = $parent_id ? $parent_id : 0;

            DB::table('admin_action')->insert([
                'parent_id' => $parent_id,
                'action_code' => 'cos_configure',
                'seller_show' => '0'
            ]);

            DB::table('shop_config')->where('code', 'cloud_storage')->update([
                'store_range' => '0,1,2'
            ]);
        }

        $count = DB::table('admin_action')->where('action_code', 'obs_configure')->count();
        if ($count <= 0) {
            $parent_id = DB::table('admin_action')->where('action_code', 'third_party_service')->value('action_id');
            $parent_id = $parent_id ? $parent_id : 0;

            DB::table('admin_action')->insert([
                'parent_id' => $parent_id,
                'action_code' => 'obs_configure',
                'seller_show' => '0'
            ]);
        }

        $count = DB::table('admin_action')->where('action_code', 'cloud_setting')->count();
        if ($count <= 0) {
            $parent_id = DB::table('admin_action')->where('action_code', 'third_party_service')->value('action_id');
            $parent_id = $parent_id ? $parent_id : 0;

            DB::table('admin_action')->insert([
                'parent_id' => $parent_id,
                'action_code' => 'cloud_setting',
                'seller_show' => '0'
            ]);
        }

        // H5自定义工具栏
        $count = DB::table('admin_action')->where('action_code', 'touch_nav_admin')->count();
        if (empty($count)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'ectouch')->value('action_id');
            $parent_id = $parent_id ? $parent_id : 0;

            DB::table('admin_action')->insert([
                'parent_id' => $parent_id,
                'action_code' => 'touch_nav_admin',
                'seller_show' => 0
            ]);
        }

        // 更新 储值卡权限 至促销模块
        $value_card = DB::table('admin_action')->where('action_code', 'value_card')->count();
        if (!empty($value_card)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'promotion')->value('action_id');
            $parent_id = $parent_id ? $parent_id : 0;

            DB::table('admin_action')->where('action_code', 'value_card')->update(['parent_id' => $parent_id]);
        }

        // 营销设置
        $favourable_setting = DB::table('admin_action')->where('action_code', 'favourable_setting')->count();
        if (empty($favourable_setting)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'promotion')->value('action_id');
            $parent_id = $parent_id ? $parent_id : 0;

            DB::table('admin_action')->insert([
                'parent_id' => $parent_id,
                'action_code' => 'favourable_setting',
                'seller_show' => 0
            ]);
        }

        // 快递跟踪配置权限
        $count = DB::table('admin_action')->where('action_code', 'express_setting')->count();
        if (empty($count)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'third_party_service')->value('action_id');
            $parent_id = $parent_id ? $parent_id : 0;

            DB::table('admin_action')->insert([
                'parent_id' => $parent_id,
                'action_code' => 'express_setting',
                'seller_show' => 0
            ]);
        }

        /* 订单导出权限 */
        $count = DB::table('admin_action')->where('action_code', 'order_export')->count();
        if (empty($count)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'order_manage')->value('action_id');
            $parent_id = $parent_id ? $parent_id : 0;
            DB::table('admin_action')->insert([
                'parent_id' => $parent_id,
                'action_code' => 'order_export',
                'seller_show' => 1
            ]);
        }

        $parent_id_merchants = DB::table('admin_action')->where('action_code', 'merchants')->value('action_id');
        $parent_id_merchants = $parent_id_merchants ? $parent_id_merchants : 0;

        // 结算工具菜单权限
        $count = DB::table('admin_action')->where('action_code', 'seller_divide')->count();
        if (empty($count) && $parent_id_merchants > 0) {
            // 默认数据
            $other = [
                'parent_id' => $parent_id_merchants,
                'action_code' => 'seller_divide',
                'seller_show' => 1
            ];
            DB::table('admin_action')->insert($other);
        }
        // 子商户进件申请 权限
        $count = DB::table('admin_action')->where('action_code', 'seller_divide_apply')->count();
        if (empty($count) && $parent_id_merchants > 0) {
            // 默认数据
            $other = [
                'parent_id' => $parent_id_merchants,
                'action_code' => 'seller_divide_apply',
                'seller_show' => 1
            ];
            DB::table('admin_action')->insert($other);
        }

        // 二级商户资金管理权限
        $count = DB::table('admin_action')->where('action_code', 'seller_divide_account')->count();
        if (empty($count) && $parent_id_merchants > 0) {
            // 默认数据
            $other = [
                'parent_id' => $parent_id_merchants,
                'action_code' => 'seller_divide_account',
                'seller_show' => 1, // 商家是否有
            ];
            DB::table('admin_action')->insert($other);
        }

        // 订单分账 权限
        $count = DB::table('admin_action')->where('action_code', 'order_divide')->count();
        if (empty($count) && $parent_id_merchants > 0) {
            // 默认数据
            $other = [
                'parent_id' => $parent_id_merchants,
                'action_code' => 'order_divide',
                'seller_show' => 0
            ];
            DB::table('admin_action')->insert($other);
        }

        /* 支付单权限 */
        $count = DB::table('admin_action')->where('action_code', 'pay_log')->count();
        if (empty($count)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'order_manage')->value('action_id');
            $parent_id = $parent_id ? $parent_id : 0;
            // 默认数据
            $other = [
                'parent_id' => $parent_id,
                'action_code' => 'pay_log',
                'seller_show' => 1
            ];
            DB::table('admin_action')->insert($other);
        }

        // h5首页咨询设置
        $count = DB::table('admin_action')->where('action_code', 'consult_set')->count();
        if (empty($count)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'ectouch')->value('action_id');
            $parent_id = $parent_id ? $parent_id : 0;
            // 默认数据
            $other = [
                'parent_id' => $parent_id,
                'action_code' => 'consult_set',
                'seller_show' => 0
            ];
            DB::table('admin_action')->insert($other);
        }

        // 词语过滤
        $count = DB::table('admin_action')->where('action_code', 'filter_words')->count();
        if (empty($count)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'sys_manage')->value('action_id');
            $parent_id = $parent_id ? $parent_id : 0;
            // 默认数据
            $other = [
                'parent_id' => $parent_id,
                'action_code' => 'filter_words',
                'seller_show' => 0
            ];
            DB::table('admin_action')->insert($other);
        }

        // 开放对外接口
        $count = DB::table('admin_action')->where('action_code', 'open_api')->count();
        if (empty($count)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'third_party_service')->value('action_id');
            $parent_id = $parent_id ? $parent_id : 0;
            // 默认数据
            $other = [
                'parent_id' => $parent_id,
                'action_code' => 'open_api',
                'seller_show' => 0
            ];
            DB::table('admin_action')->insert($other);
        }
    }

    /**
     * 补漏权限
     */
    public function addAction()
    {
        /* 促销权限 */
        $promotion_id = DB::table('admin_action')->where('action_code', 'promotion')->value('action_id');
        $promotion_id = $promotion_id ? $promotion_id : 0;

        $action_id = DB::table('admin_action')->where('action_code', 'topic_manage')->value('action_id');
        $action_id = $action_id ? $action_id : 0;

        if ($action_id < 1) {
            DB::table('admin_action')->insert([
                'parent_id' => $promotion_id,
                'action_code' => 'topic_manage',
                'seller_show' => 1
            ]);
        } else {
            DB::table('admin_action')->where('action_id', $action_id)->update([
                'seller_show' => 1
            ]);
        }

        $action_id = DB::table('admin_action')->where('action_code', 'snatch_manage')->value('action_id');
        $action_id = $action_id ? $action_id : 0;

        if ($action_id < 1) {
            DB::table('admin_action')->insert([
                'parent_id' => $promotion_id,
                'action_code' => 'snatch_manage',
                'seller_show' => 1
            ]);
        } else {
            DB::table('admin_action')->where('action_id', $action_id)->update([
                'seller_show' => 1
            ]);
        }

        $action_id = DB::table('admin_action')->where('action_code', 'favourable')->value('action_id');
        $action_id = $action_id ? $action_id : 0;

        if ($action_id < 1) {
            DB::table('admin_action')->insert([
                'parent_id' => $promotion_id,
                'action_code' => 'favourable',
                'seller_show' => 1
            ]);
        } else {
            DB::table('admin_action')->where('action_id', $action_id)->update([
                'seller_show' => 1
            ]);
        }

        $action_id = DB::table('admin_action')->where('action_code', 'package_manage')->value('action_id');
        $action_id = $action_id ? $action_id : 0;

        if ($action_id < 1) {
            DB::table('admin_action')->insert([
                'parent_id' => $promotion_id,
                'action_code' => 'package_manage',
                'seller_show' => 1
            ]);
        } else {
            DB::table('admin_action')->where('action_id', $action_id)->update([
                'seller_show' => 1
            ]);
        }

        $action_id = DB::table('admin_action')->where('action_code', 'auction')->value('action_id');
        $action_id = $action_id ? $action_id : 0;

        if ($action_id < 1) {
            DB::table('admin_action')->insert([
                'parent_id' => $promotion_id,
                'action_code' => 'auction',
                'seller_show' => 1
            ]);
        } else {
            DB::table('admin_action')->where('action_id', $action_id)->update([
                'seller_show' => 1
            ]);
        }

        /* 众筹权限 */
        $zc_id = DB::table('admin_action')->where('action_code', 'zc_manage')->value('action_id');
        $zc_id = $zc_id ? $zc_id : 0;

        if ($zc_id < 1) {
            $zc_id = DB::table('admin_action')->insertGetId([
                'parent_id' => 0,
                'action_code' => 'zc_manage',
                'seller_show' => 0
            ]);
        }

        $action_id = DB::table('admin_action')->where('action_code', 'zc_project_manage')->value('action_id');
        $action_id = $action_id ? $action_id : 0;

        if ($action_id < 1) {
            DB::table('admin_action')->insert([
                'parent_id' => $zc_id,
                'action_code' => 'zc_project_manage'
            ]);
        }

        $action_id = DB::table('admin_action')->where('action_code', 'zc_category_manage')->value('action_id');
        $action_id = $action_id ? $action_id : 0;

        if ($action_id < 1) {
            DB::table('admin_action')->insert([
                'parent_id' => $zc_id,
                'action_code' => 'zc_category_manage'
            ]);
        }

        $action_id = DB::table('admin_action')->where('action_code', 'zc_initiator_manage')->value('action_id');
        $action_id = $action_id ? $action_id : 0;

        if ($action_id < 1) {
            DB::table('admin_action')->insert([
                'parent_id' => $zc_id,
                'action_code' => 'zc_initiator_manage'
            ]);
        }

        $action_id = DB::table('admin_action')->where('action_code', 'zc_topic_manage')->value('action_id');
        $action_id = $action_id ? $action_id : 0;

        if ($action_id < 1) {
            DB::table('admin_action')->insert([
                'parent_id' => $zc_id,
                'action_code' => 'zc_topic_manage'
            ]);
        }

        $action_id = DB::table('admin_action')->where('action_code', 'ad_manage')->value('action_id');
        $action_id = $action_id ? $action_id : 0;

        if ($action_id < 1) {
            DB::table('admin_action')->insert([
                'parent_id' => $promotion_id,
                'action_code' => 'ad_manage',
                'seller_show' => 0
            ]);
        } else {
            DB::table('admin_action')->where('action_id', $action_id)->update([
                'seller_show' => 0
            ]);
        }

        // 活动标签权限
        $action_id = DB::table('admin_action')->where('action_code', 'goods_label')->value('action_id');
        $action_id = $action_id ? $action_id : 0;

        if ($action_id < 1) {
            $parent_id = DB::table('admin_action')->where('action_code', 'goods')->value('action_id');

            DB::table('admin_action')->insert([
                'parent_id' => $parent_id,
                'action_code' => 'goods_label',
                'seller_show' => 1
            ]);
        }

        /* 统计模块 */
        $action_id = DB::table('admin_action')->where('action_code', 'stats')->value('action_id');
        $action_id = $action_id ? $action_id : 0;

        if ($action_id < 1) {
            $action_id = DB::table('admin_action')->insertGetId([
                'parent_id' => 0,
                'action_code' => 'stats',
                'seller_show' => 0
            ]);

            $actions = [
                [
                    'parent_id' => $action_id,
                    'action_code' => 'shop_stats' // 店铺统计
                ],
                [
                    'parent_id' => $action_id,
                    'action_code' => 'user_stats' // 会员统计
                ],
                [
                    'parent_id' => $action_id,
                    'action_code' => 'sell_analysis' // 销售分析
                ],
                [
                    'parent_id' => $action_id,
                    'action_code' => 'industry_analysis' // 行业分析
                ],
                [
                    'parent_id' => $action_id,
                    'action_code' => 'exchange' // 积分明细
                ],
                [
                    'parent_id' => $action_id,
                    'action_code' => 'client_report_guest' // 客户统计
                ],
                [
                    'parent_id' => $action_id,
                    'action_code' => 'sale_order_stats' // 订单销售统计
                ],
                [
                    'parent_id' => $action_id,
                    'action_code' => 'client_flow_stats' //  客户流量统计
                ]
            ];

            foreach ($actions as $action) {
                DB::table('admin_action')->insert($action);
            }
        } else {
            DB::table('admin_action')->where('action_id', $action_id)->update([
                'seller_show' => 0
            ]);
        }

        /* 添加关键词权限 */
        $count = DB::table('admin_action')->where('action_code', 'goods_keyword')->count();

        if (empty($count)) {
            $action_id = DB::table('admin_action')->where('action_code', 'goods')->value('action_id');
            $action_id = $action_id ? $action_id : 0;

            $action = [
                'parent_id' => $action_id,
                'action_code' => 'goods_keyword',
                'seller_show' => 1
            ];
            DB::table('admin_action')->insert($action);
        }

        // 升级 v1.4.1 会员权益管理
        $count = DB::table('admin_action')->where('action_code', 'user_rights')->count();
        if (empty($count)) {
            $parent_id = DB::table('admin_action')->where('action_code', 'users_manage')->value('action_id');
            $parent_id = $parent_id ? $parent_id : 0;

            DB::table('admin_action')->insert([
                'parent_id' => $parent_id,
                'action_code' => 'user_rights',
                'seller_show' => '0'
            ]);
        }
    }

    /**
     * 删除无用权限
     */
    public function deleteAction()
    {
        DB::table('admin_action')->where('action_code', 'region_store')->delete();

        /* 删除赠品管理 */
        DB::table('admin_action')->where('action_code', 'gift_manage')->delete();

        /* 网店信息管理 */
        DB::table('admin_action')->where('action_code', 'shopinfo_manage')->delete();

        /* 网店帮助管理 */
        DB::table('admin_action')->where('action_code', 'shophelp_manage')->delete();

        /* 在线调查管理 */
        DB::table('admin_action')->where('action_code', 'vote_priv')->delete();

        /* 首页主广告管理 */
        DB::table('admin_action')->where('action_code', 'flash_manage')->delete();

        /* 文件校验 */
        DB::table('admin_action')->where('action_code', 'file_check')->delete();

        /* 授权证书 */
        DB::table('admin_action')->where('action_code', 'shop_authorized')->delete();

        /* 网罗天下管理 */
        DB::table('admin_action')->where('action_code', 'webcollect_manage')->delete();
    }

}

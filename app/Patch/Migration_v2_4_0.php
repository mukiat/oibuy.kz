<?php

namespace App\Patch;

use App\Contracts\MigrationContract;
use App\Events\RunOtherModulesSeederEvent;
use App\Models\AdminAction;
use App\Models\ShopConfig;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Migration_v2_4_0 implements MigrationContract
{
    public function run()
    {
        try {
            $this->migration();
            $this->seed();
            $this->clean();
            // 执行其他模块seed
            event(new RunOtherModulesSeederEvent());
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    /**
     * 数据库迁移
     * @return mixed|void
     */
    public function migration()
    {
        // 优惠券增加软删除
        $this->addCouponDeleteStatus();

        if (file_exists(MOBILE_WXSHOP)) {
            // 增加小商店开关
            $this->add_switch_config();
        }
    }

    /**
     * 数据库填充
     * @return mixed|void
     */
    public function seed()
    {
        // 下载模块分组
        $this->add_download_group();

        // 转移是否开启举报
        $this->move_is_illegal();

        if (file_exists(MOBILE_WXSHOP)) {
            $this->wxshop();
        }

        // 版本释放之前更新版本
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v2.4.0'
        ]);
    }

    public function wxshop()
    {
        $parent_id = AdminAction::where('action_code', 'seller_wxshop')->value('action_id');
        $parent_id = $parent_id ? $parent_id : 0;

        //商户后台  小商店商品分类
        $action_id = AdminAction::where('action_code', 'seller_wxshop_shop_cat')->value('action_id');
        $action_id = $action_id ? $action_id : 0;
        if (empty($action_id)) {
            AdminAction::insert([
                'parent_id' => $parent_id,
                'action_code' => 'seller_wxshop_shop_cat',
                'seller_show' => 1
            ]);
        } else {
            if (!empty($parent_id)) {
                AdminAction::where('action_code', 'seller_wxshop_shop_cat')->update([
                    'parent_id' => $parent_id
                ]);
            }
        }
    }

    /**
     * @return mixed|void
     * @throws \Exception
     */
    public function clean()
    {
        cache()->flush();
    }

    /**
     * 优惠券增加软删除
     */
    private function addCouponDeleteStatus()
    {
        /* 优惠券增加软删除 */
        $tableName = 'coupons_user';
        if (Schema::hasTable($tableName)) {
            if (!Schema::hasColumn($tableName, 'is_delete')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->tinyInteger('is_delete')->default(0)->unsigned()->comment('优惠券删除状态：0 未删除 1 已删除');
                });
            }
        }
    }

    /**
     * 转移下载模块分组
     */
    protected function add_download_group()
    {
        $result = DB::table('shop_config')->where('code', 'pc_download_open')->count();
        if ($result) {
            // 下载模块分组
            $parent_id = DB::table('shop_config')->where('code', 'download')->where('type', 'group')->value('id');
            if (empty($parent_id)) {
                // 默认数据
                $rows = [
                    'parent_id' => 0,
                    'code' => 'download',
                    'value' => 0,
                    'type' => 'group',
                    'store_range' => '',
                    'sort_order' => 3,
                    'shop_group' => ''
                ];
                $parent_id = DB::table('shop_config')->insertGetId($rows);
            }

            // 转移至下载模块
            $parent_id = !empty($parent_id) ? $parent_id : 0;
            if ($parent_id > 0) {
                $pay_code = [
                    'pc_download_open',
                    'pc_download_img'
                ];
                DB::table('shop_config')->whereIn('code', $pay_code)->where('parent_id', '<>', $parent_id)->update(['parent_id' => $parent_id, 'shop_group' => '']);
            }
        }
    }

    /**
     * 转移是否开启举报
     */
    protected function move_is_illegal()
    {
        $result = DB::table('shop_config')->where('code', 'is_illegal')->count();
        if ($result) {
            $parent_id = DB::table('shop_config')->where('code', 'extend_basic')->where('type', 'group')->value('id');
            // 转移至举报模块
            $parent_id = !empty($parent_id) ? $parent_id : 0;
            if ($parent_id > 0) {
                $code = [
                    'is_illegal'
                ];
                DB::table('shop_config')->whereIn('code', $code)->where('parent_id', '<>', $parent_id)->update(['parent_id' => $parent_id, 'shop_group' => 'report_conf', 'sort_order' => '14']);
            }
        }
    }

    /**
     * 平台后台 小商店开关
     * @return bool
     */
    protected function add_switch_config()
    {
        $tableName = 'seller_shopinfo';
        if (!Schema::hasTable($tableName)) {
            return false;
        }

        // 判断字段是否存在添加
        if (!Schema::hasColumn($tableName, 'switch_config')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->tinyInteger('switch_config')->unsigned()->default(0)->comment('小商店开关 0：关闭 1：开启');
            });
        }
    }

}

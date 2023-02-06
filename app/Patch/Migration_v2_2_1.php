<?php

namespace App\Patch;

use App\Models\ShopConfig;
use App\Models\TouchPageView;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class Migration_v2_2_1
{
    public function run()
    {
        try {
            $this->goods_label();
            $this->value_card_record();
            $this->order_info();
            $this->addQueueTable();

            // 增加退款模块分组
            $this->add_return_group();

            $this->seller_commission_bill();
            $this->seller_bill_order();

            $this->upgradePageView();
            $this->shopConfig();
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    public function addQueueTable()
    {
        $table = 'failed_jobs';
        if (!Schema::hasTable($table)) {
            Schema::create($table, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->text('connection');
                $table->text('queue');
                $table->longText('payload');
                $table->longText('exception');
                $table->timestamp('failed_at')->useCurrent();
            });
        }

        $table = 'order_export_history';
        if (!Schema::hasTable($table)) {
            Schema::create($table, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedInteger('ru_id')->comment('店铺ID');
                $table->string('file_name')->comment('文件名');
                $table->string('file_type')->comment('文件格式');
                $table->string('download_params')->comment('下载参数');
                $table->string('download_url')->comment('下载地址');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * 增加退款模块分组
     */
    protected static function add_return_group()
    {
        $parent_id = DB::table('shop_config')->where('code', 'return')->where('type', 'group')->value('id');
        if (empty($parent_id)) {
            // 默认数据
            $rows = [
                'parent_id' => 0,
                'code' => 'return',
                'value' => 0,
                'type' => 'group',
                'store_range' => '',
                'sort_order' => 50,
                'shop_group' => ''
            ];
            $parent_id = DB::table('shop_config')->insertGetId($rows);
        }

        if ($parent_id > 0) {
            // 转移至退款模块

            // 发货日期起可退换货时间
            DB::table('shop_config')->where('code', 'sign')->where('parent_id', '<>', $parent_id)->update(['parent_id' => $parent_id, 'shop_group' => 'return', 'sort_order' => 1]);
            //  被拒退换货激活设置
            DB::table('shop_config')->where('code', 'activation_number_type')->where('parent_id', '<>', $parent_id)->update(['parent_id' => $parent_id, 'shop_group' => 'return', 'sort_order' => 2]);

            // 商家退款审批
            $result = DB::table('shop_config')->where('code', 'seller_return_check')->count();
            if (empty($result)) {
                // 默认数据
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'seller_return_check',
                        'value' => 1,
                        'type' => 'select',
                        'store_range' => '1,0',
                        'sort_order' => 3,
                        'shop_group' => 'return'
                    ]
                ];
                DB::table('shop_config')->insert($rows);
            }

            // 商家退款 优先退款方式
            $result = DB::table('shop_config')->where('code', 'precedence_return_type')->count();
            if (empty($result)) {
                // 默认数据
                $rows = [
                    [
                        'parent_id' => $parent_id,
                        'code' => 'precedence_return_type',
                        'value' => 6,
                        'type' => 'options',
                        'store_range' => '1,2,6',
                        'sort_order' => 4,
                        'shop_group' => 'return'
                    ]
                ];
                DB::table('shop_config')->insert($rows);
            }
        }
    }

    public function upgradePageView()
    {
        // 更新店铺 设备 h5
        $result = TouchPageView::where('ru_id', '>', 0)->where('type', 'store')->where('device', '')->count();
        if ($result > 0) {
            TouchPageView::where('ru_id', '>', 0)->where('type', 'store')->where('device', '')->update([
                'device' => 'h5'
            ]);
        }
    }

    public function value_card_record()
    {
        $table_name = 'value_card_record';
        if (!Schema::hasTable($table_name)) {
            return false;
        }

        if (!Schema::hasColumn($table_name, 'change_desc')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->string('change_desc')->default('')->comment('操作记录');
            });
        }

        if (!Schema::hasColumn($table_name, 'vc_dis_money')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->decimal('vc_dis_money', 10, 2)->default(0)->comment('储值卡折扣金额');
            });
        }
    }

    public function order_info()
    {
        $tableName = 'order_info';
        if (!Schema::hasTable($tableName)) {
            return false;
        }

        if (Schema::hasColumn($tableName, 'uc_id')) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->string('uc_id', 255)->change();
            });
        }
    }

    public function seller_commission_bill()
    {
        $table_name = 'seller_commission_bill';
        if (!Schema::hasTable($table_name)) {
            return false;
        }

        if (!Schema::hasColumn($table_name, 'return_rate_fee')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->decimal('return_rate_fee', 10, 2)->default(0)->comment('跨境税费退款金额');
            });
        }
    }

    public function seller_bill_order()
    {
        $table_name = 'seller_bill_order';
        if (!Schema::hasTable($table_name)) {
            return false;
        }

        if (!Schema::hasColumn($table_name, 'return_rate_fee')) {
            Schema::table($table_name, function (Blueprint $table) {
                $table->decimal('return_rate_fee', 10, 2)->default(0)->comment('跨境税费退款金额');
            });
        }
    }

    /**
     * 更新版本
     * @throws Exception
     */
    private function shopConfig()
    {
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v2.2.1'
        ]);

        $this->clearCache();
    }

    /**
     * @throws Exception
     */
    private function clearCache()
    {
        cache()->flush();
    }

    /**
     * 商品标签
     * @throws Exception
     */
    private function goods_label()
    {
        $name = 'goods_label';
        if (!Schema::hasTable($name)) {
            Schema::create('goods_label', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('自增ID');
                $table->string('label_name')->default('')->comment('标签名称');
                $table->string('label_image')->default('')->comment('标签图片');
                $table->integer('sort')->default(50)->comment('标签排序');
                $table->tinyInteger('merchant_use')->unsigned()->default(0)->comment('商家可用：0 否， 1 是');
                $table->tinyInteger('status')->unsigned()->default(0)->comment('标签状态：0 关闭， 1 开启');
                $table->string('label_url')->default('')->comment('标签链接，填写可跳转到指定url');
                $table->integer('add_time')->default(0)->comment('添加时间');
            });

            $prefix = config('database.connections.mysql.prefix');
            DB::statement("ALTER TABLE `" . $prefix . "$name` comment '商品标签表'");
        }

        $name = 'goods_use_label';
        if (!Schema::hasTable($name)) {
            Schema::create('goods_use_label', function (Blueprint $table) {
                $table->bigIncrements('id')->comment('自增ID');
                $table->string('label_id')->default(0)->comment('标签ID');
                $table->string('goods_id')->default(0)->comment('商品ID');
                $table->integer('add_time')->default(0)->comment('添加时间');
            });

            $prefix = config('database.connections.mysql.prefix');
            DB::statement("ALTER TABLE `" . $prefix . "$name` comment '商品标签使用表'");
        }

        // 活动标签权限
        $action_id = DB::table('admin_action')->where('action_code', 'goods_label')->value('action_id');
        $action_id = $action_id ? $action_id : 0;

        if ($action_id < 1) {
            $parent_id = DB::table('admin_action')->where('action_code', 'goods')->value('action_id');
            $parent_id = $parent_id ? $parent_id : 0;

            DB::table('admin_action')->insert([
                'parent_id' => $parent_id,
                'action_code' => 'goods_label',
                'seller_show' => 1
            ]);
        }
    }
}

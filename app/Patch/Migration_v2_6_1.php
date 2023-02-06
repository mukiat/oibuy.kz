<?php

namespace App\Patch;

use App\Events\RunOtherModulesSeederEvent;
use App\Models\ShopConfig;
use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Migration_v2_6_1
{
    public function run()
    {
        try {
            $this->migration();

            // 执行其他模块seed
            event(new RunOtherModulesSeederEvent());

            $this->seed();
            $this->clean();
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }

    private function migration()
    {
        $this->createLotteryTable();
        $this->exchange_goods();
        $this->create_order_info_bank_transfer();
    }

    public function exchange_goods()
    {
        $table = 'exchange_goods';
        if (!Schema::hasTable($table)) {
            return false;
        }

        if (!Schema::hasColumn($table, 'sales_volume')) {
            Schema::table($table, function (Blueprint $table) {
                $table->integer('sales_volume')->default(0)->comment("积分商品销量");
            });
        }
    }

    /**
     * 创建订单抽奖活动表
     */
    private function createLotteryTable()
    {
        if (!Schema::hasTable('lotteries')) {
            Schema::create('lotteries', function (Blueprint $table) {
                $table->increments('id')->comment('自增ID');
                $table->unsignedInteger('ru_id')->comment('店铺ID');
                $table->boolean('active_state')->comment('活动状态');
                $table->timestamp('start_time')->comment('活动开始时间');
                $table->timestamp('end_time')->comment('活动结束时间');
                $table->text('active_desc')->comment('活动说明');
                $table->text('participant')->comment('活动对象');
                $table->decimal('single_amount')->comment('单笔金额');
                $table->text('participate_number')->comment('参与次数');
            });

            Schema::create('lottery_prizes', function (Blueprint $table) {
                $table->increments('id')->comment('自增ID');
                $table->unsignedInteger('ru_id')->comment('店铺ID');
                $table->unsignedInteger('lottery_id')->comment('活动ID');
                $table->text('prize_name')->comment('奖品名称');
                $table->text('prize_image')->comment('奖品图片');
                $table->text('prize_type')->comment('奖品类型结构体{type:"t",config:[]}');
                $table->unsignedInteger('prize_number')->comment('奖品数量');
                $table->unsignedInteger('prize_issued')->comment('已发放');
                $table->unsignedInteger('prize_prob')->comment('中奖概率');
            });

            Schema::create('lottery_records', function (Blueprint $table) {
                $table->increments('id')->comment('自增ID');
                $table->unsignedInteger('lottery_id')->comment('活动ID');
                $table->unsignedInteger('user_id')->comment('会员ID');
                $table->text('user_name')->comment('会员名称');
                $table->unsignedInteger('lottery_prize_id')->comment('奖品ID');
                $table->text('prize_name')->comment('奖品名称');
                $table->text('prize_type')->comment('奖品类型');
                $table->text('prize')->comment('奖品');
                $table->text('channel')->comment('参与渠道');
                $table->timestamp('created_at')->comment('参与时间');
            });
        }
    }

    protected function create_order_info_bank_transfer()
    {
        $tableName = 'order_info_bank_transfer';
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->increments('id')->comment('自增ID号');
                $table->integer('user_id')->unsigned()->default(0)->index('user_id')->comment('关联用户user_id');
                $table->integer('order_id')->unsigned()->default(0)->index('order_id')->comment('关联订单order_id');
                $table->string('payee_name')->default('')->comment('收款人姓名');
                $table->string('bank_no')->default('')->comment('收款人银行卡号');
                $table->string('bank_name')->default('')->comment('收款人开户银行');
                $table->string('bank_branch')->default('')->comment('银行支行');
                $table->string('mark')->default('')->comment('备注说明');
                $table->string('pay_document')->default('')->comment('上传汇款转账凭证');
            });

            $prefix = config('database.connections.mysql.prefix');
            DB::statement("ALTER TABLE `" . $prefix . "$tableName` comment '银行汇款转账信息'");
        }
    }

    private function seed()
    {
        /* 使用 constant id 定义 */
        $count = ShopConfig::where('code', 'app_field')->count();

        if ($count == 0) {

            $parent_id = ShopConfig::where('code', 'extend_basic')->value('id');
            $parent_id = $parent_id ? $parent_id : 0;

            ShopConfig::insert([
                'parent_id' => $parent_id,
                'code' => 'app_field',
                'type' => 'hidden',
                'store_range' => '0,1', //0【where($field, '>', 0)】 1【whereBetween($field, [1, constant配置值])】
                'value' => 0
            ]);
        }

        // 版本释放之前更新版本
        ShopConfig::where('code', 'dsc_version')->update([
            'value' => 'v2.6.1'
        ]);
    }

    private function clean()
    {
        cache()->flush();
    }
}

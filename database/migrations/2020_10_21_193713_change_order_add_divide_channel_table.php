<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeOrderAddDivideChannelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 订单表
        $tableName = 'order_info';
        if (Schema::hasTable($tableName)) {
            // 判断字段是否存在添加
            if (!Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->integer('divide_channel')->unsigned()->default(0)->index()->comment('分账渠道：0 无，1 微信收付通');
                });
            }
        }
        
        $tableName = 'order_return';
        if (Schema::hasTable($tableName)) {
            // 判断字段是否存在添加
            if (!Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->integer('divide_channel')->unsigned()->default(0)->index()->comment('分账渠道：0 无，1 微信收付通');
                });
            }
        }

        // 支付日志表
        $tableName = 'pay_log';
        if (Schema::hasTable($tableName)) {
            // 判断字段是否存在添加
            if (!Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->integer('divide_channel')->unsigned()->default(0)->index()->comment('分账渠道：0 无，1 微信收付通');
                });
            }
        }

        // 商家订单账单表
        $tableName = 'seller_bill_order';
        if (Schema::hasTable($tableName)) {
            // 判断字段是否存在添加
            if (!Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->integer('divide_channel')->unsigned()->default(0)->index()->comment('分账渠道：0 无，1 微信收付通');
                });
            }
        }
        $tableName = 'seller_commission_bill';
        if (Schema::hasTable($tableName)) {
            // 判断字段是否存在添加
            if (!Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->integer('divide_channel')->unsigned()->default(0)->index()->comment('分账渠道：0 无，1 微信收付通');
                });
            }
        }
        
        $tableName = 'seller_negative_order';
        if (Schema::hasTable($tableName)) {
            // 判断字段是否存在添加
            if (!Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->integer('divide_channel')->unsigned()->default(0)->index()->comment('分账渠道：0 无，1 微信收付通');
                });
            }
        }
        $tableName = 'seller_negative_bill';
        if (Schema::hasTable($tableName)) {
            // 判断字段是否存在添加
            if (!Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->integer('divide_channel')->unsigned()->default(0)->index()->comment('分账渠道：0 无，1 微信收付通');
                });
            }
        }
        
        $tableName = 'seller_account_log';
        if (Schema::hasTable($tableName)) {
            // 判断字段是否存在添加
            if (!Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->integer('divide_channel')->unsigned()->default(0)->index()->comment('分账渠道：0 无，1 微信收付通');
                });
            }
        }
        $tableName = 'merchants_account_log';
        if (Schema::hasTable($tableName)) {
            // 判断字段是否存在添加
            if (!Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->integer('divide_channel')->unsigned()->default(0)->index()->comment('分账渠道：0 无，1 微信收付通');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tableName = 'order_info';
        if (Schema::hasTable($tableName)) {
            if (Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('divide_channel');
                });
            }
        }
        $tableName = 'order_return';
        if (Schema::hasTable($tableName)) {
            if (Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('divide_channel');
                });
            }
        }

        $tableName = 'pay_log';
        if (Schema::hasTable($tableName)) {
            if (Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('divide_channel');
                });
            }
        }

        $tableName = 'seller_bill_order';
        if (Schema::hasTable($tableName)) {
            if (Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('divide_channel');
                });
            }
        }
        
        $tableName = 'seller_commission_bill';
        if (Schema::hasTable($tableName)) {
            if (Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('divide_channel');
                });
            }
        }
        
        $tableName = 'seller_negative_order';
        if (Schema::hasTable($tableName)) {
            if (Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('divide_channel');
                });
            }
        }
        
        $tableName = 'seller_negative_bill';
        if (Schema::hasTable($tableName)) {
            if (Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('divide_channel');
                });
            }
        }
        
        $tableName = 'seller_account_log';
        if (Schema::hasTable($tableName)) {
            if (Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('divide_channel');
                });
            }
        }
        
        $tableName = 'merchants_account_log';
        if (Schema::hasTable($tableName)) {
            if (Schema::hasColumn($tableName, 'divide_channel')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('divide_channel');
                });
            }
        }
    }
}

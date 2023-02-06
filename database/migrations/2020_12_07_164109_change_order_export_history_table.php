<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeOrderExportHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('order_export_history') && !Schema::hasTable('export_history')) {
            Schema::rename('order_export_history', 'export_history');
        }

        if (!Schema::hasColumn('export_history', 'type')) {
            Schema::table('export_history', function (Blueprint $table) {
                $table->string('type')->comment('导出类型');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('export_history', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        if (Schema::hasTable('export_history')) {
            Schema::rename('export_history', 'order_export_history');
        }
    }
}

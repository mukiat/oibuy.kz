<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ChangeConnectUserOpenidTable extends Migration
{
    protected $tableName = 'connect_user';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->tableName)) {
            return false;
        }

        if (Schema::hasColumn($this->tableName, 'open_id')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->dropIndex('open_id');
                $table->index('open_id', 'open_id');
            });
        }

        if (Schema::hasColumn($this->tableName, 'connect_code')) {
            // 增加索引
            if (!DB::table($this->tableName)->hasIndex('connect_code')) {
                Schema::table($this->tableName, function (Blueprint $table) {
                    $table->index('connect_code', 'connect_code');
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
        if (!Schema::hasTable($this->tableName)) {
            return false;
        }

        if (Schema::hasColumn($this->tableName, 'open_id')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->dropIndex('open_id');
                $table->dropIndex('connect_code');
            });
        }
    }
}

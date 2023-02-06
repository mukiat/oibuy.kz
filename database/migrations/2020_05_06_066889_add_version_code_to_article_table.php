<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVersionCodeToArticleTable extends Migration
{
    protected $tableName = 'article';
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

        if (!Schema::hasColumn($this->tableName, 'version_code')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                // 版本号
                $table->decimal('version_code', 5, 1)->default(1)->comment('文章版本号');
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
        if (Schema::hasColumn($this->table, 'version_code')) {
            Schema::table($this->table, function (Blueprint $table) {
                $table->dropColumn('version_code');
            });
        }
    }
}

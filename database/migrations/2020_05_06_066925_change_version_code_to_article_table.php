<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeVersionCodeToArticleTable extends Migration
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

        if (Schema::hasColumn($this->tableName, 'version_code')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->integer('version_code')->default(1)->comment('文章版本号')->change();
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
        if (Schema::hasColumn($this->tableName, 'version_code')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->integer('version_code')->default(1)->comment('文章版本号')->change();
            });
        }
    }
}

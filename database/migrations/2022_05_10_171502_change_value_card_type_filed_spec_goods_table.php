<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeValueCardTypeFiledSpecGoodsTable extends Migration
{
    private $name = 'value_card_type';

    /**
     * @return bool
     */
    public function up()
    {
        if (Schema::hasTable($this->name)) {
            if (Schema::hasColumn($this->name, 'spec_goods')) {
                Schema::table($this->name, function (Blueprint $table) {
                    $table->text('spec_goods')->default('')->change();
                });
            }

            if (Schema::hasColumn($this->name, 'spec_cat')) {
                Schema::table($this->name, function (Blueprint $table) {
                    $table->text('spec_cat')->default('')->change();
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
        if (Schema::hasTable($this->name)) {
            if (Schema::hasColumn($this->name, 'spec_goods')) {
                Schema::table($this->name, function (Blueprint $table) {
                    $table->string('spec_goods')->default('')->change();
                });
            }

            if (Schema::hasColumn($this->name, 'spec_cat')) {
                Schema::table($this->name, function (Blueprint $table) {
                    $table->string('spec_cat')->default('')->change();
                });
            }
        }
    }
}

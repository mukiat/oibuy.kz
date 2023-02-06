<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLinkUrlToSellerFollowListTable extends Migration
{
    private $name = 'seller_follow_list';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->name)) {
            return false;
        }

        if (!Schema::hasColumn($this->name, 'link_url')) {
            Schema::table($this->name, function (Blueprint $table) {
                $table->string('link_url')->default('')->comment("外部链接");
            });
        }

        if (!Schema::hasColumn($this->name, 'click_count')) {
            Schema::table($this->name, function (Blueprint $table) {
                $table->integer('click_count')->default(0)->comment("访问量");
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
        Schema::table($this->name, function (Blueprint $table) {
            //
        });
    }
}

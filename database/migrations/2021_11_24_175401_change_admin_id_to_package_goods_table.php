<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeAdminIdToPackageGoodsTable extends Migration
{
    /**
     * @return bool
     */
    public function up()
    {
        if (Schema::hasTable('package_goods')) {
            if (Schema::hasColumn('package_goods', 'admin_id')) {
                Schema::table('package_goods', function (Blueprint $table) {
                    $table->integer('admin_id')->unsigned()->change();
                });
            }
        }

        if (Schema::hasTable('admin_message')) {
            if (Schema::hasColumn('admin_message', 'sender_id')) {
                Schema::table('admin_message', function (Blueprint $table) {
                    $table->integer('sender_id')->unsigned()->change();
                });
            }
            if (Schema::hasColumn('admin_message', 'receiver_id')) {
                Schema::table('admin_message', function (Blueprint $table) {
                    $table->integer('receiver_id')->unsigned()->change();
                });
            }
        }

        if (Schema::hasTable('goods_article')) {
            if (Schema::hasColumn('goods_article', 'admin_id')) {
                Schema::table('goods_article', function (Blueprint $table) {
                    $table->integer('admin_id')->unsigned()->change();
                });
            }
        }

        if (Schema::hasTable('group_goods')) {
            if (Schema::hasColumn('group_goods', 'admin_id')) {
                Schema::table('group_goods', function (Blueprint $table) {
                    $table->integer('admin_id')->unsigned()->change();
                });
            }
            if (Schema::hasColumn('group_goods', 'group_id')) {
                Schema::table('group_goods', function (Blueprint $table) {
                    $table->integer('group_id')->unsigned()->change();
                });
            }
        }

        if (Schema::hasTable('link_goods')) {
            if (Schema::hasColumn('link_goods', 'admin_id')) {
                Schema::table('link_goods', function (Blueprint $table) {
                    $table->integer('admin_id')->unsigned()->change();
                });
            }
        }

        if (Schema::hasTable('users_real')) {
            if (Schema::hasColumn('users_real', 'user_id')) {
                Schema::table('users_real', function (Blueprint $table) {
                    $table->integer('user_id')->unsigned()->default(0)->change();
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

    }
}

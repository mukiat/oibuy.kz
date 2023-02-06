<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeBirthdayToUsersTable extends Migration
{
    protected $table_name = 'users';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable($this->table_name)) {
            return false;
        }

        if (Schema::hasColumn($this->table_name, 'birthday')) {
            Schema::table($this->table_name, function (Blueprint $table) {
                $table->string('birthday')->default('1980-01-01')->comment('生日日期')->change();
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
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}

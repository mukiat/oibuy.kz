<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeMerchantsStepsFieldsTable extends Migration
{

    /**
     * Run the migrations.
     */
    public function up()
    {
        if (Schema::hasColumn('merchants_steps_fields', 'companyName')) {
            Schema::table('merchants_steps_fields', function (Blueprint $table) {
                $table->renameColumn('companyName', 'company');
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
        if (Schema::hasColumn('merchants_steps_fields', 'company')) {
            Schema::table('merchants_steps_fields', function (Blueprint $table) {
                $table->renameColumn('company', 'companyName');
            });
        }
    }
}

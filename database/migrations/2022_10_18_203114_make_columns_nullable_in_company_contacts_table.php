<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeColumnsNullableInCompanyContactsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('company_contacts', function (Blueprint $table) {
            $table->string('email', 191)->nullable()->change();
            $table->string('phone_number', 191)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('company_contacts', function (Blueprint $table) {
            $table->string('email', 191)->nullable(false)->change();
            $table->string('phone_number', 191)->nullable(false)->change();
        });
    }
}

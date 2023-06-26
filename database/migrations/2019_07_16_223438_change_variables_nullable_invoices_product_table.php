<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeVariablesNullableInvoicesProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices_products', function (Blueprint $table) {
            $table->string('clave_unidad_sat', 255)->nullable()->change();
            $table->string('unidad_sat', 255)->nullable()->change();
            $table->string('clave_sat', 255)->nullable()->change();
            $table->text('description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}

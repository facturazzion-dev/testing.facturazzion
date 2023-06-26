<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateInvoicesProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /* Fields for products that are not registered. */
        Schema::table('invoices_products', function (Blueprint $table) {
            $table->integer('product_id')->nullable()->change();
            $table->integer('organization_id');
            $table->integer('company_id');
            $table->integer('user_id');
            $table->string('sku', 191)->nullable();
            $table->float('discount')->default(0);
            $table->float('total');
            $table->string('clave_unidad_sat', 255);
            $table->string('unidad_sat', 255);
            $table->string('clave_sat', 255);
            $table->text('description');
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

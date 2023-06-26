<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesOrdersProductsTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_order_products_taxes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sale_order_id');
            $table->integer('product_id')->nullable();
            $table->integer('organization_id');
            $table->integer('company_id');
            $table->integer('user_id');
            $table->integer('tax_id')->nullable();
            $table->integer('sale_order_product_id');
            $table->timestamps();
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
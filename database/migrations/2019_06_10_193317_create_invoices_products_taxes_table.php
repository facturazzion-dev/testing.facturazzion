<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoicesProductsTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices_products_taxes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('invoice_id');
            $table->integer('product_id')->nullable();
            $table->integer('organization_id');
            $table->integer('company_id');
            $table->integer('user_id');
            $table->integer('tax_id')->nullable();
            $table->integer('invoice_product_id');
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
        Schema::dropIfExists('invoices_products_taxes');
    }
}

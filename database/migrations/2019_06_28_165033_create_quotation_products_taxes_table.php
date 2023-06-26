<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuotationProductsTaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotations_products_taxes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('quotation_id');
            $table->integer('product_id')->nullable();
            $table->integer('organization_id');
            $table->integer('company_id');
            $table->integer('user_id');
            $table->integer('tax_id');
            $table->integer('quotation_product_id');
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

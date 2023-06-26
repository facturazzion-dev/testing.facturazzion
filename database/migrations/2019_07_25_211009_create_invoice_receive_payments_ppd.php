<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoiceReceivePaymentsPpd extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_receive_payments_ppd', function (Blueprint $table){
            $table->increments('id');
            $table->string('invoice_id');
            $table->integer('user_id');
            $table->integer('organization_id');
            $table->integer('customer_id')->nullable();
            $table->integer('is_delete_list')->nullable();
            $table->integer('company_id')->nullable();
            $table->integer('invoice_receive_payment_id');
            $table->string('invoice_serie')->nullable();
            $table->integer('invoice_folio')->nullable();
            $table->string('invoice_uuid');
            $table->date('payment_date');
            $table->string('currency');
            $table->float('total', 8,2);
            $table->integer('partiality');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('invoice_receive_payments_ppd');
    }
}

<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateInvoiceReceivePayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoice_receive_payments', function(Blueprint $table){
            $table->dropColumn('invoice_id');
            $table->string('payment_type');
            $table->string('currency');
            $table->string('exchange_rate');
            $table->string('company_bank')->nullable();
            $table->string('organization_bank')->nullable();
            $table->string('transaction_number')->nullable();
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

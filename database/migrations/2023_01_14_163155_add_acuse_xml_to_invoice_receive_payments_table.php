<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAcuseXmlToInvoiceReceivePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoice_receive_payments', function (Blueprint $table) {
            $table->text('acuse_xml')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoice_receive_payments', function (Blueprint $table) {
            $table->dropColumn('acuse_xml');
        });
    }
}

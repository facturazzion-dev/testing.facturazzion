<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameColumnsInCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->renameColumn('cfdi_use_id', 'cfdi_use');
            $table->renameColumn('payment_method_id', 'payment_method');
            $table->renameColumn('payment_type_id', 'payment_type');
            $table->renameColumn('fiscal_regimen_id', 'fiscal_regimen');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->renameColumn('cfdi_use', 'cfdi_use_id');
            $table->renameColumn('payment_method', 'payment_method_id');
            $table->renameColumn('payment_type', 'payment_type_id');
            $table->renameColumn('fiscal_regimen', 'fiscal_regimen_id');
        });
    }
}

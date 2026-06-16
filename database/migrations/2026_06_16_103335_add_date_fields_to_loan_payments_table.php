<?php
// database/migrations/YYYY_MM_DD_XXXXXX_add_date_fields_to_loan_payments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('loan_payments', function (Blueprint $table) {
            $table->date('payment_date')->nullable();
            $table->string('payment_date_persian')->nullable();
        });
    }

    public function down()
    {
        Schema::table('loan_payments', function (Blueprint $table) {
            $table->dropColumn(['payment_date', 'payment_date_persian']);
        });
    }
};
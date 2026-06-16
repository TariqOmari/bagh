<?php
// database/migrations/YYYY_MM_DD_XXXXXX_add_date_fields_to_factory_loans_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('factory_loans', function (Blueprint $table) {
            $table->date('loan_date')->nullable();
            $table->string('loan_date_persian')->nullable();
        });
    }

    public function down()
    {
        Schema::table('factory_loans', function (Blueprint $table) {
            $table->dropColumn(['loan_date', 'loan_date_persian']);
        });
    }
};
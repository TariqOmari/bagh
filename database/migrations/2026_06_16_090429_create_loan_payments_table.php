<?php
// database/migrations/YYYY_MM_DD_create_loan_payments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('factory_loan_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('loan_payments');
    }
};
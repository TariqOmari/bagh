<?php
// database/migrations/YYYY_MM_DD_create_stock_entries_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stock_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->decimal('price_per_carton', 10, 2);
            $table->integer('carton_quantity');
            $table->enum('currency', ['afn', 'usd'])->default('afn');
            $table->decimal('total_price', 10, 2);
            $table->enum('payment_method', ['cash', 'loan'])->default('cash');
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('remaining_amount', 10, 2)->default(0);
            $table->enum('payment_status', ['paid', 'partial', 'unpaid'])->default('unpaid');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stock_entries');
    }
};
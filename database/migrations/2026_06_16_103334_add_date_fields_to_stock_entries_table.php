<?php
// database/migrations/YYYY_MM_DD_XXXXXX_add_date_fields_to_stock_entries_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('stock_entries', function (Blueprint $table) {
            $table->date('entry_date')->nullable();
            $table->string('entry_date_persian')->nullable();
        });
    }

    public function down()
    {
        Schema::table('stock_entries', function (Blueprint $table) {
            $table->dropColumn(['entry_date', 'entry_date_persian']);
        });
    }
};
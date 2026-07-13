<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoice_settings', function (Blueprint $table) {
            $table->string('delivery_order_prefix')->nullable()->default('DO');
            $table->string('delivery_order_number_separator')->nullable()->default('-');
            $table->unsignedInteger('delivery_order_digit')->nullable()->default(3);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_settings', function (Blueprint $table) {
            $table->dropColumn(['delivery_order_prefix', 'delivery_order_number_separator', 'delivery_order_digit']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extend delivery_orders to support stock transfers as gate passes.
     */
    public function up(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            // Make invoice_id nullable so transfer-based gate passes work
            $table->unsignedInteger('invoice_id')->nullable()->change();

            // Add stock transfer reference
            $table->unsignedBigInteger('transfer_id')->nullable()->after('invoice_id')->index();
            $table->foreign('transfer_id')->references('id')->on('stock_transfers')->onDelete('cascade');

            // Add source type to distinguish invoice vs transfer gate passes
            $table->string('source_type', 20)->default('invoice')->after('id');

            // Add vehicle and driver for transfer gate passes
            $table->string('vehicle_number', 50)->nullable()->after('status');
            $table->string('driver_name', 100)->nullable()->after('vehicle_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_orders', function (Blueprint $table) {
            $table->dropForeign(['transfer_id']);
            $table->dropColumn(['transfer_id', 'source_type', 'vehicle_number', 'driver_name']);
        });
    }
};

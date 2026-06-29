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
        // 1. Add warehouse_id to stock_intake_vouchers
        if (!Schema::hasColumn('stock_intake_vouchers', 'warehouse_id')) {
            Schema::table('stock_intake_vouchers', function (Blueprint $table) {
                $table->unsignedBigInteger('warehouse_id')->nullable()->after('shipment_id')->index();
                $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
            });
        }

        // 2. Add batch_id to stock_intake_items
        if (!Schema::hasColumn('stock_intake_items', 'batch_id')) {
            Schema::table('stock_intake_items', function (Blueprint $table) {
                $table->unsignedBigInteger('batch_id')->nullable()->after('unit_cost')->index();
                $table->foreign('batch_id')->references('id')->on('product_batches')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_intake_items', function (Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropColumn('batch_id');
        });

        Schema::table('stock_intake_vouchers', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
            $table->dropColumn('warehouse_id');
        });
    }
};

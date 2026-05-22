<?php
/*
 * WorkSuite PMS - WorkOrder Management Module
 * Migration: Rename delivery_date to completion_date_time (datetime),
 *            add item-level completion_date_time and without_amount.
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // ── work_orders: rename delivery_date -> completion_date_time (datetime) ──
        Schema::table('work_orders', function (Blueprint $table) {
            // Drop the old date column and add the new datetime column
            $table->dropColumn('delivery_date');
        });

        Schema::table('work_orders', function (Blueprint $table) {
            $table->dateTime('completion_date_time')->nullable()->after('wo_date');
        });

        // ── work_order_items: add completion_date_time and without_amount ─────────
        Schema::table('work_order_items', function (Blueprint $table) {
            $table->dateTime('completion_date_time')->nullable()->after('description');
            $table->tinyInteger('without_amount')->default(0)->after('completion_date_time');
        });
    }

    public function down(): void
    {
        // Revert work_orders changes
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn('completion_date_time');
        });

        Schema::table('work_orders', function (Blueprint $table) {
            $table->date('delivery_date')->nullable()->after('wo_date');
        });

        // Revert work_order_items changes
        Schema::table('work_order_items', function (Blueprint $table) {
            $table->dropColumn(['completion_date_time', 'without_amount']);
        });
    }
};

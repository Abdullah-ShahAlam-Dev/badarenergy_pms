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
        if (Schema::hasTable('order_items') && !Schema::hasColumn('order_items', 'batch_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->unsignedBigInteger('batch_id')->nullable()->after('product_id')->index();
                $table->foreign('batch_id')->references('id')->on('product_batches')->onDelete('set null');
            });
        }

        if (Schema::hasTable('delivery_order_lines') && !Schema::hasColumn('delivery_order_lines', 'batch_id')) {
            Schema::table('delivery_order_lines', function (Blueprint $table) {
                $table->unsignedBigInteger('batch_id')->nullable()->after('product_id')->index();
                $table->foreign('batch_id')->references('id')->on('product_batches')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('order_items') && Schema::hasColumn('order_items', 'batch_id')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropForeign(['batch_id']);
                $table->dropColumn('batch_id');
            });
        }

        if (Schema::hasTable('delivery_order_lines') && Schema::hasColumn('delivery_order_lines', 'batch_id')) {
            Schema::table('delivery_order_lines', function (Blueprint $table) {
                $table->dropForeign(['batch_id']);
                $table->dropColumn('batch_id');
            });
        }
    }
};

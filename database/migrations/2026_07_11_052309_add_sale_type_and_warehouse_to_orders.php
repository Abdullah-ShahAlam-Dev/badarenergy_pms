<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'sale_type')) {
                $table->tinyInteger('sale_type')->default(0)->after('status')->comment('0: Credit Sale, 1: Cash Sale');
            }
            if (!Schema::hasColumn('orders', 'warehouse_id')) {
                $table->unsignedBigInteger('warehouse_id')->nullable()->after('sale_type')->index();
                $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
            }
            if (!Schema::hasColumn('orders', 'offline_method_id')) {
                $table->unsignedInteger('offline_method_id')->nullable()->after('warehouse_id')->index();
                $table->foreign('offline_method_id')->references('id')->on('offline_payment_methods')->onDelete('set null');
            }
            if (!Schema::hasColumn('orders', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->after('offline_method_id');
            }
            if (!Schema::hasColumn('orders', 'gateway')) {
                $table->string('gateway')->nullable()->after('transaction_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'warehouse_id')) {
                $table->dropForeign(['warehouse_id']);
                $table->dropColumn('warehouse_id');
            }
            if (Schema::hasColumn('orders', 'offline_method_id')) {
                $table->dropForeign(['offline_method_id']);
                $table->dropColumn('offline_method_id');
            }
            if (Schema::hasColumn('orders', 'transaction_id')) {
                $table->dropColumn('transaction_id');
            }
            if (Schema::hasColumn('orders', 'gateway')) {
                $table->dropColumn('gateway');
            }
            if (Schema::hasColumn('orders', 'sale_type')) {
                $table->dropColumn('sale_type');
            }
        });
    }
};

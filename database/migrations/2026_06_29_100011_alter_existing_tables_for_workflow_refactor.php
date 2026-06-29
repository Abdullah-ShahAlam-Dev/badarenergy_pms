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
        // 1. Alter inventories table
        if (!Schema::hasColumn('inventories', 'average_cost')) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->decimal('average_cost', 12, 2)->default(0.00)->after('quantity');
            });
        }

        // 2. Alter stock_movements table
        if (!Schema::hasColumn('stock_movements', 'movement_type')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->string('movement_type', 40)->nullable()->after('type')->index();
            });
        }

        // 3. Alter delivery_orders table
        Schema::table('delivery_orders', function (Blueprint $table) {
            // Drop foreign key and unique constraint on invoice_id if they exist
            try {
                $table->dropForeign('delivery_orders_invoice_id_foreign');
            } catch (\Exception $e) {}

            try {
                $table->dropUnique('delivery_orders_invoice_id_unique');
            } catch (\Exception $e) {}

            // Change column type / nullability
            $table->unsignedInteger('invoice_id')->nullable()->change();

            // Re-add foreign key constraint with cascade delete
            try {
                $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            } catch (\Exception $e) {}

            // Alter status column from enum to string to support draft, picking, etc.
            $table->string('status', 30)->default('draft')->change();

            // Add source_id for polymorphic relationships
            if (!Schema::hasColumn('delivery_orders', 'source_id')) {
                $table->unsignedBigInteger('source_id')->nullable()->after('source_type')->index();
            }

            // Add composite index for performance
            try {
                $table->index(['company_id', 'status', 'source_type'], 'idx_do_company_status_source');
            } catch (\Exception $e) {}
        });

        // 4. Alter product_serials table
        Schema::table('product_serials', function (Blueprint $table) {
            if (!Schema::hasColumn('product_serials', 'batch_id')) {
                $table->unsignedBigInteger('batch_id')->nullable()->after('product_id')->index();
                $table->foreign('batch_id')->references('id')->on('product_batches')->onDelete('set null');
            }

            if (!Schema::hasColumn('product_serials', 'intake_voucher_id')) {
                $table->unsignedBigInteger('intake_voucher_id')->nullable()->after('warehouse_id')->index();
                $table->foreign('intake_voucher_id')->references('id')->on('stock_intake_vouchers')->onDelete('set null');
            }

            if (!Schema::hasColumn('product_serials', 'stock_issue_voucher_id')) {
                $table->unsignedBigInteger('stock_issue_voucher_id')->nullable()->after('invoice_id')->index();
                $table->foreign('stock_issue_voucher_id')->references('id')->on('stock_issue_vouchers')->onDelete('set null');
            }

            // Compound index for status lookups
            try {
                $table->index(['warehouse_id', 'status'], 'idx_serials_warehouse_status');
            } catch (\Exception $e) {}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Revert product_serials changes
        Schema::table('product_serials', function (Blueprint $table) {
            try {
                $table->dropForeign(['batch_id']);
            } catch (\Exception $e) {}
            try {
                $table->dropForeign(['intake_voucher_id']);
            } catch (\Exception $e) {}
            try {
                $table->dropForeign(['stock_issue_voucher_id']);
            } catch (\Exception $e) {}

            try {
                $table->dropIndex('idx_serials_warehouse_status');
            } catch (\Exception $e) {}

            $columns = [];
            if (Schema::hasColumn('product_serials', 'batch_id')) $columns[] = 'batch_id';
            if (Schema::hasColumn('product_serials', 'intake_voucher_id')) $columns[] = 'intake_voucher_id';
            if (Schema::hasColumn('product_serials', 'stock_issue_voucher_id')) $columns[] = 'stock_issue_voucher_id';

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });

        // 2. Revert delivery_orders changes
        Schema::table('delivery_orders', function (Blueprint $table) {
            try {
                $table->dropIndex('idx_do_company_status_source');
            } catch (\Exception $e) {}

            if (Schema::hasColumn('delivery_orders', 'source_id')) {
                $table->dropColumn(['source_id']);
            }

            // Revert status to enum values from previous migration
            $table->enum('status', ['pending', 'dispatched', 'delivered', 'cancelled'])->default('pending')->change();

            // Revert foreign key and unique constraint
            try {
                $table->dropForeign('delivery_orders_invoice_id_foreign');
            } catch (\Exception $e) {}

            $table->unsignedInteger('invoice_id')->change();
            
            try {
                $table->unique('invoice_id', 'delivery_orders_invoice_id_unique');
            } catch (\Exception $e) {}

            try {
                $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            } catch (\Exception $e) {}
        });

        // 3. Revert stock_movements changes
        if (Schema::hasColumn('stock_movements', 'movement_type')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                $table->dropColumn('movement_type');
            });
        }

        // 4. Revert inventories changes
        if (Schema::hasColumn('inventories', 'average_cost')) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->dropColumn('average_cost');
            });
        }
    }
};

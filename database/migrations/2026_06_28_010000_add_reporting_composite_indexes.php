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
        Schema::table('invoices', function (Blueprint $table) {
            $table->index(['company_id', 'client_id', 'issue_date', 'status'], 'invoices_reporting_idx');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->index(['invoice_id', 'product_id'], 'invoice_items_reporting_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['company_id', 'customer_id', 'paid_on', 'status'], 'payments_reporting_idx');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->index(['company_id', 'status', 'purchase_date'], 'expenses_reporting_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex('invoices_reporting_idx');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropIndex('invoice_items_reporting_idx');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_reporting_idx');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex('expenses_reporting_idx');
        });
    }
};

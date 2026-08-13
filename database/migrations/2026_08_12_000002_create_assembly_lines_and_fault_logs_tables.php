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
        if (!Schema::hasTable('assembly_orders')) {
            Schema::create('assembly_orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id')->nullable()->index();
                $table->string('assembly_number', 50)->unique();
                $table->unsignedInteger('target_product_id')->index();
                $table->unsignedBigInteger('warehouse_id')->index();
                $table->decimal('quantity_to_assemble', 12, 2)->default(1.00);
                $table->string('status', 30)->default('draft')->index(); // draft, in_progress, completed, cancelled
                $table->text('notes')->nullable();
                $table->unsignedInteger('created_by')->nullable()->index();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
                $table->foreign('target_product_id')->references('id')->on('products')->onDelete('cascade');
                $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('assembly_order_items')) {
            Schema::create('assembly_order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('assembly_order_id')->index();
                $table->unsignedInteger('raw_product_id')->index();
                $table->decimal('quantity_required', 12, 2)->default(0.00);
                $table->decimal('quantity_used', 12, 2)->default(0.00);
                $table->decimal('quantity_faulty', 12, 2)->default(0.00);
                $table->timestamps();

                $table->foreign('assembly_order_id')->references('id')->on('assembly_orders')->onDelete('cascade');
                $table->foreign('raw_product_id')->references('id')->on('products')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('assembly_fault_logs')) {
            Schema::create('assembly_fault_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id')->nullable()->index();
                $table->unsignedBigInteger('assembly_order_id')->nullable()->index();
                $table->unsignedInteger('product_id')->index();
                $table->unsignedBigInteger('warehouse_id')->index();
                $table->decimal('fault_quantity', 12, 2)->default(1.00);
                $table->text('reason')->nullable();
                $table->unsignedInteger('user_id')->nullable()->index();
                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
                $table->foreign('assembly_order_id')->references('id')->on('assembly_orders')->onDelete('set null');
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assembly_fault_logs');
        Schema::dropIfExists('assembly_order_items');
        Schema::dropIfExists('assembly_orders');
    }
};

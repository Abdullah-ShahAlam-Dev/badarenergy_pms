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
        if (!Schema::hasTable('care_of_ledgers')) {
            Schema::create('care_of_ledgers', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id')->nullable()->index();
                $table->unsignedInteger('care_of_id')->index();
                $table->unsignedInteger('order_id')->nullable()->index();
                $table->unsignedBigInteger('delivery_order_id')->nullable()->index();
                $table->unsignedBigInteger('gate_pass_request_id')->nullable()->index();
                $table->dateTime('date')->index();
                $table->string('transaction_type', 40)->default('product_issue')->index();
                $table->string('reference_number', 50)->nullable()->index();
                $table->text('description')->nullable();
                $table->text('item_details')->nullable();
                $table->decimal('debit', 12, 2)->default(0.00);
                $table->decimal('credit', 12, 2)->default(0.00);
                $table->decimal('balance', 12, 2)->default(0.00);
                $table->text('remarks')->nullable();
                $table->unsignedInteger('created_by')->nullable()->index();
                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
                $table->foreign('care_of_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('delivery_order_id')->references('id')->on('delivery_orders')->onDelete('set null');
                $table->foreign('gate_pass_request_id')->references('id')->on('gate_pass_requests')->onDelete('set null');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            });

            // Add order_id index without strict FK to avoid Worksuite table type mismatch
            Schema::table('care_of_ledgers', function (Blueprint $table) {
                $table->index('order_id', 'care_of_ledgers_order_id_idx');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('care_of_ledgers');
    }
};

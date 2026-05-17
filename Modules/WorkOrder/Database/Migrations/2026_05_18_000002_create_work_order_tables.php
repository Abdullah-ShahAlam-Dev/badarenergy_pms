<?php
/*
 * WorkSuite PMS - WorkOrder Management Module
 * Migration 2: Create all work order database tables
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up()
    {
        // ── 1. vendors ─────────────────────────────────────────────────────────
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id')->index();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');

            $table->string('vendor_name');
            $table->string('company_name')->nullable();
            $table->string('designation')->nullable();
            $table->string('mobile', 30)->nullable();
            $table->string('alternate_mobile', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('office_address')->nullable();
            $table->string('cnic', 25)->nullable();
            $table->string('ntn', 30)->nullable();
            $table->text('bank_details')->nullable();
            $table->string('category', 100)->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->unsignedInteger('added_by')->nullable();
            $table->unsignedInteger('last_updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // ── 2. approval_mappings ───────────────────────────────────────────────
        Schema::create('approval_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id')->index();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedInteger('creator_id');
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedInteger('approver_id');
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            $table->tinyInteger('is_active')->default(1);
            $table->unsignedInteger('added_by')->nullable();
            $table->unsignedInteger('last_updated_by')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'creator_id'], 'unique_approval_mapping');
        });

        // ── 3. work_orders ─────────────────────────────────────────────────────
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id')->index();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');

            $table->string('wo_number')->unique();

            $table->date('wo_date');
            $table->date('delivery_date')->nullable();

            $table->unsignedInteger('event_id')->nullable();
            $table->foreign('event_id')->references('id')->on('events')->onDelete('set null')->onUpdate('cascade');

            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('set null')->onUpdate('cascade');

            $table->string('work_category', 100)->nullable();
            $table->string('venue')->nullable();
            $table->unsignedInteger('no_of_days')->default(1);
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');

            $table->text('description')->nullable();
            $table->text('remarks')->nullable();
            $table->text('terms_conditions')->nullable();
            $table->text('special_instructions')->nullable();

            // Financial totals
            $table->decimal('sub_total', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->enum('discount_type', ['percent', 'fixed'])->default('percent');
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);

            // Status & approval
            $table->string('status')->default('draft');
            // statuses: draft | pending_approval | approved | rejected | in_progress | completed | cancelled
            $table->tinyInteger('approval_required')->default(1);

            $table->unsignedInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');

            $table->unsignedInteger('approved_by')->nullable();
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');

            $table->timestamp('approved_at')->nullable();

            $table->unsignedInteger('added_by')->nullable();
            $table->unsignedInteger('last_updated_by')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        // ── 4. work_order_items ────────────────────────────────────────────────
        Schema::create('work_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('work_order_id');
            $table->foreign('work_order_id')->references('id')->on('work_orders')->onDelete('cascade');

            $table->string('item_name');
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->string('unit', 50)->nullable();
            $table->decimal('rate', 15, 2)->default(0);

            $table->unsignedInteger('tax_id')->nullable();
            $table->foreign('tax_id')->references('id')->on('taxes')->onDelete('set null');
            $table->enum('tax_type', ['inclusive', 'exclusive'])->default('exclusive');
            $table->decimal('tax_percent', 5, 2)->default(0);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);

            $table->timestamps();
        });

        // ── 5. work_order_approvals (audit log) ────────────────────────────────
        Schema::create('work_order_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('work_order_id');
            $table->foreign('work_order_id')->references('id')->on('work_orders')->onDelete('cascade');

            $table->unsignedInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

            $table->string('action');
            // actions: submitted | approved | rejected | sent_back | cancelled
            $table->text('remarks')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });

        // ── 6. vendor_payments ─────────────────────────────────────────────────
        Schema::create('vendor_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id')->index();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');

            $table->unsignedBigInteger('work_order_id');
            $table->foreign('work_order_id')->references('id')->on('work_orders')->onDelete('cascade');

            $table->unsignedBigInteger('vendor_id');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');

            $table->decimal('amount', 15, 2)->default(0);
            $table->date('payment_date')->nullable();
            $table->string('payment_method', 100)->nullable();
            $table->string('reference_no', 100)->nullable();
            $table->text('notes')->nullable();

            $table->unsignedInteger('added_by')->nullable();
            $table->unsignedInteger('last_updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vendor_payments');
        Schema::dropIfExists('work_order_approvals');
        Schema::dropIfExists('work_order_items');
        Schema::dropIfExists('work_orders');
        Schema::dropIfExists('approval_mappings');
        Schema::dropIfExists('vendors');
    }
};

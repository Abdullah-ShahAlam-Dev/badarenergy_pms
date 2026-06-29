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
        Schema::create('dealer_ledger_vouchers', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id')->index();
            $table->unsignedInteger('branch_id')->nullable()->index();
            $table->unsignedInteger('dealer_id')->index();
            $table->string('voucher_number')->unique()->index();
            $table->enum('type', ['opening_balance', 'adjustment', 'write_off'])->index();
            $table->decimal('amount', 12, 2);
            $table->enum('entry_type', ['debit', 'credit']);
            $table->dateTime('date')->index();
            $table->text('remarks')->nullable();
            $table->unsignedInteger('created_by')->index();
            $table->boolean('is_voided')->default(false);
            $table->dateTime('voided_at')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('dealer_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dealer_ledger_vouchers');
    }
};

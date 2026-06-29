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
        Schema::create('stock_issue_vouchers', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id')->index();
            $table->string('voucher_number', 50);
            $table->date('issue_date');
            $table->string('issue_type_key', 50);
            $table->unsignedInteger('issued_to')->nullable()->index();
            $table->string('status', 30)->default('draft');
            $table->text('remarks')->nullable();
            $table->unsignedInteger('created_by')->index();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('issued_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unique(['company_id', 'voucher_number'], 'uq_stock_issue');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_issue_vouchers');
    }
};

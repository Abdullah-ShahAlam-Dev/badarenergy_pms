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
        Schema::create('stock_intake_vouchers', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id')->index();
            $table->unsignedBigInteger('shipment_id')->nullable()->index();
            $table->string('voucher_number', 50);
            $table->date('intake_date');
            $table->string('status', 30)->default('draft');
            $table->text('remarks')->nullable();
            $table->unsignedInteger('created_by')->index();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users');
            $table->unique(['company_id', 'voucher_number'], 'uq_intake_voucher');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_intake_vouchers');
    }
};

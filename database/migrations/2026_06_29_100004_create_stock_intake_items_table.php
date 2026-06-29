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
        Schema::create('stock_intake_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('intake_voucher_id')->index();
            $table->unsignedInteger('product_id')->index();
            $table->decimal('quantity_declared', 12, 2);
            $table->decimal('quantity_received', 12, 2)->default(0.00);
            $table->decimal('unit_cost', 12, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('intake_voucher_id')->references('id')->on('stock_intake_vouchers')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_intake_items');
    }
};

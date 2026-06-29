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
        Schema::create('stock_issue_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_issue_voucher_id')->index();
            $table->unsignedInteger('product_id')->index();
            $table->decimal('quantity', 12, 2);
            $table->timestamps();

            $table->foreign('stock_issue_voucher_id')->references('id')->on('stock_issue_vouchers')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_issue_items');
    }
};

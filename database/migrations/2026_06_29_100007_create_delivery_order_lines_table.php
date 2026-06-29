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
        Schema::create('delivery_order_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_order_id')->index();
            $table->unsignedInteger('product_id')->index();
            $table->decimal('quantity_requested', 12, 2);
            $table->decimal('quantity_dispatched', 12, 2)->default(0.00);
            $table->decimal('quantity_delivered', 12, 2)->default(0.00);
            $table->decimal('quantity_damaged', 12, 2)->default(0.00);
            $table->decimal('quantity_short', 12, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('delivery_order_id')->references('id')->on('delivery_orders')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_order_lines');
    }
};

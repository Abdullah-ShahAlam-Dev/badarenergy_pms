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
        Schema::create('delivery_order_line_serials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('delivery_order_line_id')->index();
            $table->unsignedBigInteger('product_serial_id')->index();
            $table->string('status', 30)->default('reserved');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('delivery_order_line_id')->references('id')->on('delivery_order_lines')->onDelete('cascade');
            $table->foreign('product_serial_id')->references('id')->on('product_serials')->onDelete('cascade');
            $table->unique(['delivery_order_line_id', 'product_serial_id'], 'uq_do_line_serial');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_order_line_serials');
    }
};

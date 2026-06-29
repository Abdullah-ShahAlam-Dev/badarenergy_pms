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
        Schema::create('serial_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_serial_id')->index();
            $table->string('serial_number', 50)->index();
            $table->string('event_type', 50);
            $table->string('source_document_type', 50)->nullable()->index();
            $table->unsignedBigInteger('source_document_id')->nullable()->index();
            $table->unsignedBigInteger('warehouse_id')->index();
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->string('previous_status', 30)->nullable();
            $table->string('new_status', 30);
            $table->text('remarks')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('product_serial_id')->references('id')->on('product_serials')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('serial_transactions');
    }
};

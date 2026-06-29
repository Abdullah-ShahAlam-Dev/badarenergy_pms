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
        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id')->index();
            $table->string('shipment_number', 50);
            $table->string('container_number', 50)->nullable();
            $table->string('bill_of_lading', 100)->nullable();
            $table->string('manufacturing_ref', 100)->nullable();
            $table->string('port_of_origin', 100)->default('China Port');
            $table->string('port_of_discharge', 100)->default('Karachi');
            $table->date('eta')->nullable();
            $table->date('arrival_date')->nullable();
            $table->string('status', 30)->default('in_transit');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->unique(['company_id', 'shipment_number'], 'uq_shipment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};

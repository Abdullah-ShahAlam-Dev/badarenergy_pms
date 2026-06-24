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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_serialized')->default(false)->after('type');
        });

        Schema::create('product_serials', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id')->nullable()->index();
            $table->unsignedInteger('product_id')->index();
            $table->unsignedBigInteger('warehouse_id')->index();
            $table->string('serial_number')->unique();
            $table->enum('status', ['available', 'sold', 'faulty', 'in_transit'])->default('available')->index();
            $table->unsignedInteger('invoice_id')->nullable()->index();
            $table->dateTime('warranty_expires_at')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_serials');

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_serialized');
        });
    }
};

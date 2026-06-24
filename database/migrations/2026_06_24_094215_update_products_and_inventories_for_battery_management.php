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
            $table->string('voltage')->nullable()->after('taxes');
            $table->string('capacity')->nullable()->after('voltage');
            $table->string('product_code')->nullable()->after('capacity');
            $table->string('barcode')->nullable()->after('product_code');
            $table->enum('type', ['imported', 'assembled'])->default('imported')->after('barcode');
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->decimal('quantity_faulty', 12, 2)->default(0.00)->after('quantity');
            $table->decimal('quantity_in_transit', 12, 2)->default(0.00)->after('quantity_faulty');
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('stock_category')->default('available')->after('type')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn('stock_category');
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn(['quantity_faulty', 'quantity_in_transit']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['voltage', 'capacity', 'product_code', 'barcode', 'type']);
        });
    }
};

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
        if (Schema::hasTable('assembly_orders')) {
            Schema::table('assembly_orders', function (Blueprint $table) {
                $table->unsignedInteger('target_product_id')->nullable()->change();
                $table->decimal('quantity_to_assemble', 12, 2)->default(0.00)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('assembly_orders')) {
            Schema::table('assembly_orders', function (Blueprint $table) {
                $table->unsignedInteger('target_product_id')->change();
                $table->decimal('quantity_to_assemble', 12, 2)->default(1.00)->change();
            });
        }
    }
};

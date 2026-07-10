<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Product;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Change the column default value to true (1)
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_serialized')->default(true)->change();
        });

        // 2. Update existing records in DB to be serialized
        Product::query()->update(['is_serialized' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_serialized')->default(false)->change();
        });
    }
};

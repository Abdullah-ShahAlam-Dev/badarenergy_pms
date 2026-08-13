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
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'origin_type')) {
                    $table->string('origin_type', 30)->default('local')->after('allow_purchase')->index();
                }
                if (!Schema::hasColumn('products', 'product_classification')) {
                    $table->string('product_classification', 50)->default('ready_made')->after('origin_type')->index();
                }
                if (!Schema::hasColumn('products', 'product_source')) {
                    $table->string('product_source', 50)->default('oem')->after('product_classification')->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                $cols = array_filter(['origin_type', 'product_classification', 'product_source'], function ($col) {
                    return Schema::hasColumn('products', $col);
                });
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};

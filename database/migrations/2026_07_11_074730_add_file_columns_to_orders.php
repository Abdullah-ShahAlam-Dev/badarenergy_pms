<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'file')) {
                $table->string('file')->nullable()->after('gateway');
            }
            if (!Schema::hasColumn('orders', 'file_original_name')) {
                $table->string('file_original_name')->nullable()->after('file');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'file')) {
                $table->dropColumn('file');
            }
            if (Schema::hasColumn('orders', 'file_original_name')) {
                $table->dropColumn('file_original_name');
            }
        });
    }
};

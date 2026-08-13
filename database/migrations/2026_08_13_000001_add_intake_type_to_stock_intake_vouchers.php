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
        if (Schema::hasTable('stock_intake_vouchers')) {
            Schema::table('stock_intake_vouchers', function (Blueprint $table) {
                if (!Schema::hasColumn('stock_intake_vouchers', 'intake_type')) {
                    $table->string('intake_type', 30)->default('direct')->after('status')->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('stock_intake_vouchers')) {
            Schema::table('stock_intake_vouchers', function (Blueprint $table) {
                if (Schema::hasColumn('stock_intake_vouchers', 'intake_type')) {
                    $table->dropColumn('intake_type');
                }
            });
        }
    }
};

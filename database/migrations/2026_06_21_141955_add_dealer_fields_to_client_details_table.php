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
        Schema::table('client_details', function (Blueprint $table) {
            $table->string('dealer_code', 20)->nullable()->unique()->after('user_id');
            $table->decimal('credit_limit', 12, 2)->default(0.00)->after('dealer_code');
            $table->integer('credit_days')->default(0)->after('credit_limit');
            $table->string('dealer_tier', 20)->default('Tier C')->after('credit_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_details', function (Blueprint $table) {
            $table->dropColumn(['dealer_code', 'credit_limit', 'credit_days', 'dealer_tier']);
        });
    }
};

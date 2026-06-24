<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_details', function (Blueprint $table) {
            $table->string('dealer_category', 20)->default('dealer')->after('dealer_tier')
                  ->comment('distributor, dealer, or end_customer');
            $table->string('area', 100)->nullable()->after('city');
            $table->string('ntn_number', 50)->nullable()->after('gst_number');
            $table->string('strn_number', 50)->nullable()->after('ntn_number');
            $table->unsignedInteger('salesperson_id')->nullable()->after('strn_number');
            $table->foreign('salesperson_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('client_details', function (Blueprint $table) {
            $table->dropForeign(['salesperson_id']);
            $table->dropColumn(['dealer_category', 'area', 'ntn_number', 'strn_number', 'salesperson_id']);
        });
    }
};

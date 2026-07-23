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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'customer_type')) {
                $table->string('customer_type')->default('dealer')->after('client_id');
            }
            if (!Schema::hasColumn('orders', 'custom_customer_name')) {
                $table->string('custom_customer_name')->nullable()->after('customer_type');
            }
            if (!Schema::hasColumn('orders', 'custom_customer_number')) {
                $table->string('custom_customer_number')->nullable()->after('custom_customer_name');
            }
            if (!Schema::hasColumn('orders', 'custom_customer_address')) {
                $table->text('custom_customer_address')->nullable()->after('custom_customer_number');
            }
            if (!Schema::hasColumn('orders', 'care_of_id')) {
                $table->unsignedInteger('care_of_id')->nullable()->index()->after('custom_customer_address');
                $table->foreign('care_of_id')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'care_of_id')) {
                $table->dropForeign(['care_of_id']);
                $table->dropColumn('care_of_id');
            }
            if (Schema::hasColumn('orders', 'custom_customer_address')) {
                $table->dropColumn('custom_customer_address');
            }
            if (Schema::hasColumn('orders', 'custom_customer_number')) {
                $table->dropColumn('custom_customer_number');
            }
            if (Schema::hasColumn('orders', 'custom_customer_name')) {
                $table->dropColumn('custom_customer_name');
            }
            if (Schema::hasColumn('orders', 'customer_type')) {
                $table->dropColumn('customer_type');
            }
        });
    }
};

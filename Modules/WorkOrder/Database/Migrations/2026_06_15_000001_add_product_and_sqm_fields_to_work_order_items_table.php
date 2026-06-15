<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_order_items', function (Blueprint $table) {
            $table->unsignedInteger('product_id')->nullable()->after('work_order_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null')->onUpdate('cascade');
            
            $table->decimal('sqm_from', 10, 2)->default(0.00)->after('rate');
            $table->decimal('sqm_to', 10, 2)->default(0.00)->after('sqm_from');
            
            $table->string('tax_name')->nullable()->after('tax_amount');
            $table->string('tax_method')->default('percent')->after('tax_name');
        });

        // Convert tax_type enum to string to support more values like 'amount'
        Schema::table('work_order_items', function (Blueprint $table) {
            $table->string('tax_type')->default('exclusive')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('work_order_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn(['product_id', 'sqm_from', 'sqm_to', 'tax_name', 'tax_method']);
        });
    }
};

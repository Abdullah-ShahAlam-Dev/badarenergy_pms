<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations — add missing indexes/FK to already-existing warehouses table.
     *
     * @return void
     */
    public function up(): void
    {
        // First modify company_id column type to match companies.id type (INT UNSIGNED)
        DB::statement('ALTER TABLE `warehouses` MODIFY `company_id` INT UNSIGNED NULL');

        Schema::table('warehouses', function (Blueprint $table) {
            // Add unique index on code if not present
            $codeIdx = DB::select("SHOW INDEX FROM `warehouses` WHERE `Key_name` = 'warehouses_code_unique'");
            if (empty($codeIdx)) {
                $table->unique('code');
            }

            // Add company_id index if not present
            $companyIdx = DB::select("SHOW INDEX FROM `warehouses` WHERE `Key_name` = 'warehouses_company_id_index'");
            if (empty($companyIdx)) {
                $table->index('company_id');
            }

            // Add foreign key if not already present
            $fks = DB::select(
                "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'warehouses'
                   AND CONSTRAINT_TYPE = 'FOREIGN KEY'
                   AND CONSTRAINT_NAME = 'warehouses_company_id_foreign'"
            );
            if (empty($fks)) {
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropIndex('warehouses_company_id_index');
            $table->dropUnique('warehouses_code_unique');
        });
    }
};

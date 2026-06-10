<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // 1. Clean up duplicate employee_details records (keep the one with lowest id)
        $duplicates = DB::table('employee_details')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('user_id');

        foreach ($duplicates as $userId) {
            $keepId = DB::table('employee_details')
                ->where('user_id', $userId)
                ->orderBy('id', 'asc')
                ->value('id');

            if ($keepId) {
                DB::table('employee_details')
                    ->where('user_id', $userId)
                    ->where('id', '!=', $keepId)
                    ->delete();
            }
        }

        // 2. Add Unique constraint on user_id
        Schema::table('employee_details', function (Blueprint $table) {
            // Check if unique key doesn't already exist to avoid errors
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('employee_details');
            
            if (!array_key_exists('employee_details_user_id_unique', $indexes)) {
                $table->unique('user_id', 'employee_details_user_id_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->dropUnique('employee_details_user_id_unique');
        });
    }
};

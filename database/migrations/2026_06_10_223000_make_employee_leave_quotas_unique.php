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
        // 1. Clean up duplicate employee_leave_quotas records (keep the one with highest id, which is the latest)
        $duplicates = DB::table('employee_leave_quotas')
            ->select('user_id', 'leave_type_id')
            ->groupBy('user_id', 'leave_type_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            $keepId = DB::table('employee_leave_quotas')
                ->where('user_id', $dup->user_id)
                ->where('leave_type_id', $dup->leave_type_id)
                ->orderBy('id', 'desc')
                ->value('id');

            if ($keepId) {
                DB::table('employee_leave_quotas')
                    ->where('user_id', $dup->user_id)
                    ->where('leave_type_id', $dup->leave_type_id)
                    ->where('id', '!=', $keepId)
                    ->delete();
            }
        }

        // 2. Add Unique constraint on (user_id, leave_type_id)
        Schema::table('employee_leave_quotas', function (Blueprint $table) {
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('employee_leave_quotas');
            
            if (!array_key_exists('employee_leave_quotas_user_id_leave_type_id_unique', $indexes)) {
                $table->unique(['user_id', 'leave_type_id'], 'employee_leave_quotas_user_id_leave_type_id_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('employee_leave_quotas', function (Blueprint $table) {
            $table->dropUnique('employee_leave_quotas_user_id_leave_type_id_unique');
        });
    }
};

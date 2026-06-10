<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add all existing designation IDs and department IDs
     * to every leave type that has restrictions, so no employee
     * is excluded simply because their designation/department was
     * created after the leave type was configured.
     */
    public function up()
    {
        // Get all existing designation IDs and department (team) IDs
        $allDesignationIds = DB::table('designations')->pluck('id')->toArray();
        $allDepartmentIds  = DB::table('teams')->pluck('id')->toArray();

        $leaveTypes = DB::table('leave_types')->get();

        foreach ($leaveTypes as $leaveType) {

            $updates = [];

            // --- Fix designation list ---
            if (!is_null($leaveType->designation)) {
                $existing = json_decode($leaveType->designation, true) ?? [];
                // Cast all to strings for consistent comparison
                $existingStr = array_map('strval', $existing);
                $allStr      = array_map('strval', $allDesignationIds);
                $merged      = array_values(array_unique(array_merge($existingStr, $allStr)));
                $updates['designation'] = json_encode($merged);
            }

            // --- Fix department list ---
            if (!is_null($leaveType->department)) {
                $existing = json_decode($leaveType->department, true) ?? [];
                $existingStr = array_map('strval', $existing);
                $allStr      = array_map('strval', $allDepartmentIds);
                $merged      = array_values(array_unique(array_merge($existingStr, $allStr)));
                $updates['department'] = json_encode($merged);
            }

            if (!empty($updates)) {
                DB::table('leave_types')
                    ->where('id', $leaveType->id)
                    ->update($updates);
            }
        }
    }

    public function down()
    {
        // No rollback — data repair only
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sub_tasks', function (Blueprint $table) {
            // Nullable: NULL = Global record (visible only to Admin / ALL-permission users)
            $table->unsignedBigInteger('department_id')->nullable()->after('assigned_to');
            $table->index('department_id', 'sub_tasks_department_id_index');
        });

        // Backfill from creator's department — safe: unresolvable rows stay NULL
        DB::statement("
            UPDATE sub_tasks st
            JOIN employee_details ed ON ed.user_id = st.added_by
            SET st.department_id = ed.department_id
            WHERE st.department_id IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sub_tasks', function (Blueprint $table) {
            $table->dropIndex('sub_tasks_department_id_index');
            $table->dropColumn('department_id');
        });
    }
};

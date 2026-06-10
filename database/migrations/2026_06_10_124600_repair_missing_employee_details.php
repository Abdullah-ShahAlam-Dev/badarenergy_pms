<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Create missing employee_details for users who are not clients and lack details
        $usersWithoutDetails = DB::table('users')
            ->leftJoin('employee_details', 'users.id', '=', 'employee_details.user_id')
            ->whereNull('employee_details.id')
            ->select('users.id', 'users.company_id', 'users.created_at')
            ->get();

        foreach ($usersWithoutDetails as $user) {
            $isClient = DB::table('client_details')->where('user_id', $user->id)->exists();
            if (!$isClient) {
                DB::table('employee_details')->insert([
                    'user_id' => $user->id,
                    'company_id' => $user->company_id,
                    'joining_date' => $user->created_at ?? now(),
                    'employee_id' => 'EMP-' . $user->id,
                    'marital_status' => 'unmarried',
                    'calendar_view' => 'task,events,holiday,tickets,leaves',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 2. Repair mismatched/null company_id in employee_details
        DB::table('employee_details')
            ->join('users', 'users.id', '=', 'employee_details.user_id')
            ->where(function ($query) {
                $query->whereNull('employee_details.company_id')
                      ->orWhereRaw('employee_details.company_id != users.company_id');
            })
            ->update([
                'employee_details.company_id' => DB::raw('users.company_id')
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No rollbacks needed for data repair
    }
};

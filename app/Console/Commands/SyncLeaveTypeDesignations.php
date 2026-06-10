<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncLeaveTypeDesignations extends Command
{
    protected $signature   = 'fix:leave-type-restrictions';
    protected $description = 'Sync all current designation and department IDs into every leave type restriction list.';

    public function handle(): int
    {
        // ── 1. Fetch all designation and department IDs ──────────────────
        $allDesignationIds = DB::table('designations')->pluck('id')->map(fn($v) => (string)$v)->toArray();
        $allDepartmentIds  = DB::table('teams')->pluck('id')->map(fn($v) => (string)$v)->toArray();

        $this->info('Designations in DB: ' . implode(', ', $allDesignationIds));
        $this->info('Departments in DB:  ' . implode(', ', $allDepartmentIds));

        if (empty($allDesignationIds)) {
            $this->error('No designations found — aborting.');
            return 1;
        }

        // ── 2. Fetch every leave type (raw, no Eloquent scopes) ──────────
        $leaveTypes = DB::table('leave_types')->get();
        $this->info("Found {$leaveTypes->count()} leave type(s).");

        $updated = 0;

        foreach ($leaveTypes as $lt) {
            $changes = [];

            // --- Designation ---
            if (!is_null($lt->designation)) {
                $existing  = array_map('strval', json_decode($lt->designation, true) ?? []);
                $merged    = array_values(array_unique(array_merge($existing, $allDesignationIds)));
                sort($merged, SORT_NUMERIC);
                $newJson   = json_encode($merged);

                if ($newJson !== $lt->designation) {
                    $changes['designation'] = $newJson;
                    $added = array_diff($merged, $existing);
                    $this->line("  LeaveType#{$lt->id} ({$lt->type_name}): adding designation IDs " . implode(',', $added));
                }
            }

            // --- Department ---
            if (!is_null($lt->department)) {
                $existing  = array_map('strval', json_decode($lt->department, true) ?? []);
                $merged    = array_values(array_unique(array_merge($existing, $allDepartmentIds)));
                sort($merged, SORT_NUMERIC);
                $newJson   = json_encode($merged);

                if ($newJson !== $lt->department) {
                    $changes['department'] = $newJson;
                    $added = array_diff($merged, $existing);
                    $this->line("  LeaveType#{$lt->id} ({$lt->type_name}): adding department IDs " . implode(',', $added));
                }
            }

            if (!empty($changes)) {
                // Use raw PDO to guarantee the update runs with zero interference
                $sets   = implode(', ', array_map(fn($col) => "`{$col}` = ?", array_keys($changes)));
                $values = array_values($changes);
                $values[] = $lt->id;

                $affected = DB::affectingStatement(
                    "UPDATE `leave_types` SET {$sets} WHERE `id` = ?",
                    $values
                );

                if ($affected > 0) {
                    $this->info("  ✔ LeaveType#{$lt->id} updated ({$affected} row).");
                    $updated++;
                } else {
                    $this->warn("  ✘ LeaveType#{$lt->id} — update returned 0 affected rows. Checking...");

                    // Verify the current value in the DB
                    $check = DB::table('leave_types')->where('id', $lt->id)->value('designation');
                    $this->warn("    Current designation in DB: " . substr($check ?? 'NULL', 0, 80) . '...');
                }
            } else {
                $this->line("  LeaveType#{$lt->id} ({$lt->type_name}): already up-to-date. Skipping.");
            }
        }

        $this->newLine();
        $this->info("Done. {$updated} leave type(s) updated.");

        // ── 3. Quick verification ────────────────────────────────────────
        $this->newLine();
        $this->info('=== Verification (first 3 leave types) ===');
        DB::table('leave_types')->limit(3)->get()->each(function ($lt) {
            $desig = json_decode($lt->designation, true) ?? [];
            $this->line("LeaveType#{$lt->id} designation IDs: " . implode(', ', array_slice($desig, -10)) . ' (last 10)');
        });

        return 0;
    }
}

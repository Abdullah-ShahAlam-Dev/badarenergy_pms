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
        if (Schema::hasTable('gate_pass_requests')) {
            Schema::table('gate_pass_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('gate_pass_requests', 'is_manual')) {
                    $table->boolean('is_manual')->default(false)->after('type');
                }
                if (!Schema::hasColumn('gate_pass_requests', 'requires_battery_approval')) {
                    $table->boolean('requires_battery_approval')->default(false)->after('is_manual');
                }
                if (!Schema::hasColumn('gate_pass_requests', 'purpose_category')) {
                    $table->string('purpose_category', 50)->nullable()->after('purpose');
                }
                if (!Schema::hasColumn('gate_pass_requests', 'exit_scanned_at')) {
                    $table->dateTime('exit_scanned_at')->nullable()->after('security_remarks');
                }
                if (!Schema::hasColumn('gate_pass_requests', 'exit_scanned_by')) {
                    $table->unsignedInteger('exit_scanned_by')->nullable()->index()->after('exit_scanned_at');
                    $table->foreign('exit_scanned_by')->references('id')->on('users')->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('gate_pass_requests')) {
            Schema::table('gate_pass_requests', function (Blueprint $table) {
                if (Schema::hasColumn('gate_pass_requests', 'exit_scanned_by')) {
                    $table->dropForeign(['exit_scanned_by']);
                    $table->dropColumn('exit_scanned_by');
                }
                $columnsToDrop = array_filter(['is_manual', 'requires_battery_approval', 'purpose_category', 'exit_scanned_at'], function ($col) {
                    return Schema::hasColumn('gate_pass_requests', $col);
                });
                if (!empty($columnsToDrop)) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }
    }
};

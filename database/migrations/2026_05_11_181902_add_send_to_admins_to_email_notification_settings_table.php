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
        Schema::table('email_notification_settings', function (Blueprint $table) {
            $table->enum('send_to_admins', ['all', 'involved'])->default('all');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_notification_settings', function (Blueprint $table) {
            $table->dropColumn('send_to_admins');
        });
    }
};

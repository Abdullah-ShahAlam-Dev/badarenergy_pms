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
        if (Schema::hasTable('notification_integrations')) {
            // Drop MariaDB JSON validation constraints if they exist
            try {
                DB::statement('ALTER TABLE notification_integrations DROP CONSTRAINT IF EXISTS notification_integrations_credentials_check');
            } catch (\Exception $e) {
                // Ignore
            }

            try {
                DB::statement('ALTER TABLE `notification_integrations` DROP CONSTRAINT IF EXISTS `credentials`');
            } catch (\Exception $e) {
                // Ignore
            }

            Schema::table('notification_integrations', function (Blueprint $table) {
                $table->text('credentials')->nullable()->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('notification_integrations')) {
            Schema::table('notification_integrations', function (Blueprint $table) {
                $table->json('credentials')->nullable()->change();
            });
        }
    }
};

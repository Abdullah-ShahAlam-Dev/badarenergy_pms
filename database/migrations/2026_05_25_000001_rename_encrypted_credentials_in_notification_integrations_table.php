<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('notification_integrations') && Schema::hasColumn('notification_integrations', 'encrypted_credentials')) {
            Schema::table('notification_integrations', function (Blueprint $table) {
                $table->renameColumn('encrypted_credentials', 'credentials');
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
        if (Schema::hasTable('notification_integrations') && Schema::hasColumn('notification_integrations', 'credentials')) {
            Schema::table('notification_integrations', function (Blueprint $table) {
                $table->renameColumn('credentials', 'encrypted_credentials');
            });
        }
    }
};

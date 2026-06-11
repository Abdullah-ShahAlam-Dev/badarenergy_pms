<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add batch_id to crm_email_campaigns so we can track the Laravel Bus batch
     * dispatched by LaunchCampaignJob and query its progress from the job_batches table.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('crm_email_campaigns', function (Blueprint $table) {
            $table->string('batch_id')->nullable()->after('launched_by');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crm_email_campaigns', function (Blueprint $table) {
            $table->dropColumn('batch_id');
        });
    }
};

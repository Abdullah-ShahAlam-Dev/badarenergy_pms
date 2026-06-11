<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('crm_email_campaigns', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->string('name');
            $table->unsignedInteger('template_id')->nullable();
            $table->longText('email_body');
            $table->unsignedInteger('segment_id')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'sending', 'completed', 'paused', 'canceled'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('launched_at')->nullable();
            $table->unsignedInteger('launched_by')->nullable();
            $table->unsignedInteger('added_by')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('template_id')->references('id')->on('crm_email_marketing_templates')->nullOnDelete();
            $table->foreign('segment_id')->references('id')->on('crm_email_segments')->nullOnDelete();
            $table->foreign('launched_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('added_by')->references('id')->on('users')->nullOnDelete();

            // Indexes
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crm_email_campaigns');
    }
};

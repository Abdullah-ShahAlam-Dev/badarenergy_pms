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
        Schema::create('crm_campaign_emails', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('campaign_id');
            $table->string('recipient_type');
            $table->unsignedInteger('recipient_id');
            $table->string('email');
            $table->string('message_id')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('campaign_id')->references('id')->on('crm_email_campaigns')->cascadeOnDelete();

            // Indexes
            $table->index(['campaign_id', 'status']);
            $table->index(['recipient_type', 'recipient_id']);
            $table->index('next_retry_at');
            $table->index('email');
            $table->index('message_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crm_campaign_emails');
    }
};

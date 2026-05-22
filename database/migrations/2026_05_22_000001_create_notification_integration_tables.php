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
        Schema::create('notification_integrations', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->unsigned()->index();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->string('provider'); // e.g., 'meta_cloud', 'twilio'
            $table->enum('status', ['active', 'inactive'])->default('inactive');
            $table->json('credentials')->nullable();
            $table->timestamps();
        });

        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->unsigned()->index();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->string('event_name'); // e.g., 'task_reminder'
            $table->string('template_id'); // e.g., WABA template name
            $table->string('language_code', 10)->default('en');
            $table->json('parameter_mappings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->integer('company_id')->unsigned()->index();
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->integer('task_id')->unsigned()->nullable()->index();
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->string('event_name'); // 'task_overdue' etc.
            $table->string('channel'); // e.g., 'whatsapp'
            $table->string('recipient')->nullable();
            $table->string('event_cycle_key'); // e.g., task_id + due_date hash + event
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->text('response')->nullable();
            $table->timestamps();

            // Unique index for strict exactly-once delivery per cycle
            $table->unique(['company_id', 'task_id', 'event_name', 'channel', 'recipient', 'event_cycle_key'], 'idx_unique_delivery_cycle');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('notification_deliveries');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('notification_integrations');
    }
};

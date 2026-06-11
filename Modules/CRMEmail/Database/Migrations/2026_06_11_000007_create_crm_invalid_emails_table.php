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
        Schema::create('crm_invalid_emails', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->string('email');
            $table->string('reason')->nullable();
            $table->unsignedInteger('added_by')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreign('added_by')->references('id')->on('users')->nullOnDelete();

            // Unique constraint
            $table->unique(['company_id', 'email'], 'crm_invalid_emails_company_email_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crm_invalid_emails');
    }
};

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
        Schema::create('crm_unsubscribes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->string('email');
            $table->timestamps();

            // Foreign keys
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();

            // Unique constraint
            $table->unique(['company_id', 'email'], 'crm_unsubscribes_company_email_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('crm_unsubscribes');
    }
};

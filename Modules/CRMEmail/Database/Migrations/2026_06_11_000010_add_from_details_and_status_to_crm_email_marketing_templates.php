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
        Schema::table('crm_email_marketing_templates', function (Blueprint $table) {
            $table->string('from_name')->nullable()->after('subject');
            $table->string('from_email')->nullable()->after('from_name');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('content');
            $table->unsignedInteger('last_updated_by')->nullable()->after('added_by');

            // Foreign key for update tracking
            $table->foreign('last_updated_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('crm_email_marketing_templates', function (Blueprint $table) {
            $table->dropForeign(['last_updated_by']);
            $table->dropColumn(['from_name', 'from_email', 'status', 'last_updated_by']);
        });
    }
};

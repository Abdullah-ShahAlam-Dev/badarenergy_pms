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
        Schema::table('users_chat', function (Blueprint $table) {
            $table->unsignedBigInteger('message_group_id')->nullable()->after('company_id');
            $table->foreign('message_group_id')->references('id')->on('message_groups')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_chat', function (Blueprint $table) {
            $table->dropForeign(['message_group_id']);
            $table->dropColumn('message_group_id');
        });
    }
};

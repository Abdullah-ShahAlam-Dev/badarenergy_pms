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
        if (!Schema::hasTable('task_cc_users')) {
            Schema::create('task_cc_users', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('task_id');
                $table->unsignedInteger('user_id');
                $table->timestamps();

                $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_cc_users');
    }
};

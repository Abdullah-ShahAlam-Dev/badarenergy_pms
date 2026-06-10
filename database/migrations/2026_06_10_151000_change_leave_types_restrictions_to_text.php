<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->text('designation')->nullable()->change();
            $table->text('department')->nullable()->change();
            $table->text('role')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->string('designation', 191)->nullable()->change();
            $table->string('department', 191)->nullable()->change();
            $table->string('role', 191)->nullable()->change();
        });
    }
};

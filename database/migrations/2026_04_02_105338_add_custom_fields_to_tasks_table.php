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
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('annex_a')->nullable();
            $table->string('mode')->nullable();
            $table->decimal('days', 10, 2)->nullable();
            $table->string('uom')->nullable();
            $table->decimal('qty', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['annex_a', 'mode', 'days', 'uom', 'qty']);
        });
    }
};

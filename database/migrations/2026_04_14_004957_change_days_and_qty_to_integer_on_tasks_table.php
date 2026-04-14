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
        \DB::statement('ALTER TABLE tasks MODIFY days INT NULL, MODIFY qty INT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::statement('ALTER TABLE tasks MODIFY days DECIMAL(10,2) NULL, MODIFY qty DECIMAL(10,2) NULL');
    }
};

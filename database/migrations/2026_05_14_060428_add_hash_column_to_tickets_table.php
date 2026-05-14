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
        Schema::table('tickets', function (Blueprint $table) {
            $table->string('hash')->unique()->nullable()->after('id');
        });

        $tickets = \App\Models\Ticket::all();

        foreach ($tickets as $ticket) {
            $ticket->hash = \Illuminate\Support\Str::random(32);
            $ticket->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropColumn('hash');
        });
    }
};

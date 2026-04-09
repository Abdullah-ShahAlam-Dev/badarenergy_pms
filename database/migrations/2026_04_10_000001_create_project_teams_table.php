<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProjectTeamsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_teams', function (Blueprint $table) {
            $table->unsignedInteger('project_id');
            $table->unsignedInteger('team_id');

            $table->primary(['project_id', 'team_id']);

            $table->foreign('project_id')
                ->references('id')->on('projects')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('team_id')
                ->references('id')->on('teams')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });

        // Backfill: copy existing single team_id into the pivot table
        DB::statement('
            INSERT IGNORE INTO project_teams (project_id, team_id)
            SELECT id, team_id
            FROM projects
            WHERE team_id IS NOT NULL
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_teams');
    }
}

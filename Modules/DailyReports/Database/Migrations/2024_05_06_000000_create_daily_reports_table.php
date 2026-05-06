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
        if (!Schema::hasTable('daily_reports')) {
            Schema::create('daily_reports', function (Blueprint $row) {
                $row->id();
                $row->integer('company_id')->unsigned()->nullable();
                $row->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
                
                $row->integer('user_id')->unsigned();
                $row->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
                
                $row->date('report_date');
                $row->longText('summary')->nullable();
                $row->longText('blockers')->nullable();
                $row->longText('next_plan')->nullable();
                $row->integer('total_logged_minutes')->default(0);
                $row->json('timelog_ids_snapshot')->nullable();
                $row->enum('status', ['draft', 'submitted'])->default('submitted');
                $row->boolean('is_locked')->default(false);
                $row->timestamp('locked_at')->nullable();
                $row->timestamps();

                $row->unique(['user_id', 'report_date']);
                $row->index('user_id');
                $row->index('company_id');
                $row->index('report_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('daily_reports');
    }
};

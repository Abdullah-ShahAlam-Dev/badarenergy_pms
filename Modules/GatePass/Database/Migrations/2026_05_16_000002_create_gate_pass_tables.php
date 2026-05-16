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
        Schema::create('gate_pass_requests', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->integer('company_id')->unsigned()->index();
            $blueprint->foreign('company_id')->references('id')->on('companies')->onDelete('cascade')->onUpdate('cascade');
            $blueprint->integer('user_id')->unsigned()->index();
            $blueprint->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            $blueprint->integer('department_id')->unsigned()->nullable()->index();
            $blueprint->foreign('department_id')->references('id')->on('teams')->onDelete('set null')->onUpdate('cascade');
            
            $blueprint->string('request_number')->unique();
            $blueprint->date('request_date');
            $blueprint->enum('type', ['in', 'out'])->default('out');
            $blueprint->enum('return_type', ['returnable', 'non-returnable'])->default('non-returnable');
            
            $blueprint->text('purpose')->nullable();
            $blueprint->string('from_location')->nullable();
            $blueprint->string('to_location')->nullable();
            
            $blueprint->string('vehicle_number')->nullable();
            $blueprint->string('driver_name')->nullable();
            $blueprint->date('expected_return_date')->nullable();
            
            $blueprint->string('status')->default('pending_hod');
            $blueprint->string('qr_code')->nullable()->unique();
            
            $blueprint->integer('hod_id')->unsigned()->nullable();
            $blueprint->foreign('hod_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $blueprint->integer('store_id')->unsigned()->nullable();
            $blueprint->foreign('store_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            $blueprint->integer('security_id')->unsigned()->nullable();
            $blueprint->foreign('security_id')->references('id')->on('users')->onDelete('set null')->onUpdate('cascade');
            
            $blueprint->text('remarks')->nullable();
            $blueprint->text('hod_remarks')->nullable();
            $blueprint->text('store_remarks')->nullable();
            $blueprint->text('security_remarks')->nullable();
            
            $blueprint->softDeletes();
            $blueprint->timestamps();
        });

        Schema::create('gate_pass_items', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->unsignedBigInteger('gate_pass_request_id');
            $blueprint->foreign('gate_pass_request_id')->references('id')->on('gate_pass_requests')->onDelete('cascade');
            
            $blueprint->string('item_name');
            $blueprint->decimal('quantity', 16, 2);
            $blueprint->string('unit')->nullable();
            $blueprint->string('serial_number')->nullable();
            $blueprint->string('asset_tag')->nullable();
            $blueprint->string('condition')->nullable();
            $blueprint->text('remarks')->nullable();
            
            $blueprint->timestamps();
        });

        Schema::create('gate_pass_approval_logs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->unsignedBigInteger('gate_pass_request_id');
            $blueprint->foreign('gate_pass_request_id')->references('id')->on('gate_pass_requests')->onDelete('cascade');
            $blueprint->integer('user_id')->unsigned()->index();
            $blueprint->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
            
            $blueprint->string('action');
            $blueprint->text('remarks')->nullable();
            $blueprint->string('ip_address')->nullable();
            
            $blueprint->timestamps();
        });

        Schema::create('gate_pass_attachments', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->unsignedBigInteger('gate_pass_request_id');
            $blueprint->foreign('gate_pass_request_id')->references('id')->on('gate_pass_requests')->onDelete('cascade');
            
            $blueprint->string('file_name');
            $blueprint->string('hash_name');
            $blueprint->string('size')->nullable();
            $blueprint->string('external_link')->nullable();
            
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gate_pass_attachments');
        Schema::dropIfExists('gate_pass_approval_logs');
        Schema::dropIfExists('gate_pass_items');
        Schema::dropIfExists('gate_pass_requests');
    }
};

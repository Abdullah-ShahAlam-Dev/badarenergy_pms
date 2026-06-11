<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE file_storage CHANGE COLUMN storage_location storage_location ENUM('local', 'aws_s3', 'digitalocean', 'wasabi', 'minio', 'cloudinary') NOT NULL DEFAULT 'local'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("ALTER TABLE file_storage CHANGE COLUMN storage_location storage_location ENUM('local', 'aws_s3', 'digitalocean', 'wasabi', 'minio') NOT NULL DEFAULT 'local'");
    }

};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                if (!Schema::hasColumn('vendors', 'country_phonecode')) {
                    $table->string('country_phonecode', 10)->nullable()->after('designation');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                if (Schema::hasColumn('vendors', 'country_phonecode')) {
                    $table->dropColumn('country_phonecode');
                }
            });
        }
    }
};

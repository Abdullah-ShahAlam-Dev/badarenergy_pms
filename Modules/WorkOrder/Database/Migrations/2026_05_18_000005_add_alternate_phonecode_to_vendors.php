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
                if (!Schema::hasColumn('vendors', 'alternate_country_phonecode')) {
                    $table->string('alternate_country_phonecode', 10)->nullable()->after('mobile');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('vendors')) {
            Schema::table('vendors', function (Blueprint $table) {
                if (Schema::hasColumn('vendors', 'alternate_country_phonecode')) {
                    $table->dropColumn('alternate_country_phonecode');
                }
            });
        }
    }
};

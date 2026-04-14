<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add dedicated PWA icon columns for Android (192x192) and Desktop/Windows (512x512).
     * iOS uses the existing favicon field via <link rel="apple-touch-icon">.
     */
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('pwa_icon_192')->nullable()->after('favicon')
                ->comment('PWA icon for Android home screen (min 192x192px square PNG)');
            $table->string('pwa_icon_512')->nullable()->after('pwa_icon_192')
                ->comment('PWA icon for Desktop/Windows install prompt (min 512x512px square PNG)');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['pwa_icon_192', 'pwa_icon_512']);
        });
    }
};

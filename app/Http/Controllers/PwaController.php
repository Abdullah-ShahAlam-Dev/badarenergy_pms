<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;

class PwaController extends Controller
{
    /**
     * Serve the PWA manifest dynamically based on dashboard settings.
     */
    public function manifest()
    {
        $settings = companyOrGlobalSetting();
        
        // Use the actual App Name set in Dashboard
        $appName = $settings->app_name ?? $settings->global_app_name ?? config('app.name');
        
        // Use the high-res favicon uploaded in Dashboard
        $faviconUrl = $settings->favicon_url;
        
        // Theme color can also be dynamic if needed, but using your brand dark color for now
        $themeColor = '#171f29';

        $manifest = [
            'name' => $appName,
            'short_name' => Str::limit($appName, 12, ''),
            'description' => $appName . ' Project Management System',
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => '#ffffff',
            'theme_color' => $themeColor,
            'icons' => [
                [
                    'src' => $faviconUrl,
                    'sizes' => '192x192',
                    'type' => 'image/png'
                ],
                [
                    'src' => $faviconUrl,
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ]
            ]
        ];

        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json');
    }
}

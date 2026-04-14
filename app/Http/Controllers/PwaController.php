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
        // 1. Identify Company (Hostname first, then Fallback)
        // Bypass session here because PWA manifest requests often don't send cookies
        $host = request()->getHost();

        $company = \App\Models\Company::where('company_url', 'like', "%$host%")
                    ->orWhere('website', 'like', "%$host%")
                    ->first();

        // Fallback 1: If no company found by host, use the first company found in DB
        // (This handles staging/local environments where hostname might not match exactly)
        if (!$company) {
            $company = \App\Models\Company::first();
        }

        // Fallback 2: Global settings
        $settings = $company ?: \App\Models\GlobalSetting::first();

        try {
            // Use the actual App Name set in Dashboard (Force reload from DB)
            $appName = $settings->app_name ?? ($settings->global_app_name ?? config('app.name'));
            
            // Branding Assets
            $faviconUrl = $settings->favicon_url;
            
            // CLEAN URLS: Strip query strings (?v=123) before getting extension
            $fPath = parse_url($faviconUrl, PHP_URL_PATH);
            
            // Determine MIME type of favicon
            $favExt = pathinfo($fPath, PATHINFO_EXTENSION);
            $favMime = match($favExt) {
                'ico' => 'image/x-icon',
                'svg' => 'image/svg+xml',
                'jpg', 'jpeg' => 'image/jpeg',
                default => 'image/png'
            };

        } catch (\Exception $e) {
            $appName = config('app.name');
            $faviconUrl = asset('favicon.png');
            $favMime = 'image/png';
        }
        
        // Brand color
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
                    'sizes' => '32x32 48x48',
                    'type' => $favMime
                ],
                [
                    'src' => $faviconUrl,
                    'sizes' => '192x192',
                    'type' => $favMime,
                    'purpose' => 'any'
                ],
                [
                    'src' => $faviconUrl,
                    'sizes' => '512x512',
                    'type' => $favMime,
                    'purpose' => 'any maskable'
                ]
            ]
        ];

        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}

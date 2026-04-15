<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;

class PwaController extends Controller
{
    /**
     * Serve the PWA manifest dynamically based on dashboard settings.
     *
     * Icon strategy (platform-specific):
     *  - All platforms are driven by a single-source-of-truth favicon system.
     *  - The system auto-generates 192x192, 512x512, 180x180, and 32x32 static assets.
     *
     * All URLs are built using config('app.url') to guarantee HTTPS even behind a
     * reverse proxy where url()/asset() helpers produce http:// URLs.
     */
    public function manifest()
    {
        // Guaranteed-HTTPS base URL from .env APP_URL
        $baseUrl = rtrim(config('app.url'), '/');

        // 1. Company detection (Hostname → first-company fallback)
        $host    = request()->getHost();
        $company = \App\Models\Company::where('website', 'like', "%$host%")
                    ->first()
                    ?? \App\Models\Company::first();

        // 2. Global fallback settings
        $settings = $company ?: \App\Models\GlobalSetting::first();

        // 3. App Name — dynamic from dashboard with safe fallbacks
        try {
            $appName = $settings?->app_name
                ?? $settings?->global_app_name
                ?? config('app.name');
        } catch (\Exception $e) {
            $appName = config('app.name');
        }

        // 4. Build icon URLs relying strictly on the auto-generated assets
        $version = time();
        $settingId = $settings?->id ?? 'default';

        $icon192 = $baseUrl . '/user-uploads/pwa-icons/icon-192x192-' . $settingId . '.png?v=' . $version;
        $icon512 = $baseUrl . '/user-uploads/pwa-icons/icon-512x512-' . $settingId . '.png?v=' . $version;
        $iconSmall = $baseUrl . '/user-uploads/pwa-icons/icon-32x32-' . $settingId . '.png?v=' . $version;
        $smallMime = 'image/png';

        $manifest = [
            'name'             => $appName,
            'short_name'       => Str::limit($appName, 12, ''),
            'description'      => $appName . ' Project Management System',
            'start_url'        => '/',
            'scope'            => '/',
            'display'          => 'standalone',
            'background_color' => '#171f29',
            'theme_color'      => '#171f29',
            'icons'            => [
                // Browser tab favicon
                [
                    'src'   => $iconSmall,
                    'sizes' => '32x32 64x64',
                    'type'  => $smallMime,
                ],
                // Android home screen (192×192)
                [
                    'src'     => $icon192,
                    'sizes'   => '192x192',
                    'type'    => 'image/png',
                    'purpose' => 'any',
                ],
                // Desktop install + Android splash screen (512×512)
                [
                    'src'     => $icon512,
                    'sizes'   => '512x512',
                    'type'    => 'image/png',
                    'purpose' => 'any maskable',
                ],
            ],
        ];

        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;

class PwaController extends Controller
{
    /**
     * Serve the PWA manifest dynamically based on dashboard settings.
     *
     * Icon strategy (platform-specific):
     *  - iOS        → handled by <link rel="apple-touch-icon"> in layout (favicon field)
     *  - Android    → pwa_icon_192 field (min 192×192 square PNG)
     *  - Desktop    → pwa_icon_512 field (min 512×512 square PNG)
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
        $company = \App\Models\Company::where('company_url', 'like', "%$host%")
                    ->orWhere('website', 'like', "%$host%")
                    ->first()
                    ?? \App\Models\Company::first();

        $settings = $company ?: \App\Models\GlobalSetting::first();

        // 2. App Name — dynamic from dashboard
        try {
            $appName = $settings->app_name
                ?? $settings->global_app_name
                ?? config('app.name');
        } catch (\Exception $e) {
            $appName = config('app.name');
        }

        // 3. Build icon URLs with guaranteed-HTTPS base
        //    Each icon falls back to the previous tier → public/favicon.png as last resort.
        $fallbackIcon = $baseUrl . '/favicon.png';

        // 192×192 icon for Android home screen
        $icon192 = $fallbackIcon;
        if (!empty($settings->pwa_icon_192)) {
            $icon192 = $baseUrl . '/user-uploads/pwa-icons/' . $settings->pwa_icon_192;
        } elseif (!empty($settings->favicon)) {
            $icon192 = $baseUrl . '/user-uploads/favicon/' . $settings->favicon;
        }

        // 512×512 icon for Desktop/Windows install prompt & Android splash screen
        $icon512 = $icon192; // fallback to 192 tier
        if (!empty($settings->pwa_icon_512)) {
            $icon512 = $baseUrl . '/user-uploads/pwa-icons/' . $settings->pwa_icon_512;
        }

        // Small favicon icon (browser tab) — always from favicon field
        $iconSmall = $fallbackIcon;
        if (!empty($settings->favicon)) {
            $ext       = strtolower(pathinfo($settings->favicon, PATHINFO_EXTENSION));
            $iconSmall = $baseUrl . '/user-uploads/favicon/' . $settings->favicon;
            $smallMime = match($ext) {
                'ico'        => 'image/x-icon',
                'jpg', 'jpeg' => 'image/jpeg',
                default      => 'image/png',
            };
        } else {
            $smallMime = 'image/png';
        }

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

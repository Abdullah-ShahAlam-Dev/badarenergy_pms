<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;

class PwaController extends Controller
{
    /**
     * Serve the PWA manifest dynamically based on dashboard settings.
     *
     * DESIGN NOTES:
     * - We build icon URLs manually from config('app.url') instead of using
     *   url() / asset() helpers, which produce http:// URLs behind a reverse proxy.
     * - Chrome (Android + Desktop) has STRICT mixed-content blocking: an https://
     *   page cannot load http:// PWA icons. iOS Safari is lenient — hence iOS worked.
     * - Chrome ALSO validates that declared icon sizes match the real pixel dimensions.
     *   The uploaded favicon may be 32x32, but public/favicon.png is the full-size
     *   image (822KB) guaranteed to satisfy the 192x192 and 512x512 requirements.
     */
    public function manifest()
    {
        // Base URL always has the correct scheme from APP_URL in .env
        // This is the only reliable way to generate the correct scheme behind a proxy.
        $baseUrl = rtrim(config('app.url'), '/');

        // 1. Identify Company (Hostname first, then Fallback)
        $host = request()->getHost();

        $company = \App\Models\Company::where('company_url', 'like', "%$host%")
                    ->orWhere('website', 'like', "%$host%")
                    ->first();

        // Fallback: If no match by host, use the first company in DB
        if (!$company) {
            $company = \App\Models\Company::first();
        }

        $settings = $company ?: \App\Models\GlobalSetting::first();

        // 2. App Name — from DB, real-time
        $appName = 'App';
        try {
            $appName = $settings->app_name ?? ($settings->global_app_name ?? config('app.name'));
        } catch (\Exception $e) {
            $appName = config('app.name');
        }

        // 3. Build favicon URL manually — bypasses url() helper to guarantee HTTPS
        // The dynamic favicon is used for the 32x32 browser-tab icon (real-time sync).
        $dynamicFaviconUrl = $baseUrl . '/favicon.png'; // Default static
        $dynamicFaviconMime = 'image/png';

        try {
            if (!empty($settings->favicon)) {
                $ext = strtolower(pathinfo($settings->favicon, PATHINFO_EXTENSION));
                $dynamicFaviconUrl = $baseUrl . '/user-uploads/favicon/' . $settings->favicon;
                $dynamicFaviconMime = match($ext) {
                    'ico'        => 'image/x-icon',
                    'jpg', 'jpeg' => 'image/jpeg',
                    default      => 'image/png',
                };
            }
        } catch (\Exception $e) {
            // Stick to default
        }

        // 4. PWA install icons (192x192 and 512x512) MUST match actual pixel dimensions.
        // The static public/favicon.png is the full-size image used at install time.
        // It is served directly and is guaranteed to be large and accessible.
        $pwaIconUrl = $baseUrl . '/favicon.png';

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
                // Small icon for browser tabs (dynamically synced from Dashboard)
                [
                    'src'   => $dynamicFaviconUrl,
                    'sizes' => '32x32 48x48 64x64',
                    'type'  => $dynamicFaviconMime,
                ],
                // Large icons for PWA install prompt — Chrome validates the actual pixel size.
                // public/favicon.png at 822KB is guaranteed to meet the minimum.
                [
                    'src'     => $pwaIconUrl,
                    'sizes'   => '192x192',
                    'type'    => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src'     => $pwaIconUrl,
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

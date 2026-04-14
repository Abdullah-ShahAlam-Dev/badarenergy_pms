<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class PwaController extends Controller
{
    /**
     * Serve the PWA manifest dynamically based on dashboard settings.
     *
     * ROOT CAUSE FIX for Android/Desktop:
     * Chrome enforces strict mixed-content blocking. If the page is HTTPS
     * but the manifest icon URL is HTTP, Chrome silently drops the icon.
     * iOS Safari is lenient about this (hence it worked there).
     * Solution: force HTTPS scheme so all url()/asset() calls return https:// URLs.
     */
    public function manifest()
    {
        // CRITICAL: Force HTTPS for all URL generation in this request.
        // This fixes the mixed-content icon blocking on Android Chrome and Desktop Chrome.
        // The favicon_url accessor uses url() internally - this makes it produce https:// URLs.
        $isHttps = request()->secure()
            || str_starts_with(config('app.url', ''), 'https')
            || request()->server('HTTP_X_FORWARDED_PROTO') === 'https'
            || request()->server('HTTPS') === 'on';

        if ($isHttps) {
            URL::forceScheme('https');
        }

        // 1. Identify Company (Hostname first, then Fallback)
        // Bypass session — PWA manifest requests don't always send cookies
        $host = request()->getHost();

        $company = \App\Models\Company::where('company_url', 'like', "%$host%")
                    ->orWhere('website', 'like', "%$host%")
                    ->first();

        // Fallback: If no company found by host, use the first company in DB
        // (Handles staging/local where hostname may not match exactly)
        if (!$company) {
            $company = \App\Models\Company::first();
        }

        $settings = $company ?: \App\Models\GlobalSetting::first();

        try {
            $appName    = $settings->app_name ?? ($settings->global_app_name ?? config('app.name'));
            $faviconUrl = $settings->favicon_url;  // Now generates https:// due to forceScheme above

            // Determine MIME type — strip any ?v=timestamp before parsing extension
            $fPath   = parse_url($faviconUrl, PHP_URL_PATH);
            $favExt  = strtolower(pathinfo($fPath, PATHINFO_EXTENSION));
            $favMime = match($favExt) {
                'ico'        => 'image/x-icon',
                'svg'        => 'image/svg+xml',
                'jpg', 'jpeg' => 'image/jpeg',
                default      => 'image/png',
            };

        } catch (\Exception $e) {
            $appName    = config('app.name');
            $faviconUrl = asset('favicon.png');
            $favMime    = 'image/png';
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
                [
                    'src'   => $faviconUrl,
                    'sizes' => '32x32',
                    'type'  => $favMime,
                ],
                [
                    'src'     => $faviconUrl,
                    'sizes'   => '192x192',
                    'type'    => $favMime,
                    'purpose' => 'any',
                ],
                [
                    'src'     => $faviconUrl,
                    'sizes'   => '512x512',
                    'type'    => $favMime,
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

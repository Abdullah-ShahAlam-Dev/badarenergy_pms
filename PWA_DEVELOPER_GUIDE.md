# 📱 Worksuite PMS: PWA Architecture & Developer Guide

This document outlines how the Progressive Web App (PWA) architecture works inside the Worksuite PMS. We use a hybrid approach that satisfies both Apple's iOS specifics and Google Chrome's WebAPK standards, all driven dynamically by Laravel.

---

## 🏗️ 1. How the Architecture Works

### The Backend Source of Truth
The PWA relies on **one single source of truth**: The App Favicon uploaded via the **Theme Settings Dashboard**. 
Instead of requiring the user to upload 5 different sizes of icons, the backend does the heavy lifting:
1. When a Favicon is uploaded, `ThemeSettingController.php` captures the image.
2. It uses `Intervention\Image` (PHP GD Extension) to automatically slice the logo down into exact `.png` sizes (`192x192` and `512x512`).
3. These physical files are written to `public/user-uploads/pwa-icons/`.

### Frontend: Android & Desktop (Google Chrome)
Android and Desktop operating systems follow standard PWA protocols tracking a **Manifest File**.
*   **Routing**: The `manifest.json` file does not actually exist in the `public/` directory! It is a dynamic Laravel Endpoint (`PwaController.php@manifest`).
*   **Caching**: We intentionally tell the Service Worker (`sw.js`) **never** to cache the manifest or the branding icons via a `Network-First` bypass rule. This ensures that when a client hits the page, the manifest always pulls the absolute newest URLs.
*   **Updates**: On Android/Desktop, after a user installs the PWA, Chrome only checks the Manifest for updates silently in the background (usually once every 24-72 hours). Changes to branding will not instantly teleport to an already-installed app. You must uninstall/clear cache/reinstall to see immediate changes.

### Frontend: iPhone & iPad (Apple iOS Safari)
iOS Safari does **not** fully adhere to the `manifest.json` standard for the "Add to Home Screen" feature.
*   Instead of a manifest, Apple reads HTML meta tags injected dynamically on page load.
*   In `resources/views/layouts/app.blade.php`, we inject `<link rel="apple-touch-icon">` and `<meta name="apple-mobile-web-app-title">`.
*   **Updates**: Because iOS parses these HTML tags in real-time when the user clicks "Add to Home Screen", iOS branding is always instantaneous. 

---

## 🛑 2. Strict Rules & Known Gotchas (Read Before Changing)

### Gotcha A: Name Truncation ("Badar Expo S")
Because mobile Home Screens have very limited space, Apple iOS enforces an 11-13 character limit natively, replacing the rest of the text with `...`. 
To ensure cross-compatibility with Android WebAPKs, the `PwaController` formally truncates the app's `short_name` to **12 characters**. 
*Code Ref:* `Str::limit($appName, 12, '')`. 
If a client complains "My app name is cut off", explain that this is an immutable OS-level limitation.

### Gotcha B: DO NOT Upload `.ico` Files!
Because the Live VPS Production Docker Environment uses the strict Alpine `PHP-FPM GD` driver (and not `Imagick`), the server **CANNOT READ** `.ico` files. 
If an admin uploads a `favicon.ico`, the Intervention Image package will crash silently in the background. The app logo will save, but the PWA icons **will never be generated**.
**Always** upload a valid `.png` file to the Favicon settings to trigger the PWA compiler successfully.

### Gotcha C: The Docker 404 Permissions Bug
Laravel's background `Storage::put()` commands often create new directories with strict `700` (`drwx------`) ownership. 
Because your static files are served by the **Nginx Container** (which runs as a separate user than the PHP container), Nginx is blocked from reading the `pwa-icons` folder, causing a 404 Error!
**The Fix:** If you ever nuke the storage folder or rewrite the VPS data, you **must** run this command to grant Nginx read access so the PWA can fetch the images:
```bash
docker exec pms_app chmod -R 775 public/user-uploads/pwa-icons/
```

### Gotcha D: Deploying from Staging to VPS
Remember that Git `ignores` the `public/user-uploads/*` directory. If you upload beautiful new PWA logos on the Staging/Hostinger server, they **will not** be pushed to the VPS via GitHub Actions. 
Code syncs via Git. Database syncs via MySQL dumps. But **User Media must be manually mirrored** or re-uploaded manually on the Live Site!

---

## 🛠️ 3. Service Worker Quirks (`sw.js`)
If you ever change the fundamental design of the Web App, or experience users complaining about "Blank white screens" or "Auth-Login Loops":
1. Open `public/sw.js`.
2. Bump the `CACHE_NAME` constant (e.g., from `v9` to `v10`). 
3. This forces **all** devices globally to completely nuke their stale caches and download the newest CSS, JS, and HTML structure upon their next visit.

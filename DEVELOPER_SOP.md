# 🚀 Worksuite PMS: Developer Standard Operating Procedure (SOP)

This document outlines the standard workflow for developing, building, and deploying the Worksuite Property Management System (PMS).

---

## 🏗️ 1. Project Architecture Overview

- **Frontend**: Laravel Blade + Laravel Mix (Webpack)
- **Stable JS**: Core Libraries (DataTables, DateRangePicker) are loaded via `app.blade.php` to ensure SPA/Turbo Drive stability.
- **Backend**: Laravel 8+ (PHP 8.2)
- **Deployment**: GitHub Actions -> Hostinger VPS (Ubuntu 24.04 + CloudPanel)
- **Infrastructure**: Docker-compose (App, Nginx, MySQL, Redis)
- **Live URL**: `https://pms.badarexposolutions.cloud`
- **VPS Root**: `/var/www/pms`

---

## 🌿 2. Branching Strategy & Beta Environments (GitFlow Lite)

As the project scales, we follow a standard industry branching model to separate stable code from experimental features. This ensures production never breaks.

### The Branches
1.  **`main` (Production)**
    *   **Purpose**: Holds the highly stable, live code currently running on `pms.badarexposolutions.cloud`.
    *   **Rules**: Never commit directly to `main`. Only merge code into `main` after it has been fully tested on staging.
2.  **`staging` (Beta / Develop)**
    *   **Purpose**: The central integration branch for active development. This branch powers the Beta subdomain (e.g., `beta.pms.badarexposolutions.cloud`).
    *   **Rules**: All daily commits and feature developments are pushed here first.
3.  **`feature/*` (Local Branches - Optional)**
    *   **Purpose**: For massive features, developers create a local branch (e.g., `feature/multi-department`), build the code, and merge into `staging`.

### The Beta Subdomain Setup (Future Roadmap)
Big companies use a secondary deployment pipeline:
1.  Create a subdomain on Hostinger (e.g., `beta.pms.badarexposolutions.cloud`).
2.  Clone the repository into a separate VPS directory (e.g., `/var/www/beta-pms`).
3.  Checkout the `staging` branch instead of `main`.
4.  Point the beta site to a separate `beta_pms_db` database so testing doesn't corrupt client data.
5.  When a feature in Beta is approved by the client, run `git checkout main && git merge staging` to deploy to Production.

---

## 💻 3. Local Development Workflow

When you want to make a code change (e.g., UI tweaks or logic changes):

### Step A: Make Changes
Modify your Blade files, Controllers, or Javascript/SCSS in `resources/`.

### Step B: Compile Assets (CRITICAL)
Your setup uses **Laravel Mix with Versioning**. You MUST compile assets locally before pushing, as the VPS serves files from a Docker volume mount.

```bash
# Run this every time you change JS or CSS
npm run prod
```
*This updates `public/js/main.js` and `public/mix-manifest.json`.*

### Step C: Git Push
Stage your changes, including the compiled assets and the manifest.

```bash
git add .
git commit -m "Brief description of change"
git push origin staging
```
*Note: We push to `staging` now. Once tested, create a Pull Request on Github or merge directly into `main`.*

---

## 🚢 4. Live Deployment (VPS Sync)

Once you push to GitHub, the **GitHub Action** triggers. However, if the VPS goes out of sync (manifest 404s or CSS doesn't update), run this **"Master Sync"** command in your VPS Terminal:

```bash
# Force the server to match GitHub exactly and clear all caches
cd /var/www/pms && \
git fetch origin main && \
git reset --hard origin/main && \
docker-compose up -d --build && \
docker exec pms_app php artisan migrate --force && \
docker exec pms_app php artisan optimize:clear && \
docker exec pms_app php artisan view:cache
```

---

## 🧹 5. VPS Maintenance (The 30GB Cleanup)

To prevent the **34GB disk usage** we saw earlier (caused by old Docker images), run this command once a month:

```bash
# Safely deletes old Docker images and container trash
docker system prune -af --volumes
```

---

## 🛠️ 6. Troubleshooting Common Issues

### Issue: "Changes not showing on live site"
- **Reason**: Either the `git pull` failed on the VPS or the browser is caching old files.
- **Fix**: Run the "Master Sync" command in Section 4 and check if `public/mix-manifest.json` exists on the VPS.

### Issue: "500 Server Error"
- **Reason**: Permissions or stale Laravel cache.
- **Fix**: Run permissions reset:
  ```bash
  docker exec pms_app chown -R www-data:www-data storage bootstrap/cache
  docker exec pms_app chmod -R 775 storage bootstrap/cache
  ```

### Issue: "Disk usage is high (30GB+)"
- **Fix**: Run the cleanup command in Section 5.

---

## 📂 7. Key File Locations

- **App Layout**: `resources/views/layouts/app.blade.php` (Contains Turbo listeners)
- **Sidebar Menu**: `resources/views/sections/sidebar.blade.php`
- **Main JS**: `resources/js/main.js` (Compiled to `public/js/main.js`)
- **Docker Config**: `docker-compose.yml`
- **Nginx Config**: `docker/nginx/conf.d/default.conf`

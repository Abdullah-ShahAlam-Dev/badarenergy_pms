# 🚀 Worksuite PMS: Developer Standard Operating Procedure (SOP)

This document serves as the master guide for developing, deploying, and mirroring the Worksuite Property Management System (PMS) across all environments.

---

## 📌 1. Environments Overview

### 🌍 Production Environment (VPS)
- **Live URL**: `https://pms.badarexposolutions.cloud`
- **Hosting**: Dedicated VPS (Ubuntu 24.04 + CloudPanel)
- **Architecture**: Containerized via Docker-Compose (App, Nginx, MySQL 8.0, Redis)
- **Server Path**: `/var/www/pms`
- **Git Branch**: `main`

### 🧪 Staging / Beta Environment (Hostinger)
- **Beta URL**: `https://betapms.badarexposolutions.cloud`
- **Hosting**: Hostinger Shared Hosting
- **Architecture**: Native PHP 8.2 & MySQL (hPanel environment, No Docker)
- **Server Path**: `~/domains/betapms.badarexposolutions.cloud/public_html`
- **Git Branch**: `staging`

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

When you want to make code changes (e.g., UI tweaks, logic updates):

### Step A: Branching
Always ensure you are working on the correct branch. 
- For live fixes: branch from `main`
- For beta testing: checkout `staging`

### Step B: Compile Assets (CRITICAL)
The project uses **Laravel Mix with Versioning**. You MUST compile assets locally before pushing, as the remote environments rely on the generated manifest to load JS/CSS.
```bash
npm run prod
```
*This properly generates cache-busted files and updates `public/mix-manifest.json`.*

### Step C: Push & Automate
Stage and commit your mapped changes.
```bash
git add .
git commit -m "Brief description of change"
git push origin <branch-name>
```
*Note: We push to `staging` now. Once tested, create a Pull Request on Github or merge directly into `main`.*

---

## 🚢 4. Live Deployment (VPS Sync)

Deployments to Production are driven by GitHub Actions when code is pushed to `main`. 

> [!WARNING]
> **Manual Master Sync**: If the VPS goes out of sync (manifest 404s or CSS doesn't update), you must force a manual sync.

**Run this on the VPS Terminal:**
```bash
# Force the server to match GitHub exactly and clear all caches
cd /var/www/pms
git fetch origin main
git reset --hard origin/main
docker-compose up -d --build
docker exec pms_app php artisan migrate --force
docker exec pms_app php artisan optimize:clear
docker exec pms_app php artisan view:cache
```

> [!TIP]
> **VPS Disk Maintenance**: Prevent disk-full errors caused by dangling Docker images. Run this monthly:
> `docker system prune -af --volumes`

---

## 🧪 5. Staging Deployment (Hostinger - `staging` branch)

The Staging environment automatically updates via a custom GitHub Action (`deploy-staging.yml`) whenever code is pushed to `staging`.

> [!IMPORTANT]
> **Hostinger Specifics**: 
> - Hostinger uses `/opt/alt/php82/usr/bin/php` for CLI commands to enforce PHP 8.2.
> - Hostinger uses a non-standard SSH port: `65002`.

**Manual Cache Reset on Hostinger:**
If the Beta site isn't showing new changes, SSH into Hostinger and run:
```bash
cd ~/domains/betapms.badarexposolutions.cloud/public_html
/opt/alt/php82/usr/bin/php artisan optimize:clear
/opt/alt/php82/usr/bin/php artisan optimize
```

---

## 🔄 6. Environment Mirroring (VPS -> Staging)

To create a 100% 1:1 replica of Production data on Staging, we must securely transfer the database and user-uploaded media.

### Step A: Export Database & Transfer (Run on VPS)
This exports the live Docker database and securely SCPs it to Hostinger.
```bash
# 1. Export database dynamically using Docker environment variables
docker exec pms_db sh -c 'exec mysqldump -uroot -p"${MYSQL_ROOT_PASSWORD}" ${MYSQL_DATABASE}' > latest_vps_backup.sql

# 2. Secure Copy (SCP) to Hostinger (Port 65002 required)
scp -P 65002 latest_vps_backup.sql u785408036@betapms.badarexposolutions.cloud:/home/u785408036/domains/betapms.badarexposolutions.cloud/public_html/
```

### Step B: Sync User Uploads / Logos (Run on VPS)
```bash
tar -czvf latest_uploads.tar.gz public/user-uploads
scp -P 65002 latest_uploads.tar.gz u785408036@betapms.badarexposolutions.cloud:/home/u785408036/domains/betapms.badarexposolutions.cloud/public_html/
```

### Step C: Import Data (Run on Hostinger)
SSH into Hostinger and apply the backup.
```bash
cd ~/domains/betapms.badarexposolutions.cloud/public_html

# 1. Import Database
mysql -u u785408036_betapms -p u785408036_betapms < latest_vps_backup.sql
# (Enter the Hostinger DB password when prompted)

# 2. Extract media uploads
tar -xzvf latest_uploads.tar.gz

# 3. Clean up footprints
rm latest_vps_backup.sql latest_uploads.tar.gz

# 4. Final Alignment
/opt/alt/php82/usr/bin/php artisan migrate --force
/opt/alt/php82/usr/bin/php artisan optimize
```

---

## 🛠️ 7. Troubleshooting Common Issues

### Issue: "Changes not showing on live site"
- **Reason**: Either the `git pull` failed on the VPS or the browser is caching old files.
- **Fix**: Run the "Master Sync" command in Section 4 and check if `public/mix-manifest.json` exists on the VPS.

### Issue: "Disk usage is high (30GB+)"
- **Fix**: Run the cleanup command in Section 4 (VPS Disk Maintenance).

---

## 📂 8. Custom Module Quirks (Zoom & Recruit)

Nwidart Laravel Modules (`storage/app/modules_statuses.json`) are completely ignored by `.gitignore`. After setting up Staging or resetting environments, modules must be manually activated and translated.

**Run on target environment (e.g., Hostinger CLI context shown):**

#### 1. Enable Modules in File Config
```bash
/opt/alt/php82/usr/bin/php artisan module:enable Zoom
/opt/alt/php82/usr/bin/php artisan module:enable Recruit
```

#### 2. Register Modules in Database (Missing Dashboard Icons Fix)
The system requires modules to be actively inserted into the `module_settings` table.
```bash
/opt/alt/php82/usr/bin/php artisan zoom:activate
/opt/alt/php82/usr/bin/php artisan recruit:activate
```

#### 3. Fix Raw Translation Keys (e.g. `zoom::app.menu.zoomMeeting`)
If translations show as raw keys, it's usually a directory mapping issue caused by different host operating systems. Ensure the folder is named `en` and NOT `eng`.

```bash
# Rename 'eng' to 'en' so the Translation Manager recognizes it
mv Modules/Zoom/Resources/lang/eng Modules/Zoom/Resources/lang/en
mv Modules/Recruit/Resources/lang/eng Modules/Recruit/Resources/lang/en

# Force system caching
/opt/alt/php82/usr/bin/php artisan optimize
```

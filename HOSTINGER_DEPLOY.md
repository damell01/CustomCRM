# Krayin CRM — Hostinger Shared Hosting Deployment

Hostinger shared hosting does not allow you to change the Apache document root, so the entire Laravel project lives inside `public_html/` and the contents of the `public/` folder are merged into that same root directory.

---

## Overview of the flat structure

```
public_html/          ← document root (= web root)
├── index.php         ← was public/index.php (modified)
├── .htaccess         ← was public/.htaccess (copied up)
├── build/            ← Vite compiled assets (from public/build/)
├── images/           ← static images (from public/images/)
├── app/
├── bootstrap/
├── config/
├── database/
├── lang/
├── packages/
├── resources/
├── routes/
├── storage/
├── vendor/
├── artisan
├── composer.json
└── .env
```

---

## Step 1 — Upload the project

Upload **everything** from your project root into `public_html/` on Hostinger.

Then also **copy the contents of `public/`** into `public_html/` (so `public/index.php` → `public_html/index.php`, `public/.htaccess` → `public_html/.htaccess`, `public/build/` → `public_html/build/`, etc.).

> **Do NOT create a `public/` subfolder inside `public_html/`.** Merge the contents directly into the root.

---

## Step 2 — Set environment variables

Create/edit `.env` at `public_html/.env`:

```env
APP_NAME="Your CRM Name"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_KEY=                            # generated in Step 4

# CRITICAL for flat/Hostinger deployment
APP_FLAT_PUBLIC=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=you@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_FROM_ADDRESS=you@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

> **Important:** `APP_FLAT_PUBLIC=true` tells the app that `public_path()` = project root. Without this, storage links and Vite asset paths break.

> **Note:** Use `CACHE_STORE=file` and `SESSION_DRIVER=file` on shared hosting — database drivers can cause issues if the jobs/cache tables aren't present.

---

## Step 3 — Set file permissions via Hostinger File Manager or SSH

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
chmod 644 .env
```

If you have SSH access on Hostinger:

```bash
cd public_html
chmod -R 775 storage bootstrap/cache
```

---

## Step 4 — Run setup commands via SSH or Hostinger Terminal

Hostinger Business/Premium plans include SSH. Open Hostinger Terminal or connect via:

```bash
ssh username@your-server-ip
cd public_html
```

**Generate the application key:**

```bash
php artisan key:generate
```

**Clear all cached files:**

```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
```

**Run migrations:**

```bash
php artisan migrate --force
```

**Seed admin data only** (do NOT run `db:seed` if Krayin's installer already ran — it creates duplicate data):

```bash
# Only run this if you have NOT used the web installer
php artisan db:seed --class=Webkul\\User\\Database\\Seeders\\UserTableSeeder --force
```

**Create the storage symlink:**

```bash
php artisan storage:link
```

> If `storage:link` fails with "file already exists", delete `public_html/storage` (the symlink, not the folder) and re-run.

**Rebuild optimized caches for production:**

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

---

## Step 5 — Verify the .htaccess is in place

The `.htaccess` should be at `public_html/.htaccess` (copied from `public/.htaccess`). Content:

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

---

## Step 6 — Verify the index.php paths

`public_html/index.php` must reference paths **without** `/../` jumps. It should read:

```php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
```

The `index.php` in this project already handles this automatically via the `APP_FLAT_PUBLIC` env variable.

---

## Step 7 — Run the Krayin web installer (first time only)

Visit `https://yourdomain.com/installer` and complete the setup wizard. It will:
- Verify PHP extensions
- Configure DB connection
- Run migrations
- Create the admin user

If the installer has already run, skip this and ensure the `APP_INSTALLED=true` flag is set in the database or config.

---

## Troubleshooting

**White screen / 500 error**
```bash
# Check the log
tail -50 storage/logs/laravel.log

# Most common: APP_KEY missing
php artisan key:generate

# Permissions
chmod -R 775 storage bootstrap/cache
```

**"No application encryption key has been specified"**
```bash
php artisan key:generate
php artisan config:cache
```

**Assets (CSS/JS) return 404**
- Confirm `build/` folder is in `public_html/build/` (not `public_html/public/build/`)
- Check `APP_URL` in `.env` matches your actual domain exactly (including https://)
- Run `php artisan view:clear` after changing APP_URL

**Vite manifest not found**
- The `build/` directory must exist in `public_html/` (copied from `public/build/`)
- Run a fresh build if needed: `npm ci && npm run build`, then re-upload `build/`

**Storage::url() returns wrong path**
- Make sure `APP_FLAT_PUBLIC=true` is in `.env`
- Delete and re-run `php artisan storage:link`
- Set `FILESYSTEM_DISK=public` and `APP_URL` correctly

**Session / login loop**
- Switch to `SESSION_DRIVER=file` in `.env`
- Clear: `php artisan cache:clear && php artisan config:cache`

**Installer keeps showing even after setup**
- Check if `APP_INSTALLED` or the installer flag in the database is set
- In Krayin, the installer state is tracked in the `core_config` table (key: `general.general.locale.locale`)

**"Class not found" after upload**
```bash
composer dump-autoload --optimize
```

---

## White-label branding

### Set your CRM name
In `.env`:
```env
APP_NAME="DBell CRM"
```

### Upload a custom logo
1. Upload your logo SVG to `public_html/images/custom-logo.svg`
2. The overridden views will automatically use it on the login page, header, and emails

**OR** use the admin panel (recommended):
- Admin → Settings → General Settings → Design → Logo — upload PNG/SVG
- Admin → Settings → General Settings → Design → Favicon — upload ICO/PNG

### Set the footer text
Admin → Settings → General Settings → Settings → Footer → Label

Set it to something like: `© 2025 DBell CRM. All rights reserved.`

### Change the brand color
Admin → Settings → General Settings → Settings → Menu Color → Brand Color

Enter a hex color (default Krayin blue is `#0E90D9`).

---

## Files modified/created for this deployment

| File | What changed |
|---|---|
| `public/index.php` | Dual-mode: handles both standard and flat (`APP_FLAT_PUBLIC`) paths |
| `app/Providers/AppServiceProvider.php` | Rebinds `path.public` to base path when `APP_FLAT_PUBLIC=true` |
| `resources/views/vendor/admin/sessions/login.blade.php` | Removes "Powered by Krayin/Webkul" from login page |
| `resources/views/vendor/admin/components/layouts/index.blade.php` | Removes vendor footer, adds custom copyright |
| `resources/views/vendor/admin/components/layouts/header/index.blade.php` | Removes hardcoded `cache/logo.png`, supports custom logo file |
| `resources/views/vendor/admin/emails/layout.blade.php` | White-label email header and footer |
| `lang/vendor/admin/en/app.php` | Blanks the "Powered by" translation string |

---

## Composer install on Hostinger (if needed)

If Hostinger doesn't have Composer in PATH:

```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
php composer.phar install --no-dev --optimize-autoloader
```

---

## PHP version

Ensure Hostinger is set to **PHP 8.2**:
Hosting Panel → PHP Configuration → Select 8.2

Check:
```bash
php -v
```

---

## Cron job (Hostinger panel)

Add a cron job in Hostinger control panel → Cron Jobs:

```
* * * * * /usr/local/bin/php /home/{your-user}/public_html/artisan schedule:run >> /dev/null 2>&1
```

Replace `/home/{your-user}/public_html` with your actual path (check with `pwd` in terminal).

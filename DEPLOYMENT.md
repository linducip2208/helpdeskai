# Deployment Guide — HelpDesk AI

Production setup for a Laravel + MySQL server (Ubuntu/Nginx + PHP-FPM).

## 1. Requirements

- PHP 8.2+ with extensions: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pcre`, `pdo_mysql`, `tokenizer`, `xml`
- MySQL 8.0+ (or MariaDB 10.6+)
- Composer 2
- Node.js 18+ (build assets)
- Nginx + PHP-FPM
- Supervisor (queue worker + scheduler safety)
- `mysqldump` on PATH (for `db:backup`)

## 2. First deploy

```bash
git clone <repo> /var/www/helpdeskai
cd /var/www/helpdeskai

composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate

# Edit .env (DB, MAIL, APP_URL, INDEXNOW_KEY, license, AI providers...)

php artisan migrate --force
php artisan db:seed --force        # optional: demo/seed data

npm ci
npm run build

php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Set permissions:

```bash
chown -R www-data:www-data storage bootstrap/cache
```

## 3. Environment (.env) essentials

```
APP_NAME="HelpDesk AI"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=helpdeskai
DB_USERNAME=helpdeskai
DB_PASSWORD=secret

QUEUE_CONNECTION=database
CACHE_STORE=database

# SEO: IndexNow key (also served at public/<key>.txt)
INDEXNOW_KEY=7e438dacbaa7c73b49069345438e5d9c

# Optional: path to mysqldump if not on PATH
# MYSQLDUMP_PATH=/usr/bin/mysqldump
```

Generate VAPID keys for web push (once):

```bash
php artisan vapid:generate   # see App\Console\Commands\GenerateVapidKeys
```

## 4. Scheduler & queue

The scheduler runs SLA checks, ticket reminders, DB backups and IndexNow submission.

Add one cron entry:

```
* * * * * cd /var/www/helpdeskai && php artisan schedule:run >> /dev/null 2>&1
```

Scheduled jobs (see `routes/console.php`):

| Command             | Frequency      |
|---------------------|----------------|
| `sla:check`         | hourly         |
| `tickets:reminders` | daily 08:00    |
| `db:backup`         | daily 02:00    |
| `seo:indexnow`      | daily 02:45    |

Queue worker + scheduler are managed by Supervisor — see `deploy/supervisor.conf`.

## 5. Nginx

See `deploy/nginx.conf`. Reload after installing:

```bash
ln -s /var/www/helpdeskai/deploy/nginx.conf /etc/nginx/sites-enabled/helpdeskai
nginx -t && systemctl reload nginx
```

## 6. SEO / search engines

- Submit `https://your-domain.com/sitemap.xml` to Google Search Console.
- IndexNow auto-submits new/updated blog posts on publish, plus a daily batch. The key file must stay reachable at `https://your-domain.com/<INDEXNOW_KEY>.txt`.
- Blog RSS feed: `https://your-domain.com/blog/feed.xml`.

## 7. Upgrades

```bash
cd /var/www/helpdeskai
php artisan down
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm ci && npm run build
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan queue:restart
php artisan up
```

## 8. Backups

Daily SQL dumps are written to `storage/app/backups` (retains 14 by default).
Copy that directory off-server (S3/rsync) via your own cron for durability.

```bash
php artisan db:backup --keep=30
```

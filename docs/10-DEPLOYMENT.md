# 10 — Deployment Guide

## Server Requirements

| Requirement | Minimum | Recommended |
|-------------|---------|-------------|
| PHP | 8.3+ | 8.3+ |
| Database | MySQL 8.0+ | MySQL 8.0+ / MariaDB 10.11+ |
| Web Server | Nginx 1.22+ / Apache 2.4+ | Nginx 1.24+ |
| Composer | 2.x | 2.7+ |
| Node.js | 20+ | 22 LTS |
| RAM | 2 GB | 4 GB+ |
| Disk | 10 GB | 50 GB SSD |
| Redis | Optional (recommended) | 7.x |

### PHP Extensions Required
```
php8.3-common, php8.3-cli, php8.3-fpm
php8.3-mysql (or php8.3-pgsql)
php8.3-mbstring, php8.3-xml
php8.3-curl, php8.3-zip
php8.3-bcmath, php8.3-intl
php8.3-gd (or php8.3-imagick)
php8.3-redis (if using Redis)
```

---

## Installation Steps

### 1. Clone Repository
```bash
cd /var/www
git clone <repo-url> helpdeskai
cd helpdeskai
```

### 2. Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
npm install
```

### 3. Environment Configuration
```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
```env
APP_NAME="HelpDesk AI"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://helpdeskai.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=helpdeskai
DB_USERNAME=helpdeskai_user
DB_PASSWORD=strong_password_here

QUEUE_CONNECTION=database    # or redis
BROADCAST_CONNECTION=reverb
CACHE_STORE=database         # or redis
SESSION_DRIVER=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@mg.helpdeskai.com
MAIL_PASSWORD=mailgun_password
MAIL_FROM_ADDRESS=support@helpdeskai.com
MAIL_FROM_NAME="HelpDesk AI"

REVERB_APP_ID=12345
REVERB_APP_KEY=your_reverb_key
REVERB_APP_SECRET=your_reverb_secret
```

### 4. Set Permissions
```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data .
```

### 5. Database Setup
```bash
php artisan migrate --force
php artisan db:seed --force
```

### 6. Build Frontend
```bash
npm run build
```

### 7. Create Storage Link
```bash
php artisan storage:link
```

### 8. Optimize for Production
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## Nginx Configuration

```nginx
server {
    listen 80;
    server_name helpdeskai.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name helpdeskai.com;

    root /var/www/helpdeskai/public;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/helpdeskai.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/helpdeskai.com/privkey.pem;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    # Gzip
    gzip on;
    gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;

    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Static files cache
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff2?)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }

    # Reverb WebSocket proxy
    location /apps/ {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }

    # Deny access to sensitive files
    location ~ \.(env|log)$ {
        deny all;
    }
}
```

---

## Queue Worker Setup

Database queue (`QUEUE_CONNECTION=database`) adalah path yang didukung default — tidak butuh layanan tambahan selain migrasi `jobs` yang sudah ada.

### Redis (opsional)

Untuk traffic tinggi, ganti driver ke Redis:

```env
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=null
```

Lalu jalankan worker yang sama (`php artisan queue:work`). Tidak ada perubahan kode yang diperlukan — semua job (`DeliverWebhook`, `ClassifyTicketWithAi`, `AnalyzeTicketSentiment`, `SendWebPushNotification`, mail) mengimplementasikan `ShouldQueue` dan agnostik terhadap driver.

### Horizon (opsional, tidak diinstal default)

Horizon **tidak** diinstal di repo ini karena membutuhkan Redis. Database queue + Supervisor di bawah adalah setup yang didukung. Bila ingin Horizon: install `laravel/horizon` secara manual, set `QUEUE_CONNECTION=redis`, dan ikuti dokumentasi Horizon resmi — di luar cakupan panduan ini.

### Supervisor Configuration
Create `/etc/supervisor/conf.d/helpdeskai-worker.conf`:
```ini
[program:helpdeskai-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/helpdeskai/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/helpdeskai/storage/logs/queue-worker.log
stopwaitsecs=3600
```

Reload Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start helpdeskai-queue:*
```

---

## Reverb WebSocket Setup

### Supervisor Configuration
Create `/etc/supervisor/conf.d/helpdeskai-reverb.conf`:
```ini
[program:helpdeskai-reverb]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/helpdeskai/artisan reverb:start --host=0.0.0.0 --port=8080
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/helpdeskai/storage/logs/reverb.log
```

### Environment for Reverb
```env
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080
REVERB_APP_ID=12345
REVERB_APP_KEY=your_reverb_key
REVERB_APP_SECRET=your_reverb_secret
```

### Client Connection
```js
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: 'your_reverb_key',
    wsHost: window.location.hostname,
    wsPort: window.location.port || (window.location.protocol === 'https:' ? 443 : 80),
    wssPort: window.location.port || (window.location.protocol === 'https:' ? 443 : 80),
    forceTLS: window.location.protocol === 'https:',
    enabledTransports: ['ws', 'wss'],
});
```

### Firewall
Ensure port 8080 is NOT exposed publicly. Reverb should only be accessible through Nginx proxy (which handles SSL). Block direct public access:
```bash
sudo ufw deny 8080
```

---

## Scheduler Setup

Add to crontab (`crontab -e` for www-data user):
```cron
* * * * * cd /var/www/helpdeskai && php artisan schedule:run >> /dev/null 2>&1
```
### Scheduled Commands (aktual, dari `routes/console.php`)

```
sla:check            → hourly                (evaluasi breach SLA + notifikasi assignee)
tickets:reminders    → daily 08:00           (pengingat deadline / breach)
tickets:autoclose    → daily 03:00           (tutup tiket resolved yang stagnan)
maintenance:cleanup  → daily 04:00           (prune AI logs, webhook deliveries, notifikasi, sesi, file temp)
db:backup            → daily 02:00           (mysqldump ke storage/app/backups, retensi 14)
seo:indexnow         → daily 02:45           (submit URL batch ke IndexNow)
```

Semua memakai `->withoutOverlapping()`.

---

## SSL Configuration

### Let's Encrypt (Recommended)
```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d helpdeskai.com -d www.helpdeskai.com

# Auto-renewal (test first)
sudo certbot renew --dry-run
```

Certbot automatically adds a cron job for renewal.

---

## Docker Setup (Optional)

### docker-compose.yml
```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: helpdeskai-app
    restart: unless-stopped
    volumes:
      - .:/var/www/html
      - ./storage:/var/www/html/storage
    networks:
      - helpdeskai
    depends_on:
      - mysql
      - redis

  nginx:
    image: nginx:alpine
    container_name: helpdeskai-nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
      - "8080:8080"
    volumes:
      - ./nginx.conf:/etc/nginx/conf.d/default.conf
      - .:/var/www/html
      - ./public:/var/www/html/public
    networks:
      - helpdeskai
    depends_on:
      - app

  mysql:
    image: mysql:8.0
    container_name: helpdeskai-mysql
    restart: unless-stopped
    environment:
      MYSQL_ROOT_PASSWORD: root_password
      MYSQL_DATABASE: helpdeskai
      MYSQL_USER: helpdeskai_user
      MYSQL_PASSWORD: helpdeskai_password
    volumes:
      - mysql_data:/var/lib/mysql
    ports:
      - "3306:3306"
    networks:
      - helpdeskai

  redis:
    image: redis:7-alpine
    container_name: helpdeskai-redis
    restart: unless-stopped
    volumes:
      - redis_data:/data
    ports:
      - "6379:6379"
    networks:
      - helpdeskai

  reverb:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: helpdeskai-reverb
    restart: unless-stopped
    command: php artisan reverb:start --host=0.0.0.0 --port=8080
    ports:
      - "8080:8080"
    networks:
      - helpdeskai
    depends_on:
      - mysql
      - redis

  queue:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: helpdeskai-queue
    restart: unless-stopped
    command: php artisan queue:work --sleep=3 --tries=3
    networks:
      - helpdeskai
    depends_on:
      - mysql
      - redis

networks:
  helpdeskai:
    driver: bridge

volumes:
  mysql_data:
  redis_data:
```

### Dockerfile
```dockerfile
FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    nginx \
    nodejs \
    npm \
    mysql-client \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    zip \
    unzip \
    git \
    curl

RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
```

---

## Production Optimization

### Laravel Caching
```bash
php artisan config:cache       # Caches all config files into one
php artisan route:cache        # Caches route registration
php artisan view:cache         # Pre-compiles all Blade views
php artisan event:cache        # Caches event-to-listener mappings
```

### OPcache
In `php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=64
opcache.max_accelerated_files=20000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
```

### Database
- Add indexes for frequently queried columns (see `docs/04-DATABASE.md` for existing indexes)
- Use Redis for cache and session in high-traffic deployments
- Configure MySQL query cache appropriately
- Regular `OPTIMIZE TABLE` on large tables

### Assets
- All JS/CSS built with Vite (minified + tree-shaken)
- Static asset caching headers set in Nginx (1 year for hashed assets)
- No CDN for build/runtime assets — the UI is served fully offline from `public/build` (Tabler/Inter/ApexCharts bundled via npm). For high-traffic scenarios, put a reverse-proxy cache (e.g. Cloudflare in front of Nginx) instead of rewriting asset URLs to a CDN.

---

## Backup Strategy

### Cara kerja (`App\Console\Commands\BackupDatabase`)

Command `db:backup` (terjadwal daily 02:00, lihat `routes/console.php`):

- Hanya berjalan bila `config('database.default') === 'mysql'` — koneksi lain (sqlite) di-skip dengan warning (bukan error).
- Menjalankan `mysqldump` (binary dari env `MYSQLDUMP_PATH`, default `mysqldump`) dengan flags `--single-transaction --quick --skip-lock-tables`, timeout 600 dtk.
- Menulis ke `storage/app/backups/backup-Y-m-d_His.sql`, lalu prune otomatis (`--keep=14` default, hanya N file terbaru dipertahankan).

```bash
# Manual
php artisan db:backup --keep=14
ls storage/app/backups/
```

### Files Backup
```bash
# Backup uploaded files
rsync -avz /var/www/helpdeskai/storage/app/public/ /backups/files/

# Backup environment
cp /var/www/helpdeskai/.env /backups/env_$(date +%Y%m%d)
```

### Cloud Backup (Recommended)
- **Database:** Automated snapshots (AWS RDS, DigitalOcean managed DB)
- **Files:** S3 sync for `storage/app/public/`
- **Config:** Git repository (`.env` excluded, stored in password manager)

### Restore Procedure (sesuai cara backup di atas)
```bash
# 1. Restore database (dari file backup app — bukan .sql.gz eksternal)
mysql -u helpdeskai_user -p helpdeskai < storage/app/backups/backup-YYYY-MM-DD_HHMMSS.sql

# 2. Restore files
rsync -avz /backups/files/ /var/www/helpdeskai/storage/app/public/

# 3. Re-cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 4. Restart services
sudo supervisorctl restart helpdeskai-queue:*
sudo supervisorctl restart helpdeskai-reverb:*
```

---

## Health Checks

### Endpoints (aktual, `App\Http\Controllers\HealthController`, `routes/web.php`)

```http
GET /health
```

```json
{
  "status": "ok",
  "app": "HelpDesk AI",
  "version": "1.0.0",
  "time": "2026-10-06T12:00:00+07:00"
}
```

`version` dibaca dari file `VERSION` di root project (fallback `"dev"` bila file tidak ada).

```http
GET /ready
```

```json
{
  "ready": true,
  "checks": {
    "database": true,
    "cache": true,
    "storage": true
  }
}
```

`ready = false` → HTTP `503` (dipakai load balancer / orchestrator untuk menahan traffic). Halaman admin **System Health** (`/admin/system-health`, permission `settings.manage`) menampilkan status yang sama di UI.

### Monitoring Integration
- **Uptime monitoring:** UptimeRobot, Pingdom
- **Error tracking:** Sentry, Flare, Bugsnag
- **Server monitoring:** New Relic, Datadog, or self-hosted
- **Log aggregation:** Papertrail, Loggly, or ELK stack

# Production Deployment Checklist

## .env — Production Settings

```dotenv
APP_NAME="Sistem Pesantren"
APP_ENV=production
APP_KEY=base64:GENERATE_WITH_php_artisan_key:generate
APP_DEBUG=false
APP_URL=https://pesantren.com

# Subdomain tenant routing — MUST match nginx server_name wildcard
TENANT_DOMAIN={tenant}.pesantren.com

# Database — use a dedicated DB user with least privilege
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sistem_pesantren
DB_USERNAME=pesantren_app
DB_PASSWORD=STRONG_RANDOM_PASSWORD

# Redis — sessions, cache, queues
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=STRONG_RANDOM_REDIS_PASSWORD
REDIS_PORT=6379

# Session — Redis-backed, tenant-safe
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_COOKIE=pesantren_session
SESSION_SECURE_COOKIE=true      # HTTPS only
SESSION_SAME_SITE=lax

# Cache — Redis, per-tenant keys namespaced by code
CACHE_STORE=redis
CACHE_PREFIX=pesantren

# Queue — Redis-backed, Horizon recommended
QUEUE_CONNECTION=redis
HORIZON_PREFIX=pesantren

# Logging — structured JSON for log aggregator
LOG_CHANNEL=json
LOG_LEVEL=warning
LOG_DAILY_DAYS=30

# Mail
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@pesantren.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## Nginx — Wildcard Subdomain

```nginx
# /etc/nginx/sites-available/pesantren.com
server {
    listen 80;
    server_name pesantren.com *.pesantren.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name pesantren.com *.pesantren.com;

    ssl_certificate     /etc/letsencrypt/live/pesantren.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/pesantren.com/privkey.pem;

    root /var/www/sistem_pesantren/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

**Wildcard SSL**: Use Certbot with DNS-01 challenge:
```bash
certbot certonly --dns-cloudflare --dns-cloudflare-credentials ~/.secrets/certbot/cloudflare.ini \
  -d pesantren.com -d '*.pesantren.com'
```

## Queue — Laravel Horizon

```bash
composer require laravel/horizon
php artisan horizon:install
```

`config/horizon.php` — key settings:
```php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue'      => ['emails', 'default'],
            'balance'    => 'auto',
            'minProcesses' => 2,
            'maxProcesses' => 10,
            'tries'      => 3,
            'timeout'    => 60,
        ],
    ],
],
```

Supervisor daemon:
```ini
[program:horizon]
command=php /var/www/sistem_pesantren/artisan horizon
user=www-data
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/log/horizon.log
stopwaitsecs=3600
```

## Cache Strategy

| Data               | Store  | TTL       | Key pattern                        |
|--------------------|--------|-----------|------------------------------------|
| Tenant by slug     | Redis  | 5 min     | `tenant.slug.{slug}`               |
| Tenant features    | Redis  | 10 min    | `tenant.{id}.features`             |
| Santri count       | Redis  | 2 min     | `tenant.{id}.santri.count`         |
| Route cache        | File   | permanent | `php artisan route:cache`          |
| Config cache       | File   | permanent | `php artisan config:cache`         |

Per-tenant cache isolation example:
```php
Cache::remember("tenant.{$tenantId}.santri.count", 120, fn () =>
    Santri::count()  // TenantScope applies automatically
);
```

## Session Strategy

- Driver: **Redis** (not file/database — avoids I/O bottleneck under load)
- `SESSION_ENCRYPT=true` — encrypts session payload at rest in Redis
- `SESSION_SECURE_COOKIE=true` — prevents HTTP session hijack
- `SESSION_SAME_SITE=lax` — CSRF protection compatible with OAuth redirects
- Session key `tenant_id` is set on login and on subdomain switch
- `TenantRequestLogger` middleware adds `tenant_id` to every log entry for audit trail

## Security Hardening Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] `APP_KEY` rotated from default
- [ ] Database user has only SELECT/INSERT/UPDATE/DELETE (no DROP/CREATE)
- [ ] Redis requires `requirepass` + bind to 127.0.0.1 only
- [ ] HTTPS enforced via nginx redirect + `SESSION_SECURE_COOKIE=true`
- [ ] `php artisan config:cache && php artisan route:cache` in deploy script
- [ ] Queue worker restart on deploy: `php artisan horizon:terminate`
- [ ] Rate limiting on registration: `throttle:tenant-registration` (2/hr/IP)
- [ ] Wildcard SSL certificate via DNS-01 challenge (covers all subdomains)
- [ ] `X-Request-ID` response header set by `TenantRequestLogger` (traceability)

## Deploy Script (CI/CD)

```bash
#!/bin/bash
set -e
php artisan down --secret="DEPLOY_TOKEN"
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan horizon:terminate   # graceful queue restart
php artisan up
```

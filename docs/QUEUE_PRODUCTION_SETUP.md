# Queue Production Setup Guide

## Overview

Sistem Queue SaaS Multi-Tenant mendukung:
- **Database Queue**: Recommended untuk start, minimal setup
- **Redis Queue**: Recommended untuk high-volume production
- **Queue Names**: `default`, `notifications`, `reminders`

## Configuration

### Environment Variables

```env
# Queue Driver: database | redis | sync
QUEUE_CONNECTION=database

# Database Queue Settings
DB_QUEUE_CONNECTION=null
DB_QUEUE_TABLE=jobs
DB_QUEUE=default
DB_QUEUE_RETRY_AFTER=90

# Redis Queue Settings (for QUEUE_CONNECTION=redis)
REDIS_QUEUE_CONNECTION=default
REDIS_QUEUE=default
REDIS_QUEUE_RETRY_AFTER=90

# Redis Connection
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### Queue Names & Priorities

| Queue Name | Purpose | Priority |
|------------|---------|----------|
| `default` | General purpose jobs | Normal |
| `notifications` | Email, push notifications | High |
| `reminders` | Bill due, payment reminders | High |

## Supervisor Configuration

### Installation

```bash
# Ubuntu/Debian
sudo apt-get install supervisor

# CentOS/RHEL
sudo yum install supervisor
```

### Configuration Files

#### 1. Default Queue Worker

Create `/etc/supervisor/conf.d/pesantren-queue-default.conf`:

```ini
[program:pesantren-queue-default]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/pesantren/artisan queue:work --queue=default --sleep=3 --tries=3 --max-jobs=1000 --max-time=3600
directory=/var/www/pesantren
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/pesantren-queue-default.log
stopwaitsecs=3600
```

#### 2. Notifications Queue Worker

Create `/etc/supervisor/conf.d/pesantren-queue-notifications.conf`:

```ini
[program:pesantren-queue-notifications]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/pesantren/artisan queue:work --queue=notifications --sleep=3 --tries=3 --max-jobs=1000 --max-time=3600
directory=/var/www/pesantren
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=3
redirect_stderr=true
stdout_logfile=/var/log/supervisor/pesantren-queue-notifications.log
stopwaitsecs=3600
```

#### 3. Reminders Queue Worker

Create `/etc/supervisor/conf.d/pesantren-queue-reminders.conf`:

```ini
[program:pesantren-queue-reminders]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/pesantren/artisan queue:work --queue=reminders --sleep=3 --tries=3 --max-jobs=1000 --max-time=3600
directory=/var/www/pesantren
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/supervisor/pesantren-queue-reminders.log
stopwaitsecs=3600
```

### Apply Supervisor Configuration

```bash
# Reload supervisor configuration
sudo supervisorctl reread
sudo supervisorctl update

# Start all queue workers
sudo supervisorctl start pesantren-queue-default:*
sudo supervisorctl start pesantren-queue-notifications:*
sudo supervisorctl start pesantren-queue-reminders:*

# Check status
sudo supervisorctl status
```

### Supervisor Commands

```bash
# Restart all workers
sudo supervisorctl restart pesantren-queue-default:*

# Stop all workers
sudo supervisorctl stop pesantren-queue-default:*

# Check logs
sudo tail -f /var/log/supervisor/pesantren-queue-default.log
```

## Queue Worker Options

| Option | Description | Recommendation |
|--------|-------------|--------------|
| `--queue` | Queue names (comma separated) | Required |
| `--sleep` | Seconds to sleep when no jobs | 3 |
| `--tries` | Max attempts before failed | 3 |
| `--max-jobs` | Restart after N jobs | 1000 |
| `--max-time` | Restart after N seconds | 3600 (1 hour) |
| `--memory` | Max memory in MB | 128 |

## Tenant Safety Architecture

### HasTenantContext Trait

All queued jobs MUST use `HasTenantContext` trait:

```php
use App\Jobs\Concerns\HasTenantContext;

class SendNotification implements ShouldQueue
{
    use HasTenantContext;

    public function __construct(private User $user)
    {
        // Capture tenant context at dispatch time
        $this->setJobTenantId($user->tenant_id);
    }

    public function handle(): void
    {
        // Restore tenant context - MUST be first line
        $this->bootTenantContext();

        // All queries now scoped to correct tenant
        $santri = Santri::all(); // Only this tenant's santri
    }
}
```

### How It Works

1. **Dispatch Time**: Tenant ID captured and stored in job payload
2. **Processing Time**: `bootTenantContext()` restores TenantService state
3. **TenantScope**: Automatically filters all queries to correct tenant
4. **On Failure**: `failed()` method clears tenant state to prevent bleed

### Queue Jobs Checklist

- [ ] Uses `HasTenantContext` trait
- [ ] Calls `setJobTenantId()` in constructor
- [ ] Calls `bootTenantContext()` in `handle()`
- [ ] Specifies queue with `onQueue()`
- [ ] Sets `tries` and `backoff` properties

## Failed Jobs

### Configuration

Failed jobs stored in `failed_jobs` table with UUID:

```php
'failed' => [
    'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'),
    'database' => env('DB_CONNECTION', 'sqlite'),
    'table' => 'failed_jobs',
],
```

### Managing Failed Jobs

```bash
# List failed jobs
php artisan queue:failed

# Retry specific job
php artisan queue:retry <id>

# Retry all failed jobs
php artisan queue:retry all

# Delete failed job
php artisan queue:forget <id>

# Flush all failed jobs
php artisan queue:flush
```

## Monitoring

### Horizon (Optional - Redis only)

For Redis queue with advanced monitoring:

```bash
# Install Horizon
composer require laravel/horizon

# Publish config
php artisan horizon:install

# Run Horizon
php artisan horizon
```

### Basic Monitoring

```bash
# Check queue size
php artisan queue:monitor default,notifications,reminders

# Clear specific queue
php artisan queue:clear --queue=reminders
```

## Production Checklist

- [ ] QUEUE_CONNECTION set to `database` or `redis`
- [ ] Redis configured (if using redis queue)
- [ ] Supervisor installed and configured
- [ ] Queue workers running: `supervisorctl status`
- [ ] Failed jobs table exists: `php artisan migrate`
- [ ] Log directory writable: `/var/log/supervisor/`
- [ ] All jobs use `HasTenantContext` trait
- [ ] Retry and backoff configured
- [ ] Worker memory limits set
- [ ] Workers restart periodically (--max-time)

## Troubleshooting

### Jobs Not Processing

1. Check worker status: `sudo supervisorctl status`
2. Check logs: `sudo tail /var/log/supervisor/*.log`
3. Verify queue connection: `php artisan tinker` → `config('queue.default')`

### Tenant Data Bleed

1. Ensure all jobs use `HasTenantContext`
2. Ensure `bootTenantContext()` called first in `handle()`
3. Check TenantService::forJob() clears state properly

### High Memory Usage

1. Reduce `--max-jobs` to restart workers more often
2. Reduce `--max-time` for more frequent restarts
3. Add `--memory=128` limit

## Performance Tuning

### Database Queue

- Index `jobs(queue, reserved_at)` for better performance
- Use separate database connection for queue tables (optional)

### Redis Queue

- Enable `phpredis` extension (not predis)
- Configure Redis persistence for queue durability
- Use Redis Cluster for high availability

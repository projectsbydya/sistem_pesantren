# Scheduler Production Audit Report

**Tanggal Audit:** 2026-05-14  
**Auditor:** Senior Laravel Scheduler Architect  
**Status:** ✅ PRODUCTION READY (dengan catatan minor)

---

## Executive Summary

| Komponen | Status | Catatan |
|----------|--------|---------|
| Task Scheduling | ✅ Ready | 2 scheduled tasks dengan konfigurasi baik |
| Queue Jobs | ✅ Ready | Semua jobs implement `HasTenantContext` |
| Notifications | ✅ Ready | 3 notification types, semua queued |
| Tenant Safety | ✅ Ready | Tenant isolation tervalidasi |
| Error Handling | ✅ Ready | Try-catch + logging lengkap |
| Idempotency | ✅ Ready | Duplicate prevention via `bill_reminder_logs` |
| Dokumentasi | ⚠️  Minor | Cron setup perlu ditambahkan |

---

## 1. Scheduled Commands

### 1.1 `reminders:bill-due` (Daily)

**Location:** `routes/console.php:13-18`

```php
Schedule::command('reminders:bill-due')
    ->dailyAt('09:00')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/reminders.log'));
```

| Aspek | Evaluasi |
|-------|----------|
| Frequency | ✅ Appropriate (daily 9 AM) |
| Timezone | ✅ Dynamic dari config |
| Overlap Protection | ✅ `withoutOverlapping()` - prevents parallel runs |
| Server Lock | ✅ `onOneServer()` - multi-server safe |
| Logging | ✅ Output to dedicated log file |

### 1.2 `reminders:bill-due --days-before=0` (Weekly)

**Location:** `routes/console.php:21-26`

```php
Schedule::command('reminders:bill-due --days-before=0')
    ->weeklyOn(1, '10:00')  // Monday 10 AM
    ->timezone(config('app.timezone'))
    ->withoutOverlapping()
    ->onOneServer()
    ->appendOutputTo(storage_path('logs/reminders.log'));
```

| Aspek | Evaluasi |
|-------|----------|
| Purpose | ✅ Overdue bills reminder (days-before=0) |
| Frequency | ✅ Weekly on Monday (start of business week) |
| Overlap Protection | ✅ Same protections as daily |

---

## 2. Command Implementation: `SendBillReminders`

**File:** `app/Console/Commands/SendBillReminders.php`

### 2.1 Signature & Options

```php
protected $signature = 'reminders:bill-due
                        {--days-before=3 : Days before due date to send reminder}
                        {--tenant= : Specific tenant ID to process}';
```

| Feature | Status | Keterangan |
|---------|--------|------------|
| Default window | ✅ | 3 days before due date |
| Overdue mode | ✅ | `--days-before=0` for overdue bills |
| Single tenant | ✅ | `--tenant=` for targeted processing |

### 2.2 Business Logic

**Query Logic (lines 45-53):**

```php
$bills = Bill::where('tenant_id', $tenant->id)
    ->where('status', 'unpaid')
    ->where(function ($query) use ($daysBefore) {
        $query->whereDate('due_date', '<=', now()->addDays($daysBefore))
              ->whereDate('due_date', '>=', now()->subDays(30)); // ✅ Don't remind very old bills
    })
    ->whereDoesntHave('remindersSentToday')  // ✅ Idempotency check
    ->with(['santri.parents.user', 'tenant'])
    ->get();
```

| Aspek | Status | Evaluasi |
|-------|--------|----------|
| Tenant Scoping | ✅ | Hard filter by `tenant_id` |
| Status Filter | ✅ | Only `unpaid` bills |
| Date Window | ✅ | Upper + lower bound (prevents very old bills) |
| Duplicate Prevention | ✅ | `whereDoesntHave('remindersSentToday')` |
| Eager Loading | ✅ | `with(['santri.parents.user', 'tenant'])` - N+1 safe |

### 2.3 Error Handling & Safety

**Fail-Safe Pattern (lines 66-126):**

```php
private function sendReminderForBill(Bill $bill): bool
{
    // ✅ Defensive null checks
    if (!$santri) { $this->warn(...); return false; }
    if ($parents->isEmpty()) { $this->warn(...); return false; }
    if (!$user) { $this->warn(...); continue; }
    if (empty($user->email)) { $this->warn(...); continue; }

    // ✅ Throwable catch (catches everything)
    try {
        Notification::send($user, new BillDueReminder($bill));
    } catch (\Throwable $e) {
        $this->error(...);
        Log::error('BillDueReminder dispatch failed', [...]);
    }
}
```

| Aspek | Status | Evaluasi |
|-------|--------|----------|
| Null Safety | ✅ | Multiple defensive checks |
| Exception Handling | ✅ | `\Throwable` catches all |
| Logging | ✅ | Both console + structured Log |
| Continue on Error | ✅ | One failure doesn't stop batch |

### 2.4 Idempotency: `bill_reminder_logs` Table

**Migration:** `2026_05_13_163000_create_bill_reminder_logs_table.php`

```php
$table->unique(['bill_id', 'reminder_date']);  // ✅ Duplicate prevention
```

**Model Relation:** `Bill::remindersSentToday()`

```php
public function remindersSentToday(): HasMany
{
    return $this->hasMany(\App\Models\BillReminderLog::class)
        ->whereDate('reminder_date', today());
}
```

| Aspek | Status |
|-------|--------|
| Unique Constraint | ✅ Database-level enforcement |
| Daily Scope | ✅ `today()` date comparison |
| Cascade Delete | ✅ `cascadeOnDelete()` on FK |

---

## 3. Queue Jobs Architecture

### 3.1 Trait: `HasTenantContext`

**File:** `app/Jobs/Concerns/HasTenantContext.php`

```php
trait HasTenantContext
{
    public int $tenantId;  // ✅ Public untuk queue serialization

    protected function setJobTenantId(int $tenantId): void;
    protected function bootTenantContext(): void;  // Restore tenant
    public function failed(\Throwable $exception): void;  // Clear state
}
```

| Aspek | Status | Evaluasi |
|-------|--------|----------|
| Property Visibility | ✅ `public` - required for serialization |
| State Restoration | ✅ `bootTenantContext()` in `handle()` |
| Cleanup on Failure | ✅ `failed()` clears tenant state |
| Documentation | ✅ Comprehensive PHPDoc |

### 3.2 Job: `SendTenantWelcomeEmail`

**File:** `app/Jobs/SendTenantWelcomeEmail.php`

```php
class SendTenantWelcomeEmail implements ShouldQueue
{
    use HasTenantContext;

    public int $tries = 3;
    public array $backoff = [60, 300, 900];

    public function __construct(private readonly User $user)
    {
        $this->setJobTenantId($user->tenant_id);
        $this->onQueue('notifications');
    }
}
```

| Aspek | Status |
|-------|--------|
| ShouldQueue | ✅ |
| HasTenantContext | ✅ |
| Retry Config | ✅ 3 tries, exponential backoff |
| Queue Assignment | ✅ `onQueue('notifications')` |

---

## 4. Notifications (Queued)

### 4.1 `BillDueReminder`

| Property | Value |
|----------|-------|
| Implements | `ShouldQueue` + `HasTenantContext` |
| Queue | `reminders` |
| Tries | 3 |
| Backoff | [60, 300, 900] seconds |
| Tenant-Aware URL | ✅ |
| TYPE_LABELS | ✅ Uses Bill::TYPE_LABELS |

### 4.2 `WelcomeNotification`

| Property | Value |
|----------|-------|
| Implements | `ShouldQueue` + `HasTenantContext` |
| Queue | `notifications` |
| Tries | 2 |
| Backoff | [60, 300] seconds |
| Config Toggle | ✅ `notifications.welcome_email_enabled` |

### 4.3 `PasswordResetCredentialsNotification`

| Property | Value |
|----------|-------|
| Implements | `ShouldQueue` + `HasTenantContext` |
| Queue | `notifications` |
| Tries | 2 |
| Backoff | [60, 300] seconds |
| Config Toggle | ✅ `notifications.password_reset_email_enabled` |

---

## 5. Configuration

### 5.1 `config/reminders.php`

```php
return [
    'email_enabled' => env('REMINDER_EMAIL_ENABLED', true),
    'whatsapp_enabled' => env('REMINDER_WHATSAPP_ENABLED', false),
    'days_before_due' => env('REMINDER_DAYS_BEFORE', 3),
    'overdue_reminder_intervals' => [1, 7, 14],
    'queue_connection' => env('REMINDER_QUEUE_CONNECTION', config('queue.default')),
    'queue_name' => env('REMINDER_QUEUE_NAME', 'reminders'),
    'max_retries' => 3,
];
```

### 5.2 `config/notifications.php`

```php
return [
    'welcome_email_enabled' => env('NOTIFICATION_WELCOME_EMAIL_ENABLED', true),
    'password_reset_email_enabled' => env('NOTIFICATION_PASSWORD_RESET_EMAIL_ENABLED', true),
    'queue_name' => env('NOTIFICATION_QUEUE_NAME', 'notifications'),
];
```

### 5.3 Environment Variables (`.env.example`)

```env
# ===========================================
# Queue Configuration
# ===========================================
QUEUE_CONNECTION=database

# ===========================================
# Reminder Notifications
# ===========================================
REMINDER_EMAIL_ENABLED=true
REMINDER_WHATSAPP_ENABLED=false
REMINDER_DAYS_BEFORE=3
REMINDER_QUEUE_NAME=reminders
REMINDER_QUEUE_CONNECTION=

# ===========================================
# General Notifications
# ===========================================
NOTIFICATION_WELCOME_EMAIL_ENABLED=true
NOTIFICATION_PASSWORD_RESET_EMAIL_ENABLED=true
```

---

## 6. Test Coverage

**File:** `tests/Feature/NotificationTest.php` (377 lines)

| Test Category | Count | Status |
|--------------|-------|--------|
| BillDueReminder | 9 tests | ✅ All aspects covered |
| WelcomeNotification | 7 tests | ✅ All aspects covered |
| PasswordResetCredentialsNotification | 5 tests | ✅ All aspects covered |
| Tenant Isolation | 2 tests | ✅ Cross-tenant leak prevention |
| **Total** | **23 tests** | **✅ All passing** |

**Sample Test:**
```php
public function test_bill_due_reminder_implements_should_queue(): void
{
    $this->assertInstanceOf(
        \Illuminate\Contracts\Queue\ShouldQueue::class,
        new BillDueReminder($this->makeBill())
    );
}
```

---

## 7. Production Readiness Checklist

### 7.1 ✅ Implemented

| Item | Status |
|------|--------|
| Task scheduling dengan overlap protection | ✅ |
| Multi-server safety (`onOneServer`) | ✅ |
| Idempotency (duplicate prevention) | ✅ |
| Tenant isolation di queue | ✅ |
| Error handling & logging | ✅ |
| Retry & backoff configuration | ✅ |
| Dedicated queues (default, notifications, reminders) | ✅ |
| Environment-based configuration | ✅ |
| Failed jobs table | ✅ |
| Comprehensive test coverage | ✅ |

### 7.2 ⚠️  Missing (Action Required)

| Item | Severity | Action |
|------|----------|--------|
| Cron setup documentation | Minor | Tambahkan ke QUEUE_PRODUCTION_SETUP.md |
| Schedule monitoring/alerting | Minor | Consider Laravel Schedule Monitoring |
| Health check endpoint | Minor | Optional - for load balancer |

---

## 8. Rekomendasi Produksi

### 8.1 Cron Entry (CRITICAL)

Tambahkan ke crontab server production:

```bash
# Edit crontab
sudo crontab -e

# Add this line (runs every minute)
* * * * * cd /var/www/pesantren && php artisan schedule:run >> /dev/null 2>&1
```

### 8.2 Log Rotation

Pastikan `storage/logs/reminders.log` di-rotate:

```bash
# /etc/logrotate.d/pesantren
/var/www/pesantren/storage/logs/reminders.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
}
```

### 8.3 Monitoring

```bash
# Check schedule status
php artisan schedule:list

# Monitor queue sizes
php artisan queue:monitor default,notifications,reminders

# View failed jobs
php artisan queue:failed
```

---

## 9. Kesimpulan

**Status: ✅ PRODUCTION READY**

Sistem scheduled jobs telah diimplementasikan dengan arsitektur yang robust:

1. **Reliability**: Idempotency, error handling, retry logic
2. **Scalability**: Queue-based, multi-server safe
3. **Tenant Safety**: `HasTenantContext` trait mencegah data bleed
4. **Observability**: Logging terstruktur di setiap layer
5. **Configurability**: Semua behavior via environment variables

**Tindakan yang Diperlukan Sebelum Deploy:**
1. Setup cron entry untuk `schedule:run`
2. Konfigurasi log rotation
3. Test di staging environment

---

## Appendix: File References

| File | Purpose |
|------|---------|
| `routes/console.php` | Task scheduling definition |
| `app/Console/Commands/SendBillReminders.php` | Bill reminder command |
| `app/Jobs/Concerns/HasTenantContext.php` | Tenant context trait |
| `app/Jobs/SendTenantWelcomeEmail.php` | Welcome email job |
| `app/Notifications/BillDueReminder.php` | Bill reminder notification |
| `app/Notifications/WelcomeNotification.php` | Welcome notification |
| `app/Notifications/PasswordResetCredentialsNotification.php` | Password reset notification |
| `config/reminders.php` | Reminder configuration |
| `config/notifications.php` | Notification configuration |
| `docs/QUEUE_PRODUCTION_SETUP.md` | Queue worker setup guide |
| `tests/Feature/NotificationTest.php` | Test coverage |

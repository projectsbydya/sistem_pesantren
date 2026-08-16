# Subdomain-Based Multi-Tenancy Implementation

## Overview

This document describes the subdomain-based multi-tenancy routing implementation for the Pesantren SaaS application.

## Architecture

### Domain Structure

| Environment | Base Domain | Tenant Domain Pattern | Example |
|-------------|-------------|----------------------|---------|
| Development | `pesantren.test` | `{tenant}.pesantren.test` | `pondok1.pesantren.test` |
| Production | `pesantren.com` | `{tenant}.pesantren.com` | `pondok1.pesantren.com` |

### Resolution Priority

The `ResolveTenant` middleware uses this priority order:

1. **Route parameter** (`{tenant}` from `Route::domain()`)
2. **X-Tenant-ID header** (API clients)
3. **Session tenant_id** (web UI after login)
4. **User's tenant_id** (fallback for fresh sessions)
5. **Host-based resolution** (subdomain parsing)

## Configuration

### 1. Environment Variables (.env)

```env
# Subdomain tenant routing pattern
# Local:      {tenant}.pesantren.test
# Production: {tenant}.pesantren.com
TENANT_DOMAIN={tenant}.pesantren.test

# CRITICAL: For cross-subdomain authentication
# Must include leading dot for wildcard cookie scope
# Local:      .pesantren.test
# Production: .pesantren.com
SESSION_DOMAIN=.pesantren.test
```

### 2. Config (config/app.php)

```php
'tenant_domain' => env('TENANT_DOMAIN', '{tenant}.localhost'),
```

## Authentication Flow

### Login Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│ 1. User accesses login page                                             │
│    - Main domain: pesantren.test/login                                  │
│    - Subdomain:   pondok1.pesantren.test/login                          │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│ 2. User submits credentials                                             │
│    - Both main domain and subdomain use same AuthenticatedSessionController│
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                                    ▼
┌─────────────────────────────────────────────────────────────────────────┐
│ 3. Post-login redirect                                                  │
│                                                                         │
│    IF Super Admin:                                                      │
│    └── redirect to: pesantren.test/dashboard/super-admin               │
│                                                                         │
│    IF Tenant Admin:                                                     │
│    └── redirect to: pondok1.pesantren.test/dashboard                    │
│        (subdomain determined by user's tenant slug)                     │
└─────────────────────────────────────────────────────────────────────────┘
```

### Root Domain Redirect

When an authenticated user visits the root domain (`pesantren.test/`):

- **Super Admin**: Redirects to Super Admin dashboard
- **Tenant User**: Redirects to their tenant subdomain (`{tenant}.pesantren.test/dashboard`)
- **Guest**: Shows welcome/landing page

## Route Structure

### Main Domain Routes (routes/web.php)

```php
// Public routes - no tenant context required
Route::get('/', [WelcomeController::class, 'index']);

// Super Admin routes (main domain only)
Route::middleware(['auth', 'role:super_admin'])
    ->prefix('dashboard/super-admin')
    ->group(function () {
        Route::get('/', [SuperAdminController::class, 'index']);
        // ...
    });
```

### Subdomain Routes (routes/web.php)

```php
// Subdomain root: {tenant}.pesantren.test/
Route::domain(config('app.tenant_domain'))
    ->group(function () {
        Route::get('/', function () {
            if (auth()->check()) {
                return redirect()->route('dashboard.santri.index');
            }
            return redirect()->route('login');
        });
    });

// Subdomain dashboard: {tenant}.pesantren.test/dashboard
Route::domain(config('app.tenant_domain'))
    ->middleware(['auth', 'password.change', 'tenant.resolve', 'owns.tenant'])
    ->prefix('dashboard')
    ->group(function () {
        Route::get('/santri', [SantriController::class, 'index']);
        // ...
    });
```

### Auth Routes (routes/auth.php)

Auth routes are defined for BOTH main domain and subdomains:

```php
// Main domain auth routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');
    // ...
});

// Subdomain auth routes (mirrors main domain)
Route::domain(config('app.tenant_domain'))
    ->middleware('guest')
    ->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])
            ->name('tenant.login');
        // ...
    });
```

## Security

### Cross-Tenant Access Prevention

The `owns.tenant` middleware ensures:

```php
public function handle($request, Closure $next)
{
    $user = auth()->user();
    $tenant = TenantService::current();

    // Super Admin bypass (can access any tenant)
    if ($user->is_super_admin) {
        return $next($request);
    }

    // Regular users can only access their own tenant
    if ($user->tenant_id !== $tenant->id) {
        abort(403, 'Anda tidak memiliki akses ke tenant ini.');
    }

    return $next($request);
}
```

### Session Security

- Session cookies use `SESSION_DOMAIN` with leading dot (e.g., `.pesantren.test`)
- This allows the session to be shared across all subdomains
- Prevents users from having to re-login when redirected between domains

## Local Development Setup

### Option 1: hosts File (Recommended for Windows)

Edit `C:\Windows\System32\drivers\etc\hosts` as Administrator:

```
127.0.0.1  pesantren.test
127.0.0.1  pondok1.pesantren.test
127.0.0.1  pondok2.pesantren.test
127.0.0.1  demo.pesantren.test
```

### Option 2: dnsmasq (Linux/Mac)

```bash
# Install dnsmasq
sudo apt install dnsmasq  # Linux
brew install dnsmasq      # Mac

# Configure local DNS
echo 'address=/pesantren.test/127.0.0.1' | sudo tee /etc/dnsmasq.d/pesantren

# Restart dnsmasq
sudo systemctl restart dnsmasq
```

### Option 3: Laravel Valet (Mac)

```bash
valet link pesantren
valet secure pesantren
# Subdomains work automatically with Valet
```

### Laravel Serve with Subdomain Support

```bash
# Standard (doesn't support subdomains well)
php artisan serve

# Better: specify host (still limited)
php artisan serve --host=pesantren.test --port=8000

# Best: Use Laravel Valet or Sail for full subdomain support
```

## Testing Subdomain Routing

```bash
# 1. Create a test tenant
php artisan tinker
>>> $svc = app(\App\Services\TenantProvisioningService::class);
>>> $result = $svc->provision(['name'=>'Test Admin', 'email'=>'test@test.com', 'pesantren_name'=>'Test Pesantren']);
>>> echo $result['tenant']->slug;  # e.g., "test-pesantren"

# 2. Add to hosts file
# 127.0.0.1  test-pesantren.pesantren.test

# 3. Login via main domain
# Visit: http://pesantren.test:8000/login
# Enter credentials
# Should redirect to: http://test-pesantren.pesantren.test:8000/dashboard

# 4. Login via subdomain
# Visit: http://test-pesantren.pesantren.test:8000/login
# Enter credentials
# Should stay on subdomain and redirect to /dashboard
```

## Common Pitfalls

### 1. Session Domain Not Set

**Problem**: User logs in on main domain, gets redirected to subdomain, but is logged out.

**Solution**: Set `SESSION_DOMAIN=.pesantren.test` (with leading dot)

### 2. Subdomain Not in hosts File

**Problem**: `This site can't be reached` error when accessing subdomain.

**Solution**: Add subdomain to hosts file or use DNS wildcard.

### 3. Port Number Issues

**Problem**: Redirect goes to subdomain without port (e.g., `:8000`).

**Solution**: The `getTenantDomain()` method includes port detection:

```php
$port = request()->getPort();
if ($port && $port !== 80 && $port !== 443) {
    $fullDomain .= ':' . $port;
}
```

### 4. Route Cache Issues

**Problem**: Routes with `Route::domain()` not working after changes.

**Solution**: Clear route cache:

```bash
php artisan route:clear
php artisan cache:clear
```

## Code Reference

### Key Files

| File | Purpose |
|------|---------|
| `routes/web.php` | Main and subdomain route definitions |
| `routes/auth.php` | Authentication routes for both domains |
| `app/Http/Middleware/ResolveTenant.php` | Tenant resolution logic |
| `app/Http/Middleware/EnsureOwnsTenant.php` | Cross-tenant access prevention |
| `app/Http/Controllers/Auth/AuthenticatedSessionController.php` | Login redirect logic |
| `config/app.php` | Tenant domain configuration |

### Helper Methods

```php
// Get current tenant
tenant();  // or app(\App\Services\TenantService::class)->current();

// Get tenant domain URL
$domain = tenant()->slug . '.pesantren.test';

// Check if request is on subdomain
$isSubdomain = request()->route('tenant') !== null;
```

## Production Deployment

### DNS Configuration

Create a wildcard DNS record:

```
*.pesantren.com  A  123.456.789.0
```

Or individual A records:

```
pesantren.com     A  123.456.789.0
pondok1.pesantren.com  A  123.456.789.0
pondok2.pesantren.com  A  123.456.789.0
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name pesantren.com *.pesantren.com;
    root /var/www/pesantren/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        # PHP-FPM configuration
    }
}
```

### SSL Certificates

Use Let's Encrypt with wildcard certificate:

```bash
certbot certonly --manual --preferred-challenges dns \
  -d pesantren.com -d *.pesantren.com
```

Or use Cloudflare for automatic SSL on all subdomains.

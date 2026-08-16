# Multi-Tenant Setup Guide

## Architecture Overview

Sistem ini menggunakan arsitektur **Multi-Tenant SaaS** dengan tenant isolation di level database, query, dan authorization.

## Key Files & Components

```
app/
├── Services/
│   └── TenantService.php          # Static tenant context management (request-scoped)
├── Http/
│   └── Middleware/
│       ├── ResolveTenant.php     # Resolve tenant from subdomain/domain/header
│       └── EnsureOwnsTenant.php  # Verify user belongs to resolved tenant
├── Models/
│   ├── Tenant.php                  # Central tenant model
│   ├── Santri.php                  # Tenant-scoped model example
│   ├── Scopes/
│   │   └── TenantScope.php         # Fail-closed global query scope
│   └── Traits/
│       └── HasTenant.php           # Auto-apply TenantScope trait
├── Helpers/
│   └── TenantHelper.php            # Global helpers: tenant(), tenant_id()
├── Providers/
│   ├── TenantServiceProvider.php   # Register tenant helpers
│   ├── AppServiceProvider.php      # Policy registrations
│   └── AuthServiceProvider.php     # Gates & authorization
└── Policies/                       # Fail-closed authorization policies
    ├── SantriPolicy.php
    ├── UstadzPolicy.php
    └── StaffAccessPolicy.php
```

## Installation Steps

### 1. Register Service Provider

Add to `bootstrap/providers.php`:

```php
return [
    App\Providers\TenantServiceProvider::class,  // Add this line
    // ... other providers
];
```

Or in `config/app.php` providers array for older Laravel:

```php
'providers' => [
    App\Providers\TenantServiceProvider::class,
    // ...
],
```

### 2. Register Middleware

Add to `bootstrap/app.php` (Laravel 11):

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(App\Http\Middleware\ResolveTenant::class);
})
```

Or for API routes only:

```php
// In routes/api.php
Route::middleware(['tenant'])->group(function () {
    // Your API routes
});
```

### 3. Modify User Model

Add the `HasTenant` trait to your existing `User` model:

```php
<?php

namespace App\Models;

use App\Models\Traits\HasTenant;  // Add this
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'tenant_id', 'is_super_admin', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasTenant;  // Add HasTenant

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the tenant that owns the user.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
```

## Usage Examples

### In Controllers

```php
<?php

namespace App\Http\Controllers;

use App\Models\Santri;
use Illuminate\Http\Request;

class SantriController extends Controller
{
    public function index()
    {
        // Automatically filtered by current tenant
        $santri = Santri::all();
        
        return response()->json($santri);
    }

    public function store(Request $request)
    {
        // tenant_id is auto-injected by HasTenant trait
        $santri = Santri::create($request->validated());
        
        return response()->json($santri, 201);
    }

    public function show($id)
    {
        // Automatically scoped to current tenant
        $santri = Santri::findOrFail($id);
        
        return response()->json($santri);
    }

    public function allTenants()
    {
        // Super admin only - bypass tenant scope
        $this->authorize('viewAllTenants');
        
        $santri = Santri::withoutTenant()->get();
        
        return response()->json($santri);
    }
}
```

### Using Helper Functions

```php
// Get current tenant ID
$tenantId = tenant_id();
$tenantId = tenant('id');

// Get current tenant model
$tenant = tenant();
$tenantName = tenant()?->name;

// Check if super admin
if (is_super_admin()) {
    // Can access all tenants
}

// Manual tenant switching (for super admin)
set_tenant(5);  // By ID
set_tenant($tenantModel);  // By model

// Clear tenant context
clear_tenant();
```

### In Models

```php
<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasTenant;

    protected $table = 'products';
    
    protected $fillable = ['name', 'price', 'tenant_id'];

    // Queries are automatically filtered by tenant
    // tenant_id is auto-injected on create
}
```

### Available Scopes

```php
// Default: auto-filtered by current tenant
Santri::all();

// Bypass tenant filter (super admin)
Santri::withoutTenant()->get();

// Filter by specific tenant
Santri::forTenant(5)->get();

// Current tenant (explicit)
Santri::currentTenant()->get();
```

## How It Works

### 1. Request Resolution

Middleware `TenantResolver` checks (in order):
- `X-Tenant-ID` header
- Subdomain (e.g., `tenant1.example.com`)
- Custom domain field

### 2. Tenant Storage

`TenantService` stores the resolved tenant ID statically for the request lifecycle.

### 3. Global Scope

`TenantScope` automatically applies `WHERE tenant_id = ?` to all queries on models using `HasTenant` trait.

### 4. Auto-Injection

When creating a model with `HasTenant`:
- If `tenant_id` is empty AND user is not super admin → auto-set current tenant
- Super admins can create records with `null` tenant_id or manually specify

### 5. Super Admin Bypass

If `is_super_admin = true`:
- No auto-injection of tenant_id
- Global scope is skipped unless tenant is explicitly set
- Can access all tenants' data

## API Usage

### With Header

```bash
curl -H "X-Tenant-ID: 5" \
  https://api.example.com/santri
```

### With Subdomain

```bash
curl https://tenant1.example.com/api/santri
```

## Testing

```php
<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\Santri;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantTest extends TestCase
{
    use RefreshDatabase;

    public function test_santri_is_filtered_by_tenant()
    {
        $tenant1 = Tenant::factory()->create();
        $tenant2 = Tenant::factory()->create();

        Santri::factory()->create(['tenant_id' => $tenant1->id, 'name' => 'Santri 1']);
        Santri::factory()->create(['tenant_id' => $tenant2->id, 'name' => 'Santri 2']);

        // Set tenant context
        TenantService::setTenant($tenant1);

        // Should only get tenant1's santri
        $results = Santri::all();
        $this->assertCount(1, $results);
        $this->assertEquals('Santri 1', $results->first()->name);
    }

    public function test_super_admin_can_see_all_tenants()
    {
        $superAdmin = User::factory()->create(['is_super_admin' => true]);
        $this->actingAs($superAdmin);

        TenantService::clear(); // No specific tenant

        $results = Santri::all();
        $this->assertCount(2, $results); // Both tenants' data
    }
}
```

## Important Notes

1. **Never manually set tenant_id in forms** - it's auto-injected
2. **Super admin users** should have `is_super_admin = true` and `tenant_id = null`
3. **Regular users** must have a valid `tenant_id`
4. **Always use middleware** for API routes to resolve tenant
5. **Testing**: Manually set tenant with `TenantService::setTenant($tenant)`

## Troubleshooting

### "Tenant not found" error
- Check if X-Tenant-ID header is present
- Verify subdomain matches tenant's slug or domain field
- Check if tenant is_active = true

### "Access denied" for super admin
- Ensure user has `is_super_admin = true`
- Check if `tenant_id` is null for super admin

### Data leaking between tenants
- Make sure model uses `HasTenant` trait
- Check if `TenantScope` is applied
- Verify `tenant_id` column exists in table

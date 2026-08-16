<?php

use App\Http\Controllers\Api\SantriController;
use App\Http\Controllers\Webhook\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

// =========================================================================
// Public Webhook Routes — NO auth, NO CSRF
// Payment gateway callbacks must be reachable without a session or token.
// Security is enforced inside the controller via signature/token verification.
// =========================================================================
Route::post('/webhooks/payment/{gateway}', [PaymentWebhookController::class, 'handle'])
    ->name('webhooks.payment')
    ->where('gateway', 'xendit|midtrans');

// =========================================================================
// API Routes - Protected by Sanctum Authentication
//
// All routes require valid bearer token authentication.
// Android client must include: Authorization: Bearer {token} header
// =========================================================================
Route::middleware(['auth:sanctum', 'tenant.resolve', 'tenant.log'])->group(function () {
    // List santri in current tenant (filtered by TenantScope)
    Route::get('/santri', [SantriController::class, 'index']);

    // List all santri across all tenants - super admin only
    Route::get('/santri/all', [SantriController::class, 'all']);
});
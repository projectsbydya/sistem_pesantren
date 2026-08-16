<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\FirstLoginController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

// =========================================================================
// Main Domain Auth Routes
// =========================================================================

Route::middleware('guest')->group(function () {
    // [DISABLED] Public registration - sistem sekarang private
    Route::redirect('register', '/login')->name('register');

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    // First-login password change (accessible without password.change middleware)
    Route::get('password/change', [FirstLoginController::class, 'show'])
        ->name('password.change');

    Route::post('password/update-first', [FirstLoginController::class, 'update'])
        ->name('password.update-first');
});

// =========================================================================
// Subdomain Auth Routes — {tenant}.config('app.app_domain')
// =========================================================================
// SaaS design: auth is centralised on the main domain.
// Subdomain guest routes redirect to main domain login.
// password/change remains available on the subdomain (needed mid-session).
// logout is intentionally NOT here — forms POST directly to main domain.
// =========================================================================

Route::domain('{tenant}.' . config('app.app_domain'))
    ->middleware('guest')
    ->group(function () {
        // Redirect any subdomain auth attempt to main domain login
        Route::get('login', function () {
            return redirect()->to(
                config('app.scheme') . '://' . config('app.app_domain') . '/login'
            );
        })->name('tenant.login');

        Route::redirect('register', '/login');
        Route::redirect('forgot-password', '/forgot-password');
    });

Route::domain('{tenant}.' . config('app.app_domain'))
    ->middleware(['auth', 'tenant.resolve'])
    ->group(function () {
        // First-login password change — runs on subdomain mid-session
        Route::get('password/change', [FirstLoginController::class, 'show']);
        Route::post('password/update-first', [FirstLoginController::class, 'update']);
    });

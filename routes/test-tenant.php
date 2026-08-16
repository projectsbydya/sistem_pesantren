<?php

/**
 * Test Routes for Tenant Middleware Testing
 * 
 * These routes are used by TenantMiddlewareTest
 * Copy these to your routes/api.php or routes/web.php for testing
 */

use App\Models\Santri;
use Illuminate\Support\Facades\Route;

// Group with tenant middleware
Route::middleware(['auth', 'tenant'])->group(function () {
    
    // Standard CRUD for santri
    Route::get('/api/santri', function () {
        return Santri::all();
    });
    
    Route::get('/api/santri/{id}', function ($id) {
        return Santri::findOrFail($id);
    });
    
    Route::post('/api/santri', function (\Illuminate\Http\Request $request) {
        return Santri::create($request->all());
    });
    
    Route::put('/api/santri/{id}', function ($id, \Illuminate\Http\Request $request) {
        $santri = Santri::findOrFail($id);
        $santri->update($request->all());
        return $santri;
    });
    
    Route::delete('/api/santri/{id}', function ($id) {
        Santri::findOrFail($id)->delete();
        return response()->json(['message' => 'Deleted']);
    });
    
});

// Super admin only routes
Route::middleware(['auth'])->group(function () {
    
    // View all tenants (super admin only)
    Route::get('/api/santri/all', function () {
        // This should only work for super admin
        return Santri::withoutTenant()->get();
    });
    
});

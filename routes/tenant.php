<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;
use App\Http\Middleware\TenantFileUrlMiddleware;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
    TenantFileUrlMiddleware::class,
])->group(function () {
    // Tenant dashboard routes are handled by Filament TenantPanel
    // This is just for debugging tenant context
    Route::get('/tenant-info', function () {
        return response()->json([
            'tenant_id' => tenant('id'),
            'tenant_name' => tenant('name'),
            'domain' => request()->getHost(),
            'message' => 'Tenant context is active'
        ]);
    });
    
    // Additional tenant-specific routes can be added here
});

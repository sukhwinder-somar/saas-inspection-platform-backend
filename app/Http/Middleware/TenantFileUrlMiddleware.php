<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantFileUrlMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if we're in tenant context
        if (tenant()) {
            // Set tenant-specific file URL path
            config()->set(
                'filesystems.disks.public.url',
                config('app.url') . '/storage/tenants/' . tenant('id')
            );
            
            // Also set the root path for tenant files
            config()->set(
                'filesystems.disks.public.root',
                storage_path('app/public/tenants/' . tenant('id'))
            );
        }

        return $next($request);
    }
}
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\FileUpload;

class TenantFileUploadServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Configure tenant-aware file uploads
        FileUpload::configureUsing(function (FileUpload $component): void {
            // Check if we're in tenant context
            if (function_exists('tenant') && tenant()) {
                $component
                    ->disk('public')
                    ->directory('tenants/' . tenant('id') . '/uploads')
                    ->visibility('public');
            } else {
                // Central domain uploads
                $component
                    ->disk('public')
                    ->directory('central/uploads')
                    ->visibility('public');
            }
            
            // Add some defensive configurations to prevent JavaScript errors
            $component
                ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'image/gif'])
                ->maxSize(10240) // 10MB
                ->uploadingMessage('Uploading file...')
                ->loadingIndicatorPosition('right');
        });
    }
}
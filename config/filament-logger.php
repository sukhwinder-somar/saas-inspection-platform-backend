<?php

return [
    'datetime_format' => 'm/d/Y H:i:s',
    'date_format' => 'm/d/Y',

    'activity_resource' => App\Filament\Resources\ActivityResource::class,

    'resources' => [
        'enabled' => true,
        'log_name' => 'Resource',
        'logger' => \Z3d0X\FilamentLogger\Loggers\ResourceLogger::class,
        'color' => 'success',
        'exclude' => [
            BezhanSalleh\FilamentExceptions\Resources\ExceptionResource::class,
            Croustibat\FilamentJobsMonitor\Resources\QueueMonitorResource::class,
            // Exclude tenant panel resources to prevent cross-tenant issues
            App\Filament\Tenant\Resources\AssetResource::class,
            App\Filament\Tenant\Resources\ChecklistTemplateResource::class,
            App\Filament\Tenant\Resources\InspectionResource::class,
        ],
    ],

    'access' => [
        'enabled' => true,
        'logger' => \Z3d0X\FilamentLogger\Loggers\AccessLogger::class,
        'color' => 'danger',
        'log_name' => 'Access',
    ],

    'notifications' => [
        'enabled' => true,
        'logger' => \Z3d0X\FilamentLogger\Loggers\NotificationLogger::class,
        'color' => null,
        'log_name' => 'Notification',
    ],

    'models' => [
        'enabled' => false, // Disabled temporarily to prevent class not found errors
        'log_name' => 'Model',
        'color' => 'warning',
        'logger' => \Z3d0X\FilamentLogger\Loggers\ModelLogger::class,
        'register' => [
            // App\Models\User::class,
            // Will enable after all models are properly set up
        ],
    ],

    'custom' => [
        // [
        //     'log_name' => 'Custom',
        //     'color' => 'primary',
        // ]
    ],
];

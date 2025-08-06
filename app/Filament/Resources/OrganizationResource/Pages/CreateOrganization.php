<?php

namespace App\Filament\Resources\OrganizationResource\Pages;

use App\Filament\Resources\OrganizationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrganization extends CreateRecord
{
    protected static string $resource = OrganizationResource::class;

    protected function afterCreate(): void
    {
        // The TenancyServiceProvider will automatically handle database creation
        // and migrations through the TenantCreated event
        
        \Filament\Notifications\Notification::make()
            ->title('Success')
            ->body('Organization created successfully! Tenant database will be set up automatically.')
            ->success()
            ->send();
    }
}

<?php

namespace App\Filament\Tenant\Resources\ChecklistTemplateResource\Pages;

use App\Filament\Tenant\Resources\ChecklistTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateChecklistTemplate extends CreateRecord
{
    protected static string $resource = ChecklistTemplateResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['version'] = 1;
        
        return $data;
    }
}

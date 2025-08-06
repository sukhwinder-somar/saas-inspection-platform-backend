<?php

namespace App\Filament\Tenant\Resources\ChecklistTemplateResource\Pages;

use App\Filament\Tenant\Resources\ChecklistTemplateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditChecklistTemplate extends EditRecord
{
    protected static string $resource = ChecklistTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Increment version when updating
        $data['version'] = $this->record->version + 1;
        
        return $data;
    }
}

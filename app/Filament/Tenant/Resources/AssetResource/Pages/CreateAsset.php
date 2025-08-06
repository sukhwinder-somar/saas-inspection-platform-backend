<?php

namespace App\Filament\Tenant\Resources\AssetResource\Pages;

use App\Filament\Tenant\Resources\AssetResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateAsset extends CreateRecord
{
    protected static string $resource = AssetResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Generate QR code if not provided
        if (empty($data['qr_code'])) {
            $data['qr_code'] = 'asset-' . Str::random(8);
        }

        return $data;
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'asset_id' => $this->asset_id,
            'qr_code' => $this->qr_code,
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category,
            'status' => $this->status,
            'location' => $this->location,
            'department' => $this->department,
            'assigned_to' => $this->assigned_to,
            'purchase_date' => $this->purchase_date,
            'purchase_cost' => $this->purchase_cost,
            'warranty_expiry' => $this->warranty_expiry,
            'last_maintenance' => $this->last_maintenance,
            'next_maintenance' => $this->next_maintenance,
            'maintenance_frequency' => $this->maintenance_frequency,
            'compliance_requirements' => $this->compliance_requirements,
            'documentation' => $this->documentation,
            'organization' => [
                'id' => $this->organization->id,
                'name' => $this->organization->name,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChecklistTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'asset_types',
        'sections',
        'version',
        'active',
        'created_by',
    ];

    protected $casts = [
        'asset_types' => 'array',
        'sections' => 'array',
        'active' => 'boolean',
    ];

    public function questions()
    {
        return $this->hasMany(ChecklistQuestion::class, 'template_id');
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class, 'template_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeForAssetType($query, $assetType)
    {
        return $query->whereJsonContains('asset_types', $assetType);
    }
}

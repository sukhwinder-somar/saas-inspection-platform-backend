<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Events\AssetStatusChanged;
use Carbon\Carbon;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'asset_id',
        'type',
        'make',
        'model',
        'serial_number',
        'registration_number',
        'qr_code',
        'custom_fields',
        'registration_expiry',
        'next_service_due',
        'insurance_renewal',
        'active',
    ];

    protected $casts = [
        'custom_fields' => 'array',
        'documents' => 'array',
        'registration_expiry' => 'date',
        'next_service_due' => 'date',
        'insurance_renewal' => 'date',
        'active' => 'boolean',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'asset_user_assignments');
    }

    public function inspections()
    {
        return $this->hasMany(Inspection::class);
    }

    public function isUnderWarranty()
    {
        return $this->registration_expiry && $this->registration_expiry > now();
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('type', $category);
    }

    public static function validationRules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'asset_id' => ['required', 'string', 'max:100', 'unique:assets,asset_id'],
            'type' => ['required', 'string', 'max:100'],
            'make' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'registration_expiry' => ['nullable', 'date'],
            'next_service_due' => ['nullable', 'date'],
            'insurance_renewal' => ['nullable', 'date'],
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($asset) {
            if (!$asset->qr_code) {
                $asset->qr_code = 'QR-' . uniqid();
            }
        });

        static::updating(function ($asset) {
            if ($asset->isDirty('active')) {
                $oldStatus = $asset->getOriginal('active') ? 'active' : 'inactive';
                $newStatus = $asset->active ? 'active' : 'inactive';

                event(new AssetStatusChanged($asset, $oldStatus, $newStatus));
            }
        });
    }
}

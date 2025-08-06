<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Stancl\Tenancy\Database\Models\Tenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Organization extends Tenant implements TenantWithDatabase
{
    use HasFactory, HasDatabase, HasDomains;

    protected $table = 'tenants';

    protected $fillable = [
        'id',
        'name',
        'slug',
        'subdomain',
        'domain',
        'data',
        'settings',
        'active',
        'trial_ends_at',
        'stripe_customer_id',
    ];

    protected $casts = [
        'data' => 'array',
        'settings' => 'array',
        'active' => 'boolean',
        'trial_ends_at' => 'datetime',
    ];

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function assets()
    {
        return $this->hasMany(Asset::class);
    }

    public function getSetting($key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    public function setSetting($key, $value)
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->update(['settings' => $settings]);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public static function getCustomColumns(): array
    {
        return [
            'id',
            'name',
            'slug',
            'subdomain',
            'domain',
            'settings',
            'active',
            'trial_ends_at',
            'stripe_customer_id',
        ];
    }

    public static function validationRules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255', 'unique:tenants,domain'],
            'settings' => ['nullable', 'array']
        ];
    }
}

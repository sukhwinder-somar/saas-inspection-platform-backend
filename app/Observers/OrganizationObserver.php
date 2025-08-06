<?php

namespace App\Observers;

use App\Models\Organization;
use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Models\Domain;

class OrganizationObserver
{
    /**
     * Handle the Organization "creating" event.
     */
    public function creating(Organization $organization): void
    {
        // Generate subdomain if not provided
        if (empty($organization->subdomain)) {
            $organization->subdomain = $this->generateUniqueSubdomain($organization->name);
        }

        // Generate slug if not provided
        if (empty($organization->slug)) {
            $organization->slug = Str::slug($organization->name);
        }
    }

    /**
     * Handle the Organization "created" event.
     */
    public function created(Organization $organization): void
    {
        // Create subdomain if provided
        if ($organization->subdomain) {
            $this->createSubdomain($organization);
        }
        
        // Initialize default settings
        $this->initializeDefaultSettings($organization);
    }

    /**
     * Handle the Organization "updated" event.
     */
    public function updated(Organization $organization): void
    {
        // If subdomain was changed, update the domain record
        if ($organization->wasChanged('subdomain')) {
            $this->updateSubdomain($organization);
        }
    }

    /**
     * Handle the Organization "deleted" event.
     */
    public function deleted(Organization $organization): void
    {
        // Remove associated domains
        $organization->domains()->delete();
    }

    /**
     * Generate a unique subdomain based on organization name
     */
    private function generateUniqueSubdomain(string $name): string
    {
        $baseSubdomain = Str::slug($name);
        $subdomain = $baseSubdomain;
        $counter = 1;

        // Check if subdomain already exists
        $domainSuffix = config('app.tenant_domain_suffix', env('TENANT_DOMAIN_SUFFIX', 'localhost'));
        while (Domain::where('domain', $subdomain . '.' . $domainSuffix)->exists()) {
            $subdomain = $baseSubdomain . '-' . $counter;
            $counter++;
        }

        return $subdomain;
    }

    /**
     * Create subdomain for the organization
     */
    private function createSubdomain(Organization $organization): void
    {
        $domainSuffix = config('app.tenant_domain_suffix', env('TENANT_DOMAIN_SUFFIX', 'localhost'));
        $domain = $organization->subdomain . '.' . $domainSuffix;
        
        // Create the domain record for tenancy using the tenant's domains relationship
        $organization->domains()->create([
            'domain' => $domain,
        ]);
    }

    /**
     * Update subdomain when organization subdomain changes
     */
    private function updateSubdomain(Organization $organization): void
    {
        // Remove old domain
        $organization->domains()->delete();
        
        // Create new domain
        $this->createSubdomain($organization);
    }

    /**
     * Initialize default settings for new organization
     */
    private function initializeDefaultSettings(Organization $organization): void
    {
        $defaultSettings = [
            'notifications' => [
                'email_enabled' => true,
                'sms_enabled' => false,
                'slack_enabled' => false,
            ],
            'features' => [
                'asset_management' => true,
                'inspections' => true,
                'notifications' => true,
                'analytics' => true,
            ],
            'theme' => [
                'primary_color' => '#3b82f6',
                'logo_url' => null,
            ],
            'limits' => [
                'max_users' => 10,
                'max_assets' => 1000,
                'max_inspections_per_month' => 500,
            ],
        ];

        $organization->update(['settings' => $defaultSettings]);
    }
}

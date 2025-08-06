<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Super Admin Gates
        Gate::define('view-admin', function (User $user) {
            return $user->is_super_admin || $user->hasRole('super_admin');
        });

        Gate::define('manage-posts', function (User $user) {
            return $user->is_super_admin || $user->hasRole('super_admin');
        });

        Gate::define('create-posts', function (User $user) {
            return $user->is_super_admin || $user->hasRole('super_admin');
        });

        // Asset Management Gates
        Gate::define('view-assets', function (User $user) {
            return $user->hasAnyRole(['super_admin', 'admin', 'manager', 'operator']);
        });

        Gate::define('manage-assets', function (User $user) {
            return $user->is_super_admin || $user->hasAnyRole(['super_admin', 'admin', 'manager']);
        });

        Gate::define('create-assets', function (User $user) {
            return $user->is_super_admin || $user->hasAnyRole(['super_admin', 'admin']);
        });

        // Inspection Gates
        Gate::define('view-inspections', function (User $user) {
            return $user->hasAnyRole(['super_admin', 'admin', 'manager', 'operator']);
        });

        Gate::define('manage-inspections', function (User $user) {
            return $user->is_super_admin || $user->hasAnyRole(['super_admin', 'admin', 'manager']);
        });

        Gate::define('create-inspections', function (User $user) {
            return $user->is_super_admin || $user->hasAnyRole(['super_admin', 'admin', 'manager']);
        });

        // Checklist Template Gates
        Gate::define('view-checklist-templates', function (User $user) {
            return $user->is_super_admin || $user->hasAnyRole(['super_admin', 'admin', 'manager']);
        });

        Gate::define('manage-checklist-templates', function (User $user) {
            return $user->is_super_admin || $user->hasAnyRole(['super_admin', 'admin']);
        });

        // Notification Gates
        Gate::define('view-notifications', function (User $user) {
            return $user->hasAnyRole(['super_admin', 'admin', 'manager', 'operator']);
        });

        Gate::define('manage-notifications', function (User $user) {
            return $user->is_super_admin || $user->hasAnyRole(['super_admin', 'admin']);
        });

        // Organization Management Gates
        Gate::define('manage-organization', function (User $user) {
            return $user->is_super_admin || $user->hasAnyRole(['super_admin', 'admin']);
        });

        Gate::define('view-organization-settings', function (User $user) {
            return $user->hasAnyRole(['super_admin', 'admin', 'manager']);
        });

        // User Management Gates
        Gate::define('manage-users', function (User $user) {
            return $user->is_super_admin || $user->hasRole('super_admin');
        });

        Gate::define('view-users', function (User $user) {
            return $user->hasAnyRole(['super_admin', 'admin', 'manager']);
        });

        // Subscription Gates
        Gate::define('view-subscriptions', function (User $user) {
            return $user->is_super_admin || $user->hasAnyRole(['super_admin', 'admin']);
        });

        Gate::define('manage-subscriptions', function (User $user) {
            return $user->is_super_admin || $user->hasRole('super_admin');
        });
    }
}

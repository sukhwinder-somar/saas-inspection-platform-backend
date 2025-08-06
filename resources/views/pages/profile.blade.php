<x-layouts.saas>
    <div class="py-8 px-4">
        <div class="container mx-auto max-w-4xl">
            <h1 class="text-3xl font-bold text-foreground mb-8">Profile Settings</h1>

            <x-ui.card>
                <x-ui.card-header>
                    <x-ui.card-title>User Profile</x-ui.card-title>
                    <x-ui.card-description>Manage your account settings and preferences</x-ui.card-description>
                </x-ui.card-header>
                <x-ui.card-content>
                    <p class="text-muted-foreground">
                        Profile management features will be integrated here. For now, you can manage your profile through the admin panel.
                    </p>
                    <a href="{{ route('filament.admin.pages.dashboard') }}" class="mt-4 block">
                        <x-ui.button>Go to Admin Panel</x-ui.button>
                    </a>
                </x-ui.card-content>
            </x-ui.card>
        </div>
    </div>
</x-layouts.saas>

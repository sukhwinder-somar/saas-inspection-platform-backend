<x-layouts.saas>
    <div class="py-8 px-4">
        <div class="container mx-auto max-w-4xl">
            <h1 class="text-3xl font-bold text-foreground mb-8">Dashboard</h1>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Stats cards would go here -->
                <x-ui.card>
                    <x-ui.card-header>
                        <x-ui.card-title>Welcome!</x-ui.card-title>
                        <x-ui.card-description>
                            This is a placeholder dashboard. The full dashboard with live data will be available once you're logged in through Filament.
                        </x-ui.card-description>
                    </x-ui.card-header>
                </x-ui.card>
            </div>

            <div class="text-center">
                <p class="text-muted-foreground mb-4">
                    Access the full admin panel with all features:
                </p>
                <a href="{{ route('filament.admin.pages.dashboard') }}">
                    <x-ui.button>Go to Admin Dashboard</x-ui.button>
                </a>
            </div>
        </div>
    </div>
</x-layouts.saas>

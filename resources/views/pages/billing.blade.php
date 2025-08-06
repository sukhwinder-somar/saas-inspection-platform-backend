<x-layouts.saas>
    <div class="py-8 px-4">
        <div class="container mx-auto max-w-4xl">
            <h1 class="text-3xl font-bold text-foreground mb-8">Billing & Subscription</h1>

            <x-ui.card>
                <x-ui.card-header>
                    <x-ui.card-title>Subscription Management</x-ui.card-title>
                    <x-ui.card-description>Manage your subscription and billing information</x-ui.card-description>
                </x-ui.card-header>
                <x-ui.card-content>
                    <p class="text-muted-foreground">
                        Billing and subscription management features will be integrated here using Laravel Cashier and Stripe.
                    </p>
                    <a href="{{ route('filament.admin.pages.dashboard') }}" class="mt-4 block">
                        <x-ui.button>Go to Admin Panel</x-ui.button>
                    </a>
                </x-ui.card-content>
            </x-ui.card>
        </div>
    </div>
</x-layouts.saas>

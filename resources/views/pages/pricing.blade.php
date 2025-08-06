<x-layouts.saas>
    <div class="py-20 px-4">
        <div class="container mx-auto max-w-6xl">
            <div class="text-center mb-16">
                <h1 class="text-4xl md:text-5xl font-bold text-foreground mb-6">
                    Simple, Transparent Pricing
                </h1>
                <p class="text-xl text-muted-foreground max-w-2xl mx-auto">
                    Choose the plan that's right for your organization. All plans include our core features.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Starter Plan -->
                <x-ui.card>
                    <x-ui.card-header>
                        <x-ui.card-title>Starter</x-ui.card-title>
                        <x-ui.card-description>Perfect for small teams</x-ui.card-description>
                        <div class="mt-4">
                            <span class="text-4xl font-bold">$29</span>
                            <span class="text-muted-foreground">/month</span>
                        </div>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <ul class="space-y-2 text-sm">
                            <li>✓ Up to 5 users</li>
                            <li>✓ 100 assets</li>
                            <li>✓ Basic inspections</li>
                            <li>✓ Email notifications</li>
                        </ul>
                        <a href="{{ route('register') }}" class="mt-6 block">
                            <x-ui.button class="w-full">Get Started</x-ui.button>
                        </a>
                    </x-ui.card-content>
                </x-ui.card>

                <!-- Professional Plan -->
                <x-ui.card class="border-primary">
                    <x-ui.card-header>
                        <x-ui.card-title>Professional</x-ui.card-title>
                        <x-ui.card-description>Most popular choice</x-ui.card-description>
                        <div class="mt-4">
                            <span class="text-4xl font-bold">$79</span>
                            <span class="text-muted-foreground">/month</span>
                        </div>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <ul class="space-y-2 text-sm">
                            <li>✓ Up to 25 users</li>
                            <li>✓ 1,000 assets</li>
                            <li>✓ Advanced inspections</li>
                            <li>✓ All notification channels</li>
                            <li>✓ Custom integrations</li>
                        </ul>
                        <a href="{{ route('register') }}" class="mt-6 block">
                            <x-ui.button class="w-full">Get Started</x-ui.button>
                        </a>
                    </x-ui.card-content>
                </x-ui.card>

                <!-- Enterprise Plan -->
                <x-ui.card>
                    <x-ui.card-header>
                        <x-ui.card-title>Enterprise</x-ui.card-title>
                        <x-ui.card-description>For large organizations</x-ui.card-description>
                        <div class="mt-4">
                            <span class="text-4xl font-bold">$199</span>
                            <span class="text-muted-foreground">/month</span>
                        </div>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <ul class="space-y-2 text-sm">
                            <li>✓ Unlimited users</li>
                            <li>✓ Unlimited assets</li>
                            <li>✓ Premium support</li>
                            <li>✓ Custom deployment</li>
                            <li>✓ Advanced analytics</li>
                        </ul>
                        <a href="{{ route('register') }}" class="mt-6 block">
                            <x-ui.button class="w-full">Contact Sales</x-ui.button>
                        </a>
                    </x-ui.card-content>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layouts.saas>

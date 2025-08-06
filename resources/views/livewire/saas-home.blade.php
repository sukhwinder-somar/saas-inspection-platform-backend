<x-layouts.saas>
    <!-- Hero Section -->
    <section class="py-20 px-4">
        <div class="container mx-auto text-center">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-4xl md:text-6xl font-bold text-foreground mb-6">
                    Streamline Your
                    <span class="text-primary">Asset Management</span>
                </h1>
                <p class="text-xl text-muted-foreground mb-8 max-w-2xl mx-auto">
                    Comprehensive SaaS platform for asset tracking, inspections, compliance monitoring, and team collaboration. Built for modern enterprises.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    @auth
                        <a href="{{ route('dashboard') }}">
                            <x-ui.button size="lg" class="w-full sm:w-auto">
                                Go to Dashboard
                            </x-ui.button>
                        </a>
                    @else
                        <a href="{{ route('register') }}">
                            <x-ui.button size="lg" class="w-full sm:w-auto">
                                Start Free Trial
                            </x-ui.button>
                        </a>
                        <a href="{{ route('pricing') }}">
                            <x-ui.button variant="outline" size="lg" class="w-full sm:w-auto">
                                View Pricing
                            </x-ui.button>
                        </a>
                    @endauth
                </div>

                <!-- Trust indicators -->
                <div class="mt-12">
                    <p class="text-sm text-muted-foreground mb-4">Trusted by 1000+ companies worldwide</p>
                    <div class="flex justify-center items-center space-x-8 opacity-60">
                        <div class="h-8 w-24 bg-muted rounded"></div>
                        <div class="h-8 w-24 bg-muted rounded"></div>
                        <div class="h-8 w-24 bg-muted rounded"></div>
                        <div class="h-8 w-24 bg-muted rounded"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 px-4 bg-muted/50">
        <div class="container mx-auto">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-foreground mb-4">
                    Everything you need to manage your assets
                </h2>
                <p class="text-xl text-muted-foreground max-w-2xl mx-auto">
                    From asset tracking to compliance monitoring, our platform provides all the tools your team needs.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Asset Management -->
                <x-ui.card>
                    <x-ui.card-header>
                        <div class="h-12 w-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                            <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <x-ui.card-title>Asset Management</x-ui.card-title>
                        <x-ui.card-description>
                            Track and manage all your assets with real-time monitoring and detailed reporting.
                        </x-ui.card-description>
                    </x-ui.card-header>
                </x-ui.card>

                <!-- Inspections -->
                <x-ui.card>
                    <x-ui.card-header>
                        <div class="h-12 w-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                            <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <x-ui.card-title>Smart Inspections</x-ui.card-title>
                        <x-ui.card-description>
                            Create custom checklists and automate inspection workflows with mobile support.
                        </x-ui.card-description>
                    </x-ui.card-header>
                </x-ui.card>

                <!-- Notifications -->
                <x-ui.card>
                    <x-ui.card-header>
                        <div class="h-12 w-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                            <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19.5A2.5 2.5 0 016.5 17H20"></path>
                            </svg>
                        </div>
                        <x-ui.card-title>Smart Notifications</x-ui.card-title>
                        <x-ui.card-description>
                            Get notified instantly about important events via email, SMS, and push notifications.
                        </x-ui.card-description>
                    </x-ui.card-header>
                </x-ui.card>

                <!-- Multi-tenant -->
                <x-ui.card>
                    <x-ui.card-header>
                        <div class="h-12 w-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                            <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <x-ui.card-title>Multi-Tenant</x-ui.card-title>
                        <x-ui.card-description>
                            Separate organizations with custom domains and complete data isolation.
                        </x-ui.card-description>
                    </x-ui.card-header>
                </x-ui.card>

                <!-- Analytics -->
                <x-ui.card>
                    <x-ui.card-header>
                        <div class="h-12 w-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                            <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <x-ui.card-title>Advanced Analytics</x-ui.card-title>
                        <x-ui.card-description>
                            Comprehensive reporting and analytics to track performance and compliance.
                        </x-ui.card-description>
                    </x-ui.card-header>
                </x-ui.card>

                <!-- API -->
                <x-ui.card>
                    <x-ui.card-header>
                        <div class="h-12 w-12 bg-primary/10 rounded-lg flex items-center justify-center mb-4">
                            <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <x-ui.card-title>API Integration</x-ui.card-title>
                        <x-ui.card-description>
                            RESTful API for seamless integration with your existing systems and workflows.
                        </x-ui.card-description>
                    </x-ui.card-header>
                </x-ui.card>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-20 px-4">
        <div class="container mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-4xl font-bold text-primary mb-2">10M+</div>
                    <div class="text-muted-foreground">Assets Tracked</div>
                </div>
                <div>
                    <div class="text-4xl font-bold text-primary mb-2">500K+</div>
                    <div class="text-muted-foreground">Inspections Completed</div>
                </div>
                <div>
                    <div class="text-4xl font-bold text-primary mb-2">99.9%</div>
                    <div class="text-muted-foreground">Uptime</div>
                </div>
                <div>
                    <div class="text-4xl font-bold text-primary mb-2">1K+</div>
                    <div class="text-muted-foreground">Happy Customers</div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 px-4 bg-primary">
        <div class="container mx-auto text-center">
            <div class="max-w-2xl mx-auto">
                <h2 class="text-3xl md:text-4xl font-bold text-primary-foreground mb-4">
                    Ready to get started?
                </h2>
                <p class="text-xl text-primary-foreground/80 mb-8">
                    Join thousands of companies already using our platform to streamline their operations.
                </p>
                @guest
                    <a href="{{ route('register') }}">
                        <x-ui.button variant="secondary" size="lg">
                            Start Your Free Trial
                        </x-ui.button>
                    </a>
                @endguest
            </div>
        </div>
    </section>
</x-layouts.saas>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/css/shadcn.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased">
    <div class="min-h-screen bg-background">
        <!-- Navigation -->
        <nav class="border-b border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div class="container mx-auto px-4">
                <div class="flex h-16 items-center justify-between">
                    <!-- Logo -->
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('home') }}" class="flex items-center space-x-2">
                            <div class="h-8 w-8 rounded-lg bg-primary flex items-center justify-center">
                                <span class="text-primary-foreground font-bold text-sm">S</span>
                            </div>
                            <span class="text-xl font-bold text-foreground">{{ config('app.name') }}</span>
                        </a>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden md:flex items-center space-x-6">
                        @auth
                            @can('view-admin')
                                <a href="{{ route('filament.admin.pages.dashboard') }}" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                                    Admin Dashboard
                                </a>
                            @endcan
                            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                                Dashboard
                            </a>
                        @endauth

                        @guest
                            <a href="{{ route('about') }}" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                                About
                            </a>
                            <a href="{{ route('pricing') }}" class="text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                                Pricing
                            </a>
                        @endguest
                    </div>

                    <!-- User Menu -->
                    <div class="flex items-center space-x-4">
                        @auth
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" class="flex items-center space-x-2 text-sm font-medium text-muted-foreground hover:text-foreground transition-colors">
                                    <div class="h-8 w-8 rounded-full bg-primary/10 flex items-center justify-center">
                                        <span class="text-xs font-medium">{{ substr(auth()->user()->name, 0, 2) }}</span>
                                    </div>
                                    <span>{{ auth()->user()->name }}</span>
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-popover rounded-md shadow-lg border border-border z-50">
                                    <div class="py-1">
                                        <a href="/admin" class="block px-4 py-2 text-sm text-popover-foreground hover:bg-accent">Dashboard</a>
                                        <a href="#" class="block px-4 py-2 text-sm text-popover-foreground hover:bg-accent">Settings</a>
                                        <div class="border-t border-border"></div>
                                        <form method="POST" action="{{ route('filament.admin.auth.logout') }}">
                                            @csrf
                                            <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-popover-foreground hover:bg-accent">Sign out</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('login') }}">
                                <x-ui.button variant="ghost">Sign in</x-ui.button>
                            </a>
                            <a href="{{ route('register') }}">
                                <x-ui.button>Get Started</x-ui.button>
                            </a>
                        @endauth
                    </div>

                    <!-- Mobile menu button -->
                    <div class="md:hidden">
                        <button class="text-muted-foreground hover:text-foreground" x-data @click="$refs.mobileMenu.classList.toggle('hidden')">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Mobile menu -->
                <div x-ref="mobileMenu" class="hidden md:hidden py-4 border-t border-border">
                    @auth
                        @can('view-admin')
                            <a href="{{ route('filament.admin.pages.dashboard') }}" class="block py-2 text-sm font-medium text-muted-foreground hover:text-foreground">
                                Admin Dashboard
                            </a>
                        @endcan
                        <a href="{{ route('dashboard') }}" class="block py-2 text-sm font-medium text-muted-foreground hover:text-foreground">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('about') }}" class="block py-2 text-sm font-medium text-muted-foreground hover:text-foreground">
                            About
                        </a>
                        <a href="{{ route('pricing') }}" class="block py-2 text-sm font-medium text-muted-foreground hover:text-foreground">
                            Pricing
                        </a>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main>
            {{ $slot }}
        </main>

        <!-- Footer -->
        <footer class="border-t border-border bg-background">
            <div class="container mx-auto px-4 py-12">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div class="space-y-4">
                        <div class="flex items-center space-x-2">
                            <div class="h-8 w-8 rounded-lg bg-primary flex items-center justify-center">
                                <span class="text-primary-foreground font-bold text-sm">S</span>
                            </div>
                            <span class="text-xl font-bold text-foreground">{{ config('app.name') }}</span>
                        </div>
                        <p class="text-sm text-muted-foreground">
                            Streamline your operations with our comprehensive SaaS platform for asset management, inspections, and compliance.
                        </p>
                    </div>

                    <div>
                        <h3 class="font-semibold text-foreground mb-4">Product</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="text-muted-foreground hover:text-foreground transition-colors">Features</a></li>
                            <li><a href="#" class="text-muted-foreground hover:text-foreground transition-colors">Pricing</a></li>
                            <li><a href="#" class="text-muted-foreground hover:text-foreground transition-colors">API</a></li>
                            <li><a href="#" class="text-muted-foreground hover:text-foreground transition-colors">Documentation</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="font-semibold text-foreground mb-4">Company</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="text-muted-foreground hover:text-foreground transition-colors">About</a></li>
                            <li><a href="#" class="text-muted-foreground hover:text-foreground transition-colors">Blog</a></li>
                            <li><a href="#" class="text-muted-foreground hover:text-foreground transition-colors">Careers</a></li>
                            <li><a href="#" class="text-muted-foreground hover:text-foreground transition-colors">Contact</a></li>
                        </ul>
                    </div>

                    <div>
                        <h3 class="font-semibold text-foreground mb-4">Support</h3>
                        <ul class="space-y-2 text-sm">
                            <li><a href="#" class="text-muted-foreground hover:text-foreground transition-colors">Help Center</a></li>
                            <li><a href="#" class="text-muted-foreground hover:text-foreground transition-colors">Privacy Policy</a></li>
                            <li><a href="#" class="text-muted-foreground hover:text-foreground transition-colors">Terms of Service</a></li>
                            <li><a href="#" class="text-muted-foreground hover:text-foreground transition-colors">Status</a></li>
                        </ul>
                    </div>
                </div>

                <div class="border-t border-border mt-8 pt-8 flex flex-col md:flex-row justify-between items-center">
                    <p class="text-sm text-muted-foreground">
                        © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                    </p>
                    <div class="flex space-x-6 mt-4 md:mt-0">
                        <a href="#" class="text-muted-foreground hover:text-foreground transition-colors">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                            </svg>
                        </a>
                        <a href="#" class="text-muted-foreground hover:text-foreground transition-colors">
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M22.46 6c-.77.35-1.6.58-2.46.69.88-.53 1.56-1.37 1.88-2.38-.83.5-1.75.85-2.72 1.05C18.37 4.5 17.26 4 16 4c-2.35 0-4.27 1.92-4.27 4.29 0 .34.04.67.11.98C8.28 9.09 5.11 7.38 3 4.79c-.37.63-.58 1.37-.58 2.15 0 1.49.75 2.81 1.91 3.56-.71 0-1.37-.2-1.95-.5v.03c0 2.08 1.48 3.82 3.44 4.21a4.22 4.22 0 0 1-1.93.07 4.28 4.28 0 0 0 4 2.98 8.521 8.521 0 0 1-5.33 1.84c-.34 0-.68-.02-1.02-.06C3.44 20.29 5.7 21 8.12 21 16 21 20.33 14.46 20.33 8.79c0-.19 0-.37-.01-.56.84-.6 1.56-1.36 2.14-2.23z"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    @livewireScripts
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>

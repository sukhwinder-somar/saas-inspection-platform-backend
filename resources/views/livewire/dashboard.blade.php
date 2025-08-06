<x-layouts.saas>
    <div class="py-8 px-4">
        <div class="container mx-auto max-w-7xl">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-foreground">Dashboard</h1>
                <p class="text-muted-foreground">Welcome back, {{ auth()->user()->name }}. Here's an overview of your organization.</p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Total Assets -->
                <x-ui.card>
                    <x-ui.card-header class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <x-ui.card-title class="text-sm font-medium">Total Assets</x-ui.card-title>
                        <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <div class="text-2xl font-bold">{{ number_format($totalAssets) }}</div>
                        <p class="text-xs text-muted-foreground">All tracked assets</p>
                    </x-ui.card-content>
                </x-ui.card>

                <!-- Pending Inspections -->
                <x-ui.card>
                    <x-ui.card-header class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <x-ui.card-title class="text-sm font-medium">Pending Inspections</x-ui.card-title>
                        <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <div class="text-2xl font-bold">{{ number_format($pendingInspections) }}</div>
                        <p class="text-xs text-muted-foreground">Awaiting completion</p>
                    </x-ui.card-content>
                </x-ui.card>

                <!-- Overdue Inspections -->
                <x-ui.card>
                    <x-ui.card-header class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <x-ui.card-title class="text-sm font-medium">Overdue Inspections</x-ui.card-title>
                        <svg class="h-4 w-4 text-destructive" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.73-.833-2.5 0L4.268 13.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <div class="text-2xl font-bold text-destructive">{{ number_format($overdueInspections) }}</div>
                        <p class="text-xs text-muted-foreground">Require immediate attention</p>
                    </x-ui.card-content>
                </x-ui.card>

                <!-- Notifications -->
                <x-ui.card>
                    <x-ui.card-header class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <x-ui.card-title class="text-sm font-medium">New Notifications</x-ui.card-title>
                        <svg class="h-4 w-4 text-muted-foreground" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19.5A2.5 2.5 0 016.5 17H20"></path>
                        </svg>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <div class="text-2xl font-bold">{{ number_format($recentNotifications->count()) }}</div>
                        <p class="text-xs text-muted-foreground">Last 24 hours</p>
                    </x-ui.card-content>
                </x-ui.card>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Quick Actions -->
                <x-ui.card>
                    <x-ui.card-header>
                        <x-ui.card-title>Quick Actions</x-ui.card-title>
                        <x-ui.card-description>Common tasks and shortcuts</x-ui.card-description>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        <div class="grid grid-cols-2 gap-4">
                            @can('view-admin')
                                <a href="{{ route('filament.admin.resources.assets.index') }}" class="flex items-center p-4 border border-border rounded-lg hover:bg-accent transition-colors">
                                    <div class="h-10 w-10 bg-primary/10 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="h-5 w-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-medium">Add Asset</div>
                                        <div class="text-sm text-muted-foreground">Register new asset</div>
                                    </div>
                                </a>
                            @endcan

                            <a href="{{ route('filament.admin.resources.inspections.index') }}" class="flex items-center p-4 border border-border rounded-lg hover:bg-accent transition-colors">
                                <div class="h-10 w-10 bg-primary/10 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="h-5 w-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-medium">View Inspections</div>
                                    <div class="text-sm text-muted-foreground">Manage inspections</div>
                                </div>
                            </a>

                            @can('view-admin')
                                <a href="{{ route('filament.admin.resources.checklist-templates.index') }}" class="flex items-center p-4 border border-border rounded-lg hover:bg-accent transition-colors">
                                    <div class="h-10 w-10 bg-primary/10 rounded-lg flex items-center justify-center mr-3">
                                        <svg class="h-5 w-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-medium">Checklists</div>
                                        <div class="text-sm text-muted-foreground">Manage templates</div>
                                    </div>
                                </a>
                            @endcan

                            <a href="{{ route('filament.admin.resources.notifications.index') }}" class="flex items-center p-4 border border-border rounded-lg hover:bg-accent transition-colors">
                                <div class="h-10 w-10 bg-primary/10 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="h-5 w-5 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19.5A2.5 2.5 0 016.5 17H20"></path>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-medium">Notifications</div>
                                    <div class="text-sm text-muted-foreground">View all alerts</div>
                                </div>
                            </a>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>

                <!-- Recent Notifications -->
                <x-ui.card>
                    <x-ui.card-header>
                        <x-ui.card-title>Recent Notifications</x-ui.card-title>
                        <x-ui.card-description>Latest updates and alerts</x-ui.card-description>
                    </x-ui.card-header>
                    <x-ui.card-content>
                        @if($recentNotifications->count() > 0)
                            <div class="space-y-4">
                                @foreach($recentNotifications as $notification)
                                    <div class="flex items-start space-x-3 p-3 border border-border rounded-lg">
                                        <div class="h-2 w-2 bg-primary rounded-full mt-2 flex-shrink-0"></div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-foreground">{{ $notification->title }}</p>
                                            <p class="text-sm text-muted-foreground">{{ $notification->message }}</p>
                                            <p class="text-xs text-muted-foreground mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-6">
                                <svg class="h-12 w-12 text-muted-foreground mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <p class="text-muted-foreground">No notifications yet</p>
                            </div>
                        @endif
                    </x-ui.card-content>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layouts.saas>

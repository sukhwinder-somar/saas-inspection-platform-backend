<section class="py-20 px-4 bg-muted/30">
    <div class="container mx-auto">
        @if(!empty($data['title']))
            <div class="text-center mb-16 space-y-4">
                <h2 class="text-3xl font-bold tracking-tighter sm:text-4xl md:text-5xl text-foreground">
                    {{ $data['title'] }}
                </h2>
                @if(!empty($data['subtitle']))
                    <p class="mx-auto max-w-[700px] text-lg text-muted-foreground">
                        {{ $data['subtitle'] }}
                    </p>
                @endif
            </div>
        @endif

        @if(!empty($data['features']))
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($data['features'] as $feature)
                    <x-ui.card class="border-0 shadow-sm hover:shadow-md transition-shadow">
                        <x-ui.card-header class="space-y-4">
                            @if(!empty($feature['icon']))
                                <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                                    <svg class="h-6 w-6 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                            @endif
                            <div class="space-y-2">
                                <x-ui.card-title class="text-xl">{{ $feature['title'] }}</x-ui.card-title>
                                @if(!empty($feature['description']))
                                    <x-ui.card-description class="text-base">
                                        {{ $feature['description'] }}
                                    </x-ui.card-description>
                                @endif
                            </div>
                        </x-ui.card-header>
                    </x-ui.card>
                @endforeach
            </div>
        @endif
    </div>
</section>
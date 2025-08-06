<section class="relative py-20 px-4 overflow-hidden">
    {{-- Background Image --}}
    @if(!empty($data['background_image']))
        <div class="absolute inset-0 z-0">
            <img src="{{ Storage::url($data['background_image']) }}" alt="" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-background/80"></div>
        </div>
    @else
        <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-secondary/5"></div>
    @endif
    
    <div class="relative z-10 container mx-auto text-center">
        <div class="max-w-4xl mx-auto space-y-6">
            <h1 class="text-4xl font-bold tracking-tighter sm:text-5xl md:text-6xl lg:text-7xl text-foreground">
                {{ $data['title'] ?? 'Hero Title' }}
            </h1>
            @if(!empty($data['subtitle']))
                <p class="mx-auto max-w-[700px] text-lg text-muted-foreground sm:text-xl">
                    {{ $data['subtitle'] }}
                </p>
            @endif
            @if(!empty($data['button_text']) && !empty($data['button_url']))
                <div class="flex justify-center gap-4">
                    <a href="{{ $data['button_url'] }}">
                        <x-ui.button size="lg" class="h-12 px-8">
                            {{ $data['button_text'] }}
                        </x-ui.button>
                    </a>
                </div>
            @endif
        </div>
    </div>
</section>
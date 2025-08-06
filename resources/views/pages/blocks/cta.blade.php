<section class="py-20 px-4 bg-primary">
    <div class="container mx-auto text-center">
        <div class="mx-auto max-w-2xl space-y-6">
            <h2 class="text-3xl font-bold tracking-tighter sm:text-4xl md:text-5xl text-primary-foreground">
                {{ $data['title'] ?? 'Ready to get started?' }}
            </h2>
            @if(!empty($data['subtitle']))
                <p class="mx-auto max-w-[600px] text-lg text-primary-foreground/90">
                    {{ $data['subtitle'] }}
                </p>
            @endif
            @if(!empty($data['button_text']) && !empty($data['button_url']))
                <div class="flex justify-center">
                    <a href="{{ $data['button_url'] }}">
                        <x-ui.button variant="secondary" size="lg" class="h-12 px-8 text-base">
                            {{ $data['button_text'] }}
                        </x-ui.button>
                    </a>
                </div>
            @endif
        </div>
    </div>
</section>
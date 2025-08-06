<section class="py-20 px-4 bg-muted/50">
    <div class="container mx-auto">
        @if(!empty($data['title']))
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-foreground mb-4">
                    {{ $data['title'] }}
                </h2>
                @if(!empty($data['subtitle']))
                    <p class="text-xl text-muted-foreground max-w-2xl mx-auto">
                        {{ $data['subtitle'] }}
                    </p>
                @endif
            </div>
        @endif

        @if(!empty($data['testimonials']))
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($data['testimonials'] as $testimonial)
                    <x-ui.card>
                        <x-ui.card-content class="p-6">
                            @if(!empty($testimonial['quote']))
                                <blockquote class="text-lg text-foreground mb-4">
                                    "{{ $testimonial['quote'] }}"
                                </blockquote>
                            @endif
                            <div class="flex items-center">
                                @if(!empty($testimonial['avatar']))
                                    <img 
                                        src="{{ Storage::url($testimonial['avatar']) }}" 
                                        alt="{{ $testimonial['name'] ?? '' }}"
                                        class="w-12 h-12 rounded-full mr-4"
                                    >
                                @endif
                                <div>
                                    @if(!empty($testimonial['name']))
                                        <div class="font-semibold text-foreground">
                                            {{ $testimonial['name'] }}
                                        </div>
                                    @endif
                                    @if(!empty($testimonial['position']) && !empty($testimonial['company']))
                                        <div class="text-sm text-muted-foreground">
                                            {{ $testimonial['position'] }} at {{ $testimonial['company'] }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </x-ui.card-content>
                    </x-ui.card>
                @endforeach
            </div>
        @endif
    </div>
</section>
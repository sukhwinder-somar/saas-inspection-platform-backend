<section class="py-16 px-4">
    <div class="container mx-auto">
        @if(!empty($data['title']))
            <div class="text-center mb-12">
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

        @if(!empty($data['images']))
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($data['images'] as $image)
                    <div class="relative group overflow-hidden rounded-lg">
                        <img 
                            src="{{ Storage::url($image['image']) }}" 
                            alt="{{ $image['alt'] ?? '' }}"
                            class="w-full h-64 object-cover transition-transform duration-300 group-hover:scale-110"
                        >
                        @if(!empty($image['caption']))
                            <div class="absolute bottom-0 left-0 right-0 bg-black/50 text-white p-4">
                                <p class="text-sm">{{ $image['caption'] }}</p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
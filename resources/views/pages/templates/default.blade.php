<x-layouts.saas>
    @if($page->content)
        @foreach($page->content as $block)
            @includeWhen(view()->exists("pages.blocks.{$block['type']}"), "pages.blocks.{$block['type']}", ['data' => $block['data']])
        @endforeach
    @else
        <div class="py-20 px-4">
            <div class="container mx-auto max-w-4xl">
                <h1 class="text-4xl md:text-5xl font-bold text-foreground mb-6">
                    {{ $page->title }}
                </h1>
                <div class="prose prose-lg max-w-none">
                    <p>This is a default page template. Content blocks will appear here when configured.</p>
                </div>
            </div>
        </div>
    @endif
</x-layouts.saas>
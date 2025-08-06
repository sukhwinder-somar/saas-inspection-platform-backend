<x-layouts.saas>
    <div class="py-20 px-4">
        <div class="container mx-auto max-w-4xl text-center">
            <h1 class="text-4xl md:text-5xl font-bold text-foreground mb-6">
                About {{ config('app.name') }}
            </h1>
            <p class="text-xl text-muted-foreground mb-8 max-w-2xl mx-auto">
                We're revolutionizing asset management and inspection workflows for modern enterprises.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-16">
                <x-ui.card>
                    <x-ui.card-header>
                        <x-ui.card-title>Our Mission</x-ui.card-title>
                        <x-ui.card-description>
                            To simplify and streamline asset management processes through innovative technology.
                        </x-ui.card-description>
                    </x-ui.card-header>
                </x-ui.card>

                <x-ui.card>
                    <x-ui.card-header>
                        <x-ui.card-title>Our Vision</x-ui.card-title>
                        <x-ui.card-description>
                            A world where every organization can efficiently manage their assets and ensure compliance.
                        </x-ui.card-description>
                    </x-ui.card-header>
                </x-ui.card>
            </div>
        </div>
    </div>
</x-layouts.saas>

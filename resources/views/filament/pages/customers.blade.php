<x-filament-panels::page>
    <div class="sticky top-0 z-30 -mx-6 px-6 py-3 mb-2 bg-white dark:bg-gray-950 border-b border-gray-100 dark:border-white/5">
        <div class="flex gap-2 flex-wrap">
            <button
                type="button"
                wire:click="selectProduct('{{ \App\Filament\Pages\Customers::ALL_SLUG }}')"
                @class([
                    'inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold transition shadow-sm',
                    'bg-primary-600 text-white shadow-primary-600/30' => $this->isAllSelected(),
                    'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50 dark:bg-white/5 dark:text-white dark:ring-white/10 dark:hover:bg-white/10' => ! $this->isAllSelected(),
                ])
            >
                <x-heroicon-o-squares-2x2 class="w-4 h-4" />
                All
                <span @class([
                    'text-xs px-1.5 py-0.5 rounded-full',
                    'bg-white/20' => $this->isAllSelected(),
                    'bg-gray-100 dark:bg-white/10' => ! $this->isAllSelected(),
                ])>{{ $this->getAllOrdersCount() }}</span>
            </button>

            @foreach ($this->getProducts() as $product)
                <button
                    type="button"
                    wire:click="selectProduct('{{ $product->slug }}')"
                    @class([
                        'inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold transition shadow-sm',
                        'bg-primary-600 text-white shadow-primary-600/30' => $productSlug === $product->slug,
                        'bg-white text-gray-700 ring-1 ring-gray-200 hover:bg-gray-50 dark:bg-white/5 dark:text-white dark:ring-white/10 dark:hover:bg-white/10' => $productSlug !== $product->slug,
                    ])
                >
                    {{ $product->name }}
                    <span @class([
                        'text-xs px-1.5 py-0.5 rounded-full',
                        'bg-white/20' => $productSlug === $product->slug,
                        'bg-gray-100 dark:bg-white/10' => $productSlug !== $product->slug,
                    ])>{{ $this->getProductCustomerCount($product) }}</span>
                </button>
            @endforeach

            @if ($this->getProducts()->isEmpty())
                <p class="text-sm text-gray-500">No products with active plans yet.</p>
            @endif
        </div>
    </div>

    <div
        x-data="{
            isFullscreen: false,
            toggle() {
                const el = $refs.fullscreenWrapper;
                if (!document.fullscreenElement) {
                    el.requestFullscreen?.();
                } else {
                    document.exitFullscreen?.();
                }
            }
        }"
        x-ref="fullscreenWrapper"
        @fullscreenchange.window="isFullscreen = !!document.fullscreenElement"
        class="[&:fullscreen]:bg-white dark:[&:fullscreen]:bg-gray-900 [&:fullscreen]:overflow-auto [&:fullscreen]:p-6"
    >
        <div class="flex justify-end mb-3">
            <x-filament::button color="gray" size="sm" icon="heroicon-o-arrows-pointing-out" x-on:click="toggle()">
                <span x-text="isFullscreen ? 'Exit Full Screen' : 'Full Screen'"></span>
            </x-filament::button>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>

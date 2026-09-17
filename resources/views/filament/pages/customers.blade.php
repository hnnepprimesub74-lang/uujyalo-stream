<x-filament-panels::page>
    <div class="flex gap-2 flex-wrap mb-4">
        @forelse ($this->getProducts() as $product)
            <button
                type="button"
                wire:click="selectProduct('{{ $product->slug }}')"
                @class([
                    'px-4 py-2 rounded-full text-sm font-bold transition',
                    'bg-primary-600 text-white' => $productSlug === $product->slug,
                    'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' => $productSlug !== $product->slug,
                ])
            >
                {{ $product->name }}
            </button>
        @empty
            <p class="text-sm text-gray-500">No products with active plans yet.</p>
        @endforelse
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

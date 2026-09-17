<x-filament-panels::page>
    <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
        <div class="flex gap-2 flex-wrap">
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
                <p class="text-sm text-gray-500">No shared-account products yet.</p>
            @endforelse
        </div>

        @if ($this->getBulkAddUrl())
            <x-filament::button icon="heroicon-o-table-cells" tag="a" :href="$this->getBulkAddUrl()">
                Bulk Add Accounts
            </x-filament::button>
        @endif
    </div>

    {{ $this->table }}
</x-filament-panels::page>

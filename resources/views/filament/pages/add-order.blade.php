<x-filament-panels::page>
    <form wire:submit="create">
        {{ $this->form }}

        <div class="mt-6 flex items-center gap-3">
            <x-filament::button type="submit">
                Create Order
            </x-filament::button>

            <x-filament::button type="button" color="warning" wire:click="createPending">
                Save as Pending
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>

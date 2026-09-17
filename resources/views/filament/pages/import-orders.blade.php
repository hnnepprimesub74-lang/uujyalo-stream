<x-filament-panels::page>
    <form wire:submit="import">
        {{ $this->form }}

        <div class="mt-6">
            <x-filament::button type="submit">
                Import
            </x-filament::button>
        </div>
    </form>

    @if ($lastImportedCount !== null)
        <div class="mt-6 rounded-lg bg-green-50 p-4 text-sm text-green-700">
            Successfully imported {{ $lastImportedCount }} order(s).
        </div>
    @endif

    @if (! empty($lastSkipped))
        <div class="mt-4 rounded-lg bg-red-50 p-4 text-sm text-red-700">
            <p class="font-medium mb-2">{{ count($lastSkipped) }} row(s) skipped:</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach ($lastSkipped as $skip)
                    <li>Row {{ $skip['row'] }}: {{ $skip['reason'] }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</x-filament-panels::page>

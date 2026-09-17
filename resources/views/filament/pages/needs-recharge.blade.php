<x-filament-panels::page>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-2">
        <div class="fi-section rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">Overdue</p>
            <p class="text-2xl font-bold text-danger-600 dark:text-danger-400">{{ $this->getOverdueCount() }}</p>
        </div>
        <div class="fi-section rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-gray-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">Due Within {{ \App\Filament\Pages\NeedsRecharge::LOOKAHEAD_DAYS }} Days</p>
            <p class="text-2xl font-bold text-warning-600 dark:text-warning-400">{{ $this->getDueSoonCount() }}</p>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>

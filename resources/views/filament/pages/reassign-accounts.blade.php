<x-filament-panels::page>
    <div class="flex justify-end mb-3">
        <x-filament::button color="gray" size="sm" icon="heroicon-o-arrow-path" wire:click="runNow">
            Re-check Now
        </x-filament::button>
    </div>

    {{ $this->table }}

    @php $logs = $this->getRecentLogs(); @endphp

    @if ($logs->isNotEmpty())
        <div class="mt-8">
            <h2 class="text-base font-semibold mb-1">Recently Reassigned</h2>
            <p class="text-sm text-gray-500 mb-4">Tell these customers their new login, then mark them notified.</p>

            <div class="overflow-x-auto rounded-xl ring-1 ring-gray-950/5 dark:ring-white/10">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5">
                        <tr class="text-left">
                            <th class="px-4 py-3 font-semibold">Customer</th>
                            <th class="px-4 py-3 font-semibold">Old Account</th>
                            <th class="px-4 py-3 font-semibold">New Account</th>
                            <th class="px-4 py-3 font-semibold">When</th>
                            <th class="px-4 py-3 font-semibold"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr class="border-t border-gray-100 dark:border-white/5" wire:key="reassign-log-{{ $log->id }}">
                                <td class="px-4 py-3">{{ $log->subscription?->user?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $log->oldSharedAccount?->email ?? '—' }}</td>
                                <td class="px-4 py-3 font-medium">{{ $log->newSharedAccount?->email ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-500">{{ $log->created_at->diffForHumans() }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if ($log->customer_notified)
                                        <span class="text-success-600 text-xs font-semibold">✓ Notified</span>
                                    @else
                                        <button
                                            type="button"
                                            wire:click="markNotified({{ $log->id }})"
                                            class="text-xs font-semibold text-primary-600 hover:underline"
                                        >
                                            Mark Notified
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</x-filament-panels::page>

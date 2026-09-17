<x-filament-panels::page>
    <a href="{{ $this->getAccountsUrl() }}" class="text-sm text-primary-600 hover:underline inline-flex items-center gap-1 mb-4">
        <x-heroicon-o-arrow-left class="w-4 h-4" /> Back to accounts
    </a>

    <div class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
        <h2 class="text-base font-semibold mb-4">Step 1 — How many accounts, and how long each one lasts</h2>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="text-sm font-medium block mb-1">How many accounts to add</label>
                <input type="number" min="1" max="200" wire:model="count" class="fi-input block w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-600" />
            </div>
            <div>
                <label class="text-sm font-medium block mb-1">Device slots per account</label>
                <input type="number" min="1" max="20" wire:model="slots_per_account" class="fi-input block w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-600" />
            </div>
            <div>
                <label class="text-sm font-medium block mb-1">Account valid for (days)</label>
                <input type="number" min="1" wire:model="duration_days" placeholder="Leave blank if it doesn't expire" class="fi-input block w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-600" />
                <p class="text-xs text-gray-500 mt-1">When this many days pass, these accounts will show up flagged as needing rotation if customers are still on them.</p>
            </div>
        </div>

        <div class="mt-4">
            <x-filament::button wire:click="generateTable">
                Generate Table
            </x-filament::button>
        </div>
    </div>

    @if ($tableGenerated)
        <div
            x-data="{
                onPaste(event) {
                    const text = (event.clipboardData || window.clipboardData).getData('text');
                    if (text && text.includes('\n')) {
                        event.preventDefault();
                        $wire.fillFromPaste(text);
                    }
                }
            }"
            class="fi-section rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 mt-6"
        >
            <h2 class="text-base font-semibold mb-2">Step 2 — Paste from Excel or type manually</h2>
            <p class="text-sm text-gray-500 mb-4">
                Copy two columns (Email, Password) from Excel — one account per row — then click into the box below and paste (Ctrl+V). The table fills automatically.
            </p>

            <textarea
                x-on:paste="onPaste($event)"
                placeholder="Paste Email + Password columns here (tab or comma separated)..."
                rows="3"
                class="fi-input block w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-600 mb-6"
            ></textarea>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b border-gray-200 dark:border-gray-700">
                            <th class="py-2 pr-2 w-10">#</th>
                            <th class="py-2 pr-2">Account Email</th>
                            <th class="py-2 pr-2">Account Password</th>
                            <th class="py-2 w-10"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rows as $i => $row)
                            <tr class="border-b border-gray-100 dark:border-gray-800" wire:key="row-{{ $i }}">
                                <td class="py-1 pr-2 text-gray-400">{{ $i + 1 }}</td>
                                <td class="py-1 pr-2">
                                    <input type="text" wire:model.defer="rows.{{ $i }}.email" class="fi-input block w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-600" />
                                </td>
                                <td class="py-1 pr-2">
                                    <input type="text" wire:model.defer="rows.{{ $i }}.password" class="fi-input block w-full rounded-lg border-gray-300 dark:bg-gray-800 dark:border-gray-600" />
                                </td>
                                <td class="py-1">
                                    <button type="button" wire:click="removeRow({{ $i }})" class="text-danger-500 hover:text-danger-700" title="Remove row">
                                        <x-heroicon-o-x-mark class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex gap-2">
                <x-filament::button color="gray" size="sm" wire:click="addRow">
                    + Add Row
                </x-filament::button>
                <x-filament::button color="gray" size="sm" wire:click="resetTable">
                    Cancel
                </x-filament::button>
                <x-filament::button wire:click="save">
                    Save All Accounts
                </x-filament::button>
            </div>
        </div>
    @endif
</x-filament-panels::page>

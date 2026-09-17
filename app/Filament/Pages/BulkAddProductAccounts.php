<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\SharedAccount;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class BulkAddProductAccounts extends Page
{
    protected static ?string $slug = 'accounts/{product:slug}/bulk-add';

    protected static string $view = 'filament.pages.bulk-add-product-accounts';

    protected static bool $shouldRegisterNavigation = false;

    public Product $product;

    public int $count = 5;

    public int $slots_per_account = 3;

    public ?int $duration_days = null;

    public array $rows = [];

    public bool $tableGenerated = false;

    public function mount(Product $product): void
    {
        $this->product = $product;
        $this->slots_per_account = $product->default_shared_slots ?? 3;
    }

    public static function getRelativeRouteName(): string
    {
        return 'bulk-add-product-accounts';
    }

    public function getTitle(): string
    {
        return "Bulk Add {$this->product->name} Accounts";
    }

    public function getAccountsUrl(): string
    {
        return Accounts::getUrl(['productSlug' => $this->product->slug]);
    }

    public function generateTable(): void
    {
        $this->validate([
            'count' => 'required|integer|min:1|max:200',
            'slots_per_account' => 'required|integer|min:1|max:20',
            'duration_days' => 'nullable|integer|min:1',
        ]);

        $this->rows = array_fill(0, $this->count, ['email' => '', 'password' => '']);
        $this->tableGenerated = true;
    }

    public function fillFromPaste(string $pasted): void
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($pasted));
        $rows = [];

        foreach ($lines as $line) {
            if (trim($line) === '') {
                continue;
            }

            $cols = array_values(array_filter(
                preg_split('/\t|,/', $line),
                fn ($col) => trim($col) !== ''
            ));

            $rows[] = [
                'email' => trim($cols[0] ?? ''),
                'password' => trim($cols[1] ?? ''),
            ];
        }

        if (empty($rows)) {
            return;
        }

        $this->count = count($rows);
        $this->rows = $rows;
        $this->tableGenerated = true;
    }

    public function addRow(): void
    {
        $this->rows[] = ['email' => '', 'password' => ''];
        $this->count = count($this->rows);
    }

    public function removeRow(int $index): void
    {
        unset($this->rows[$index]);
        $this->rows = array_values($this->rows);
        $this->count = count($this->rows);
    }

    public function resetTable(): void
    {
        $this->reset(['rows', 'tableGenerated']);
    }

    public function save(): void
    {
        $created = 0;
        $skipped = 0;

        foreach ($this->rows as $row) {
            $email = trim($row['email'] ?? '');
            $password = trim($row['password'] ?? '');

            if ($email === '' || $password === '') {
                $skipped++;

                continue;
            }

            SharedAccount::create([
                'product_id' => $this->product->id,
                'email' => $email,
                'password' => $password,
                'max_slots' => $this->slots_per_account,
                'purchased_at' => now(),
                'duration_days' => $this->duration_days,
                'is_active' => true,
            ]);

            $created++;
        }

        Notification::make()
            ->title("{$created} accounts added".($skipped ? ", {$skipped} empty rows skipped" : ''))
            ->success()
            ->send();

        $this->reset(['rows', 'tableGenerated']);
    }
}

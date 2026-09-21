<?php

namespace App\Filament\Imports;

use App\Models\MarketingContact;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class MarketingContactImporter extends Importer
{
    protected static ?string $model = MarketingContact::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')
                ->rules(['nullable', 'max:255']),
            ImportColumn::make('email')
                ->rules(['nullable', 'email', 'max:255', 'required_without:phone']),
            ImportColumn::make('phone')
                ->castStateUsing(fn (?string $state) => blank($state) ? null : self::normalizePhone($state))
                ->rules(['nullable', 'max:20', 'required_without:email']),
        ];
    }

    /**
     * Match existing contacts by phone first (the primary identity for the
     * imported number list), falling back to email, so re-importing the
     * same list updates rather than duplicates.
     */
    public function resolveRecord(): ?MarketingContact
    {
        if (filled($this->data['phone'] ?? null)) {
            return MarketingContact::firstOrNew(['phone' => $this->data['phone']]);
        }

        if (filled($this->data['email'] ?? null)) {
            return MarketingContact::firstOrNew(['email' => $this->data['email']]);
        }

        return new MarketingContact();
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your marketing contact import has completed and '.number_format($import->successful_rows).' '.str('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }

    protected static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        if (str_starts_with((string) $digits, '977')) {
            $digits = substr($digits, 3);
        }

        return $digits;
    }
}

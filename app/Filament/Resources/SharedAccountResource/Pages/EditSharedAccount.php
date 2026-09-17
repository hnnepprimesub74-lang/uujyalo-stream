<?php

namespace App\Filament\Resources\SharedAccountResource\Pages;

use App\Filament\Pages\Accounts;
use App\Filament\Resources\SharedAccountResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSharedAccount extends EditRecord
{
    protected static string $resource = SharedAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        $product = $this->record->product;

        return $product ? Accounts::getUrl(['productSlug' => $product->slug]) : $this->getResource()::getUrl('index');
    }
}

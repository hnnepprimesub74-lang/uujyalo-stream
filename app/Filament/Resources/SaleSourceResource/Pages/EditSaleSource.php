<?php

namespace App\Filament\Resources\SaleSourceResource\Pages;

use App\Filament\Resources\SaleSourceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSaleSource extends EditRecord
{
    protected static string $resource = SaleSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

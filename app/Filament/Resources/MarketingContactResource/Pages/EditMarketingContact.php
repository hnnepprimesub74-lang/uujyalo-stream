<?php

namespace App\Filament\Resources\MarketingContactResource\Pages;

use App\Filament\Resources\MarketingContactResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMarketingContact extends EditRecord
{
    protected static string $resource = MarketingContactResource::class;

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

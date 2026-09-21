<?php

namespace App\Filament\Resources\MarketingContactResource\Pages;

use App\Filament\Resources\MarketingContactResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarketingContacts extends ListRecords
{
    protected static string $resource = MarketingContactResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

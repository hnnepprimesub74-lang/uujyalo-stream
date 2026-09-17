<?php

namespace App\Filament\Resources\SaleSourceResource\Pages;

use App\Filament\Resources\SaleSourceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSaleSources extends ListRecords
{
    protected static string $resource = SaleSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

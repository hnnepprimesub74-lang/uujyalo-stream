<?php

namespace App\Filament\Resources\SharedAccountResource\Pages;

use App\Filament\Resources\SharedAccountResource;
use Filament\Resources\Pages\ListRecords;

class ListSharedAccounts extends ListRecords
{
    protected static string $resource = SharedAccountResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}

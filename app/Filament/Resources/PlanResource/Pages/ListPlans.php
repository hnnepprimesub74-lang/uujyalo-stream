<?php

namespace App\Filament\Resources\PlanResource\Pages;

use App\Filament\Resources\PlanResource;
use App\Filament\Pages\AddPlan;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPlans extends ListRecords
{
    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('addPlan')
                ->label('Add Plan')
                ->icon('heroicon-o-plus-circle')
                ->url(fn () => AddPlan::getUrl()),
        ];
    }
}

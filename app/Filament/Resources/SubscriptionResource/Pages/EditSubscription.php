<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Pages\Customers;
use App\Filament\Resources\SubscriptionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubscription extends EditRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        $product = $this->record->plan?->product;

        return $product ? Customers::getUrl(['productSlug' => $product->slug]) : $this->getResource()::getUrl('index');
    }
}

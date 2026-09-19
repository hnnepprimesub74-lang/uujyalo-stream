<?php

namespace App\Filament\Resources\SubscriptionResource\Pages;

use App\Filament\Pages\Customers;
use App\Filament\Resources\SubscriptionResource;
use Filament\Resources\Pages\ListRecords;

/**
 * The old standalone Orders list. Superseded by the Customers page's "All"
 * tab — this route stays registered only because Filament's EditRecord
 * (breadcrumbs, delete redirect, etc.) assumes an 'index' page exists. Anyone
 * who lands here is bounced straight to the real list.
 */
class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;

    public function mount(): void
    {
        $this->redirect(Customers::getUrl(['productSlug' => Customers::ALL_SLUG]));
    }
}

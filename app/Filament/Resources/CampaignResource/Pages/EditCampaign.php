<?php

namespace App\Filament\Resources\CampaignResource\Pages;

use App\Filament\Resources\CampaignResource;
use App\Models\Campaign;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCampaign extends EditRecord
{
    protected static string $resource = CampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['campaign_type'] = ($data['send_sms'] ?? false) ? 'sms' : 'email';

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['send_email'] = ($data['campaign_type'] ?? 'email') === 'email';
        $data['send_sms'] = ($data['campaign_type'] ?? 'email') === 'sms';
        unset($data['campaign_type']);

        $data['status'] = filled($data['scheduled_at'] ?? null) ? Campaign::STATUS_SCHEDULED : Campaign::STATUS_DRAFT;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

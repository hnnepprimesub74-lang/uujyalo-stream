<?php

namespace App\Filament\Resources\CampaignResource\Pages;

use App\Filament\Resources\CampaignResource;
use App\Models\Campaign;
use Filament\Resources\Pages\CreateRecord;

class CreateCampaign extends CreateRecord
{
    protected static string $resource = CampaignResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['send_email'] = ($data['campaign_type'] ?? 'email') === 'email';
        $data['send_sms'] = ($data['campaign_type'] ?? 'email') === 'sms';
        unset($data['campaign_type']);

        $data['created_by'] = auth()->id();
        $data['status'] = filled($data['scheduled_at'] ?? null) ? Campaign::STATUS_SCHEDULED : Campaign::STATUS_DRAFT;

        return $data;
    }
}

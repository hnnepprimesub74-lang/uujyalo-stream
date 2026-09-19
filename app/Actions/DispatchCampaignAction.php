<?php

namespace App\Actions;

use App\Jobs\FinalizeCampaignBatch;
use App\Jobs\SendCampaignChunk;
use App\Models\Campaign;
use Illuminate\Support\Facades\Bus;

class DispatchCampaignAction
{
    public function execute(Campaign $campaign): void
    {
        $userIds = $campaign->audienceQuery()->pluck('id')->all();

        $campaign->update([
            'status' => Campaign::STATUS_SENDING,
            'total_recipients' => count($userIds),
        ]);

        $jobs = collect($userIds)
            ->chunk(100)
            ->map(fn ($chunk) => new SendCampaignChunk($campaign, $chunk->values()->all()))
            ->all();

        $batch = Bus::batch($jobs)
            ->name("campaign-{$campaign->id}")
            ->finally(fn () => FinalizeCampaignBatch::dispatch($campaign))
            ->dispatch();

        $campaign->update(['batch_id' => $batch->id]);
    }
}

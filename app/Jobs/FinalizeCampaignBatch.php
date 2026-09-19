<?php

namespace App\Jobs;

use App\Models\Campaign;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Bus;

class FinalizeCampaignBatch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Campaign $campaign)
    {
    }

    public function handle(): void
    {
        $batch = $this->campaign->batch_id ? Bus::findBatch($this->campaign->batch_id) : null;

        $this->campaign->refresh();

        // $batch->hasFailures() reflects chunk-job-level crashes (e.g. a DB
        // outage), not per-recipient send failures — those are expected and
        // already tracked in failed_count/CampaignDelivery.
        $status = $batch?->hasFailures() && $this->campaign->sent_count === 0
            ? Campaign::STATUS_FAILED
            : Campaign::STATUS_SENT;

        $this->campaign->update([
            'status' => $status,
            'sent_at' => now(),
        ]);
    }
}

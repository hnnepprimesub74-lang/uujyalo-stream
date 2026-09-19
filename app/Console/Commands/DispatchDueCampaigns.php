<?php

namespace App\Console\Commands;

use App\Actions\DispatchCampaignAction;
use App\Models\Campaign;
use Illuminate\Console\Command;

class DispatchDueCampaigns extends Command
{
    protected $signature = 'campaigns:dispatch-due';

    protected $description = 'Dispatch scheduled marketing campaigns whose send time has arrived.';

    public function handle(DispatchCampaignAction $action): int
    {
        $due = Campaign::query()
            ->where('status', Campaign::STATUS_SCHEDULED)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now())
            ->get();

        foreach ($due as $campaign) {
            $action->execute($campaign);
            $this->info("Dispatched campaign #{$campaign->id}");
        }

        return self::SUCCESS;
    }
}

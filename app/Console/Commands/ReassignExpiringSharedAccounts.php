<?php

namespace App\Console\Commands;

use App\Services\AccountReassignmentService;
use Illuminate\Console\Command;

class ReassignExpiringSharedAccounts extends Command
{
    protected $signature = 'accounts:reassign-expiring';

    protected $description = 'Move customers off shared accounts that are expiring soon onto a healthy account with free slots, where one is available.';

    public function handle(AccountReassignmentService $service): int
    {
        $result = $service->run();

        $this->info("Reassigned: {$result['reassigned']}. Still waiting for stock: {$result['stuck']}.");

        return self::SUCCESS;
    }
}

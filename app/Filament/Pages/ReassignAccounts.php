<?php

namespace App\Filament\Pages;

use App\Models\AccountReassignmentLog;
use App\Models\Subscription;
use App\Services\AccountReassignmentService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ReassignAccounts extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $slug = 'reassign';

    protected static string $view = 'filament.pages.reassign-accounts';

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'Reassign';

    protected static ?int $navigationSort = 22;

    protected static ?string $title = 'Reassign Accounts';

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = app(AccountReassignmentService::class)->subscriptionsNeedingAttention()->count();

        return $count > 0 ? (string) $count : null;
    }

    public function mount(): void
    {
        // Re-check on every visit, so adding new stock resolves waiting
        // customers immediately instead of waiting for the next scheduled run.
        app(AccountReassignmentService::class)->run();
    }

    public function runNow(): void
    {
        $result = app(AccountReassignmentService::class)->run();

        Notification::make()
            ->title("Reassigned {$result['reassigned']}, still waiting for stock: {$result['stuck']}")
            ->success()
            ->send();

        $this->resetTable();
    }

    public function markNotified(int $logId): void
    {
        AccountReassignmentLog::where('id', $logId)->update(['customer_notified' => true]);
    }

    public function getRecentLogs()
    {
        return AccountReassignmentLog::with(['subscription.user', 'oldSharedAccount', 'newSharedAccount'])
            ->latest()
            ->limit(20)
            ->get();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Subscription::query()
                    ->where('status', Subscription::STATUS_ACTIVE)
                    ->where(function ($query) {
                        // Null expires_at means it's never been activated yet
                        // (approved while no stock was available) — still
                        // needs attention, not just accounts nearing expiry.
                        $query->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                    })
                    ->whereHas('plan.product', fn ($query) => $query->where('is_shared_account', true))
                    ->where(function ($query) {
                        $query->whereNull('shared_account_id')
                            ->orWhereHas('sharedAccount', function ($query) {
                                $query->whereNotNull('purchased_at')
                                    ->whereNotNull('duration_days')
                                    ->whereRaw(
                                        'DATEDIFF(DATE_ADD(purchased_at, INTERVAL duration_days DAY), CURDATE()) <= ?',
                                        [AccountReassignmentService::WARNING_DAYS]
                                    );
                            });
                    })
            )
            ->heading('Waiting for a New Account')
            ->description('These customers have no account yet, or their current account is expiring or expired. They\'ll be moved automatically once you add stock with free slots.')
            ->columns([
                TextColumn::make('user.name')->label('Customer')->searchable(),
                TextColumn::make('user.phone')->label('Phone'),
                TextColumn::make('plan.product.name')->label('Product')->badge(),
                TextColumn::make('sharedAccount.email')->label('Current Account')->copyable()->placeholder('Awaiting stock'),
                TextColumn::make('slots_used')->label('Slots Needed')->alignCenter(),
                TextColumn::make('account_expires_in')
                    ->label('Account Expires In')
                    ->getStateUsing(function (Subscription $record) {
                        if (! $record->sharedAccount) {
                            return 'No account yet';
                        }

                        $days = $record->sharedAccount->daysUntilAccountExpiry();

                        return $days > 0 ? "{$days} day(s)" : 'Expired';
                    })
                    ->color('danger')
                    ->weight('bold'),
                TextColumn::make('expires_at')->label('Customer Expires At')->date('M j, Y'),
            ])
            ->emptyStateHeading('Nobody is waiting')
            ->emptyStateDescription('Every customer on an expiring account has already been moved to a healthy one.');
    }
}

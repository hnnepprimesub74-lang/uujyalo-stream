<?php

namespace App\Filament\Pages;

use App\Mail\AccountReadyMail;
use App\Models\Plan;
use App\Models\Product;
use App\Models\SaleSource;
use App\Models\SharedAccount;
use App\Models\Subscription;
use App\Models\User;
use App\Services\RechargeService;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;

class AddOrder extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';

    protected static ?string $navigationLabel = 'Add Order';

    protected static ?string $navigationGroup = 'Subscriptions';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.pages.add-order';

    public ?array $data = [];

    #[Url]
    public ?int $subscriptionId = null;

    public function mount(): void
    {
        $this->form->fill();

        $subscription = $this->subscriptionId
            ? Subscription::with(['plan.product', 'user'])->find($this->subscriptionId)
            : null;

        if (! $subscription) {
            return;
        }

        $email = $subscription->account_email ?? $subscription->user->email;

        $saleSourceId = $subscription->sale_source_id
            ?? SaleSource::firstOrCreate(
                ['name' => 'Website'],
                ['details' => 'Orders placed directly on the website checkout.']
            )->id;

        $this->form->fill([
            'order_product_id' => $subscription->plan->product_id,
            'order_type' => $subscription->plan->type ?: '',
            'plan_id' => $subscription->plan_id,
            'sale_source_id' => $saleSourceId,
            'amount' => $subscription->amount,
            'credit_due' => $subscription->credit_due,
            'name' => $subscription->user->name,
            'phone' => $subscription->user->phone,
            'email' => str_ends_with((string) $email, '@no-email.local') ? null : $email,
            'password' => $subscription->account_password,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Order')
                ->columns(2)
                ->schema([
                    Select::make('order_product_id')
                        ->label('Product')
                        ->options(fn () => Product::where('is_active', true)
                            ->whereHas('plans', fn ($q) => $q->where('is_active', true))
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->live()
                        ->afterStateUpdated(function ($set) {
                            $set('order_type', null);
                            $set('plan_id', null);
                        })
                        ->required(),
                    Select::make('order_type')
                        ->label('Type')
                        ->options(function ($get) {
                            $productId = $get('order_product_id');

                            if (! $productId) {
                                return [];
                            }

                            return Plan::where('product_id', $productId)
                                ->where('is_active', true)
                                ->get()
                                ->pluck('type')
                                ->map(fn ($type) => $type ?: '')
                                ->unique()
                                ->mapWithKeys(fn ($type) => [$type => $type ?: 'General']);
                        })
                        ->visible(fn ($get) => filled($get('order_product_id')))
                        ->required(fn ($get) => filled($get('order_product_id')))
                        ->live()
                        ->afterStateUpdated(fn ($set) => $set('plan_id', null)),
                    Select::make('plan_id')
                        ->label('Duration')
                        ->options(function ($get) {
                            $productId = $get('order_product_id');
                            $type = $get('order_type');

                            if (! $productId || $type === null) {
                                return [];
                            }

                            return Plan::where('product_id', $productId)
                                ->where('is_active', true)
                                ->where('type', $type === '' ? null : $type)
                                ->get()
                                ->mapWithKeys(fn (Plan $plan) => [$plan->id => "{$plan->name} — NPR ".number_format($plan->price, 0)]);
                        })
                        ->visible(fn ($get) => $get('order_type') !== null)
                        ->required(fn ($get) => $get('order_type') !== null)
                        ->live(),
                    Select::make('shared_account_id')
                        ->label('Shared Account')
                        ->helperText(function ($get) {
                            $plan = Plan::find($get('plan_id'));

                            return $plan?->hasFixedDeviceSlots()
                                ? "This plan needs {$plan->device_slots} free device slot(s) on the account — only accounts with enough room are listed."
                                : 'Pick which pooled account to assign this customer to. Only accounts with a free slot are listed.';
                        })
                        ->options(function ($get) {
                            $plan = Plan::find($get('plan_id'));

                            if (! $plan || ! $plan->product?->is_shared_account) {
                                return [];
                            }

                            $required = $plan->device_slots ?? 1;

                            return SharedAccount::where('product_id', $plan->product_id)
                                ->get()
                                ->filter(fn (SharedAccount $account) => $account->availableSlots() >= $required)
                                ->mapWithKeys(fn (SharedAccount $account) => [
                                    $account->id => "{$account->email} ({$account->usedSlots()}/{$account->max_slots} used)",
                                ]);
                        })
                        ->visible(fn ($get) => Plan::find($get('plan_id'))?->product?->is_shared_account)
                        ->required(fn ($get) => Plan::find($get('plan_id'))?->product?->is_shared_account)
                        ->live(),
                    Select::make('slots_used')
                        ->label('Devices')
                        ->helperText('How many of this account\'s device slots does this customer occupy?')
                        ->options(function ($get) {
                            $account = SharedAccount::find($get('shared_account_id'));

                            if (! $account) {
                                return [];
                            }

                            $max = min($account->max_slots, $account->availableSlots());

                            return collect(range(1, max(1, $max)))->mapWithKeys(fn ($n) => [$n => "{$n} device".($n > 1 ? 's' : '')]);
                        })
                        ->default(1)
                        ->visible(fn ($get) => Plan::find($get('plan_id'))?->product?->is_shared_account
                            && ! Plan::find($get('plan_id'))?->hasFixedDeviceSlots())
                        ->required(fn ($get) => Plan::find($get('plan_id'))?->product?->is_shared_account
                            && ! Plan::find($get('plan_id'))?->hasFixedDeviceSlots()),
                    Select::make('sale_source_id')
                        ->label('Source of Sale')
                        ->options(fn () => SaleSource::query()->where('is_active', true)->pluck('name', 'id'))
                        ->searchable(),
                    TextInput::make('amount')
                        ->label('Amount Paid')
                        ->numeric()
                        ->prefix('NPR')
                        ->required(),
                    TextInput::make('credit_due')
                        ->label('Credit (Amount Still Due)')
                        ->helperText('If the customer still owes part of the bill, enter it here to follow up later.')
                        ->numeric()
                        ->prefix('NPR')
                        ->default(0),
                    TextInput::make('recharge_card_amount')
                        ->label('Recharge Card ($)')
                        ->helperText('If you\'re topping up the account now, enter the card/amount added.')
                        ->numeric()
                        ->prefix('$')
                        ->visible(fn ($get) => ! Plan::find($get('plan_id'))?->product?->is_shared_account),
                ]),
            Section::make('Customer')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->label('Phone Number')
                        ->required()
                        ->maxLength(20),
                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->maxLength(255)
                        ->required(fn ($get) => ! Plan::find($get('plan_id'))?->product?->is_shared_account)
                        ->helperText(fn ($get) => Plan::find($get('plan_id'))?->product?->is_shared_account
                            ? 'Optional for shared-account orders — leave blank if the customer didn\'t give one.'
                            : 'Used for the customer account and, unless overridden later, as the account login.'),
                    TextInput::make('password')
                        ->label('Account Password')
                        ->helperText('The account password given to this customer.')
                        ->visible(fn ($get) => ! Plan::find($get('plan_id'))?->product?->is_shared_account),
                ]),
        ])->statePath('data');
    }

    public function create(RechargeService $rechargeService): void
    {
        $data = $this->form->getState();

        $email = trim($data['email'] ?? '');

        $existingSubscription = $this->subscriptionId
            ? Subscription::find($this->subscriptionId)
            : null;

        $user = $existingSubscription?->user ?? User::where('phone', $data['phone'])->first();

        if ($user) {
            $updates = ['name' => $data['name']];

            if ($email !== '' && $user->email !== $email) {
                $updates['email'] = $email;
            }

            $user->update($updates);
        } else {
            if ($email === '') {
                $email = preg_replace('/\D/', '', $data['phone']).'@no-email.local';
            }

            $user = User::create([
                'email' => $email,
                'name' => $data['name'],
                'phone' => $data['phone'],
                'password' => Hash::make(Str::random(16)),
                'role' => User::ROLE_CUSTOMER,
            ]);
        }

        $plan = Plan::findOrFail($data['plan_id']);
        $accountWasNotReadyYet = $existingSubscription !== null && $existingSubscription->starts_at === null;
        $startsAt = $existingSubscription?->starts_at ?? now();
        $totalDays = ($existingSubscription && $existingSubscription->plan_id === $plan->id && $existingSubscription->total_days)
            ? $existingSubscription->total_days
            : $plan->duration_days;
        $isSharedAccount = (bool) $plan->product?->is_shared_account;

        $slotsUsed = $plan->device_slots ?? (int) ($data['slots_used'] ?? 1);
        $accountEmail = $email !== '' ? $email : $user->email;

        if ($isSharedAccount) {
            if (empty($data['shared_account_id'])) {
                Notification::make()
                    ->title('Please choose a CapCut account for this order.')
                    ->danger()
                    ->send();

                return;
            }

            $account = SharedAccount::find($data['shared_account_id']);

            if (! $account || $account->availableSlots() < $slotsUsed) {
                Notification::make()
                    ->title('That account no longer has enough free device slots. Please pick another.')
                    ->danger()
                    ->send();

                return;
            }
        }

        $subscriptionData = [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'sale_source_id' => $data['sale_source_id'] ?? null,
            'shared_account_id' => $isSharedAccount ? $data['shared_account_id'] : null,
            'slots_used' => $isSharedAccount ? $slotsUsed : 1,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => $startsAt,
            'expires_at' => $startsAt->copy()->addDays($totalDays),
            'amount' => $data['amount'],
            'credit_due' => $data['credit_due'] ?? 0,
            'account_email' => $isSharedAccount ? null : $accountEmail,
            'account_password' => $isSharedAccount ? null : ($data['password'] ?? null),
            'total_days' => $totalDays,
            'days_recharged' => $isSharedAccount ? $totalDays : 0,
            'next_recharge_date' => $isSharedAccount ? null : $startsAt->toDateString(),
        ];

        if ($existingSubscription) {
            $existingSubscription->update($subscriptionData);
            $subscription = $existingSubscription;
        } else {
            $subscription = Subscription::create($subscriptionData);
        }

        if (! empty($data['recharge_card_amount'])) {
            $rechargeService->applyRecharge($subscription, (float) $data['recharge_card_amount']);
        }

        if (($accountWasNotReadyYet || ! $existingSubscription) && ! str_ends_with((string) $user->email, '@no-email.local')) {
            Mail::to($user->email)->send(new AccountReadyMail($subscription));
        }

        Notification::make()
            ->title($existingSubscription ? 'Account created for this order' : 'Order created')
            ->success()
            ->send();

        $this->form->fill();

        $product = $plan->product;

        $this->redirect($product ? Customers::getUrl(['productSlug' => $product->slug]) : Customers::getUrl());
    }

    /**
     * For private-account orders where payment has been taken but the actual
     * account can't be created yet — saves the order without starting the
     * countdown, so it shows up on the Pending Orders page for follow-up.
     */
    public function createPending(): void
    {
        $data = $this->form->getState();

        $plan = Plan::findOrFail($data['plan_id']);

        if ($plan->product?->is_shared_account) {
            Notification::make()
                ->title('Shared-account orders can\'t be left pending')
                ->body('Pick a shared account above and use "Create Order" instead — slots are assigned immediately.')
                ->danger()
                ->send();

            return;
        }

        $email = trim($data['email'] ?? '');

        $existingSubscription = $this->subscriptionId
            ? Subscription::find($this->subscriptionId)
            : null;

        $user = $existingSubscription?->user ?? User::where('phone', $data['phone'])->first();

        if ($user) {
            $updates = ['name' => $data['name']];

            if ($email !== '' && $user->email !== $email) {
                $updates['email'] = $email;
            }

            $user->update($updates);
        } else {
            if ($email === '') {
                $email = preg_replace('/\D/', '', $data['phone']).'@no-email.local';
            }

            $user = User::create([
                'email' => $email,
                'name' => $data['name'],
                'phone' => $data['phone'],
                'password' => Hash::make(Str::random(16)),
                'role' => User::ROLE_CUSTOMER,
            ]);
        }

        $accountEmail = $email !== '' ? $email : $user->email;

        $subscriptionData = [
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'sale_source_id' => $data['sale_source_id'] ?? null,
            'shared_account_id' => null,
            'slots_used' => 1,
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => null,
            'expires_at' => null,
            'amount' => $data['amount'],
            'credit_due' => $data['credit_due'] ?? 0,
            'account_email' => $accountEmail,
            'account_password' => $data['password'] ?? null,
            'total_days' => $plan->duration_days,
            'days_recharged' => 0,
            'next_recharge_date' => null,
        ];

        if ($existingSubscription) {
            $existingSubscription->update($subscriptionData);
        } else {
            Subscription::create($subscriptionData);
        }

        Notification::make()
            ->title('Order saved — pending account creation')
            ->body('It\'ll stay on the Pending Orders page until you come back to finish creating the account.')
            ->success()
            ->send();

        $this->form->fill();

        $this->redirect(PendingOrders::getUrl());
    }
}

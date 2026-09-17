<?php

namespace App\Filament\Pages;

use App\Filament\Resources\PlanResource;
use App\Models\Plan;
use App\Models\Product;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

class AddPlan extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';

    protected static ?string $navigationLabel = 'Add Plan';

    protected static ?string $title = 'Add Plan';

    protected static ?string $navigationGroup = 'Catalog Setup';

    protected static ?int $navigationSort = 3;

    protected static string $view = 'filament.pages.add-plan';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'tiers' => [[]],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('product_id')
                ->label('Product')
                ->options(fn () => Product::pluck('name', 'id'))
                ->required()
                ->searchable()
                ->preload()
                ->live(),
            TextInput::make('type')
                ->label('Type')
                ->helperText('The tier name, e.g. Mobile, Basic, Standard, Premium, Private, Shared')
                ->required()
                ->maxLength(255),
            Textarea::make('description')
                ->columnSpanFull(),
            TextInput::make('monthly_cost')
                ->label('Our Cost (per month)')
                ->helperText(fn ($get) => Product::find($get('product_id'))?->is_shared_account
                    ? 'Optional for shared-account products — the account\'s own cost is tracked on the CapCut Accounts page instead.'
                    : 'What this account tier costs us per month, e.g. 3.99 — shared across all durations below')
                ->required(fn ($get) => ! Product::find($get('product_id'))?->is_shared_account)
                ->numeric()
                ->prefix('$'),
            TextInput::make('device_slots')
                ->label('Device Slots (min)')
                ->helperText('Leave blank to let the admin pick devices per order (e.g. "Shared" — 1 or 2). Set a number to fix it for this whole type (e.g. "Private" — always 3), or the low end of a range.')
                ->numeric()
                ->minValue(1)
                ->visible(fn ($get) => Product::find($get('product_id'))?->is_shared_account),
            TextInput::make('device_slots_max')
                ->label('Device Slots (max)')
                ->helperText('Optional — only set this if the device count is a range (e.g. 3–4 devices).')
                ->numeric()
                ->minValue(1)
                ->visible(fn ($get) => Product::find($get('product_id'))?->is_shared_account),
            TextInput::make('device_label')
                ->label('Device Limit Wording')
                ->helperText('What the number means, e.g. "Device Login" or "Screen at a Time". Defaults to "Device Login".')
                ->maxLength(50),
            TextInput::make('quality')
                ->label('Screen Quality')
                ->helperText('Optional, e.g. HD, Full HD, 4K — shared across all durations for this tier.')
                ->maxLength(50),
            TextInput::make('supported_devices')
                ->label('Supported Devices')
                ->helperText('Optional, e.g. "Phone, Tablet" or "TV, Mobile, Laptop" — shared across all durations for this tier.')
                ->maxLength(100),
            Repeater::make('tiers')
                ->label('Durations & Pricing')
                ->schema([
                    TextInput::make('duration_days')
                        ->label('Duration (days)')
                        ->helperText('e.g. 30, 60, 115, 150, 230, 345 — any number of days')
                        ->numeric()
                        ->minValue(1)
                        ->required(),
                    TextInput::make('price')
                        ->label('Customer Price')
                        ->required()
                        ->numeric()
                        ->prefix('NPR'),
                ])
                ->columns(2)
                ->addActionLabel('Add another duration')
                ->defaultItems(1)
                ->columnSpanFull(),
        ])->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();

        $usedDurations = [];

        foreach ($data['tiers'] as $tier) {
            if (in_array($tier['duration_days'], $usedDurations)) {
                Notification::make()
                    ->title('Each duration can only be used once per plan.')
                    ->danger()
                    ->send();

                return;
            }

            $usedDurations[] = $tier['duration_days'];
        }

        $product = Product::find($data['product_id']);
        $created = 0;
        $duplicates = [];

        foreach ($data['tiers'] as $index => $tier) {
            $name = "{$tier['duration_days']} Days";
            $displayName = "{$data['type']} - {$name}";
            $slug = Str::slug("{$product?->slug}-{$data['type']}-{$name}");

            if (Plan::where('slug', $slug)->exists()) {
                $duplicates[] = $displayName;

                continue;
            }

            Plan::create([
                'product_id' => $data['product_id'],
                'type' => $data['type'],
                'name' => $name,
                'slug' => $slug,
                'description' => $data['description'] ?? null,
                'duration_days' => $tier['duration_days'],
                'device_slots' => $data['device_slots'] ?? null,
                'device_slots_max' => $data['device_slots_max'] ?? null,
                'device_label' => $data['device_label'] ?? null,
                'quality' => $data['quality'] ?? null,
                'supported_devices' => $data['supported_devices'] ?? null,
                'price' => $tier['price'],
                'monthly_cost' => $data['monthly_cost'],
                'sort_order' => $index,
                'is_active' => true,
            ]);

            $created++;
        }

        if ($created > 0) {
            Notification::make()
                ->title("{$created} plan".($created === 1 ? '' : 's')." created")
                ->success()
                ->send();
        }

        if (! empty($duplicates)) {
            Notification::make()
                ->title('Already exists, skipped: '.implode(', ', $duplicates))
                ->warning()
                ->send();
        }

        if ($created === 0) {
            return;
        }

        redirect(PlanResource::getUrl('index'));
    }
}

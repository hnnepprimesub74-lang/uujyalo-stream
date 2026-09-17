<?php

namespace App\Filament\Pages;

use App\Imports\OrdersImport;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\HtmlString;
use Maatwebsite\Excel\Facades\Excel;

class ImportOrders extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';

    protected static ?string $navigationLabel = 'Import Orders';

    protected static ?string $navigationGroup = 'Subscriptions';

    protected static ?int $navigationSort = 14;

    protected static string $view = 'filament.pages.import-orders';

    public ?array $data = [];

    public ?int $lastImportedCount = null;

    /** @var array<int, array{row: int, reason: string}> */
    public array $lastSkipped = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            Placeholder::make('instructions')
                ->label('Expected Columns')
                ->content(new HtmlString(
                    '<div class="text-sm text-gray-600">Upload a .xlsx, .xls, or .csv file with a header row containing: '
                    .'<code>Name</code>, <code>Email</code>, <code>Number</code>, <code>Password</code>, '
                    .'<code>Product</code>, <code>Plan</code>, <code>Subscription Date</code>, <code>Expiry Date</code>, '
                    .'<code>Total Days</code>, <code>Status</code> (optional), <code>Source</code> (optional, e.g. WhatsApp 1, Instagram). '
                    .'A customer account is created automatically if the email is new, and Product/Plan/Source entries are auto-created if they don\'t exist yet.</div>'
                )),
            FileUpload::make('file')
                ->label('Orders File')
                ->required()
                ->acceptedFileTypes([
                    'text/csv', 'application/csv', 'application/vnd.ms-excel',
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ])
                ->disk('local')
                ->directory('imports')
                ->visibility('private'),
        ])->statePath('data');
    }

    public function import(): void
    {
        $data = $this->form->getState();

        $path = storage_path('app/'.$data['file']);

        $import = new OrdersImport;
        Excel::import($import, $path);

        $this->lastImportedCount = $import->imported;
        $this->lastSkipped = $import->skipped;

        Notification::make()
            ->title("Imported {$import->imported} orders".(count($import->skipped) ? ', '.count($import->skipped).' skipped' : ''))
            ->success()
            ->send();

        $this->form->fill();
    }
}

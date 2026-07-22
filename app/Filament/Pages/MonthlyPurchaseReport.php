<?php

// namespace App\Filament\Pages;

// use App\Models\Purchase;
// use Filament\Pages\Page;
// use Filament\Forms\Components\DatePicker;
// use Filament\Forms\Components\Section;
// use Filament\Forms\Form;
// use Illuminate\Support\Carbon;
// use Filament\Forms;
// use Illuminate\Support\Facades\Auth;

// class MonthlyPurchaseReport extends Page
// {
//     protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
//     protected static ?string $navigationLabel = 'Laporan Pemasukan';
//     protected static ?string $navigationGroup = 'Laporan';
//     protected static ?int $navigationSort = 2;
//     protected static ?string $title = 'Laporan Pemasukan';

//     public static function getNavigationGroup(): ?string
//     {
//         // Group under 'Laporan' for Bendahara and other panels; hide group for SPV
//         $panelId = \Filament\Facades\Filament::getCurrentPanel()?->getId();
//         return $panelId === 'spv' ? null : 'Laporan';
//     }

//     protected static string $view = 'filament.pages.monthly-purchase-report';

//     public ?array $data = [];
//     public $startDate;
//     public $endDate;

//     public function mount()
//     {
//         $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
//         $this->endDate = Carbon::now()->endOfMonth()->format('Y-m-d');
//     }

//     public function form(Form $form): Form
//     {
//         return $form
//             ->schema([
//                 Section::make('Filter Laporan')
//                     ->schema([
//                         DatePicker::make('startDate')
//                             ->label('Tanggal Mulai')
//                             ->required()
//                             ->default(now()->startOfMonth()),
//                         DatePicker::make('endDate')
//                             ->label('Tanggal Selesai')
//                             ->required()
//                             ->default(now()->endOfMonth()),
//                     ])
//                     ->columns(2),
//             ]);
//     }

//     protected function getFormActions(): array
//     {
//         return [
//             Forms\Components\Actions\Action::make('generateReport')
//                 ->label('Generate Laporan')
//                 ->action('generateReport'),
//             Forms\Components\Actions\Action::make('exportPdf')
//                 ->label('Export PDF')
//                 ->action('exportPdf')
//                 ->disabled(fn() => empty($this->data)),
//             Forms\Components\Actions\Action::make('exportExcel')
//                 ->label('Export Excel')
//                 ->action('exportExcel')
//                 ->disabled(fn() => empty($this->data)),
//         ];
//     }

//     public function generateReport()
//     {
//         $this->validate([
//             'startDate' => 'required|date',
//             'endDate' => 'required|date|after_or_equal:startDate',
//         ]);

//         $this->data = Purchase::query()
//             ->with(['supplier', 'details.product'])
//             ->whereBetween('purchase_date', [$this->startDate, $this->endDate])
//             ->where('cooperation_id', Auth::user()->cooperation_id)
//             ->get()
//             ->map(function ($purchase) {
//                 return [
//                     'invoice_number' => $purchase->invoice_number,
//                     'purchase_date' => $purchase->purchase_date,
//                     'supplier' => $purchase->supplier->name,
//                     'total_items' => $purchase->details->sum('quantity'),
//                     'total_amount' => $purchase->total_amount,
//                     'details' => $purchase->details->map(fn($detail) => [
//                         'product' => $detail->product->name,
//                         'quantity' => $detail->quantity,
//                         'price' => $detail->price,
//                         'subtotal' => $detail->subtotal,
//                     ])->toArray(),
//                 ];
//             })
//             ->toArray();
//     }

//     public function exportPdf()
//     {
//         // Implementation for PDF export
//     }

//     public function exportExcel()
//     {
//         // Implementation for Excel export
//     }
// } 



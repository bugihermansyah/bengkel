<?php

namespace App\Filament\Pages\Report;

use App\Filament\Exports\TransactionExporter;
use App\Models\Transaction;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;
use BackedEnum;
use Filament\Forms\Components\DatePicker;

class LaporanPendapatan extends Page implements HasTable
{
    use InteractsWithTable;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationLabel = 'Laporan Transaksi';
    protected static ?string $title = 'Laporan Transaksi';
    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected string $view = 'filament.pages.report.laporan-pendapatan';

    public function table(Table $table): Table
    {
        return $table
            ->query(Transaction::query())
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('No. Invoice'),
                TextColumn::make('customer_name')
                    ->label('Nama Customer'),
                TextColumn::make('discount_amount')
                    ->label('Diskon')
                    ->money('IDR', decimalPlaces: 0),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->summarize(Sum::make()
                        ->label('Total')
                        ->money('IDR', decimalPlaces: 0))
                    ->money('IDR', decimalPlaces: 0),
                TextColumn::make('created_at')
                    ->label('Tanggal'),
            ])
            ->filters([
                SelectFilter::make('membership_status')
                    ->label('Status Member')
                    ->options([
                        'member' => 'Member',
                        'non_member' => 'Non-Member',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['value'] === 'member',
                                fn(Builder $query) => $query->whereNotNull('vehicle_id'),
                            )
                            ->when(
                                $data['value'] === 'non_member',
                                fn(Builder $query) => $query->whereNull('vehicle_id'),
                            );
                    }),
                SelectFilter::make('payment_method')
                    ->label('Jenis Pembayaran')
                    ->options([
                        'cash' => 'Cash',
                        'transfer' => 'Transfer',
                        'qris' => 'Qris',
                    ]),
                SelectFilter::make('user_id')
                    ->label('Kasir')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('created_from')
                    ->schema([
                        DatePicker::make('created_from')
                            ->label('Dari')
                            ->default(now()->toDateString()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            );
                    }),
                Filter::make('created_until')
                    ->schema([
                        DatePicker::make('created_until')
                            ->label('Sampai')
                            ->default(now()->toDateString()),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ], layout: FiltersLayout::AboveContent)
            ->recordActions([
                // ...
            ])
            ->toolbarActions([
                ExportAction::make()
                    ->label('Export')
                    ->icon('heroicon-o-arrow-up-on-square')
                    ->exporter(TransactionExporter::class)
                    ->columnMapping(false)
                    ->formats([
                        ExportFormat::Xlsx,
                        ExportFormat::Csv,
                    ]),
            ]);
    }
}
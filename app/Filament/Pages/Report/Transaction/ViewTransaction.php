<?php

namespace App\Filament\Pages\Report\Transaction;

use App\Models\Transaction;
use Filament\Infolists\Components\TextEntry;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Http\Request;

class ViewTransaction extends Page implements HasSchemas
{
    use InteractsWithSchemas;

    protected string $view = 'filament.pages.report.transaction.view-transaction';
    protected static ?string $title = 'Detail Transaksi';

    public Transaction $record;

    public function mount(Request $request): void
    {
        $recordId = $request->query('record');

        if (!$recordId) {
            abort(404, 'ID Transaksi tidak ditemukan.');
        }
        // Mencari data transaksi berdasarkan ID yang dikirim lewat URL
        $this->record = Transaction::with(['items', 'user'])->findOrFail($recordId);
    }

    public function transactionInfolist(Schema $schema): Schema
    {
        return $schema
            ->record($this->record)
            ->components([
                Section::make()
                    ->schema([
                        Grid::make(2) // Membuat 2 kolom
                            ->schema([
                                TextEntry::make('invoice_number')
                                    ->label('No. Invoice')
                                    ->weight('bold')
                                    ->color('primary'),
                                TextEntry::make('created_at')
                                    ->label('Waktu Transaksi')
                                    ->dateTime('d M Y H:i'),
                                TextEntry::make('customer_name')
                                    ->label('Nama Pelanggan'),
                                TextEntry::make('vehicle.plate_number')
                                    ->label('Plat Nomor')
                                    ->placeholder('Non-Member'),
                                TextEntry::make('user.name') // Kasir pindah ke sini
                                    ->label('Kasir/Admin')
                                    ->icon('heroicon-m-user'),
                                TextEntry::make('payment_method') // Metode pindah ke sini
                                    ->label('Metode Pembayaran')
                                    ->badge()
                                    ->color('success')
                                    ->formatStateUsing(fn(string $state): string => strtoupper($state)),
                            ]),
                    ])
            ]);
    }
}

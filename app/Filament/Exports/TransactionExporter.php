<?php

namespace App\Filament\Exports;

use App\Models\Transaction;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Number;

class TransactionExporter extends Exporter
{
    protected static ?string $model = Transaction::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('vehicle_id'),
            ExportColumn::make('invoice_number'),
            ExportColumn::make('customer_name'),
            ExportColumn::make('discount_amount'),
            ExportColumn::make('total_amount'),
            ExportColumn::make('payment_received'),
            ExportColumn::make('payment_method'),
            ExportColumn::make('status'),
            ExportColumn::make('user.name'),
            ExportColumn::make('created_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor data transaksi telah selesai dan ' . Number::format($export->successful_rows) . ' ' . str('row')->plural($export->successful_rows) . ' berhasil di ekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' gagal di ekspor.';
        }

        return $body;
    }
}

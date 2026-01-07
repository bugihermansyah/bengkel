<?php

namespace App\Filament\Resources\PurchaseOrders\Tables;

use DB;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PurchaseOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable(),
                TextColumn::make('po_number')
                    ->searchable(),
                TextColumn::make('supplier.name')
                    ->searchable(),
                TextColumn::make('order_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('complete_order')
                    ->label('Terima Barang')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn($record) => $record->status === 'draft')
                    ->action(function ($record) {
                        DB::transaction(function () use ($record) {
                            foreach ($record->items as $item) {
                                $product = $item->product;
                                $oldStock = $product->stock;

                                // 1. Update Stok Produk
                                $product->increment('stock', $item->quantity);

                                // 2. Update Harga Beli Terakhir di Master
                                $product->update(['purchase_price' => $item->unit_price]);

                                // 3. Catat di Stock Log
                                $product->stockLogs()->create([
                                    'id' => (string) str()->ulid(),
                                    'reference_id' => $record->id,
                                    'type' => 'in',
                                    'quantity' => $item->quantity,
                                    'stock_before' => $oldStock,
                                    'stock_after' => $oldStock + $item->quantity,
                                    'note' => "Masuk dari PO #{$record->po_number}",
                                ]);
                            }

                            $record->update(['status' => 'completed']);
                        });

                        Notification::make()->title('Stok berhasil ditambahkan!')->success()->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

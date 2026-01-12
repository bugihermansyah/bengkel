<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\StockLog;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Foto')
                    ->disk('public')
                    ->visibility('public'),
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->searchable(),
                TextColumn::make('rack.name')
                    ->label('Rak')
                    ->searchable(),
                TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable(),
                TextColumn::make('purchase_price')
                    ->label('Harga beli')
                    ->money('idr', decimalPlaces: 0)
                    ->sortable(),
                TextColumn::make('selling_price')
                    ->label('Harga jual')
                    ->money('idr', decimalPlaces: 0)
                    ->sortable(),
                TextColumn::make('barcode')
                    ->label('Barcode')
                    ->searchable(),
                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),
                IconColumn::make('status')
                    ->boolean(),
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
                ViewAction::make()
                    ->icon(Heroicon::OutlinedEye)
                    ->color('blue')
                    ->disableLabel(),
                EditAction::make()
                    ->disableLabel(),
                Action::make('adjust_stock')
                    ->disableLabel()
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('warning')
                    ->form([
                        TextInput::make('stock_system')
                            ->label('Stok Sistem')
                            ->default(fn($record) => $record->stock)
                            ->disabled(),
                        TextInput::make('stock_physical')
                            ->label('Stok Fisik Saat Ini')
                            ->numeric()
                            ->required(),
                        Select::make('reason')
                            ->options([
                                'Barang rusak' => 'Barang Rusak',
                                'Barang hilang' => 'Barang Hilang',
                                'Salah input sebelumnya' => 'Salah Input Sebelumnya',
                                'Opname rutin' => 'Opname Rutin',
                            ])->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $stockBefore = $record->stock;
                        $stockPhysical = (int) $data['stock_physical'];
                        $diff = $stockPhysical - $stockBefore;

                        // Update Stok Produk
                        $record->update(['stock' => $stockPhysical]);

                        // Catat Log
                        StockLog::create([
                            'product_id' => $record->id,
                            'reference_id' => 1,
                            'type' => 'adjustment',
                            'quantity' => $diff,
                            'stock_before' => $stockBefore,
                            'stock_after' => $stockPhysical,
                            'note' => "Opname: " . $data['reason'],
                        ]);
                    })
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}

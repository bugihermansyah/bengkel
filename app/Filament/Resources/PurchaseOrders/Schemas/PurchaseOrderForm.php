<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use App\Models\Product;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PurchaseOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make()
                    ->schema([
                        Section::make('Informasi Utama')
                            ->columnSpan(1)
                            ->schema([
                                TextInput::make('po_number')
                                    ->label('No. PO')
                                    ->default('PO-' . now()->format('Ymd-His'))
                                    ->required()
                                    ->readonly(),
                                Select::make('supplier_id')
                                    ->label('Supplier')
                                    ->relationship('supplier', 'name')
                                    ->required(),
                                DatePicker::make('order_date')
                                    ->label('Tanggal PO')
                                    ->default(now())
                                    ->required(),
                                Placeholder::make('total_amount_display')
                                    ->label('Total Pembelian')
                                    ->content(function ($get) {
                                        $items = $get('items') ?? [];
                                        $total = 0;
                                        foreach ($items as $item) {
                                            $total += (float) ($item['subtotal'] ?? 0);
                                        }
                                        return 'Rp ' . number_format($total, 0, ',', '.');
                                    }),

                                Hidden::make('total_amount') // Simpan nilai asli ke database
                                    ->default(12),
                                Textarea::make('notes')
                                    ->label('Catatan Internal'),
                            ])
                    ])
                    ->columnSpan(['lg' => 1]),
                Group::make()
                    ->schema([
                        Section::make('Daftar Barang')
                            ->schema([
                                Repeater::make('items')
                                    ->relationship()
                                    ->defaultItems(1)
                                    ->hiddenLabel()
                                    ->required()
                                    ->table([
                                        TableColumn::make('Produk'),
                                        TableColumn::make('Qty')
                                            ->width(100),
                                        TableColumn::make('Harga Beli')
                                            ->width(130),
                                        TableColumn::make('Subtotal')
                                            ->width(130),
                                    ])
                                    ->schema([
                                        Select::make('product_id')
                                            ->label('Produk')
                                            ->relationship('product', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(fn($state, Set $set) => $set('unit_price', Product::find($state)->purchase_price ?? 0))
                                            ->distinct()
                                            ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                        TextInput::make('quantity')
                                            ->label('Qty')
                                            ->numeric()
                                            ->default(1)
                                            ->live()
                                            ->afterStateUpdated(fn($get, Set $set) => $set('subtotal', (int) $get('quantity') * (float) $get('unit_price')))
                                            ->required(),
                                        TextInput::make('unit_price')
                                            ->label('Harga Beli')
                                            ->numeric()
                                            // ->prefix('Rp')
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(fn($get, Set $set) => $set('subtotal', (int) $get('quantity') * (float) $get('unit_price'))),
                                        TextInput::make('subtotal')
                                            ->numeric()
                                            // ->prefix('Rp')
                                            ->readonly()
                                            ->required(),
                                    ]),
                            ]),
                    ])->columnSpan(['lg' => 3]),
            ])->columns(4);
    }
}

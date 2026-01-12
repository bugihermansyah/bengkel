<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('barcode'),
                TextInput::make('name')
                    ->label('Nama')
                    ->required(),
                TextInput::make('sku')
                    ->label('SKU'),
                Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name')
                    ->preload()
                    ->searchable()
                    ->required(),
                Select::make('rack_id')
                    ->label('Rak')
                    ->preload()
                    ->searchable()
                    ->relationship('rack', 'name')
                    ->required(),
                TextInput::make('stock')
                    ->label('Stok')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('purchase_price')
                    ->label('Harga beli')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                TextInput::make('selling_price')
                    ->label('Harga jual')
                    ->required()
                    ->numeric()
                    ->prefix('Rp'),
                Textarea::make('description')
                    ->label('Keterangan')
                    ->columnSpanFull(),
                FileUpload::make('image')
                    ->label('Foto')
                    ->columnSpanFull()
                    ->image()
                    ->disk('public')
                    ->directory('products')
                    ->visibility('public'),
            ]);
    }
}

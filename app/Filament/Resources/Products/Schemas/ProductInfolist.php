<?php

namespace App\Filament\Resources\Products\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Flex::make([
                            Grid::make(2)
                                ->schema([
                                    Group::make([
                                        TextEntry::make('name'),
                                        TextEntry::make('barcode'),
                                        TextEntry::make('sku')
                                            ->label('SKU')
                                            ->placeholder('-'),
                                        TextEntry::make('stock')
                                    ]),

                                    Group::make([
                                        TextEntry::make('category.name')
                                            ->label('Category'),
                                        TextEntry::make('rack.name')
                                            ->label('Rack')
                                            ->placeholder('-'),
                                    ])
                                ]),
                        ])
                    ]),
                Section::make()
                    ->schema([
                        ImageEntry::make('image')
                            ->disk('public')
                            ->visibility('public')
                            ->hiddenLabel()
                            ->grow(false),
                    ])
            ]);
    }
}

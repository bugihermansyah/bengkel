<?php

namespace App\Filament\Pages\Clusters\Settings\Pages;

use App\Filament\Pages\Clusters\Settings\SettingsCluster;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageApp extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string $settings = \App\Settings\AppSettings::class;

    protected static ?string $cluster = SettingsCluster::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Header')
                    ->schema([
                        TextInput::make('brand_name')
                            ->label('Nama Usaha')
                            ->required(),
                        TextInput::make('brand_logo')
                            ->label('Logo'),
                        TextInput::make('favicon')
                            ->label('Favicon'),
                    ])
                    ->columnSpanFull(),
                Section::make('Footer')
                    ->schema([
                        TextInput::make('address')
                            ->label('Alamat'),
                        TextInput::make('telp')
                            ->label('No Telp'),
                    ])
                    ->columnSpanFull(),
                // Section::make('Printer')
                //     ->schema([
                //         TextInput::make('printer_name')
                //             ->label('Nama Printer'),
                //         Select::make('paper_size')
                //             ->label('Ukuran Kertas')
                //             ->options([
                //                 '58' => '58',
                //                 '80' => '80'
                //             ]),
                //     ])
                //     ->columnSpanFull(),
            ]);
    }
}

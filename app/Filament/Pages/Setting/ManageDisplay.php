<?php

namespace App\Filament\Pages\Setting;

use App\Filament\Pages\Clusters\Settings\SettingsCluster;
use BackedEnum;
use Filament\Schemas\Components\Section;
use UnitEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageDisplay extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedComputerDesktop;
    protected static string $settings = \App\Settings\DisplaySettings::class;
    protected static ?string $cluster = SettingsCluster::class;
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->description('Prevent abuse by limiting the number of requests per period')
                    ->aside()
                    ->schema([
                        TextInput::make('brand_name')
                            ->label('Nama Bengkel')
                            ->required(),
                        TextInput::make('slogan')
                            ->label('Slogan')
                            ->required(),
                        TextInput::make('footer')
                            ->label('Footer'),
                    ])
                    ->columnSpanFull()
            ]);
    }
}

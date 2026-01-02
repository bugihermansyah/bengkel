<?php

namespace App\Filament\Pages\Setting;

use BackedEnum;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageDisplay extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string $settings = \App\Settings\DisplaySettings::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }
}

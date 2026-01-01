<?php

namespace App\Filament\Resources\Racks\Pages;

use App\Filament\Resources\Racks\RackResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageRacks extends ManageRecords
{
    protected static string $resource = RackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

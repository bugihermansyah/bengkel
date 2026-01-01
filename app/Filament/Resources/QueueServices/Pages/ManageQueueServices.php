<?php

namespace App\Filament\Resources\QueueServices\Pages;

use App\Filament\Resources\QueueServices\QueueServiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageQueueServices extends ManageRecords
{
    protected static string $resource = QueueServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

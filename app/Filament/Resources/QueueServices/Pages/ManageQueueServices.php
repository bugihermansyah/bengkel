<?php

namespace App\Filament\Resources\QueueServices\Pages;

use App\Filament\Resources\QueueServices\QueueServiceResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Pages\Concerns\ExposesTableToWidgets;
use Filament\Resources\Pages\ManageRecords;

class ManageQueueServices extends ManageRecords
{
    use ExposesTableToWidgets;

    protected static string $resource = QueueServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->modalHeading(false),
            Action::make('quick_pos')
                ->label('Quick POS'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return QueueServiceResource::getWidgets();
    }
}

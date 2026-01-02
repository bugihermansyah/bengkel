<?php

namespace App\Filament\Resources\QueueServices\Widgets;

use App\Filament\Resources\QueueServices\Pages\ManageQueueServices;
use App\Models\QueueService;
use Filament\Widgets\Concerns\InteractsWithPageTable;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;

class QueueServiceOverview extends StatsOverviewWidget
{
    use InteractsWithPageTable;

    protected ?string $pollingInterval = null;

    protected function getTablePage(): string
    {
        return ManageQueueServices::class;
    }
    protected function getStats(): array
    {
        $queueData = Trend::model(QueueService::class)
            ->between(today()->startOfDay(), today()->endOfDay())
            ->perDay()
            ->count();

        return [
            Stat::make('Waiting', $this->getPageTableQuery()->where('status', 'waiting')->count()),
            Stat::make('Process', $this->getPageTableQuery()->where('status', 'processing')->count()),
            Stat::make('Finish', $this->getPageTableQuery()->where('status', 'finished')->count()),
            Stat::make('Cancel', $this->getPageTableQuery()->where('status', 'canceled')->count()),
        ];
    }
}

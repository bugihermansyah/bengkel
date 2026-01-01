<?php

namespace App\Filament\Resources\QueueServices;

use App\Filament\Resources\QueueServices\Pages\ManageQueueServices;
use App\Models\QueueService;
use BackedEnum;
use UnitEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QueueServiceResource extends Resource
{
    protected static ?string $model = QueueService::class;
    protected static string|UnitEnum|null $navigationGroup = 'POS';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('number')
                    ->required()
                    ->numeric(),
                Select::make('vehicle_id')
                    ->relationship('vehicle', 'plate_number')
                    ->required(),
                Select::make('mechanic_id')
                    ->relationship('mechanic', 'name')
                    ->required(),
                Textarea::make('complaint')
                    ->columnSpanFull(),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->whereIn('status', ['waiting', 'processing', 'finished']))
            ->columns([
                Stack::make([
                    TextColumn::make('number')
                        ->alignCenter()
                        ->numeric()
                        ->sortable(),
                    TextColumn::make('vehicle.plate_number')
                        ->alignCenter()
                        ->formatStateUsing(fn($state) => strtoupper($state))
                        ->size(TextSize::Large)
                        ->tooltip(
                            fn(QueueService $record) =>
                            "Created: {$record->created_at->format('d M Y H:i')}" . " | " .
                            "Process: " . ($record->process_at
                                ? $record->process_at->format('d M Y H:i')
                                : '-')
                        )
                        ->weight(FontWeight::ExtraBold)
                        ->searchable(),
                    TextColumn::make('mechanic.name')
                        ->alignCenter()
                        ->searchable(),
                    TextColumn::make('status')
                        ->badge()
                        ->alignCenter()
                        ->searchable(),
                ]),
            ])
            ->contentGrid([
                'md' => 5,
                'xl' => 5,
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('cancel')
                    ->button()
                    ->outlined()
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-trash')
                    ->modalHeading('Cancel Service')
                    ->modalDescription('Are you sure you want to cancel this service?')
                    ->schema([
                        Textarea::make('notes')
                            ->required(),
                    ])
                    ->action(function (QueueService $record, array $data): void {
                        $record->update([
                            'status' => 'cancelled',
                            'notes' => $data['notes'],
                        ]);
                    }),
                Action::make('process')
                    ->button()
                    ->color('primary')
                    ->icon('heroicon-o-rocket-launch')
                    ->visible(fn(QueueService $record) => $record->status === 'waiting')
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-trash')
                    ->modalHeading('Process Service')
                    ->modalDescription('Are you sure you want to process this service?')
                    ->action(function (QueueService $record, array $data): void {
                        $record->update([
                            'status' => 'processing',
                            'process_at' => now(),
                        ]);
                    }),
                Action::make('finish')
                    ->button()
                    ->color('primary')
                    ->icon('heroicon-o-check-circle')
                    ->visible(fn(QueueService $record) => $record->status === 'processing')
                    ->action(function (QueueService $record, array $data): void {
                        $record->update([
                            'status' => 'finished',
                            'finish_at' => now(),
                        ]);
                    }),
                Action::make('payment')
                    ->label('Payment')
                    ->button()
                    ->color('success')
                    ->icon('heroicon-o-credit-card')
                    ->visible(fn(QueueService $record) => $record->status === 'finished')
                    ->url(
                        fn($record) =>
                        route('filament.admin.pages.payments', [
                            'queue' => $record->id
                        ])
                    ),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageQueueServices::route('/'),
        ];
    }
}

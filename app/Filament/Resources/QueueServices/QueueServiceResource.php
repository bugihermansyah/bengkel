<?php

namespace App\Filament\Resources\QueueServices;

use App\Filament\Resources\QueueServices\Pages\ManageQueueServices;
use App\Filament\Resources\QueueServices\Widgets\QueueServiceOverview;
use App\Models\QueueService;
use App\Models\User;
use App\Models\Vehicle;
use BackedEnum;
use Filament\Actions\ActionGroup;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
    protected static ?string $navigationLabel = 'Antrian';
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Input Antrian')
                    ->schema([
                        Select::make('vehicle_id')
                            ->label('Cari Member (Nopol/Nama)')
                            ->relationship(
                                name: 'vehicle',
                                titleAttribute: 'plate_number',
                                // Tambahkan modifyQueryUsing untuk men-join tabel customers
                                modifyQueryUsing: fn(Builder $query) => $query
                                    ->join('customers', 'vehicles.customer_id', '=', 'customers.id')
                                    ->select('vehicles.*', 'customers.name as customer_name')
                            )
                            ->searchable(['plate_number', 'customers.name'])
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state) {
                                    $vehicle = Vehicle::with('customer')->find($state);
                                    // Jika ketemu, otomatis isi field plate_number & customer_name
                                    if ($vehicle) {
                                        $set('plate_number', $vehicle->plate_number);
                                        // Mengambil nama dari relasi customer
                                        $set('customer_name', $vehicle->customer?->name ?? 'Tanpa Nama');
                                    } else {
                                        // Jika pilihan member dihapus, kosongkan field di bawahnya
                                        $set('plate_number', null);
                                        $set('customer_name', null);
                                    }
                                }
                            })
                            ->helperText('Kosongkan jika bukan member / pelanggan umum')
                            ->getOptionLabelFromRecordUsing(fn($record) => "{$record->plate_number} - {$record->customer_name}"),
                        TextInput::make('plate_number')
                            ->label('Nomor Polisi (Display)')
                            ->required()
                            ->maxLength(9)
                            ->live(onBlur: true) // Menjalankan logic saat kasir pindah field
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                // LOGIC: Jika Nopol diisi manual DAN bukan member DAN nama masih kosong
                                if (filled($state) && blank($get('vehicle_id')) && blank($get('customer_name'))) {
                                    $set('customer_name', 'Umum');
                                }
                            })
                            ->readonly(fn(Get $get) => filled($get('vehicle_id')))
                            ->extraInputAttributes(['style' => 'text-transform: uppercase'])
                            ->dehydrateStateUsing(fn($state) => strtoupper(str_replace(' ', '', $state)))
                            ->placeholder('B1234ABC')
                            ->helperText('Ketik manual jika bukan member'),
                        TextInput::make('customer_name')
                            ->label('Nama Pelanggan')
                            ->placeholder('Nama atau "Umum"'),
                        Select::make('mechanic_id')
                            ->label('Mekanik')
                            ->relationship('mechanic', 'name')
                            ->required(),
                        Textarea::make('complaint')
                            ->label('Keluhan')
                            ->columnStart(2)
                            ->required(),
                    ])->columns(2),
            ])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->whereIn('status', ['waiting', 'processing', 'finished']))
            ->columns([
                Stack::make([
                    TextColumn::make('queue_code')
                        ->alignCenter()
                        ->disabledClick()
                        ->numeric()
                        ->sortable(),
                    TextColumn::make('plate_number')
                        ->alignCenter()
                        ->disabledClick()
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
                        ->disabledClick()
                        ->searchable(),
                    TextColumn::make('status')
                        ->badge()
                        ->disabledClick()
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
                ActionGroup::make([
                    Action::make('cancel')
                        ->color('danger')
                        ->icon(Heroicon::Trash)
                        ->hiddenLabel()
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
                                'status' => 'canceled',
                                'notes' => $data['notes'],
                            ]);
                        }),
                    Action::make('formEdit')
                        ->color('secondary')
                        ->icon(Heroicon::PencilSquare)
                        ->hiddenLabel()
                        ->requiresConfirmation()
                        ->fillForm(fn(QueueService $record): array => [
                            'mechanic_id' => $record->mechanic->id,
                            'complaint' => $record->complaint,
                        ])
                        ->schema([
                            Select::make('mechanic_id')
                                ->label('Mechanic')
                                ->options(User::query()->pluck('name', 'id')),
                            Textarea::make('complaint')
                                ->required(),
                        ])
                        ->action(function (QueueService $record, array $data): void {
                            $record->update([
                                'mechanic_id' => $data['mechanic_id'],
                                'complaint' => $data['complaint'],
                            ]);
                        }),
                ])
                    ->buttonGroup(),
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

    public static function getWidgets(): array
    {
        return [
            QueueServiceOverview::class,
        ];
    }
}

<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StockLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockLogs';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reference_id'),
                Select::make('type')
                    ->options(['in' => 'In', 'out' => 'Out', 'adjustment' => 'Adjustment'])
                    ->required(),
                TextInput::make('quantity')
                    ->required()
                    ->numeric(),
                TextInput::make('stock_before')
                    ->required()
                    ->numeric(),
                TextInput::make('stock_after')
                    ->required()
                    ->numeric(),
                Textarea::make('note')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference_id')
                    ->label('Referensi / Dokumen')
                    // ->placeholder('-') // Jika NULL (seperti pada adjustment), tampilkan tanda strip
                    ->formatStateUsing(function ($record, $state) {
                        // Jika null/kosong, beri label statis agar kolom tidak benar-benar kosong
                        if (!$state) {
                            return match ($record->type) {
                                'adjustment' => 'Adjustment',
                                default => '-',
                            };
                        }

                        return match ($record->type) {
                            'in' => "PO: " . ($record->purchaseOrder?->po_number ?? $state),
                            'out' => "TRX: " . ($record->transaction?->invoice_number ?? $state),
                            'adjustment' => 'Manual Adjustment',
                            default => $state,
                        };
                    })
                    ->description(function ($record) {
                        // Fokuskan description untuk menampilkan 'Note' atau keterangan tipe
                        $typeLabel = match ($record->type) {
                            'in' => 'Penerimaan Stok',
                            'out' => 'Penjualan / Pemakaian',
                            'adjustment' => 'Penyesuaian Manual',
                            default => 'Lainnya',
                        };

                        // Jika ada note, tampilkan note. Jika tidak, tampilkan label tipenya saja.
                        return $record->note ? "{$typeLabel}: {$record->note}" : $typeLabel;
                    })
                    ->searchable(),
                TextColumn::make('type')
                    ->label('Tipe')
                    ->badge(),
                TextColumn::make('quantity')
                    ->label('Qty')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stock_before')
                    ->label('Stock awal')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('stock_after')
                    ->label('Stock akhir')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                // EditAction::make(),
                // DissociateAction::make(),
                // DeleteAction::make(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DissociateBulkAction::make(),
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}

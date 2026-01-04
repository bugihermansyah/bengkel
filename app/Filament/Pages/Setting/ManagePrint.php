<?php

namespace App\Filament\Pages\Setting;

use App\Filament\Pages\Clusters\Settings\SettingsCluster;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\Printer;

class ManagePrint extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPrinter;

    protected static string $settings = \App\Settings\PrintSettings::class;
    protected static ?string $cluster = SettingsCluster::class;

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Header')
                    ->description('Header bagian yang akan di cetak di bagian atas struk')
                    ->aside()
                    ->schema([
                        TextInput::make('brand_name')
                            ->label('Nama Usaha')
                            ->required(),
                        TextInput::make('header_1')
                            ->label('Alamat'),
                        TextInput::make('header_2')
                            ->label('No Telp'),
                    ])
                    ->columnSpanFull(),
                Section::make('Footer')
                    ->description('Footer bagian yang akan di cetak di bagian bawah struk')
                    ->aside()
                    ->schema([
                        TextInput::make('footer_1')
                            ->label('Footer 1'),
                        TextInput::make('footer_2')
                            ->label('Footer 2'),
                        TextInput::make('footer_3')
                            ->label('Footer 3'),
                    ])
                    ->columnSpanFull(),
                Section::make('Printer')
                    ->description('Konfigurasi printer')
                    ->aside()
                    ->schema([
                        TextInput::make('printer_name')
                            ->label('Nama Printer'),
                        Select::make('paper_size')
                            ->label('Ukuran Kertas')
                            ->options([
                                '58' => '58',
                                '80' => '80'
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('testPrint')
                ->label('Test Print')
                ->icon('heroicon-o-printer')
                ->requiresConfirmation()
                ->modalHeading('Test Printer')
                ->modalDescription('Sistem akan mencetak sample header dan footer ke printer yang dikonfigurasi. Lanjutkan?')
                ->action(function () {
                    // Ambil data terbaru dari form yang sedang diisi (belum tersimpan ke DB)
                    $data = $this->form->getRawState();

                    try {
                        $connector = new WindowsPrintConnector($data['printer_name']);
                        $printer = new Printer($connector);

                        // --- HEADER TEST ---
                        $printer->setJustification(Printer::JUSTIFY_CENTER);
                        $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
                        $printer->text($data['brand_name'] . "\n");
                        $printer->selectPrintMode();
                        $printer->text(($data['header_1'] ?? '') . "\n");
                        $printer->text(($data['header_2'] ?? '') . "\n");
                        $printer->text(str_repeat("-", $data['paper_size'] == '80' ? 48 : 32) . "\n");

                        // --- BODY TEST ---
                        $printer->text("TEST PRINT BERHASIL\n");
                        $printer->text("Printer: " . $data['printer_name'] . "\n");
                        $printer->text("Ukuran: " . $data['paper_size'] . "mm\n");
                        $printer->text(str_repeat("-", $data['paper_size'] == '80' ? 48 : 32) . "\n");

                        // --- FOOTER TEST ---
                        if ($data['footer_1'])
                            $printer->text($data['footer_1'] . "\n");
                        if ($data['footer_2'])
                            $printer->text($data['footer_2'] . "\n");
                        if ($data['footer_3'])
                            $printer->text($data['footer_3'] . "\n");

                        $printer->feed(3);
                        $printer->cut();
                        $printer->close();

                        Notification::make()
                            ->title('Test print terkirim ke printer!')
                            ->success()
                            ->send();

                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Printer Error')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}

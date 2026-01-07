<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Header Buttons (Top) --}}
        <div class="flex justify-between items-center no-print">
            <x-filament::button 
                color="gray" 
                icon="heroicon-m-arrow-left" 
                tag="a" 
                href="{{ route('filament.admin.pages.laporan-pendapatan') }}"
                variant="outline"
            >
                Kembali
            </x-filament::button>

            <x-filament::button 
                icon="heroicon-m-printer" 
                onclick="window.print()"
            >
                Cetak Struk
            </x-filament::button>
        </div>

        {{-- Info Grid (Infolist) --}}
        <div class="no-print">
            {{ $this->transactionInfolist }}
        </div>

        {{-- Detail Items Table --}}
        <div class="bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl overflow-hidden shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/50">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-500">Rincian Pekerjaan & Sparepart</h3>
            </div>
            
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="text-xs font-semibold text-gray-500 uppercase bg-gray-50 dark:bg-gray-800/50 border-b border-gray-100 dark:border-gray-800">
                        <th class="px-6 py-3">Item / Jasa</th>
                        <th class="px-6 py-3 text-center">Qty</th>
                        <th class="px-6 py-3 text-right">Harga Satuan</th>
                        <th class="px-6 py-3 text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($record->items as $item)
                    <tr class="text-sm hover:bg-gray-50 dark:hover:bg-gray-800/30 transition">
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                            {{ $item->name }}
                        </td>
                        <td class="px-6 py-4 text-center text-gray-600 dark:text-gray-400">
                            {{ $item->qty }}
                        </td>
                        <td class="px-6 py-4 text-right text-gray-600 dark:text-gray-400">
                            Rp {{ number_format($item->price, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Footer Summary --}}
            <div class="p-6 bg-gray-50/30 dark:bg-gray-800/10 border-t border-gray-100 dark:border-gray-800">
                <div class="flex flex-col items-end space-y-3">
                    <div class="flex justify-between w-full md:w-80 text-sm text-gray-500">
                        <span>Total Kotor:</span>
                        <span class="font-semibold text-gray-800 dark:text-white">Rp {{ number_format($record->total_amount + $record->discount_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between w-full md:w-80 text-sm text-danger-600 font-medium">
                        <span>Diskon:</span>
                        <span>- Rp {{ number_format($record->discount_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between w-full md:w-80 pt-3 border-t-2 border-dashed border-gray-200 dark:border-gray-700 items-center">
                        <span class="text-base font-bold text-gray-900 dark:text-white uppercase">Grand Total</span>
                        <span class="text-2xl font-black text-primary-600">Rp {{ number_format($record->total_amount, 0, ',', '.') }}</span>
                    </div>
                    
                    <div class="w-full md:w-80 pt-2 flex justify-between text-xs text-gray-400 italic">
                        <span>Diterima: Rp {{ number_format($record->payment_received, 0, ',', '.') }}</span>
                        <span>Kembali: Rp {{ number_format($record->payment_received - $record->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- CSS Khusus Print --}}
    <style>
        @media print {
            .no-print { display: none !important; }
            .fi-sidebar, .fi-topbar, .fi-header { display: none !important; }
            .fi-main { padding: 0 !important; }
            body { background: white !important; }
        }
    </style>
</x-filament-panels::page>
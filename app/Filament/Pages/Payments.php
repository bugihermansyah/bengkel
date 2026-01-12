<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Product;
use App\Models\QueueService;
use App\Models\Transaction;
use App\Models\Vehicle;
use Filament\Actions\Action;
use Filament\Actions\Concerns\HasAction;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Payments extends Page
{
    use HasAction;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;
    protected static string|\UnitEnum|null $navigationGroup = 'POS';
    protected static ?string $navigationLabel = 'POS';
    protected string $view = 'filament.pages.payments';
    protected ?string $heading = '';

    public ?QueueService $queue = null;
    public array $customer = [];
    public $searchMember = '';
    public array $products = [];
    public array $categories = [];

    public int $page = 1;
    public int $perPage = 20;
    public $category = null;
    public bool $hasMore = true;
    public $search = '';

    public int $discount = 0;
    public string $payment_method = 'cash';
    public array $manual_services = [];

    public $cart = [];
    public $totalAfterDiscount = 0;
    public $paymentReceived = 0;

    public string $paymentMethod = 'cash';


    public function mount(): void
    {
        $queueId = request()->query('queue');

        if ($queueId) {
            // JALUR SERVICE: Ambil data dari antrean
            $this->queue = QueueService::with(['mechanic', 'vehicle'])->find($queueId);

            if (!$this->queue || $this->queue->status !== 'finished') {
                // ... tetap simpan validasi status jika lewat jalur antrean ...
                Notification::make()->title('Antrean belum siap')->danger()->send();
                $this->redirect('/queue-services');
                return;
            }

            $this->customer = [
                'name' => $this->queue->customer_name ?? 'Pelanggan Umum',
                'plate' => $this->queue->plate_number ?? '-',
                'mechanic' => $this->queue->mechanic->name ?? '-',
                'cashier' => auth()->user()->name,
                'is_from_queue' => $this->queue ? true : false,
            ];
        } else {
            // JALUR BELI LANGSUNG: Set default pelanggan umum
            $this->queue = null;
            $this->customer = [
                'name' => 'Pelanggan Umum',
                'plate' => 'Non-Kendaraan',
                'mechanic' => 'Tanpa Mekanik',
                'cashier' => auth()->user()->name,
                'is_from_queue' => $this->queue ? true : false,
            ];
        }
        // $this->queue = QueueService::find($queueId);

        // // 2. Validasi: Jika antrean tidak ada atau statusnya sudah 'done' / 'finish'
        // if (!$this->queue || $this->queue->status !== 'finished') {
        //     $pesan = ($this->queue && $this->queue->status === 'done')
        //         ? 'Antrean ini sudah dibayar.'
        //         : 'Antrean belum siap untuk pembayaran.';

        //     Notification::make()
        //         ->title('Akses Ditolak')
        //         ->body($pesan)
        //         ->danger()
        //         ->send();

        //     $this->redirect('/queue-services');
        //     return;
        // }
        // // if (!$queueId)
        // //     abort(404);

        // $this->queue = QueueService::with([
        //     'mechanic',
        // ])->findOrFail($queueId);

        // $this->customer = [
        //     'name' => $this->queue->customer_name ?? '-',
        //     'plate' => $this->queue->plate_number ?? '-',
        //     'mechanic' => $this->queue->mechanic->name ?? '-',
        // ];

        $this->categories = Category::select('id', 'name')
            ->orderBy('name')
            ->get()
            ->toArray();

        $this->loadMoreProducts();
    }

    public function loadMoreProducts(): void
    {
        if (!$this->hasMore)
            return;

        $query = Product::with('category')
            ->where('status', true)
            ->when($this->category, function ($q) {
                $q->where('category_id', $this->category);
            })
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });

        // Load products
        $paginator = $query->orderBy('name')->paginate($this->perPage, ['*'], 'page', $this->page);

        if ($paginator->isEmpty()) {
            $this->hasMore = false;
            // Jangan return dulu jika ini halaman 1 (mungkin memang kosong)
            if ($this->page > 1)
                return;
        }

        $newProducts = collect($paginator->items())->map(fn($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'price' => (int) $p->selling_price,
            'stock' => (int) $p->stock,
            'category_id' => $p->category_id,
            'category_name' => $p->category->name ?? '-',
            'image' => $p->image ? Storage::url($p->image) : asset('images/no-image.jpg'),
        ])->toArray();

        $this->products = array_merge($this->products, $newProducts);
        $this->page++;
        $this->hasMore = $paginator->hasMorePages();
    }

    public function updatedCategory()
    {
        $this->resetPageAndProducts();
    }

    public function addManualService($name, $price)
    {
        // Kita buat ID unik sementara untuk jasa manual
        $id = 'service-' . time();
        $this->dispatch('manual-service-added', [
            'id' => $id,
            'name' => $name,
            'price' => (int) $price,
        ]);
    }

    public function checkoutAction(): Action
    {
        return Action::make('checkoutAction')
            ->label('Konfirmasi Pembayaran')
            // Heading dinamis sesuai metode
            ->modalHeading(fn() => "Konfirmasi Pembayaran " . strtoupper($this->paymentMethod))
            ->closeModalByClickingAway(false)
            ->closeModalByEscaping(false)
            ->modalCloseButton(false)
            // Deskripsi informatif ala struk belanja
            ->modalDescription(function () {
                $totalStr = number_format($this->totalAfterDiscount, 0, ',', '.');
                $bayarStr = number_format($this->paymentReceived, 0, ',', '.');
                $kembali = (int) $this->paymentReceived - (int) $this->totalAfterDiscount;
                $kembaliStr = number_format(abs($kembali), 0, ',', '.');
                $labelKembali = $kembali < 0 ? 'Kekurangan' : 'Kembalian';
                $warnaTeks = $kembali < 0 ? 'text-danger-600' : 'text-primary-600';

                return new \Illuminate\Support\HtmlString("
                <div class='space-y-3'>
                    <p class='text-sm text-gray-500'>Detail transaksi yang akan diproses:</p>
                    <div class='p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200 dark:border-gray-700 font-mono'>
                        <div class='flex justify-between mb-1'>
                            <span class='text-gray-500'>Total Tagihan</span>
                            <span class='font-bold text-gray-900 dark:text-white'>Rp $totalStr</span>
                        </div>
                        <div class='flex justify-between mb-2'>
                            <span class='text-gray-500'>Uang Diterima</span>
                            <span class='font-bold text-gray-900 dark:text-white'>Rp $bayarStr</span>
                        </div>
                        <div class='border-t border-dashed border-gray-300 dark:border-gray-600 my-2'></div>
                        <div class='flex justify-between items-center'>
                            <span class='text-sm font-bold uppercase'>$labelKembali</span>
                            <span class='text-lg font-black $warnaTeks'>Rp $kembaliStr</span>
                        </div>
                    </div>
                </div>
            ");
            })
            ->modalSubmitActionLabel('Ya, Selesaikan Transaksi')
            ->modalCancelActionLabel('Batal') // Gunakan ini, bukan cancelAction()
            ->modalIcon('heroicon-o-check-badge')
            ->modalIconColor('success')
            ->color('success')
            ->modalAutofocus(false)
            // ->keyBindings(['enter', 'ctrl+s'])
            // ->extraAttributes([
            //     'x-on:keydown.window.enter' => '$wire.call(\'mountAction\', \'checkoutAction\')',
            // ])
            ->requiresConfirmation()
            ->action(function () {
                $this->checkout(
                    $this->cart,
                    $this->totalAfterDiscount,
                    $this->discount,
                    $this->paymentMethod,
                    $this->paymentReceived
                );
            });
    }

    // Helper untuk notifikasi dari Alpine
    public function notifyError($message)
    {
        Notification::make()->title($message)->danger()->send();
    }

    public function checkout(array $cart, int $total, int $discount, string $payment_method, int $paymentReceived)
    {
        if (empty($cart)) {
            Notification::make()->title('Keranjang kosong!')->danger()->send();
            return;
        }

        try {
            $transaction = DB::transaction(function () use ($cart, $total, $discount, $payment_method, $paymentReceived) {
                // 1. Simpan Header Transaksi
                $trx = Transaction::create([
                    'invoice_number' => 'INV-' . now()->format('YmdHis'),
                    'queue_service_id' => $this->queue?->id,
                    'vehicle_id' => $this->queue?->vehicle_id,
                    'customer_name' => $this->customer['name'],
                    'total_amount' => $total,
                    'discount_amount' => $discount,
                    'payment_method' => $payment_method,
                    'payment_received' => $paymentReceived,
                    'user_id' => auth()->id(),
                ]);

                // 2. Simpan Items
                foreach ($cart as $item) {
                    $isService = str_contains($item['id'], 'service-');
                    $purchasePrice = 0; // Default untuk jasa

                    if (!$isService) {
                        $product = Product::find($item['id']);
                        if ($product) {
                            if ($product->stock < $item['qty']) {
                                throw new \Exception("Stok {$product->name} tidak mencukupi.");
                            }

                            $purchasePrice = $product->purchase_price;
                            $stockBefore = $product->stock;

                            $product->decrement('stock', $item['qty']);

                            $product->stockLogs()->create([
                                'reference_id' => $trx->id,
                                'type' => 'out',
                                'quantity' => $item['qty'],
                                'stock_before' => $stockBefore,
                                'stock_after' => $product->stock,
                                'note' => "Penjualan: {$trx->invoice_number}",
                            ]);

                            if ($product->stock <= $product->minimum_stock) {
                                Notification::make()
                                    ->title('Peringatan Stok Rendah!')
                                    ->body("Stok {$product->name} tersisa {$product->stock}. Segera restock!")
                                    ->warning()
                                    ->persistent()
                                    ->sendToDatabase(auth()->user())
                                    ->send();
                            }
                        }
                    }

                    $trx->items()->create([
                        'product_id' => str_contains($item['id'], 'service-') ? null : $item['id'],
                        'name' => $item['name'],
                        'qty' => $item['qty'],
                        'purchase_price' => $purchasePrice,
                        'price' => $item['price'],
                        'subtotal' => $item['price'] * $item['qty'],
                    ]);

                }

                if ($this->queue) {
                    $this->queue->update(['status' => 'done']);
                }
                return $trx;
            });

            // 3. PROSES CETAK OTOMATIS
            $printService = new \App\Services\PrintService();
            $result = $printService->printReceipt($transaction);

            if ($result === true) {
                Notification::make()->title('Transaksi Berhasil & Struk Dicetak')->success()->send();
            } else {
                Notification::make()->title('Transaksi Berhasil, Tapi Printer Error: ' . $result)->warning()->send();
            }

            // Redirect kembali ke list antrian
            return redirect()->to('/queue-services');

        } catch (\Exception $e) {
            Notification::make()->title('Terjadi kesalahan: ' . $e->getMessage())->danger()->send();
        }
    }

    public function updatedSearch()
    {
        $this->resetPageAndProducts();
    }

    protected function resetPageAndProducts()
    {
        $this->products = [];
        $this->page = 1;
        $this->hasMore = true;
        $this->loadMoreProducts();
    }

    public function updatedSearchMember()
    {
        if (strlen($this->searchMember) < 3)
            return;

        // Cari di tabel Vehicle (asumsi tabel vehicle menyimpan nopol dan relasi ke customer)
        $vehicle = Vehicle::where('plate_number', 'like', '%' . $this->searchMember . '%')
            ->with('customer')
            ->first();

        if ($vehicle) {
            // Update array customer di backend
            $this->customer['name'] = $vehicle->customer->name ?? 'Member Tanpa Nama';
            $this->customer['plate'] = $vehicle->plate_number;

            $this->searchMember = ''; // Bersihkan input pencarian

            Notification::make()
                ->title('Member ditemukan!')
                ->body("Nama: " . $this->customer['name'])
                ->success()
                ->send();
        } else {
            // Opsional: Notif jika tidak ketemu
            Notification::make()
                ->title('Data tidak ditemukan')
                ->warning()
                ->send();
        }
    }
}

<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Product;
use App\Models\QueueService;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Actions\Concerns\HasAction;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Payments extends Page
{
    use HasAction;
    protected static bool $shouldRegisterNavigation = false;
    protected string $view = 'filament.pages.payments';

    public ?QueueService $queue = null;
    public array $customer = [];
    public array $products = [];
    public array $categories = [];

    public int $page = 1;
    public int $perPage = 20;
    public ?int $category = null;
    public bool $hasMore = true;

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
        $this->queue = QueueService::find($queueId);

        // 2. Validasi: Jika antrean tidak ada atau statusnya sudah 'done' / 'finish'
        if (!$this->queue || $this->queue->status !== 'finished') {
            $pesan = ($this->queue && $this->queue->status === 'done')
                ? 'Antrean ini sudah dibayar.'
                : 'Antrean belum siap untuk pembayaran.';

            Notification::make()
                ->title('Akses Ditolak')
                ->body($pesan)
                ->danger()
                ->send();

            $this->redirect('/queue-services');
            return;
        }
        // if (!$queueId)
        //     abort(404);

        $this->queue = QueueService::with([
            'mechanic',
        ])->findOrFail($queueId);

        $this->customer = [
            'name' => $this->queue->customer_name ?? '-',
            'plate' => $this->queue->plate_number ?? '-',
            'mechanic' => $this->queue->mechanic->name ?? '-',
        ];

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

        $query = Product::with('category')->where('status', true);

        // Load products
        $paginator = $query->orderBy('name')->paginate($this->perPage, ['*'], 'page', $this->page);

        if ($paginator->isEmpty()) {
            $this->hasMore = false;
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
        // 1. Kosongkan array produk yang ada saat ini
        $this->products = [];

        // 2. Reset halaman ke nomor 1
        $this->page = 1;

        // 3. Set kembali hasMore menjadi true agar bisa loading lagi
        $this->hasMore = true;

        // 4. Panggil fungsi load produk dengan filter kategori yang baru
        $this->loadMoreProducts();
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
                    'queue_service_id' => $this->queue->id,
                    'vehicle_id' => $this->queue->vehicle_id,
                    'customer_name' => $this->customer['name'],
                    'total_amount' => $total,
                    'discount_amount' => $discount,
                    'payment_method' => $payment_method,
                    'payment_received' => $paymentReceived,
                    'user_id' => auth()->id(),
                ]);

                // 2. Simpan Items
                foreach ($cart as $item) {
                    $trx->items()->create([
                        'product_id' => str_contains($item['id'], 'service-') ? null : $item['id'],
                        'name' => $item['name'],
                        'qty' => $item['qty'],
                        'price' => $item['price'],
                        'subtotal' => $item['price'] * $item['qty'],
                    ]);

                    if (!str_contains($item['id'], 'service-')) {
                        Product::find($item['id'])?->decrement('stock', $item['qty']);
                    }
                }

                $this->queue->update(['status' => 'done']);
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

}

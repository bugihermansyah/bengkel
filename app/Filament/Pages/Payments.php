<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Product;
use App\Models\QueueService;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Storage;

class Payments extends Page
{
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


    public function mount(): void
    {
        $queueId = request()->query('queue');

        if (!$queueId) {
            abort(404);
        }

        $this->queue = QueueService::with([
            'mechanic',
            'vehicle.customer',
        ])->findOrFail($queueId);

        $this->customer = [
            'name' => $this->queue->vehicle->customer->name ?? '-',
            'plate' => $this->queue->vehicle->plate_number ?? '-',
            'mechanic' => $this->queue->mechanic->name ?? '-',
        ];

        $this->categories = Category::select('id', 'name')
            ->orderBy('name')
            ->get()
            ->toArray();

        $this->loadMoreProducts();

        // $this->products = Product::with('category')
        //     ->select('id', 'name', 'selling_price', 'stock', 'category_id', 'image')
        //     ->where('status', true)
        //     ->limit(50)
        //     ->get()
        //     ->map(fn($p) => [
        //         'id' => $p->id,
        //         'name' => $p->name,
        //         'price' => $p->selling_price,
        //         'stock' => $p->stock,
        //         'category_id' => $p->category_id,
        //         'category_name' => $p->category->name ?? '-',
        //         'image' => $p->image,
        //     ])
        //     ->toArray();
    }

    public function loadMoreProducts(): void
    {
        if (!$this->hasMore)
            return;

        $products = Product::with('category')
            ->where('status', true)
            ->orderBy('name')
            ->paginate($this->perPage, ['*'], 'page', $this->page);

        if ($products->isEmpty()) {
            $this->hasMore = false;
            return;
        }

        $this->products = array_merge(
            $this->products,
            $products->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'price' => $p->selling_price,
                'stock' => $p->stock,
                'category_id' => $p->category_id,
                'category_name' => $p->category->name ?? '-',
                'image' => $p->image
                    ? Storage::url($p->image)
                    : asset('images/no-image.jpg'),
            ])->toArray()
        );

        $this->page++;
    }

}

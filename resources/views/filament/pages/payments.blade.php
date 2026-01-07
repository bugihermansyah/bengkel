<x-filament::page>
    <div x-data="posApp()" class="p-0">
        <div class="grid grid-cols-12 gap-4">

            <div class="col-span-8">
                <div class="flex flex-col md:flex-row gap-3 mb-6 items-center">
                    <div class="relative w-full md:w-2/3 group">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <x-heroicon-o-magnifying-glass class="w-5 h-5 text-gray-400" />
                        </div>
                        <input type="text" x-model.debounce.300ms="search" placeholder="Cari sparepart atau jasa..."
                            class="block w-full pl-10 pr-10 py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition shadow-sm" />
                        <button x-show="search.length > 0" @click="search = ''" type="button"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600">
                            <x-heroicon-m-x-mark class="w-5 h-5" />
                        </button>
                    </div>

                    <div class="w-full md:w-1/3">
                        <select wire:model.live="category"
                            class="block w-full py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition shadow-sm">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat['id'] }}">{{ $cat['name'] }}</option>
                            @endforeach
                        </select>
                        <!-- <select x-model="category"
                            class="block w-full py-2.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition shadow-sm">
                            <option value="">Semua Kategori</option>
                            <template x-for="cat in categories" :key="cat.id">
                                <option :value="cat.id" x-text="cat.name"></option>
                            </template>
                        </select> -->
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-4 overflow-y-auto pr-2" @scroll.passive="handleScroll"
                    style="max-height: 75vh;">
                    <template x-for="item in filteredItems" :key="item.id">
                        <div @click="addToCart(item)"
                            :class="item.stock <= 0 ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer hover:border-primary-500'"
                            class="bg-white dark:bg-gray-800 border rounded-xl p-2 transition shadow-sm">
                            <img :src="item.image" class="w-full h-24 object-cover rounded-lg mb-2">
                            <h3 class="text-xs font-bold truncate" x-text="item.name"></h3>
                            <p class="text-primary-600 font-bold text-sm" x-text="formatRupiah(item.price)"></p>
                            <div class="flex justify-between items-center mt-1">
                                <span class="text-[10px] text-gray-500" x-text="item.category_name"></span>
                                <span class="text-[10px] px-1 bg-gray-100 dark:bg-gray-700 rounded"
                                    :class="item.stock < 5 ? 'text-red-500' : ''">
                                    Stok: <span x-text="item.stock"></span>
                                </span>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <div class="col-span-4 bg-white dark:bg-gray-800 border rounded-xl p-4 shadow-sm h-fit sticky top-4">
                <div class="border-b pb-3 mb-3">
                    <h2 class="font-bold text-lg" x-text="customer.name"></h2>
                    <div class="flex justify-between items-center">
                        <p class="text-xs text-gray-500" x-text="customer.plate"></p>
                        <span class="text-[10px] bg-blue-100 text-blue-700 px-2 py-1 rounded-full"
                            x-text="customer.mechanic"></span>
                    </div>
                </div>

                <div class="overflow-y-auto mb-4 border-b border-dashed" style="max-height: 30vh;">
                    <template x-for="item in cart" :key="item.id">
                        <div class="flex justify-between items-center mb-3">
                            <div class="flex-1">
                                <p class="text-sm font-medium leading-tight" x-text="item.name"></p>
                                <p class="text-xs text-gray-500" x-text="formatRupiah(item.price)"></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <button @click="decrease(item)"
                                    class="w-6 h-6 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">-</button>
                                <span class="font-bold text-sm w-4 text-center" x-text="item.qty"></span>
                                <button @click="increase(item)"
                                    class="w-6 h-6 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">+</button>
                            </div>
                        </div>
                    </template>
                    <template x-if="cart.length === 0">
                        <p class="text-center text-gray-400 py-6 text-sm italic">Keranjang Kosong</p>
                    </template>
                </div>

                <div
                    class="bg-gray-50 dark:bg-gray-900 p-[6px] rounded-xl mb-4 border border-gray-200 dark:border-gray-700">
                    <p class="text-[10px] font-bold mb-2 uppercase text-gray-500 tracking-wider">Tambah Jasa</p>
                    <div class="space-y-2">
                        <input type="text" x-model="manualName" placeholder="Nama Jasa..."
                            class="text-xs w-full border bg-white border-gray-300 dark:bg-gray-800 ">
                        <div class="flex gap-2">
                            <div class="flex-1">
                                <input type="number" x-model.number="manualPrice" placeholder="Input harga"
                                    class="text-xs text-right w-full border bg-white rounded-xs border-gray-300 dark:bg-gray-800">
                                <p class="text-[11px] text-blue-500 mt-1" x-text="formatRupiah(manualPrice)"></p>
                            </div>
                            <button @click="addManualItem"
                                class="bg-blue-600 text-white px-3 h-8 rounded-md text-xs font-bold hover:bg-blue-700">TAMBAH</button>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <div>
                        <p class="text-[10px] font-bold mb-2 uppercase text-gray-500 tracking-wider">Metode Pembayaran
                        </p>
                        <div class="grid grid-cols-3 gap-2">
                            <template x-for="mode in ['cash', 'transfer', 'qris']">
                                <button @click="paymentMethod = mode"
                                    :class="paymentMethod === mode ? 'bg-primary-600 text-white border-primary-600' : 'bg-white dark:bg-gray-800 border-gray-300 text-gray-600'"
                                    class="text-[10px] py-2 border rounded-lg uppercase font-black transition shadow-sm"
                                    x-text="mode">
                                </button>
                            </template>
                        </div>
                    </div>

                    <div
                        class="flex justify-between items-center bg-red-50 dark:bg-red-900/20 p-2 rounded-lg border border-red-100 dark:border-red-900/50">
                        <span class="text-xs font-bold text-red-700 dark:text-red-400">DISKON (Rp)</span>
                        <input type="number" x-model.number="discount" placeholder="Input diskon" @focus="$el.select()"
                            class="text-right text-sm w-28 rounded-md border bg-white text-gray-600 focus:ring-0 p-0">
                        <p class="text-[14px] text-right font-black text-red-600" x-text="formatRupiah(discount)"></p>
                    </div>

                    <div x-show="paymentMethod === 'cash'"
                        class="flex justify-between items-center bg-green-50 dark:bg-green-900/20 p-2 rounded-lg border border-green-100 dark:border-green-900/50">
                        <span class="text-xs font-bold text-green-700 dark:text-green-400">DIBAYAR (Rp)</span>
                        <input type="number" x-model.number="paymentReceived" placeholder="Input dibayar" @focus="$el.select()"
                            class="text-right text-sm w-28 rounded-md border bg-white text-gray-600 focus:ring-0 p-0">
                        <p class="text-[14px] text-right font-black text-green-600"
                            x-text="formatRupiah(paymentReceived)">
                        </p>
                    </div>

                    <div x-show="paymentMethod === 'cash'" class="flex justify-between items-center px-2">
                        <span class="text-xs font-medium text-gray-500"
                            x-text="changeAmount < 0 ? 'Kekurangan' : 'Kembalian'"></span>
                        <span class="text-sm font-bold" :class="changeAmount < 0 ? 'text-red-600' : 'text-blue-600'"
                            x-text="formatRupiah(changeAmount)"></span>
                    </div>

                    <div class="border-t-2 border-gray-100 dark:border-gray-700 pt-3">
                        <div class="flex justify-between items-center mb-4">
                            <span class="font-bold text-gray-500">TOTAL</span>
                            <span class="text-2xl font-black text-primary-600"
                                x-text="formatRupiah(totalAfterDiscount)"></span>
                        </div>
                        <button type="button" x-on:click="confirmPayment()" :disabled="cart.length === 0"
                            :class="{'opacity-50 cursor-not-allowed': cart.length === 0}"
                            class="flex items-center justify-center w-full px-4 py-3 text-white transition-all bg-red-500 rounded-xl hover:bg-red-600 font-black shadow-lg">
                            <svg wire:loading wire:target="mountAction, checkout" class="w-5 h-5 mr-3 animate-spin"
                                viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"
                                    fill="none"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>

                            <span wire:loading.remove wire:target="mountAction, checkout">
                                PROSES BAYAR (<span x-text="paymentMethod.toUpperCase()"></span>)
                            </span>

                            <span wire:loading wire:target="mountAction, checkout">
                                MEMPROSES...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('posApp', () => ({
                    loading: false,
                    categories: @js($this->categories),
                    customer: @js($this->customer),
                    items: @entangle('products'),
                    hasMore: @entangle('hasMore'),
                    search: '',
                    category: @entangle('category'),
                    cart: [],
                    manualName: '',
                    manualPrice: '',
                    discount: '0',
                    paymentMethod: 'cash',
                    paymentReceived: '',

                    get filteredItems() {
                        return this.items.filter(i => {
                            const term = this.search.toLowerCase();
                            return !this.search || i.name.toLowerCase().includes(term);
                        });
                    },

                    init() {
                        this.$watch('paymentMethod', (value) => {
                            if (value !== 'cash') {
                                this.paymentReceived = this.totalAfterDiscount;
                            }
                        });

                        // window.addEventListener('close-modal', () => {
                        //     this.loading = false;
                        // });

                        this.$watch('items', () => {
                            // Jika items berubah (karena kategori ganti), pastikan loading state di frontend mati
                            this.loading = false;
                        });
                    },

                    get subtotal() {
                        return this.cart.reduce((s, i) => s + (i.price * i.qty), 0);
                    },

                    get totalAfterDiscount() {
                        let res = this.subtotal - this.discount;
                        return res < 0 ? 0 : res;
                    },

                    get changeAmount() {
                        return this.paymentReceived - this.totalAfterDiscount;
                    },

                    formatRupiah(number) {
                        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(number);
                    },

                    addToCart(item) {
                        if (item.stock <= 0) return;
                        let cartItem = this.cart.find(i => i.id === item.id);
                        if (cartItem) {
                            this.increase(cartItem);
                        } else {
                            this.cart.push({ ...item, qty: 1 });
                            item.stock--;
                        }
                    },

                    increase(item) {
                        let source = this.items.find(i => i.id === item.id);
                        if (source && source.stock > 0) {
                            item.qty++;
                            source.stock--;
                        } else if (item.id.toString().includes('service-')) {
                            item.qty++; // No stock for manual service
                        }
                    },

                    decrease(item) {
                        let source = this.items.find(i => i.id === item.id);
                        if (item.qty > 1) {
                            item.qty--;
                            if (source) source.stock++;
                        } else {
                            if (source) source.stock++;
                            this.cart = this.cart.filter(i => i.id !== item.id);
                        }
                    },

                    addManualItem() {
                        if (!this.manualName || this.manualPrice <= 0) return;
                        this.cart.push({
                            id: 'service-' + Date.now(),
                            name: '[Jasa] ' + this.manualName,
                            price: parseInt(this.manualPrice),
                            qty: 1,
                            stock: 999
                        });
                        this.manualName = '';
                        this.manualPrice = 0;
                    },

                    handleScroll(e) {
                        if (this.loading || !this.hasMore) return;
                        if (e.target.scrollTop + e.target.clientHeight >= e.target.scrollHeight - 50) {
                            this.loading = true;
                            this.$wire.loadMoreProducts().finally(() => this.loading = false);
                        }
                    },

                    confirmPayment() {
                        if (this.cart.length === 0) return;

                        if (this.paymentMethod === 'cash' && this.paymentReceived < this.totalAfterDiscount) {
                            // Gunakan notifikasi Filament jika uang kurang
                            this.$wire.notifyError('Uang pembayaran kurang!');
                            return;
                        }

                        if (this.paymentMethod !== 'cash') {
                            this.paymentReceived = this.totalAfterDiscount;
                        }

                        this.$wire.set('cart', this.cart);
                        this.$wire.set('totalAfterDiscount', this.totalAfterDiscount);
                        this.$wire.set('discount', this.discount);
                        this.$wire.set('paymentMethod', this.paymentMethod);
                        this.$wire.set('paymentReceived', this.paymentReceived);

                        // Panggil action konfirmasi di backend
                        this.$wire.mountAction('checkoutAction');
                    }
                    // confirmPayment() {
                    //     if (this.paymentMethod === 'cash' && this.paymentReceived < this.totalAfterDiscount) {
                    //         alert('Uang yang diterima kurang!');
                    //         return;
                    //     }
                    //     if (confirm(`Konfirmasi pembayaran via ${this.paymentMethod.toUpperCase()}?`)) {
                    //         this.loading = true;
                    //         this.$wire.checkout(this.cart, this.totalAfterDiscount, this.discount, this.paymentMethod, this.paymentReceived)
                    //             .finally(() => this.loading = false);
                    //     }
                    // }
                }))
            })
        </script>
    @endpush
    <x-filament-actions::modals />
</x-filament::page>
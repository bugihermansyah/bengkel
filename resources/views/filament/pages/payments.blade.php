<x-filament::page>
    <div x-data="posApp()" class="p-4 bg-gray-100 dark:bg-gray-900 min-h-screen">

        <div class="p-4">

            <div class="grid grid-cols-12 gap-4">

                <!-- LEFT: ITEM LIST -->
                <div class="col-span-8">
                    <!-- SEARCH & CATEGORY -->
                    <div class="flex gap-2 mb-4">
                        <input type="text" x-model.debounce.300ms="search" placeholder="Cari item..." class="w-full rounded-lg border border-gray-300 dark:border-gray-700
                                bg-white dark:bg-gray-800
                                text-gray-900 dark:text-gray-100 px-3 py-2" />

                        <select x-model="category" class="rounded-lg border border-gray-300 dark:border-gray-700
                                bg-white dark:bg-gray-800
                                text-gray-900 dark:text-gray-100 px-3">
                            <option value="">Semua</option>
                            <template x-for="cat in categories">
                                <option :value="cat.id" x-text="cat.name"></option>
                            </template>
                        </select>
                    </div>

                    <!-- ITEMS -->
                    <div class="grid grid-cols-5 gap-4" x-ref="itemContainer" @scroll.passive="handleScroll"
                        style="max-height: calc(100vh - 180px); overflow-y: auto;">
                        <template x-for="item in filteredItems" :key="item.id">
                            <div @click="addToCart(item)" class="relative rounded-xl overflow-hidden cursor-pointer
                                    bg-white dark:bg-gray-800
                                    border border-gray-200 dark:border-gray-700
                                    hover:shadow-md transition">

                                <!-- IMAGE -->
                                <div class="relative h-28 bg-gray-100 dark:bg-gray-700">
                                    <img :src="item.image" class="w-full h-full object-cover">

                                    <!-- PRICE -->
                                    <div class="absolute bottom-1 right-1
                                            bg-black/70 text-white text-xs
                                            px-2 py-0.5 rounded">
                                        Rp <span x-text="item.price"></span>
                                    </div>
                                </div>

                                <!-- CONTENT -->
                                <div class="p-2">
                                    <!-- ROW 1 -->
                                    <div class="flex justify-between text-xs mb-1">
                                        <span x-show="item.stock > 0" class="px-2 py-0.5 rounded
                                                bg-green-100 text-green-700
                                                dark:bg-green-900 dark:text-green-300">
                                            Sisa <span x-text="item.stock"></span>
                                        </span>

                                        <span x-show="item.stock === 0" class="px-2 py-0.5 rounded
                                                bg-red-100 text-red-700
                                                dark:bg-red-900 dark:text-red-300">
                                            Habis
                                        </span>

                                        <span class="text-gray-500 dark:text-gray-400 capitalize"
                                            x-text="item.category_name"></span>
                                    </div>

                                    <!-- ROW 2 -->
                                    <h3 class="text-sm font-semibold truncate
                                            text-gray-900 dark:text-gray-100" x-text="item.name"></h3>
                                </div>
                            </div>
                        </template>
                        <div x-show="loading" class="col-span-5 py-6 text-center text-gray-400">
                            Loading items...
                        </div>

                        <div x-show="!hasMore" class="col-span-5 py-6 text-center text-gray-400">
                            Semua item sudah dimuat
                        </div>
                    </div>
                </div>

                <!-- RIGHT: CART -->
                <div class="col-span-4">
                    <div class="rounded-xl p-4 sticky top-4
                            bg-white dark:bg-gray-800
                            border border-gray-200 dark:border-gray-700">

                        <!-- CUSTOMER INFO -->
                        <div
                            class="mb-4 p-3 rounded-lg bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700">
                            <p class="text-sm font-semibold text-gray-900 dark:text-gray-100" x-text="customer.name">
                            </p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                Plat: <span x-text="customer.plate"></span>
                                · Mekanik: <span x-text="customer.mechanic"></span>
                            </p>
                        </div>

                        <h2 class="font-bold text-lg mb-3 text-gray-900 dark:text-gray-100">
                            Keranjang
                        </h2>

                        <template x-for="item in cart" :key="item.id">
                            <div class="flex justify-between items-center mb-3">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100"
                                        x-text="item.name"></p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Rp <span
                                            x-text="item.price"></span></p>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button @click="decrease(item)"
                                        class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded">-</button>
                                    <span class="text-sm" x-text="item.qty"></span>
                                    <button @click="increase(item)"
                                        class="px-2 py-1 bg-gray-200 dark:bg-gray-700 rounded">+</button>
                                </div>
                            </div>
                        </template>

                        <div class="border-t border-gray-200 dark:border-gray-700 mt-4 pt-4">
                            <div class="flex justify-between font-bold text-gray-900 dark:text-gray-100">
                                <span>Total</span>
                                <span>Rp <span x-text="total"></span></span>
                            </div>

                            <button class="w-full mt-4 py-2 rounded-lg
                                    bg-blue-600 hover:bg-blue-700
                                    text-white font-semibold">
                                Bayar
                            </button>
                        </div>
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

                    categories: @js($this->categories ?? []),
                    customer: @js($this->customer ?? null),
                    // items: @js($this->products ?? []),
                    items: @entangle('products'),
                    hasMore: @entangle('hasMore'),

                    search: '',
                    category: '',
                    cart: [],

                    get filteredItems() {
                        return this.items.filter(i => {
                            return (!this.search || i.name.toLowerCase().includes(this.search.toLowerCase()))
                                && (!this.category || i.category_id === Number(this.category));
                        });
                    },

                    get total() {
                        return this.cart.reduce((s, i) => s + i.price * i.qty, 0)
                    },

                    handleScroll() {
                        const el = this.$refs.itemContainer
                        if (this.loading || !this.hasMore) return

                        if (el.scrollTop + el.clientHeight >= el.scrollHeight - 80) {
                            this.loadMore()
                        }
                    },

                    loadMore() {
                        this.loading = true
                        this.$wire.loadMoreProducts()
                            .finally(() => this.loading = false)
                    },

                    addToCart(item) {
                        if (item.stock <= 0) return
                        const cartItem = this.cart.find(i => i.id === item.id)
                        if (cartItem) {
                            cartItem.qty++
                        } else {
                            this.cart.push({ ...item, qty: 1 })
                        }
                        item.stock--
                    },

                    increase(item) {
                        const source = this.items.find(i => i.id === item.id)
                        if (source?.stock > 0) {
                            item.qty++
                            source.stock--
                        }
                    },

                    decrease(item) {
                        const source = this.items.find(i => i.id === item.id)
                        if (!source) return
                        if (item.qty > 1) {
                            item.qty--
                            source.stock++
                        } else {
                            source.stock++
                            this.cart = this.cart.filter(i => i !== item)
                        }
                    },
                }))
            })
        </script>
    @endpush
</x-filament::page>
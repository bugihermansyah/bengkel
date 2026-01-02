<div x-data="{ 
    time: '', 
    date: '',
    isFullscreen: false,
    updateTime() {
        const now = new Date();
        this.date = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        this.time = now.toLocaleTimeString('id-ID');
    },
    toggleFS() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(err => {
                alert(`Error: ${err.message}`);
            });
            this.isFullscreen = true;
        } else {
            document.exitFullscreen();
            this.isFullscreen = false;
        }
    }
}" 
x-init="updateTime(); setInterval(() => updateTime(), 1000)">
    <!-- Header -->
    <header class="bg-slate-900 text-white px-10 py-6 flex justify-between items-center shadow-lg">
        <div class="flex items-center gap-4">
            <!-- Logo Bengkel -->
            <!-- <img src="/logo-bengkel.png" alt="Logo Bengkel" class="h-14 w-auto object-contain" /> -->
            @if($settings['brand_logo'])
                <img src="{{ Storage::url($settings['brand_logo']) }}"
                    class="h-14 w-auto object-contain" />
            @endif
            <h1 class="text-4xl font-bold tracking-wide">{{ $settings['brand_name'] }}</h1>
        </div>

        <div class="flex items-center gap-6">
            <div class="text-right" wire:ignore>
                <div class="text-lg" x-text="date"></div>
                <div class="text-2xl font-semibold" x-text="time"></div>
            </div>

            <!-- Fullscreen Button -->
            <button @click="toggleFS()" class="p-3 rounded-full hover:bg-slate-700 transition border border-slate-700">
                <svg x-show="!isFullscreen" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                </svg>

                <svg x-show="isFullscreen" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </header>

    <!-- Content -->
    <main wire:poll.5s="loadQueue" class="flex-1 min-h-0 grid grid-cols-3 gap-6 p-8 pb-20">

        <!-- Waiting -->
        <section class="bg-white rounded-2xl shadow-xl p-5 flex flex-col h-full">
            <h2 class="text-xl font-semibold text-center text-slate-600 mb-4">WAITING</h2>
            <div class="flex-1 space-y-3">
                @foreach($waiting as $item)
                <div class="rounded-xl border px-4 py-3 flex justify-between items-center">
                    <div>
                        <div class="text-2xl font-bold uppercase">{{ $item->vehicle->plate_number }}</div>
                        <div class="text-xs text-slate-500">{{ $item->queue_code }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-slate-500">Mechanic</div>
                        <div class="text-base font-semibold">{{ $item->mechanic->name }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </section>

        <!-- Process -->
        <section class="bg-white rounded-2xl shadow-xl p-5 flex flex-col h-full border-4 border-blue-500">
            <h2 class="text-xl font-semibold text-center text-blue-600 mb-4">PROCESSING</h2>
            <div class="flex-1 space-y-4">
                @foreach($process as $item)
                <div class="rounded-xl border px-4 py-3 flex justify-between items-center">
                    <div>
                        <div class="text-2xl font-bold text-blue-600 uppercase">{{ $item->vehicle->plate_number }}</div>
                        <div class="text-xs text-slate-500">{{ $item->queue_code }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-slate-500">Mechanic</div>
                        <div class="text-base font-semibold">{{ $item->mechanic->name }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </section>

        <!-- Finished -->
        <section class="bg-white rounded-2xl shadow-xl p-5 flex flex-col h-full">
            <h2 class="text-xl font-semibold text-center text-green-600 mb-4">FINISH</h2>
            <div class="flex-1 space-y-3">
                @foreach($finish as $item)
                <div class="rounded-xl bg-green-50 px-4 py-3 flex justify-between items-center">
                    <div>
                        <div class="text-2xl font-bold text-green-700 uppercase">{{ $item->vehicle->plate_number }}</div>
                        <div class="text-xs text-green-600">{{ $item->queue_code }}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-green-500">Mechanic</div>
                        <div class="text-base font-semibold text-green-700">{{ $item->mechanic->name }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="bg-slate-900 text-white py-4 overflow-hidden fixed bottom-0 left-0 w-full z-50">
        <div class="whitespace-nowrap animate-marquee text-2xl font-semibold px-10">
            {{ $settings['footer'] }}
        </div>
    </footer>

    <style>
        @keyframes marquee {
            0% {
                transform: translateX(100%);
            }

            100% {
                transform: translateX(-100%);
            }
        }

        .animate-marquee {
            animation: marquee 20s linear infinite;
        }
    </style>
</div>
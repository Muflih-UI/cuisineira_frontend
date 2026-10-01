@extends('layouts.customer')
@section('title', 'Cuisineria - Toko Kue Online')

@push('styles')
<style>[x-cloak] { display: none !important; }</style>
@endpush

@section('content')

<!-- Hero -->
<section class="relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-cream via-cream to-wheat/20"></div>
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 py-20 sm:py-28 grid lg:grid-cols-2 gap-12 items-center">
        <div>
            <p class="uppercase tracking-[0.2em] text-terracotta text-xs font-semibold mb-4">Cuisineria Premium Bakery</p>
            <h1 class="font-display text-5xl sm:text-6xl leading-[1.05] mb-6">Kue pilihan,<br>dipanggang segar<br>setiap hari.</h1>
            <p class="text-muted text-lg leading-relaxed mb-8 max-w-md">Pesan tanpa perlu daftar akun — cukup nama, nomor telpon, dan lokasi. Ambil langsung di toko atau minta diantar.</p>
            <div class="flex flex-wrap gap-4">
                <a href="{{ url('/produk') }}" class="inline-flex items-center gap-2 bg-terracotta hover:bg-terracotta-dark text-cream px-7 py-3.5 rounded-full font-medium transition">
                    Jelajahi Produk
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
                <a href="{{ url('/lacak-pesanan') }}" class="inline-flex items-center gap-2 border border-espresso/20 hover:border-terracotta hover:text-terracotta px-7 py-3.5 rounded-full font-medium transition">
                    Lacak Pesanan Saya
                </a>
            </div>
        </div>

        <!-- Slideshow: gambar dikelola admin lewat /admin/slideshow. Kalau belum ada
             slide yang ditambahkan, tampil 1 gambar default supaya beranda tetap rapi. -->
        <div class="relative"
             x-data="heroSlideshow()"
             x-init="init()"
             @mouseenter="stop()" @mouseleave="start()">
            <div class="aspect-[4/5] rounded-3xl bg-espresso/5 border border-espresso/10 overflow-hidden relative">
                <template x-for="(slide, index) in slides" :key="index">
                    <img :src="slide.image" :alt="slide.caption || 'Kue Cuisineria'"
                         class="absolute inset-0 w-full h-full object-cover transition-opacity duration-700 ease-in-out"
                         :class="index === current ? 'opacity-100' : 'opacity-0'">
                </template>

                <!-- Caption singkat, ditaruh RAPI di dalam batas gambar (bukan menimpa/keluar
                     dari kotak gambar seperti sebelumnya) supaya tidak menyatu/berantakan. -->
                <div x-show="slides[current] && slides[current].caption" x-cloak
                     class="absolute top-4 left-4 right-4 bg-espresso/80 backdrop-blur text-cream text-xs sm:text-sm font-medium px-4 py-2.5 rounded-full w-fit max-w-[85%]">
                    <span x-text="slides[current] ? slides[current].caption : ''"></span>
                </div>

                <!-- Indikator dot, hanya tampil kalau slide lebih dari 1 -->
                <div x-show="slides.length > 1" class="absolute bottom-4 left-1/2 -translate-x-1/2 flex gap-2">
                    <template x-for="(slide, index) in slides" :key="'dot-' + index">
                        <button @click="goTo(index)"
                                class="h-2 rounded-full transition-all duration-300"
                                :class="index === current ? 'bg-cream w-6' : 'bg-cream/50 w-2'"
                                :aria-label="'Slide ' + (index + 1)"></button>
                    </template>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How it works -->
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-20">
    <h2 class="font-display text-3xl mb-2">Cara memesan</h2>
    <p class="text-muted mb-12 max-w-xl">Tiga langkah sederhana, tanpa perlu bikin akun.</p>
    <div class="grid sm:grid-cols-3 gap-8">
        <div class="bg-white rounded-2xl p-7 border border-espresso/5">
            <p class="font-display text-3xl text-terracotta mb-3">1</p>
            <p class="font-medium mb-2">Pilih kue favorit</p>
            <p class="text-sm text-muted leading-relaxed">Beberapa produk berlaku pre-order dan perlu tanggal pengambilan.</p>
        </div>
        <div class="bg-white rounded-2xl p-7 border border-espresso/5">
            <p class="font-display text-3xl text-terracotta mb-3">2</p>
            <p class="font-medium mb-2">Isi data pengiriman</p>
            <p class="text-sm text-muted leading-relaxed">Nama, nomor telpon, dan titik lokasi di peta — tanpa login.</p>
        </div>
        <div class="bg-white rounded-2xl p-7 border border-espresso/5">
            <p class="font-display text-3xl text-terracotta mb-3">3</p>
            <p class="font-medium mb-2">Ambil sendiri atau diantar</p>
            <p class="text-sm text-muted leading-relaxed">Ambil di toko gratis, atau pilih antar dengan biaya tambahan.</p>
        </div>
    </div>
</section>

<!-- Featured products -->
<section class="bg-white border-y border-espresso/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-20">
        <div class="flex items-end justify-between mb-10">
            <div>
                <h2 class="font-display text-3xl mb-2">Produk pilihan</h2>
                <p class="text-muted">Favorit pelanggan minggu ini.</p>
            </div>
            <a href="{{ url('/produk') }}" class="text-terracotta font-medium text-sm hidden sm:inline">Lihat semua &rarr;</a>
        </div>

        <div id="featured-products" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Diisi via fetch ke CuisineriaAPI.getBestSellers() -->
            <p class="text-muted text-sm col-span-full">Memuat produk...</p>
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
/**
 * Data Alpine untuk slideshow di hero. Dipanggil lewat x-data="heroSlideshow()"
 * pada elemen di section Hero di atas.
 */
function heroSlideshow() {
    return {
        slides: [
            { image: 'https://images.unsplash.com/photo-1509440159596-0249088772ff?q=80&w=1200', caption: null },
        ],
        current: 0,
        timer: null,
        async init() {
            try {
                const res = await CuisineriaAPI.getHeroSlides();
                const list = res.data || res;
                if (list.length) this.slides = list;
            } catch (e) {
                // Backend/API belum siap atau belum ada slide -> tetap pakai gambar default di atas.
            }
            this.start();
        },
        start() {
            this.stop();
            if (this.slides.length > 1) {
                this.timer = setInterval(() => this.next(), 5000);
            }
        },
        stop() {
            if (this.timer) clearInterval(this.timer);
        },
        next() {
            this.current = (this.current + 1) % this.slides.length;
        },
        goTo(index) {
            this.current = index;
            this.start(); // reset timer supaya tidak langsung pindah lagi sesaat setelah diklik manual
        },
    };
}

document.addEventListener('DOMContentLoaded', async () => {
    const container = document.getElementById('featured-products');
    try {
        const products = await CuisineriaAPI.getBestSellers();
        container.innerHTML = (products.data || products).map(p => `
            <a href="/produk/${p.id}" class="group bg-cream rounded-2xl overflow-hidden border border-espresso/5 hover:border-terracotta/40 transition">
                <div class="aspect-square overflow-hidden">
                    <img src="${p.image}" alt="${p.name}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                </div>
                <div class="p-4">
                    ${p.is_preorder ? '<span class="text-[11px] font-medium text-terracotta bg-terracotta/10 rounded-full px-2 py-0.5">Pre-Order</span>' : ''}
                    <p class="font-medium mt-1">${p.name}</p>
                    <p class="text-terracotta font-semibold mt-1">Rp ${Number(p.price).toLocaleString('id-ID')}</p>
                </div>
            </a>
        `).join('');
    } catch (e) {
        container.innerHTML = `<p class="text-sm text-muted col-span-full">Belum bisa memuat produk (${e.message}). Pastikan API backend sudah berjalan.</p>`;
    }
});
</script>
@endpush

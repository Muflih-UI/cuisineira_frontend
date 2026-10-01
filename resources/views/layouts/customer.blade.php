<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Cuisineria - Toko Kue Online')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        cream: '#FBF4E9',
                        espresso: '#2E1D12',
                        terracotta: '#B4622E',
                        'terracotta-dark': '#95501F',
                        wheat: '#C9963E',
                        muted: '#8A7461',
                        sage: '#6B8F5C',
                    },
                    fontFamily: {
                        display: ['Fraunces', 'serif'],
                        body: ['Inter', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body class="bg-cream text-espresso font-body antialiased">

    <!-- Top strip -->
    <div class="bg-espresso text-cream text-xs sm:text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-2 flex flex-col sm:flex-row items-center justify-between gap-1">
            <p class="font-medium">Dipanggang segar setiap hari &bull; Bahan premium pilihan</p>
            <p class="text-cream/70">Pre-order: pesan minimal 1 hari sebelum tanggal ambil</p>
        </div>
    </div>

    <!-- Navbar -->
    <header class="bg-cream/95 backdrop-blur border-b border-espresso/10 sticky top-0 z-40">
        <nav class="max-w-7xl mx-auto px-4 sm:px-6 h-20 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-full bg-terracotta flex items-center justify-center text-cream font-display text-lg">C</span>
                <span class="font-display text-2xl font-semibold tracking-tight">Cuisineria</span>
            </a>

            <div class="hidden md:flex items-center gap-8 font-medium text-sm">
                <a href="{{ url('/') }}" class="hover:text-terracotta transition">Beranda</a>
                <a href="{{ url('/produk') }}" class="hover:text-terracotta transition">Produk</a>
                <a href="{{ url('/lacak-pesanan') }}" class="hover:text-terracotta transition">Lacak Pesanan</a>
                <a href="{{ url('/kontak') }}" class="hover:text-terracotta transition">Kontak</a>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ url('/keranjang') }}" class="relative inline-flex items-center gap-2 border border-espresso/20 rounded-full px-4 py-2 text-sm font-medium hover:border-terracotta hover:text-terracotta transition"
                   x-data
                   @cart-updated.window="$el.querySelector('span.cart-count').innerText = $event.detail.count">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.982-4.756 2.507-7.315a.75.75 0 00-.738-.885H5.106M7.5 14.25L5.106 5.272M6 18.75a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                    Keranjang <span class="cart-count bg-terracotta text-cream rounded-full text-xs w-5 h-5 flex items-center justify-center">0</span>
                </a>
                {{-- Link "Admin" sengaja tidak ditampilkan di sini supaya pelanggan
                     tidak salah pencet/iseng klik. Halaman /admin/login tetap ada
                     dan bisa diakses langsung lewat URL — cukup bookmark sendiri. --}}
            </div>
        </nav>
    </header>

    <main>
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-espresso text-cream/80 mt-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-14 grid grid-cols-1 md:grid-cols-3 gap-10">
            <div>
                <p class="font-display text-2xl text-cream mb-3">Cuisineria</p>
                <p class="text-sm leading-relaxed">Toko kue rumahan dengan sistem pre-order. Ambil langsung di toko atau minta diantar ke lokasi Anda.</p>
            </div>
            <div>
                <p class="text-cream font-medium mb-3 text-sm tracking-wide uppercase">Navigasi</p>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/') }}" class="hover:text-terracotta">Beranda</a></li>
                    <li><a href="{{ url('/produk') }}" class="hover:text-terracotta">Produk</a></li>
                    <li><a href="{{ url('/lacak-pesanan') }}" class="hover:text-terracotta">Lacak Pesanan</a></li>
                    <li><a href="{{ url('/kontak') }}" class="hover:text-terracotta">Kontak</a></li>
                </ul>
            </div>
            <div>
                <p class="text-cream font-medium mb-3 text-sm tracking-wide uppercase">Jam Operasional</p>
                <p class="text-sm">Setiap hari, 08.00 - 20.00</p>
                <p class="text-sm mt-1">Order antar: sebelum pukul 17.00</p>
            </div>
        </div>
        <div class="border-t border-cream/10 text-center text-xs py-5 text-cream/50">
            &copy; {{ date('Y') }} Cuisineria. Semua hak cipta dilindungi.
        </div>
    </footer>

    <script src="{{ asset('js/api.js') }}"></script>
    <script src="{{ asset('js/cart.js') }}"></script>
    @stack('scripts')
</body>
</html>

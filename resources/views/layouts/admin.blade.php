<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin - Cuisineria')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

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
                        panel: '#F4EBDD',
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
<body class="bg-panel text-espresso font-body antialiased" x-data="{ sidebarOpen: false }">

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed lg:static lg:translate-x-0 transition-transform z-40 w-64 h-screen bg-espresso text-cream flex flex-col overflow-y-auto">
            <div class="h-20 flex items-center gap-3 px-6 border-b border-cream/10 flex-shrink-0">
                <span class="w-9 h-9 rounded-full bg-terracotta flex items-center justify-center font-display">C</span>
                <div>
                    <p class="font-display text-lg leading-none">Cuisineria</p>
                    <p class="text-[11px] text-cream/50 tracking-wide uppercase">Admin Panel</p>
                </div>
            </div>
            <nav class="flex-1 px-4 py-6 space-y-1 text-sm">
                <a href="{{ url('/admin/dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-cream/10 {{ request()->is('admin/dashboard') ? 'bg-cream/10 text-terracotta' : '' }}">
                    <span>Dashboard</span>
                </a>
                <a href="{{ url('/admin/pesanan') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-cream/10 {{ request()->is('admin/pesanan*') ? 'bg-cream/10 text-terracotta' : '' }}">
                    <span>Pesanan</span>
                </a>
                <a href="{{ url('/admin/produk') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-cream/10 {{ request()->is('admin/produk*') ? 'bg-cream/10 text-terracotta' : '' }}">
                    <span>Produk</span>
                </a>
                <a href="{{ url('/admin/pesanan/offline/baru') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-cream/10 {{ request()->is('admin/pesanan/offline*') ? 'bg-cream/10 text-terracotta' : '' }}">
                    <span>Catat Pesanan Offline</span>
                </a>
                <a href="{{ url('/admin/pesan-kontak') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-cream/10 {{ request()->is('admin/pesan-kontak*') ? 'bg-cream/10 text-terracotta' : '' }}">
                    <span>Pesan Kontak</span>
                    <span id="contact-unread-badge" class="hidden bg-terracotta text-cream text-[10px] rounded-full w-5 h-5 items-center justify-center"></span>
                </a>

                <p class="px-3 pt-5 pb-1 text-[11px] uppercase tracking-wider text-cream/40">Beranda</p>
                <a href="{{ url('/admin/slideshow') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-cream/10 {{ request()->is('admin/slideshow*') ? 'bg-cream/10 text-terracotta' : '' }}">
                    <span>Slideshow Beranda</span>
                </a>
            </nav>
            <div class="px-4 py-5 border-t border-cream/10 flex-shrink-0">
                <form action="{{ url('/admin/logout') }}" method="POST">
                    @csrf
                    <button class="w-full text-left text-sm text-cream/70 hover:text-terracotta px-3 py-2">Keluar</button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col min-w-0">
            <!-- Topbar -->
            <header class="h-20 bg-cream border-b border-espresso/10 flex items-center justify-between px-6">
                <button class="lg:hidden" @click="sidebarOpen = !sidebarOpen">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                </button>
                <h1 class="font-display text-xl">@yield('page-title', 'Dashboard')</h1>
                <div class="flex items-center gap-2 text-sm">
                    <span class="w-8 h-8 rounded-full bg-terracotta text-cream flex items-center justify-center font-medium">A</span>
                    <span class="hidden sm:inline text-muted">Admin</span>
                </div>
            </header>

            <main class="flex-1 p-6">
                @yield('content')
            </main>
        </div>
    </div>

    <script src="{{ asset('js/api.js') }}"></script>
    <script src="{{ asset('js/admin.js') }}"></script>
    <script>
        // Badge jumlah pesan kontak yang belum dibaca, ditampilkan di semua halaman admin.
        document.addEventListener('DOMContentLoaded', async () => {
            const token = localStorage.getItem('cuisineria_admin_token');
            if (!token) return;
            try {
                const res = await CuisineriaAPI.adminListContactMessages(token, { unread: 1 });
                const badge = document.getElementById('contact-unread-badge');
                if (res.unread_count > 0) {
                    badge.innerText = res.unread_count;
                    badge.classList.remove('hidden');
                    badge.classList.add('flex');
                }
            } catch (e) { /* diamkan saja kalau gagal, badge cuma pemanis */ }
        });
    </script>
    @stack('scripts')
</body>
</html>

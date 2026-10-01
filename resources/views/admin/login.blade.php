<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Cuisineria</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { theme: { extend: { colors: {
            cream: '#FBF4E9', espresso: '#2E1D12', terracotta: '#B4622E',
            'terracotta-dark': '#95501F', muted: '#8A7461',
        }, fontFamily: { display: ['Fraunces', 'serif'], body: ['Inter', 'sans-serif'] } } } }
    </script>
</head>
<body class="bg-espresso text-espresso font-body min-h-screen flex items-center justify-center px-4">

    <!-- Tombol kembali ke beranda pelanggan -->
    <a href="{{ url('/') }}"
       class="absolute top-6 left-6 inline-flex items-center gap-2 text-cream/70 hover:text-cream text-sm font-medium transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
        </svg>
        Kembali ke Beranda
    </a>

    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <span class="w-12 h-12 rounded-full bg-terracotta inline-flex items-center justify-center text-cream font-display text-xl mb-4">C</span>
            <h1 class="font-display text-3xl text-cream">Cuisineria Admin</h1>
            <p class="text-cream/50 text-sm mt-1">Panel khusus admin toko</p>
        </div>

        <div class="bg-cream rounded-2xl p-8 shadow-2xl">
            <form id="login-form" class="space-y-5">
                <div>
                    <label class="text-sm font-medium block mb-1.5">Email</label>
                    <input name="email" type="email" required class="w-full border border-espresso/15 rounded-xl px-4 py-3 outline-none focus:border-terracotta" placeholder="admin@cuisineria.id">
                </div>
                <div>
                    <label class="text-sm font-medium block mb-1.5">Kata sandi</label>
                    <input name="password" type="password" required class="w-full border border-espresso/15 rounded-xl px-4 py-3 outline-none focus:border-terracotta" placeholder="••••••••">
                </div>
                <p id="login-error" class="text-sm text-red-600 hidden"></p>
                <button type="submit" id="login-btn" class="w-full bg-terracotta hover:bg-terracotta-dark text-cream py-3.5 rounded-full font-medium transition">Masuk</button>
            </form>
        </div>

        <div class="text-center mt-6 space-y-2">
            <p class="text-cream/40 text-xs">Halaman ini hanya untuk staf Cuisineria.</p>
            <a href="{{ url('/') }}" class="inline-block text-cream/60 hover:text-cream text-xs underline underline-offset-4">
                Bukan admin? Kembali ke halaman pelanggan
            </a>
        </div>
    </div>

    <script src="{{ asset('js/api.js') }}"></script>
    <script>
        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const errorEl = document.getElementById('login-error');
            const btn = document.getElementById('login-btn');
            errorEl.classList.add('hidden');
            btn.disabled = true; btn.innerText = 'Memproses...';

            try {
                const res = await CuisineriaAPI.adminLogin(fd.get('email'), fd.get('password'));
                localStorage.setItem('cuisineria_admin_token', res.token);
                window.location.href = '/admin/dashboard';
            } catch (err) {
                errorEl.innerText = err.message || 'Email atau kata sandi salah.';
                errorEl.classList.remove('hidden');
                btn.disabled = false; btn.innerText = 'Masuk';
            }
        });
    </script>
</body>
</html>

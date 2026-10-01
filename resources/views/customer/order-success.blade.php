@extends('layouts.customer')
@section('title', 'Status Pesanan - Cuisineria')

@section('content')
<section class="max-w-xl mx-auto px-4 sm:px-6 py-20 text-center">
    <div id="status-icon" class="w-16 h-16 rounded-full bg-sage/15 text-sage flex items-center justify-center mx-auto mb-6">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
    </div>
    <h1 id="status-title" class="font-display text-3xl mb-3">Memeriksa status pembayaran...</h1>
    <p id="status-desc" class="text-muted mb-8">Mohon tunggu sebentar.</p>
    <button id="pay-btn" type="button" class="hidden mb-8 bg-terracotta hover:bg-terracotta-dark text-cream px-6 py-3 rounded-full font-medium transition">Bayar Sekarang</button>

    <div class="ticket-edge bg-panel border border-espresso/10 rounded-2xl p-8 inline-block">
        <p class="text-xs uppercase tracking-widest text-muted mb-1">Kode Pesanan</p>
        <p id="order-code" class="font-display text-3xl text-terracotta">-</p>
        <div class="ticket-divider my-4"></div>
        <p class="text-sm text-muted">Nomor telpon terdaftar</p>
        <p id="order-phone" class="font-medium">-</p>
    </div>

    <div class="flex justify-center gap-4 mt-10">
        <a href="{{ url('/lacak-pesanan') }}" class="bg-terracotta hover:bg-terracotta-dark text-cream px-6 py-3 rounded-full font-medium transition">Lacak Pesanan</a>
        <a href="{{ url('/produk') }}" class="border border-espresso/20 hover:border-terracotta hover:text-terracotta px-6 py-3 rounded-full font-medium transition">Belanja Lagi</a>
    </div>
</section>
@endsection

@push('scripts')
{{-- Snap.js: dipakai untuk membuka ulang popup pembayaran kalau pesanan belum dibayar. --}}
<script
    src="https://app.sandbox.midtrans.com/snap/snap.js"
    data-client-key="Mid-client-wAEaoGCe_TO6VEtE">
</script>
<script>
const CHECK_ICON = '<svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>';
const CLOCK_ICON = '<svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m6-2a10 10 0 11-20 0 10 10 0 0120 0z"/></svg>';
const CROSS_ICON = '<svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>';

const params = new URLSearchParams(window.location.search);
const orderCode = params.get('kode');
const phone = params.get('phone');

function showState({ title, desc, icon, tone }) {
    document.getElementById('status-title').innerText = title;
    document.getElementById('status-desc').innerText = desc;
    const iconEl = document.getElementById('status-icon');
    iconEl.innerHTML = icon;
    iconEl.className = `w-16 h-16 rounded-full bg-${tone}/15 text-${tone} flex items-center justify-center mx-auto mb-6`;
}

async function loadPaymentStatus() {
    const payBtn = document.getElementById('pay-btn');
    payBtn.classList.add('hidden');

    let data;
    try {
        data = await CuisineriaAPI.getPaymentStatus(orderCode, phone);
    } catch (e) {
        showState({
            title: 'Pesanan tersimpan',
            desc: 'Status pembayaran belum bisa dipastikan saat ini. Cek kembali lewat menu Lacak Pesanan.',
            icon: CLOCK_ICON, tone: 'wheat',
        });
        return;
    }

    switch (data.payment_status) {
        case 'paid':
            showState({
                title: 'Pembayaran berhasil, pesanan diterima!',
                desc: 'Simpan kode pesanan berikut untuk melacak status pesanan Anda.',
                icon: CHECK_ICON, tone: 'sage',
            });
            break;
        case 'pending':
            showState({
                title: 'Pesanan tersimpan, menunggu pembayaran',
                desc: 'Pesanan baru diproses setelah pembayaran selesai. Anda bisa melanjutkan pembayaran kapan saja lewat tombol di bawah.',
                icon: CLOCK_ICON, tone: 'wheat',
            });
            if (data.snap_token) {
                payBtn.classList.remove('hidden');
                payBtn.onclick = () => openPayment(data.snap_token);
            }
            break;
        case 'expired':
        case 'failed':
        case 'cancelled':
            showState({
                title: 'Pembayaran tidak berhasil',
                desc: 'Pembayaran gagal, dibatalkan, atau sudah kedaluwarsa. Silakan hubungi kami atau buat pesanan baru.',
                icon: CROSS_ICON, tone: 'terracotta',
            });
            break;
        default:
            showState({
                title: 'Pesanan tersimpan',
                desc: 'Cek status pesanan Anda lewat menu Lacak Pesanan.',
                icon: CLOCK_ICON, tone: 'wheat',
            });
    }
}

function openPayment(token) {
    if (typeof snap === 'undefined') {
        alert('Halaman pembayaran belum termuat. Muat ulang halaman ini lalu coba lagi.');
        return;
    }
    // Apa pun callback popup-nya, status dimuat ulang dari server —
    // menutup popup TIDAK PERNAH dianggap pembayaran selesai.
    snap.pay(token, {
        onSuccess: loadPaymentStatus,
        onPending: loadPaymentStatus,
        onError: loadPaymentStatus,
        onClose: loadPaymentStatus,
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('order-code').innerText = orderCode || '-';
    document.getElementById('order-phone').innerText = phone || '-';
    if (!orderCode || !phone) {
        showState({ title: 'Pesanan tidak ditemukan', desc: 'Kode pesanan atau nomor telpon tidak ada di alamat halaman ini.', icon: CROSS_ICON, tone: 'terracotta' });
        return;
    }
    loadPaymentStatus();
});
</script>
@endpush

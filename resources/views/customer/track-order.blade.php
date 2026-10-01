@extends('layouts.customer')
@section('title', 'Lacak Pesanan - Cuisineria')

@section('content')
<section class="max-w-2xl mx-auto px-4 sm:px-6 py-16">
    <h1 class="font-display text-4xl mb-2 text-center">Lacak Pesanan</h1>
    <p class="text-muted text-center mb-10">Masukkan nomor telpon yang Anda gunakan saat memesan.</p>

    <form id="track-form" class="flex gap-3 mb-10">
        <input id="phone-input" type="tel" required placeholder="08xxxxxxxxxx"
               class="flex-1 border border-espresso/15 rounded-full px-5 py-3.5 outline-none focus:border-terracotta">
        <button class="bg-terracotta hover:bg-terracotta-dark text-cream px-7 py-3.5 rounded-full font-medium transition">Cari</button>
    </form>

    <div id="results" class="space-y-5"></div>
</section>
@endsection

@push('scripts')
<script>
const STATUS_LABEL = {
    pending: { label: 'Menunggu Konfirmasi', color: 'bg-wheat/20 text-wheat' },
    confirmed: { label: 'Dikonfirmasi', color: 'bg-terracotta/15 text-terracotta' },
    processing: { label: 'Sedang Diproses', color: 'bg-terracotta/15 text-terracotta' },
    ready: { label: 'Siap Diambil/Diantar', color: 'bg-sage/15 text-sage' },
    delivering: { label: 'Sedang Diantar', color: 'bg-sage/15 text-sage' },
    completed: { label: 'Selesai', color: 'bg-sage/20 text-sage' },
    cancelled: { label: 'Dibatalkan', color: 'bg-red-100 text-red-600' },
};

document.getElementById('track-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const phone = document.getElementById('phone-input').value.trim();
    const results = document.getElementById('results');
    results.innerHTML = '<p class="text-muted text-sm text-center">Mencari pesanan...</p>';

    try {
        const orders = await CuisineriaAPI.trackOrder(phone);
        const list = orders.data || orders;
        if (!list.length) {
            results.innerHTML = '<p class="text-muted text-sm text-center">Tidak ditemukan pesanan dengan nomor ini.</p>';
            return;
        }
        results.innerHTML = list.map(o => {
            const s = STATUS_LABEL[o.status] || { label: o.status, color: 'bg-espresso/10 text-espresso' };
            return `
            <div class="ticket-edge bg-panel border border-espresso/10 rounded-2xl p-6">
                <div class="flex items-center justify-between mb-3">
                    <p class="font-display text-lg text-terracotta">${o.order_code}</p>
                    <span class="text-xs font-medium px-3 py-1 rounded-full ${s.color}">${s.label}</span>
                </div>
                <p class="text-sm text-muted mb-1">${o.fulfillment_method === 'delivery' ? 'Diantar' : 'Ambil di toko'} &middot; ${new Date(o.created_at).toLocaleDateString('id-ID')}</p>
                <div class="ticket-divider my-3"></div>
                <ul class="text-sm space-y-1 mb-3">
                    ${(o.items || []).map(i => `<li class="flex justify-between"><span>${i.product_name} &times;${i.qty}</span><span>Rp ${Number(i.price * i.qty).toLocaleString('id-ID')}</span></li>`).join('')}
                </ul>
                <div class="flex justify-between font-medium text-sm pt-2 border-t border-espresso/10">
                    <span>Total</span><span>Rp ${Number(o.total).toLocaleString('id-ID')}</span>
                </div>
            </div>`;
        }).join('');
    } catch (err) {
        results.innerHTML = `<p class="text-sm text-red-600 text-center">${err.message}</p>`;
    }
});
</script>
@endpush

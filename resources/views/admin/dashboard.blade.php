@extends('layouts.admin')
@section('title', 'Dashboard - Admin Cuisineria')
@section('page-title', 'Dashboard')

@section('content')
<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8" id="stat-cards">
    <div class="bg-white rounded-2xl border border-espresso/5 p-6">
        <p class="text-sm text-muted mb-1">Pesanan Baru</p>
        <p class="font-display text-3xl" id="stat-new">-</p>
    </div>
    <div class="bg-white rounded-2xl border border-espresso/5 p-6">
        <p class="text-sm text-muted mb-1">Sedang Diproses</p>
        <p class="font-display text-3xl" id="stat-processing">-</p>
    </div>
    <div class="bg-white rounded-2xl border border-espresso/5 p-6">
        <p class="text-sm text-muted mb-1">Pre-Order Aktif</p>
        <p class="font-display text-3xl" id="stat-preorder">-</p>
    </div>
    <div class="bg-white rounded-2xl border border-espresso/5 p-6">
        <p class="text-sm text-muted mb-1">Pesan Kontak Baru</p>
        <p class="font-display text-3xl text-terracotta" id="stat-unread-messages">-</p>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white rounded-2xl border border-espresso/5 p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="font-medium">Pesanan Terbaru</h2>
            <a href="{{ url('/admin/pesanan') }}" class="text-sm text-terracotta font-medium">Lihat semua &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-muted border-b border-espresso/10">
                        <th class="py-2 pr-4">Kode</th>
                        <th class="py-2 pr-4">Pelanggan</th>
                        <th class="py-2 pr-4">Metode</th>
                        <th class="py-2 pr-4">Total</th>
                        <th class="py-2 pr-4">Status</th>
                    </tr>
                </thead>
                <tbody id="recent-orders">
                    <tr><td colspan="5" class="py-6 text-center text-muted">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-espresso/5 p-6">
        <div class="flex items-center justify-between mb-5">
            <h2 class="font-medium">Pesan Kontak Terbaru</h2>
            <a href="{{ url('/admin/pesan-kontak') }}" class="text-sm text-terracotta font-medium">Lihat semua &rarr;</a>
        </div>
        <div id="recent-messages" class="space-y-4">
            <p class="text-muted text-sm text-center py-4">Memuat pesan...</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const token = requireAdminAuth();
    if (!token) return;

    // ---- Pesanan terbaru ----
    try {
        const res = await CuisineriaAPI.adminListOrders(token, { limit: 8 });
        const orders = res.data || res;

        // "Pesanan Baru" = baru lunas & belum mulai diproses. Status 'pending'
        // sekarang berarti "belum dibayar" dan memang tidak ikut ditampilkan
        // (lihat filter payment_status=paid di endpoint /admin/orders), jadi
        // titik awal pesanan yang perlu diproses admin adalah 'confirmed'.
        document.getElementById('stat-new').innerText = orders.filter(o => o.status === 'confirmed').length;
        document.getElementById('stat-processing').innerText = orders.filter(o => o.status === 'processing').length;
        document.getElementById('stat-preorder').innerText = orders.filter(o => (o.items || []).some(i => i.is_preorder)).length;

        document.getElementById('recent-orders').innerHTML = orders.map(o => `
            <tr class="border-b border-espresso/5 hover:bg-cream/60 cursor-pointer" onclick="window.location.href='/admin/pesanan/${o.id}'">
                <td class="py-3 pr-4 font-medium text-terracotta">${o.order_code}</td>
                <td class="py-3 pr-4">${o.customer_name}</td>
                <td class="py-3 pr-4">${o.fulfillment_method === 'delivery' ? 'Antar' : 'Ambil sendiri'}</td>
                <td class="py-3 pr-4">Rp ${Number(o.total).toLocaleString('id-ID')}</td>
                <td class="py-3 pr-4">${statusBadge(o.status)}</td>
            </tr>
        `).join('') || '<tr><td colspan="5" class="py-6 text-center text-muted">Belum ada pesanan.</td></tr>';
    } catch (e) {
        document.getElementById('recent-orders').innerHTML =
            `<tr><td colspan="5" class="py-6 text-center text-muted">Gagal memuat data (${e.message}).</td></tr>`;
    }

    // ---- Pesan kontak terbaru ----
    try {
        const res = await CuisineriaAPI.adminListContactMessages(token);
        const messages = (res.data || res).slice(0, 5);

        document.getElementById('stat-unread-messages').innerText = res.unread_count ?? 0;

        document.getElementById('recent-messages').innerHTML = messages.length ? messages.map(m => `
            <a href="/admin/pesan-kontak" class="block border-b border-espresso/5 last:border-0 pb-3 last:pb-0">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium">${m.name}</p>
                    ${!m.is_read ? '<span class="text-[10px] bg-terracotta text-cream rounded-full px-2 py-0.5">Baru</span>' : ''}
                </div>
                <p class="text-xs text-muted truncate mt-0.5">${m.message}</p>
            </a>
        `).join('') : '<p class="text-muted text-sm text-center py-4">Belum ada pesan masuk.</p>';
    } catch (e) {
        document.getElementById('recent-messages').innerHTML =
            `<p class="text-muted text-sm text-center py-4">Gagal memuat pesan (${e.message}).</p>`;
    }
});
</script>
@endpush

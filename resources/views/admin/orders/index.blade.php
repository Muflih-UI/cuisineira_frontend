@extends('layouts.admin')
@section('title', 'Pesanan - Admin Cuisineria')
@section('page-title', 'Pesanan')

@section('content')
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div class="flex flex-wrap gap-2" id="status-filters">
        <button data-status="" class="filter-btn px-4 py-2 rounded-full text-sm font-medium bg-terracotta text-cream">Semua</button>
        <button data-status="pending" class="filter-btn px-4 py-2 rounded-full text-sm font-medium bg-white border border-espresso/10">Baru</button>
        <button data-status="processing" class="filter-btn px-4 py-2 rounded-full text-sm font-medium bg-white border border-espresso/10">Diproses</button>
        <button data-status="ready" class="filter-btn px-4 py-2 rounded-full text-sm font-medium bg-white border border-espresso/10">Siap</button>
        <button data-status="completed" class="filter-btn px-4 py-2 rounded-full text-sm font-medium bg-white border border-espresso/10">Selesai</button>
    </div>
    <a href="{{ url('/admin/pesanan/offline/baru') }}" class="bg-espresso hover:bg-terracotta text-cream px-5 py-2.5 rounded-full text-sm font-medium transition">+ Catat Pesanan Offline</a>
</div>

<div class="bg-white rounded-2xl border border-espresso/5 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-muted border-b border-espresso/10 bg-cream/60">
                <th class="py-3 px-5">Kode</th>
                <th class="py-3 px-5">Pelanggan</th>
                <th class="py-3 px-5">Sumber</th>
                <th class="py-3 px-5">Metode</th>
                <th class="py-3 px-5">Total</th>
                <th class="py-3 px-5">Status</th>
                <th class="py-3 px-5"></th>
            </tr>
        </thead>
        <tbody id="order-rows">
            <tr><td colspan="7" class="py-8 text-center text-muted">Memuat pesanan...</td></tr>
        </tbody>
    </table>
</div>
@endsection

@push('scripts')
<script>
let token = null;
let currentStatus = '';

async function loadOrders() {
    const rows = document.getElementById('order-rows');
    rows.innerHTML = `<tr><td colspan="7" class="py-8 text-center text-muted">Memuat pesanan...</td></tr>`;
    try {
        const res = await CuisineriaAPI.adminListOrders(token, currentStatus ? { status: currentStatus } : {});
        const orders = res.data || res;
        rows.innerHTML = orders.map(o => `
            <tr class="border-b border-espresso/5 hover:bg-cream/60">
                <td class="py-3 px-5 font-medium text-terracotta cursor-pointer" onclick="window.location.href='/admin/pesanan/${o.id}'">${o.order_code}</td>
                <td class="py-3 px-5">${o.customer_name}<br><span class="text-xs text-muted">${o.customer_phone}</span></td>
                <td class="py-3 px-5">${o.source === 'offline' ? '<span class="text-xs bg-wheat/20 text-wheat px-2 py-0.5 rounded-full">Offline</span>' : '<span class="text-xs bg-sage/15 text-sage px-2 py-0.5 rounded-full">Online</span>'}</td>
                <td class="py-3 px-5">${o.fulfillment_method === 'delivery' ? 'Antar' : 'Ambil sendiri'}</td>
                <td class="py-3 px-5">Rp ${Number(o.total).toLocaleString('id-ID')}</td>
                <td class="py-3 px-5">${statusBadge(o.status)}</td>
                <td class="py-3 px-5 text-right space-x-3">
                    <a href="/admin/pesanan/${o.id}" class="text-terracotta text-xs font-medium">Detail</a>
                    <button onclick="deleteOrder(${o.id})" class="text-red-600 text-xs font-medium">Hapus</button>
                </td>
            </tr>
        `).join('') || '<tr><td colspan="7" class="py-8 text-center text-muted">Belum ada pesanan.</td></tr>';
    } catch (e) {
        rows.innerHTML = `<tr><td colspan="7" class="py-8 text-center text-muted">Gagal memuat (${e.message}).</td></tr>`;
    }
}

async function deleteOrder(id) {
    if (!confirm('Hapus pesanan ini? Tindakan tidak bisa dibatalkan.')) return;
    try {
        await CuisineriaAPI.adminDeleteOrder(token, id);
        loadOrders();
    } catch (e) { alert(e.message); }
}

document.addEventListener('DOMContentLoaded', () => {
    token = requireAdminAuth();
    if (!token) return;
    loadOrders();

    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.filter-btn').forEach(b => {
                b.classList.remove('bg-terracotta', 'text-cream');
                b.classList.add('bg-white', 'border', 'border-espresso/10');
            });
            btn.classList.add('bg-terracotta', 'text-cream');
            btn.classList.remove('bg-white', 'border', 'border-espresso/10');
            currentStatus = btn.dataset.status;
            loadOrders();
        });
    });
});
</script>
@endpush

@extends('layouts.admin')
@section('title', 'Detail Pesanan - Admin Cuisineria')
@section('page-title', 'Detail Pesanan')

@section('content')
<div id="order-detail" data-id="{{ $id ?? '' }}" class="grid lg:grid-cols-3 gap-6">
    <p class="text-muted col-span-full">Memuat pesanan...</p>
</div>
@endsection

@push('scripts')
<script>
const NEXT_STATUS = {
    pending: 'confirmed', confirmed: 'processing', processing: 'ready',
    ready: 'completed', delivering: 'completed',
};
const STATUS_ORDER = ['pending', 'confirmed', 'processing', 'ready', 'completed'];

let token = null;
const orderId = document.getElementById('order-detail').dataset.id;

async function load() {
    const wrap = document.getElementById('order-detail');
    try {
        const o = await CuisineriaAPI.adminGetOrder(token, orderId);

        wrap.innerHTML = `
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl border border-espresso/5 p-6">
                    <div class="flex items-center justify-between mb-5">
                        <div>
                            <p class="font-display text-2xl text-terracotta">${o.order_code}</p>
                            <p class="text-xs text-muted mt-1">${new Date(o.created_at).toLocaleString('id-ID')} &middot; ${o.source === 'offline' ? 'Dicatat manual (offline)' : 'Dipesan online'}</p>
                        </div>
                        ${statusBadge(o.status)}
                    </div>
                    <table class="w-full text-sm">
                        <thead><tr class="text-left text-muted border-b border-espresso/10">
                            <th class="py-2">Produk</th><th class="py-2">Qty</th><th class="py-2">Harga</th><th class="py-2 text-right">Subtotal</th>
                        </tr></thead>
                        <tbody>
                            ${(o.items || []).map(i => `
                                <tr class="border-b border-espresso/5">
                                    <td class="py-2.5">${i.product_name} ${i.is_preorder ? `<span class="text-[11px] text-terracotta">(Pre-Order &middot; ambil ${i.preorder_date || '-'})</span>` : ''}</td>
                                    <td class="py-2.5">${i.qty}</td>
                                    <td class="py-2.5">Rp ${Number(i.price).toLocaleString('id-ID')}</td>
                                    <td class="py-2.5 text-right">Rp ${Number(i.price * i.qty).toLocaleString('id-ID')}</td>
                                </tr>`).join('')}
                        </tbody>
                    </table>
                    <div class="flex justify-between text-sm mt-4 pt-4 border-t border-espresso/10">
                        <span class="text-muted">Biaya antar</span><span>Rp ${Number(o.delivery_fee || 0).toLocaleString('id-ID')}</span>
                    </div>
                    <div class="flex justify-between font-medium mt-1">
                        <span>Total</span><span>Rp ${Number(o.total).toLocaleString('id-ID')}</span>
                    </div>
                    ${o.notes ? `<p class="text-sm text-muted mt-4 bg-cream rounded-xl p-3">Catatan: ${o.notes}</p>` : ''}
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white rounded-2xl border border-espresso/5 p-6">
                    <h2 class="font-medium mb-4">Data Pelanggan</h2>
                    <p class="text-sm"><span class="text-muted">Nama:</span> ${o.customer_name}</p>
                    <p class="text-sm mt-1"><span class="text-muted">Telpon:</span> ${o.customer_phone}</p>
                    <p class="text-sm mt-1"><span class="text-muted">Metode:</span> ${o.fulfillment_method === 'delivery' ? 'Diantar' : 'Ambil di toko'}</p>
                    ${o.fulfillment_method === 'delivery' ? `<p class="text-sm mt-1"><span class="text-muted">Alamat:</span> ${o.address_detail || '-'}</p>
                    ${o.latitude ? `<a class="text-xs text-terracotta underline" target="_blank" href="https://www.google.com/maps?q=${o.latitude},${o.longitude}">Lihat titik lokasi di peta</a>` : ''}` : ''}
                </div>

                <div class="bg-white rounded-2xl border border-espresso/5 p-6">
                    <h2 class="font-medium mb-4">Proses Pesanan</h2>
                    <div class="flex flex-col gap-2">
                        ${o.status !== 'cancelled' && o.status !== 'completed' ? `
                        <button onclick="updateStatus('${NEXT_STATUS[o.status] || 'completed'}')" class="bg-terracotta hover:bg-terracotta-dark text-cream py-2.5 rounded-full text-sm font-medium">
                            Tandai: ${(STATUS_META_LABEL(NEXT_STATUS[o.status] || 'completed'))}
                        </button>` : ''}
                        ${o.status !== 'cancelled' && o.status !== 'completed' ? `
                        <button onclick="updateStatus('cancelled')" class="border border-red-200 text-red-600 py-2.5 rounded-full text-sm font-medium">Batalkan Pesanan</button>` : ''}
                        <button onclick="removeOrder()" class="text-muted hover:text-red-600 text-xs mt-2">Hapus pesanan ini</button>
                    </div>
                </div>
            </div>
        `;
    } catch (e) {
        wrap.innerHTML = `<p class="text-muted col-span-full">Gagal memuat pesanan (${e.message}).</p>`;
    }
}

function STATUS_META_LABEL(status) {
    return { confirmed: 'Dikonfirmasi', processing: 'Diproses', ready: 'Siap', completed: 'Selesai' }[status] || status;
}

async function updateStatus(status) {
    try {
        await CuisineriaAPI.adminUpdateOrderStatus(token, orderId, status);
        load();
    } catch (e) { alert(e.message); }
}

async function removeOrder() {
    if (!confirm('Hapus pesanan ini secara permanen?')) return;
    try {
        await CuisineriaAPI.adminDeleteOrder(token, orderId);
        window.location.href = '/admin/pesanan';
    } catch (e) { alert(e.message); }
}

document.addEventListener('DOMContentLoaded', () => {
    token = requireAdminAuth();
    if (!token) return;
    load();
});
</script>
@endpush

@extends('layouts.admin')
@section('title', 'Catat Pesanan Offline - Admin Cuisineria')
@section('page-title', 'Catat Pesanan Offline')

@section('content')
<p class="text-muted text-sm mb-6 max-w-2xl">Gunakan formulir ini untuk mencatat pesanan yang masuk lewat telpon atau datang langsung ke toko, supaya tetap tercatat di sistem yang sama dengan pesanan online.</p>

<div class="grid lg:grid-cols-3 gap-6">
    <form id="offline-form" class="lg:col-span-2 bg-white rounded-2xl border border-espresso/5 p-6 space-y-5">
        <div class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium block mb-1.5">Nama pelanggan</label>
                <input name="customer_name" required class="w-full border border-espresso/15 rounded-xl px-4 py-2.5 outline-none focus:border-terracotta">
            </div>
            <div>
                <label class="text-sm font-medium block mb-1.5">Nomor telpon</label>
                <input name="customer_phone" required class="w-full border border-espresso/15 rounded-xl px-4 py-2.5 outline-none focus:border-terracotta">
            </div>
        </div>

        <div>
            <label class="text-sm font-medium block mb-1.5">Metode</label>
            <select name="fulfillment_method" id="fulfillment_method" class="w-full border border-espresso/15 rounded-xl px-4 py-2.5 outline-none focus:border-terracotta">
                <option value="pickup">Ambil di toko</option>
                <option value="delivery">Diantar</option>
            </select>
        </div>

        <div id="offline-address-field" class="hidden">
            <label class="text-sm font-medium block mb-1.5">Alamat pengantaran</label>
            <textarea name="address_detail" rows="2" class="w-full border border-espresso/15 rounded-xl px-4 py-2.5 outline-none focus:border-terracotta"></textarea>
        </div>

        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="text-sm font-medium">Item Pesanan</label>
                <button type="button" onclick="addItemRow()" class="text-terracotta text-sm font-medium">+ Tambah item</button>
            </div>
            <div id="item-rows" class="space-y-3"></div>
        </div>

        <div>
            <label class="text-sm font-medium block mb-1.5">Catatan</label>
            <textarea name="notes" rows="2" class="w-full border border-espresso/15 rounded-xl px-4 py-2.5 outline-none focus:border-terracotta"></textarea>
        </div>

        <button type="submit" class="w-full bg-terracotta hover:bg-terracotta-dark text-cream py-3.5 rounded-full font-medium">Simpan Pesanan</button>
        <p id="offline-error" class="text-sm text-red-600 hidden"></p>
    </form>

    <div class="bg-panel rounded-2xl p-6 border border-espresso/10 h-fit">
        <h2 class="font-medium mb-4">Ringkasan</h2>
        <p class="text-sm text-muted mb-2">Total item: <span id="summary-count">0</span></p>
        <p class="text-lg font-semibold">Rp <span id="summary-total">0</span></p>
    </div>
</div>
@endsection

@push('scripts')
<script>
let token = null;
let products = [];
let rowCount = 0;

function addItemRow(selected = null, qty = 1) {
    rowCount++;
    const id = `row-${rowCount}`;
    const wrap = document.getElementById('item-rows');
    const div = document.createElement('div');
    div.className = 'flex gap-3 items-center';
    div.id = id;
    div.innerHTML = `
        <select class="item-product flex-1 border border-espresso/15 rounded-xl px-3 py-2 text-sm">
            ${products.map(p => `<option value="${p.id}" data-price="${p.price}" data-preorder="${p.is_preorder}" data-stock="${p.stock}" ${p.stock <= 0 ? 'disabled' : ''}>${p.name} — Rp ${Number(p.price).toLocaleString('id-ID')} (stok: ${p.stock})</option>`).join('')}
        </select>
        <input type="number" min="1" max="${products.find(p => p.id === selected)?.stock ?? ''}" value="${qty}" class="item-qty w-20 border border-espresso/15 rounded-xl px-3 py-2 text-sm">
        <button type="button" onclick="document.getElementById('${id}').remove(); updateSummary();" class="text-red-600 text-xs">Hapus</button>
    `;
    wrap.appendChild(div);
    div.querySelector('.item-qty').addEventListener('input', updateSummary);
    div.querySelector('.item-product').addEventListener('change', (e) => {
        div.querySelector('.item-qty').max = e.target.selectedOptions[0]?.dataset.stock ?? '';
        updateSummary();
    });
    updateSummary();
}

function updateSummary() {
    const rows = document.querySelectorAll('#item-rows > div');
    let count = 0, total = 0;
    rows.forEach(r => {
        const qty = parseInt(r.querySelector('.item-qty').value || 0);
        const opt = r.querySelector('.item-product').selectedOptions[0];
        const price = opt ? Number(opt.dataset.price) : 0;
        count += qty;
        total += qty * price;
    });
    document.getElementById('summary-count').innerText = count;
    document.getElementById('summary-total').innerText = total.toLocaleString('id-ID');
}

document.getElementById('fulfillment_method').addEventListener('change', (e) => {
    document.getElementById('offline-address-field').classList.toggle('hidden', e.target.value !== 'delivery');
});

document.addEventListener('DOMContentLoaded', async () => {
    token = requireAdminAuth();
    if (!token) return;
    try {
        const res = await CuisineriaAPI.getProducts();
        products = res.data || res;
        addItemRow();
    } catch (e) {
        document.getElementById('offline-error').innerText = 'Gagal memuat daftar produk: ' + e.message;
        document.getElementById('offline-error').classList.remove('hidden');
    }

    document.getElementById('offline-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        const rows = document.querySelectorAll('#item-rows > div');
        const items = Array.from(rows).map(r => {
            const opt = r.querySelector('.item-product').selectedOptions[0];
            return {
                product_id: Number(opt.value),
                qty: parseInt(r.querySelector('.item-qty').value || 1),
                price: Number(opt.dataset.price),
                is_preorder: opt.dataset.preorder === 'true',
            };
        });

        const payload = {
            customer_name: fd.get('customer_name'),
            customer_phone: fd.get('customer_phone'),
            fulfillment_method: fd.get('fulfillment_method'),
            address_detail: fd.get('address_detail') || null,
            notes: fd.get('notes') || null,
            source: 'offline',
            items,
        };

        try {
            const order = await CuisineriaAPI.adminCreateOfflineOrder(token, payload);
            window.location.href = `/admin/pesanan/${order.id}`;
        } catch (err) {
            const errorEl = document.getElementById('offline-error');
            errorEl.innerText = err.message;
            errorEl.classList.remove('hidden');
        }
    });
});
</script>
@endpush

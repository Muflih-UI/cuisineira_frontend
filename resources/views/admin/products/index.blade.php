@extends('layouts.admin')
@section('title', 'Produk - Admin Cuisineria')
@section('page-title', 'Kelola Produk')

@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-muted text-sm">Tambah, ubah, atau hapus menu kue. Tandai "Pre-Order" untuk produk yang perlu dipesan H-1.</p>
    <button onclick="openForm()" class="bg-terracotta hover:bg-terracotta-dark text-cream px-5 py-2.5 rounded-full text-sm font-medium transition">+ Tambah Produk</button>
</div>

<div class="bg-white rounded-2xl border border-espresso/5 overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-muted border-b border-espresso/10 bg-cream/60">
                <th class="py-3 px-5">Produk</th>
                <th class="py-3 px-5">Harga</th>
                <th class="py-3 px-5">Stok</th>
                <th class="py-3 px-5">Tipe</th>
                <th class="py-3 px-5">Status</th>
                <th class="py-3 px-5"></th>
            </tr>
        </thead>
        <tbody id="product-rows">
            <tr><td colspan="6" class="py-8 text-center text-muted">Memuat produk...</td></tr>
        </tbody>
    </table>
</div>

<!-- Modal form -->
<div id="product-modal" class="fixed inset-0 bg-espresso/40 hidden items-center justify-center z-50 p-4">
    <div class="bg-cream rounded-2xl w-full max-w-lg p-7">
        <h2 id="modal-title" class="font-display text-2xl mb-5">Tambah Produk</h2>
        <form id="product-form" class="space-y-4">
            <input type="hidden" name="id">
            <div>
                <label class="text-sm font-medium block mb-1.5">Nama produk</label>
                <input name="name" required class="w-full border border-espresso/15 rounded-xl px-4 py-2.5 outline-none focus:border-terracotta">
            </div>
            <div>
                <label class="text-sm font-medium block mb-1.5">Deskripsi</label>
                <textarea name="description" rows="2" class="w-full border border-espresso/15 rounded-xl px-4 py-2.5 outline-none focus:border-terracotta"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-medium block mb-1.5">Harga (Rp)</label>
                    <input name="price" type="number" min="0" required class="w-full border border-espresso/15 rounded-xl px-4 py-2.5 outline-none focus:border-terracotta">
                </div>
                <div>
                    <label class="text-sm font-medium block mb-1.5">Stok</label>
                    <input name="stock" type="number" min="0" required class="w-full border border-espresso/15 rounded-xl px-4 py-2.5 outline-none focus:border-terracotta">
                    <p class="text-xs text-muted mt-1">0 = habis, tombol beli otomatis terkunci di toko.</p>
                </div>
            </div>
            <div>
                <label class="text-sm font-medium block mb-1.5">Gambar produk</label>
                <input name="image" type="file" accept="image/*" id="image-input"
                       class="w-full border border-espresso/15 rounded-xl px-4 py-2.5 outline-none focus:border-terracotta text-sm">
                <p class="text-xs text-muted mt-1">Maks 2MB. Kosongkan saat mengubah produk jika tidak ingin ganti gambar.</p>
                <img id="image-preview" class="hidden mt-2 w-20 h-20 rounded-lg object-cover border border-espresso/10">
            </div>
            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_preorder" class="rounded"> Produk Pre-Order</label>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" checked class="rounded"> Aktif / tampil di toko</label>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="closeForm()" class="flex-1 border border-espresso/15 py-3 rounded-full font-medium">Batal</button>
                <button type="submit" class="flex-1 bg-terracotta hover:bg-terracotta-dark text-cream py-3 rounded-full font-medium">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
let token = null;
let products = [];

function openForm(product = null) {
    document.getElementById('product-modal').classList.remove('hidden');
    document.getElementById('product-modal').classList.add('flex');
    const form = document.getElementById('product-form');
    form.reset();
    const preview = document.getElementById('image-preview');
    document.getElementById('image-input').required = !product; // wajib hanya saat tambah baru
    document.getElementById('modal-title').innerText = product ? 'Ubah Produk' : 'Tambah Produk';
    if (product) {
        form.id.value = product.id;
        form.name.value = product.name;
        form.description.value = product.description || '';
        form.price.value = product.price;
        form.stock.value = product.stock;
        // Input type="file" tidak bisa diisi lewat JS (dibatasi browser) -> tampilkan preview saja.
        preview.src = product.image;
        preview.classList.remove('hidden');
        form.is_preorder.checked = !!product.is_preorder;
        form.is_active.checked = product.is_active !== false;
    } else {
        preview.classList.add('hidden');
    }
}
function closeForm() {
    document.getElementById('product-modal').classList.add('hidden');
    document.getElementById('product-modal').classList.remove('flex');
}

async function loadProducts() {
    const rows = document.getElementById('product-rows');
    try {
        const res = await CuisineriaAPI.adminGetProducts(token);
        products = res.data || res;
        rows.innerHTML = products.map(p => `
            <tr class="border-b border-espresso/5">
                <td class="py-3 px-5 flex items-center gap-3">
                    <img src="${p.image}" class="w-10 h-10 rounded-lg object-cover">
                    <span class="font-medium">${p.name}</span>
                </td>
                <td class="py-3 px-5">Rp ${Number(p.price).toLocaleString('id-ID')}</td>
                <td class="py-3 px-5">
                    ${p.stock <= 0
                        ? '<span class="text-xs font-medium text-red-600">Habis</span>'
                        : p.stock < 5
                            ? `<span class="text-xs font-medium text-amber-600">${p.stock} (menipis)</span>`
                            : `<span class="text-xs">${p.stock}</span>`}
                </td>
                <td class="py-3 px-5">${p.is_preorder ? '<span class="text-xs text-terracotta bg-terracotta/10 rounded-full px-2 py-0.5">Pre-Order</span>' : '<span class="text-xs text-muted">Reguler</span>'}</td>
                <td class="py-3 px-5">${p.is_active !== false ? '<span class="text-xs text-sage">Aktif</span>' : '<span class="text-xs text-muted">Nonaktif</span>'}</td>
                <td class="py-3 px-5 text-right space-x-3">
                    <button onclick='openForm(${JSON.stringify(p)})' class="text-terracotta text-xs font-medium">Ubah</button>
                    <button onclick="deleteProduct(${p.id})" class="text-red-600 text-xs font-medium">Hapus</button>
                </td>
            </tr>
        `).join('') || '<tr><td colspan="6" class="py-8 text-center text-muted">Belum ada produk.</td></tr>';
    } catch (e) {
        rows.innerHTML = `<tr><td colspan="6" class="py-8 text-center text-muted">Gagal memuat (${e.message}).</td></tr>`;
    }
}

async function deleteProduct(id) {
    if (!confirm('Hapus produk ini?')) return;
    try {
        await CuisineriaAPI.adminDeleteProduct(token, id);
        loadProducts();
    } catch (e) { alert(e.message); }
}

document.addEventListener('DOMContentLoaded', () => {
    token = requireAdminAuth();
    if (!token) return;
    loadProducts();

    document.getElementById('product-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        // Checkbox yang tidak dicentang tidak ikut terkirim oleh FormData secara default,
        // jadi tulis ulang jadi '1'/'0' eksplisit supaya backend selalu menerima nilainya.
        fd.set('is_preorder', fd.get('is_preorder') === 'on' ? '1' : '0');
        fd.set('is_active', fd.get('is_active') === 'on' ? '1' : '0');
        // Kalau tidak upload gambar baru (mode ubah), buang field image kosong
        // supaya backend tidak menganggap ada file yang dikirim.
        const imageFile = fd.get('image');
        if (!imageFile || imageFile.size === 0) fd.delete('image');

        const id = fd.get('id');
        fd.delete('id');

        try {
            if (id) await CuisineriaAPI.adminUpdateProduct(token, id, fd);
            else await CuisineriaAPI.adminCreateProduct(token, fd);
            closeForm();
            loadProducts();
        } catch (err) { alert(err.message); }
    });
});
</script>
@endpush

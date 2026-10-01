@extends('layouts.customer')
@section('title', 'Produk - Cuisineria')

@section('content')
<section class="max-w-7xl mx-auto px-4 sm:px-6 py-14">
    <h1 class="font-display text-4xl mb-2">Semua Produk</h1>
    <p class="text-muted mb-8">Kue reguler tersedia langsung. Kue bertanda "Pre-Order" perlu tanggal pengambilan minimal H-1.</p>

    <div class="flex flex-wrap items-center gap-3 mb-10" x-data="{ filter: 'all' }">
        <button @click="filter='all'" :class="filter==='all' ? 'bg-terracotta text-cream' : 'bg-white text-espresso border border-espresso/10'"
                class="px-4 py-2 rounded-full text-sm font-medium transition">Semua</button>
        <button @click="filter='reguler'" :class="filter==='reguler' ? 'bg-terracotta text-cream' : 'bg-white text-espresso border border-espresso/10'"
                class="px-4 py-2 rounded-full text-sm font-medium transition">Reguler</button>
        <button @click="filter='preorder'" :class="filter==='preorder' ? 'bg-terracotta text-cream' : 'bg-white text-espresso border border-espresso/10'"
                class="px-4 py-2 rounded-full text-sm font-medium transition">Pre-Order</button>
    </div>

    <div id="product-grid" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-7">
        <p class="text-muted text-sm col-span-full">Memuat produk...</p>
    </div>
</section>
@endsection

@push('scripts')
<script>
let allProducts = [];

function addToCart(product) {
    const before = Cart.getAll().find(i => i.id === product.id)?.qty || 0;
    const after = Cart.add(product, 1);
    if (after === before) {
        alert(`Maaf, sisa stok "${product.name}" hanya ${product.stock} dan semuanya sudah ada di keranjang Anda.`);
    }
}

function renderProducts(list) {
    const grid = document.getElementById('product-grid');
    if (!list.length) {
        grid.innerHTML = '<p class="text-muted text-sm col-span-full">Belum ada produk pada kategori ini.</p>';
        return;
    }
    grid.innerHTML = list.map(p => `
        <div class="bg-white rounded-2xl overflow-hidden border border-espresso/5 flex flex-col">
            <a href="/produk/${p.id}" class="aspect-[4/3] overflow-hidden block">
                <img src="${p.image}" alt="${p.name}" class="w-full h-full object-cover hover:scale-105 transition duration-500">
            </a>
            <div class="p-5 flex flex-col flex-1">
                ${p.is_preorder ? '<span class="text-[11px] w-fit font-medium text-terracotta bg-terracotta/10 rounded-full px-2 py-0.5 mb-2">Pre-Order</span>' : ''}
                <a href="/produk/${p.id}" class="font-medium hover:text-terracotta">${p.name}</a>
                <p class="text-sm text-muted mt-1 line-clamp-2">${p.description || ''}</p>
                <p class="text-xs mt-2 ${p.stock <= 0 ? 'text-red-600 font-medium' : p.stock < 5 ? 'text-amber-600' : 'text-muted'}">
                    ${p.stock <= 0 ? 'Stok habis' : `Sisa stok: ${p.stock}`}
                </p>
                <div class="flex items-center justify-between mt-4">
                    <p class="text-terracotta font-semibold">Rp ${Number(p.price).toLocaleString('id-ID')}</p>
                    ${p.stock <= 0
                        ? `<button disabled class="bg-espresso/20 text-espresso/40 text-sm px-4 py-2 rounded-full cursor-not-allowed">Stok Habis</button>`
                        : `<button onclick='addToCart(${JSON.stringify({ id: p.id, name: p.name, price: p.price, image: p.image, is_preorder: !!p.is_preorder, stock: p.stock })})'
                            class="bg-espresso text-cream text-sm px-4 py-2 rounded-full hover:bg-terracotta transition">
                        + Keranjang
                    </button>`}
                </div>
            </div>
        </div>
    `).join('');
}

document.addEventListener('DOMContentLoaded', async () => {
    try {
        const res = await CuisineriaAPI.getProducts();
        allProducts = res.data || res;
        renderProducts(allProducts);
    } catch (e) {
        document.getElementById('product-grid').innerHTML =
            `<p class="text-sm text-muted col-span-full">Belum bisa memuat produk (${e.message}).</p>`;
    }
});

document.querySelectorAll('[x-data] button').forEach(btn => {
    btn.addEventListener('click', () => {
        const filter = btn.getAttribute('@click').includes("'all'") ? 'all'
            : btn.getAttribute('@click').includes("'reguler'") ? 'reguler' : 'preorder';
        if (filter === 'all') renderProducts(allProducts);
        else if (filter === 'reguler') renderProducts(allProducts.filter(p => !p.is_preorder));
        else renderProducts(allProducts.filter(p => p.is_preorder));
    });
});
</script>
@endpush

@extends('layouts.customer')
@section('title', 'Keranjang - Cuisineria')

@section('content')
<section class="max-w-5xl mx-auto px-4 sm:px-6 py-14">
    <h1 class="font-display text-4xl mb-8">Keranjang Anda</h1>

    <div id="cart-items" class="space-y-4 mb-8"></div>

    <div id="cart-summary" class="bg-white rounded-2xl border border-espresso/5 p-6 hidden">
        <div class="flex items-center justify-between mb-2">
            <p class="text-muted">Subtotal</p>
            <p id="cart-subtotal" class="font-semibold text-lg"></p>
        </div>
        <p class="text-xs text-muted mb-6">Biaya antar (jika dipilih) dihitung di halaman checkout.</p>
        <a href="{{ url('/checkout') }}" class="block text-center bg-terracotta hover:bg-terracotta-dark text-cream py-3.5 rounded-full font-medium transition">
            Lanjut ke Checkout
        </a>
    </div>

    <div id="cart-empty" class="hidden text-center py-20">
        <p class="text-muted mb-6">Keranjang Anda masih kosong.</p>
        <a href="{{ url('/produk') }}" class="inline-flex bg-espresso text-cream px-6 py-3 rounded-full font-medium hover:bg-terracotta transition">Mulai Belanja</a>
    </div>
</section>
@endsection

@push('scripts')
<script>
function renderCart() {
    const items = Cart.getAll();
    const wrap = document.getElementById('cart-items');
    const empty = document.getElementById('cart-empty');
    const summary = document.getElementById('cart-summary');

    if (!items.length) {
        wrap.innerHTML = '';
        empty.classList.remove('hidden');
        summary.classList.add('hidden');
        return;
    }
    empty.classList.add('hidden');
    summary.classList.remove('hidden');

    wrap.innerHTML = items.map(i => `
        <div class="bg-white rounded-2xl border border-espresso/5 p-4 flex items-center gap-4">
            <img src="${i.image}" class="w-20 h-20 rounded-xl object-cover flex-shrink-0" alt="${i.name}">
            <div class="flex-1 min-w-0">
                <p class="font-medium truncate">${i.name}</p>
                ${i.is_preorder ? `<p class="text-xs text-terracotta mt-0.5">Pre-Order &middot; ambil ${i.preorder_date || '-'}</p>` : ''}
                <p class="text-terracotta font-semibold mt-1">Rp ${Number(i.price).toLocaleString('id-ID')}</p>
                ${i.stock != null && i.qty >= i.stock ? `<p class="text-xs text-amber-600 mt-0.5">Sudah mencapai sisa stok (${i.stock})</p>` : ''}
            </div>
            <div class="flex items-center border border-espresso/15 rounded-full overflow-hidden">
                <button class="w-8 h-8 hover:bg-cream" onclick="Cart.updateQty(${i.id}, ${i.qty - 1}); renderCart();">-</button>
                <span class="w-8 text-center text-sm">${i.qty}</span>
                <button class="w-8 h-8 hover:bg-cream disabled:opacity-30 disabled:cursor-not-allowed"
                        ${i.stock != null && i.qty >= i.stock ? 'disabled' : ''}
                        onclick="Cart.updateQty(${i.id}, ${i.qty + 1}); renderCart();">+</button>
            </div>
            <button onclick="Cart.remove(${i.id}); renderCart();" class="text-muted hover:text-terracotta text-sm">Hapus</button>
        </div>
    `).join('');

    document.getElementById('cart-subtotal').innerText = `Rp ${Cart.subtotal().toLocaleString('id-ID')}`;
}

document.addEventListener('DOMContentLoaded', async () => {
    renderCart();
    // Segarkan data (harga, status pre-order, ketersediaan) dari server —
    // lihat cart.js -> syncWithProducts untuk alasannya.
    try {
        const res = await CuisineriaAPI.getProducts();
        const { removed, changed } = Cart.syncWithProducts(res.data || res);
        if (removed.length || changed) renderCart();
    } catch (e) { /* Kalau gagal sync, tetap tampilkan data lama di keranjang. */ }
});
</script>
@endpush

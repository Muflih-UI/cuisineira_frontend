@extends('layouts.customer')
@section('title', 'Detail Produk - Cuisineria')

@section('content')
<section class="max-w-6xl mx-auto px-4 sm:px-6 py-14" id="product-detail" data-id="{{ $id ?? '' }}">
    <div id="product-content" class="grid md:grid-cols-2 gap-12">
        <p class="text-muted col-span-full">Memuat produk...</p>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const wrap = document.getElementById('product-detail');
    const id = wrap.dataset.id;
    const content = document.getElementById('product-content');

    try {
        const p = await CuisineriaAPI.getProduct(id);

        content.innerHTML = `
            <div class="aspect-square rounded-3xl overflow-hidden bg-white border border-espresso/5">
                <img src="${p.image}" alt="${p.name}" class="w-full h-full object-cover">
            </div>
            <div>
                ${p.is_preorder ? '<span class="text-xs font-medium text-terracotta bg-terracotta/10 rounded-full px-3 py-1">Pre-Order &mdash; pesan minimal H-1</span>' : ''}
                <h1 class="font-display text-4xl mt-4 mb-3">${p.name}</h1>
                <p class="text-muted leading-relaxed mb-4">${p.description || ''}</p>
                <p class="text-sm mb-2 ${p.stock <= 0 ? 'text-red-600 font-medium' : p.stock < 5 ? 'text-amber-600' : 'text-muted'}">
                    ${p.stock <= 0 ? 'Stok habis' : `Sisa stok: ${p.stock}`}
                </p>
                <p class="text-terracotta font-display text-3xl mb-8">Rp ${Number(p.price).toLocaleString('id-ID')}</p>

                ${p.stock <= 0 ? `
                <button disabled class="w-full bg-espresso/20 text-espresso/40 py-3.5 rounded-full font-medium cursor-not-allowed">
                    Stok Habis
                </button>` : `
                <form id="add-form" class="space-y-5">
                    <div>
                        <label class="text-sm font-medium block mb-2">Jumlah</label>
                        <div class="inline-flex items-center border border-espresso/15 rounded-full overflow-hidden">
                            <button type="button" id="qty-minus" class="w-10 h-10 hover:bg-cream">-</button>
                            <input id="qty" type="number" value="1" min="1" max="${p.stock}" class="w-14 text-center border-x border-espresso/15 outline-none" />
                            <button type="button" id="qty-plus" class="w-10 h-10 hover:bg-cream">+</button>
                        </div>
                    </div>

                    ${p.is_preorder ? `
                    <div>
                        <label class="text-sm font-medium block mb-2">Tanggal pengambilan (minimal H-1)</label>
                        <input id="preorder-date" type="date" required
                               min="${new Date(Date.now() + 86400000).toISOString().split('T')[0]}"
                               class="w-full border border-espresso/15 rounded-xl px-4 py-3 outline-none focus:border-terracotta" />
                        <p class="text-xs text-muted mt-1.5">Produk ini wajib dipesan minimal 1 hari sebelum tanggal ambil.</p>
                    </div>` : ''}

                    <button type="submit" class="w-full bg-terracotta hover:bg-terracotta-dark text-cream py-3.5 rounded-full font-medium transition">
                        Tambah ke Keranjang
                    </button>
                </form>`}
            </div>
        `;

        if (p.stock <= 0) return; // Tidak ada form untuk dipasangi event listener.

        document.getElementById('qty-minus').onclick = () => {
            const q = document.getElementById('qty');
            q.value = Math.max(1, parseInt(q.value || 1) - 1);
        };
        document.getElementById('qty-plus').onclick = () => {
            const q = document.getElementById('qty');
            q.value = Math.min(p.stock, parseInt(q.value || 1) + 1);
        };

        document.getElementById('add-form').addEventListener('submit', (e) => {
            e.preventDefault();
            const qty = Math.min(p.stock, Math.max(1, parseInt(document.getElementById('qty').value || 1)));
            const dateInput = document.getElementById('preorder-date');
            if (p.is_preorder && (!dateInput || !dateInput.value)) {
                dateInput.reportValidity();
                return;
            }
            Cart.add({
                id: p.id,
                name: p.name,
                price: p.price,
                image: p.image,
                is_preorder: !!p.is_preorder,
                stock: p.stock,
                preorder_date: p.is_preorder ? dateInput.value : null,
            }, qty);
            window.location.href = '/keranjang';
        });

    } catch (e) {
        content.innerHTML = `<p class="text-sm text-muted col-span-full">Produk tidak dapat dimuat (${e.message}).</p>`;
    }
});
</script>
@endpush

@extends('layouts.customer')
@section('title', 'Checkout - Cuisineria')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>#map { height: 260px; border-radius: 1rem; }</style>
@endpush

@section('content')
<section class="max-w-6xl mx-auto px-4 sm:px-6 py-14">
    <h1 class="font-display text-4xl mb-8">Checkout</h1>

    <div class="grid lg:grid-cols-3 gap-10">
        <form id="checkout-form" class="lg:col-span-2 space-y-8">

            <div class="bg-white rounded-2xl border border-espresso/5 p-6">
                <h2 class="font-medium mb-5">Data Pemesan</h2>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="text-sm font-medium block mb-1.5">Nama lengkap</label>
                        <input name="customer_name" required class="w-full border border-espresso/15 rounded-xl px-4 py-3 outline-none focus:border-terracotta" placeholder="Nama Anda">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="text-sm font-medium block mb-1.5">Nomor telpon (WhatsApp)</label>
                        <input name="customer_phone" required type="tel" pattern="[0-9+ ]{8,15}" class="w-full border border-espresso/15 rounded-xl px-4 py-3 outline-none focus:border-terracotta" placeholder="08xxxxxxxxxx">
                        <p class="text-xs text-muted mt-1.5">Nomor ini dipakai untuk melacak status pesanan Anda nanti.</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl border border-espresso/5 p-6">
                <h2 class="font-medium mb-5">Metode Penerimaan</h2>
                <div class="grid sm:grid-cols-2 gap-4" id="fulfillment-options">
                    <label class="border border-espresso/15 rounded-xl p-4 cursor-pointer flex items-start gap-3 has-[:checked]:border-terracotta has-[:checked]:bg-terracotta/5">
                        <input type="radio" name="fulfillment" value="pickup" checked class="mt-1">
                        <span>
                            <span class="block font-medium">Ambil di toko</span>
                            <span class="block text-xs text-muted mt-0.5">Gratis &mdash; ambil langsung di outlet Cuisineria</span>
                        </span>
                    </label>
                    <label class="border border-espresso/15 rounded-xl p-4 cursor-pointer flex items-start gap-3 has-[:checked]:border-terracotta has-[:checked]:bg-terracotta/5">
                        <input type="radio" name="fulfillment" value="delivery" class="mt-1">
                        <span>
                            <span class="block font-medium">Diantar</span>
                            <span class="block text-xs text-muted mt-0.5">Biaya tambahan Rp <span id="delivery-fee-label">15.000</span></span>
                        </span>
                    </label>
                </div>
            </div>

            <div id="address-section" class="bg-white rounded-2xl border border-espresso/5 p-6">
                <h2 class="font-medium mb-2">Lokasi Pengantaran</h2>
                <p class="text-xs text-muted mb-4">Geser peta lalu ketuk titik lokasi Anda, atau gunakan lokasi saat ini.</p>
                <div id="map" class="mb-4"></div>
                <button type="button" id="use-my-location" class="text-sm text-terracotta font-medium mb-4">Gunakan lokasi saya saat ini</button>
                <label class="text-sm font-medium block mb-1.5">Detail alamat (patokan, nomor rumah, dll.)</label>
                <textarea name="address_detail" rows="2" class="w-full border border-espresso/15 rounded-xl px-4 py-3 outline-none focus:border-terracotta" placeholder="Contoh: Jl. Mawar No. 12, dekat minimarket"></textarea>
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">
            </div>

            <div id="preorder-section"></div>

            <div class="bg-white rounded-2xl border border-espresso/5 p-6">
                <h2 class="font-medium mb-2">Catatan Pesanan (opsional)</h2>
                <textarea name="notes" rows="3" class="w-full border border-espresso/15 rounded-xl px-4 py-3 outline-none focus:border-terracotta" placeholder="Contoh: tanpa kacang, tulis 'Happy Birthday' di kue"></textarea>
            </div>
        </form>

        <div>
            <div class="ticket-edge bg-panel border border-espresso/10 rounded-2xl p-6 sticky top-28">
                <h2 class="font-display text-xl mb-4">Ringkasan Pesanan</h2>
                <div id="summary-items" class="space-y-3 mb-4 max-h-64 overflow-y-auto"></div>
                <div class="ticket-divider my-4"></div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-muted">Subtotal</span><span id="sum-subtotal"></span></div>
                    <div class="flex justify-between"><span class="text-muted">Biaya antar</span><span id="sum-delivery">Rp 0</span></div>
                    <div class="flex justify-between font-semibold text-base pt-2"><span>Total</span><span id="sum-total"></span></div>
                </div>
                <button form="checkout-form" type="submit" id="submit-btn" class="w-full mt-6 bg-terracotta hover:bg-terracotta-dark text-cream py-3.5 rounded-full font-medium transition">
                    Bayar & Buat Pesanan
                </button>
                <p class="text-xs text-muted text-center mt-3">Anda akan diarahkan ke halaman pembayaran Midtrans (kartu, e-wallet, transfer bank, dll).</p>
                <p id="checkout-error" class="text-xs text-red-600 mt-3 hidden"></p>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
{{--
    Midtrans Snap.js — ganti data-client-key dengan Client Key akun Midtrans
    Anda (sandbox atau production). JANGAN pernah menaruh Server Key di sini,
    Server Key hanya boleh dipakai di backend.
    Sandbox: https://app.sandbox.midtrans.com/snap/snap.js
    Production: https://app.midtrans.com/snap/snap.js
--}}
<script
    src="https://app.sandbox.midtrans.com/snap/snap.js"
    data-client-key="Mid-client-wAEaoGCe_TO6VEtE">
</script>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const DELIVERY_FEE = 15000; // TODO: ambil dari API pengaturan toko saat backend tersedia
let map, marker;

function initMap() {
    map = L.map('map').setView([-1.2379, 116.8529], 13); // default: Balikpapan
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    const setMarker = (lat, lng) => {
        if (marker) marker.setLatLng([lat, lng]);
        else marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        document.getElementById('latitude').value = lat;
        document.getElementById('longitude').value = lng;
        marker.on('dragend', () => {
            const p = marker.getLatLng();
            document.getElementById('latitude').value = p.lat;
            document.getElementById('longitude').value = p.lng;
        });
    };

    map.on('click', (e) => setMarker(e.latlng.lat, e.latlng.lng));

    document.getElementById('use-my-location').addEventListener('click', () => {
        if (!navigator.geolocation) return;
        navigator.geolocation.getCurrentPosition((pos) => {
            const { latitude, longitude } = pos.coords;
            map.setView([latitude, longitude], 16);
            setMarker(latitude, longitude);
        });
    });
}

function renderSummary() {
    const items = Cart.getAll();
    document.getElementById('summary-items').innerHTML = items.map(i => `
        <div class="flex justify-between text-sm">
            <span class="text-espresso/80">${i.name} &times;${i.qty}</span>
            <span>Rp ${(i.price * i.qty).toLocaleString('id-ID')}</span>
        </div>
    `).join('') || '<p class="text-sm text-muted">Keranjang kosong.</p>';

    renderPreorderSection(items);

    const subtotal = Cart.subtotal();
    document.getElementById('sum-subtotal').innerText = `Rp ${subtotal.toLocaleString('id-ID')}`;
    updateTotal();
}

/**
 * Menampilkan satu input tanggal per produk pre-order di keranjang.
 * Ini satu-satunya tempat tanggal pengambilan diminta — baik yang
 * ditambahkan lewat halaman list produk maupun halaman detail produk,
 * semua diminta mengisi tanggalnya di sini sebelum bisa checkout.
 */
function renderPreorderSection(items) {
    const section = document.getElementById('preorder-section');
    const preorderItems = items.filter(i => i.is_preorder);

    if (!preorderItems.length) {
        section.innerHTML = '';
        return;
    }

    const minDate = new Date(Date.now() + 86400000).toISOString().split('T')[0]; // H-1

    section.innerHTML = `
        <div class="bg-white rounded-2xl border border-espresso/5 p-6">
            <h2 class="font-medium mb-1">Tanggal Pengambilan (Pre-Order)</h2>
            <p class="text-xs text-muted mb-4">Wajib diisi untuk produk pre-order, minimal H-1 dari sekarang.</p>
            <div class="space-y-4">
                ${preorderItems.map(i => `
                    <div>
                        <label class="text-sm font-medium block mb-1.5">${i.name}</label>
                        <input type="date" class="preorder-date-input w-full border border-espresso/15 rounded-xl px-4 py-2.5 outline-none focus:border-terracotta"
                               data-product-id="${i.id}" min="${minDate}" value="${i.preorder_date || ''}" required>
                    </div>
                `).join('')}
            </div>
        </div>
    `;

    // Simpan tanggal ke keranjang begitu diisi/diubah, supaya kalau pengguna
    // reload atau balik lagi ke checkout, tanggalnya tidak hilang.
    section.querySelectorAll('.preorder-date-input').forEach(input => {
        input.addEventListener('change', () => {
            Cart.setPreorderDate(Number(input.dataset.productId), input.value);
        });
    });
}

function updateTotal() {
    const subtotal = Cart.subtotal();
    const fulfillment = document.querySelector('input[name="fulfillment"]:checked').value;
    const deliveryFee = fulfillment === 'delivery' ? DELIVERY_FEE : 0;
    document.getElementById('sum-delivery').innerText = `Rp ${deliveryFee.toLocaleString('id-ID')}`;
    document.getElementById('sum-total').innerText = `Rp ${(subtotal + deliveryFee).toLocaleString('id-ID')}`;
    document.getElementById('address-section').classList.toggle('hidden', fulfillment !== 'delivery');
}

/**
 * Buka popup pembayaran Midtrans Snap. Dipanggil setelah pesanan berhasil
 * dibuat di backend dan backend mengembalikan snap_token.
 * Keranjang sudah dikosongkan sebelum ini dipanggil — pesanan sudah
 * tersimpan di database terlepas dari hasil pembayaran di popup ini.
 */
function openSnapPayment(snapToken, orderCode, phone) {
    const errorEl = document.getElementById('checkout-error');

    if (typeof snap === 'undefined') {
        // Script Snap.js gagal dimuat (mis. client key belum diisi) — tetap
        // arahkan ke halaman sukses supaya pesanan tidak "hilang" dari sisi pelanggan.
        window.location.href = `/pesanan-berhasil?kode=${orderCode}&phone=${encodeURIComponent(phone)}`;
        return;
    }

    snap.pay(snapToken, {
        onSuccess: function () {
            window.location.href = `/pesanan-berhasil?kode=${orderCode}&phone=${encodeURIComponent(phone)}`;
        },
        onPending: function () {
            // Pelanggan sudah memilih metode bayar & mendapat instruksi (mis. VA
            // transfer bank) -> arahkan ke halaman status untuk lihat instruksinya.
            window.location.href = `/pesanan-berhasil?kode=${orderCode}&phone=${encodeURIComponent(phone)}`;
        },
        onError: function () {
            // Pembayaran gagal diproses -> TETAP di checkout, jangan redirect.
            showPaymentRetry(errorEl, snapToken, orderCode, phone,
                'Pembayaran gagal diproses.');
        },
        onClose: function () {
            // Pelanggan menutup popup tanpa menyelesaikan pembayaran -> TETAP
            // di checkout, jangan redirect kemana pun. Pesanan sudah tersimpan
            // (status "menunggu pembayaran"), jadi cukup tawarkan buka lagi
            // popup yang sama -- tidak perlu submit ulang form dari awal.
            showPaymentRetry(errorEl, snapToken, orderCode, phone,
                'Anda menutup halaman pembayaran sebelum selesai.');
        },
    });
}

/**
 * Tampilkan pesan + tombol "Bayar Sekarang" di halaman checkout (bukan redirect),
 * supaya pelanggan yang belum menyelesaikan pembayaran tetap berada di checkout
 * dan bisa langsung coba bayar lagi tanpa mengulang isi form dari awal.
 */
function showPaymentRetry(errorEl, snapToken, orderCode, phone, message) {
    errorEl.innerHTML = `
        ${message} Pesanan Anda tetap tersimpan (kode <strong>${orderCode}</strong>) dan
        belum akan diproses sampai pembayaran selesai.
        <button id="retry-payment" type="button"
                class="block mt-2 underline font-medium">
            Bayar Sekarang &rarr;
        </button>
    `;
    errorEl.classList.remove('hidden');
    document.getElementById('retry-payment').onclick = () => openSnapPayment(snapToken, orderCode, phone);
}

document.addEventListener('DOMContentLoaded', async () => {
    if (!Cart.getAll().length) { window.location.href = '/produk'; return; }
    initMap();

    // Segarkan keranjang dari data produk terkini SEBELUM dirender, supaya
    // status pre-order & harga tidak basi (lihat cart.js -> syncWithProducts).
    try {
        const res = await CuisineriaAPI.getProducts();
        const { removed } = Cart.syncWithProducts(res.data || res);
        if (removed.length) {
            const names = removed.map(i => i.name).join(', ');
            document.getElementById('checkout-error').innerText =
                `${names} sudah tidak tersedia dan dihapus dari keranjang. Silakan periksa kembali pesanan Anda.`;
            document.getElementById('checkout-error').classList.remove('hidden');
        }
        if (!Cart.getAll().length) { window.location.href = '/produk'; return; }
    } catch (e) { /* Kalau gagal sync, lanjut pakai data lama di keranjang. */ }

    renderSummary();
    document.getElementById('fulfillment-options').addEventListener('change', updateTotal);

    document.getElementById('checkout-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const errorEl = document.getElementById('checkout-error');
        errorEl.classList.add('hidden');

        const fd = new FormData(e.target);
        const fulfillment = fd.get('fulfillment');

        if (fulfillment === 'delivery' && (!document.getElementById('latitude').value)) {
            errorEl.innerText = 'Silakan tandai lokasi pengantaran di peta.';
            errorEl.classList.remove('hidden');
            return;
        }

        // Pastikan semua input tanggal pre-order sudah terisi sebelum lanjut.
        const dateInputs = document.querySelectorAll('.preorder-date-input');
        for (const input of dateInputs) {
            if (!input.value) {
                errorEl.innerText = 'Mohon isi tanggal pengambilan untuk semua produk pre-order di keranjang.';
                errorEl.classList.remove('hidden');
                input.reportValidity();
                return;
            }
            // Sinkronkan ke keranjang sekali lagi (jaga-jaga kalau event 'change'
            // belum sempat terpicu, mis. isi lalu langsung klik tombol submit).
            Cart.setPreorderDate(Number(input.dataset.productId), input.value);
        }

        const cartItems = Cart.getAll();
        const payload = {
            customer_name: fd.get('customer_name'),
            customer_phone: fd.get('customer_phone'),
            fulfillment_method: fulfillment, // 'pickup' | 'delivery'
            address_detail: fd.get('address_detail') || null,
            latitude: document.getElementById('latitude').value || null,
            longitude: document.getElementById('longitude').value || null,
            notes: fd.get('notes') || null,
            delivery_fee: fulfillment === 'delivery' ? DELIVERY_FEE : 0,
            items: cartItems.map(i => ({
                product_id: i.id,
                qty: i.qty,
                price: i.price,
                is_preorder: i.is_preorder,
                preorder_date: i.preorder_date || null,
            })),
        };

        const btn = document.getElementById('submit-btn');
        btn.disabled = true;
        btn.innerText = 'Memproses...';

        try {
            const order = await CuisineriaAPI.createOrder(payload);
            Cart.clear();

            if (order.payment_error) {
                // Pesanan tersimpan tapi Midtrans gagal dibuat di sisi backend
                // (mis. Server Key salah). JANGAN langsung redirect — biarkan
                // pelanggan benar-benar membaca pesannya dulu, baru pilih lanjut.
                errorEl.innerHTML = `
                    ${order.payment_error}
                    <button id="continue-without-payment" type="button"
                            class="block mt-2 underline font-medium">
                        Lanjut ke halaman pesanan &rarr;
                    </button>
                `;
                errorEl.classList.remove('hidden');
                document.getElementById('continue-without-payment').onclick = () => {
                    window.location.href = `/pesanan-berhasil?kode=${order.order_code}&phone=${encodeURIComponent(payload.customer_phone)}`;
                };
                // Tombol TETAP nonaktif (bukan diaktifkan ulang) -- pesanan sudah
                // tersimpan & keranjang sudah kosong, submit ulang cuma bikin bingung.
                btn.innerText = 'Pesanan tersimpan';
                return;
            }

            if (order.snap_token) {
                // Pesanan sudah tersimpan di backend -- kunci tombol supaya tidak
                // memicu pembuatan pesanan duplikat kalau diklik lagi. Interaksi
                // pembayaran selanjutnya lewat popup Snap / tombol "Bayar Sekarang".
                btn.innerText = 'Pesanan tersimpan';
                openSnapPayment(order.snap_token, order.order_code, payload.customer_phone);
            } else {
                // Mode demo / backend belum mengirim snap_token — langsung ke halaman sukses.
                window.location.href = `/pesanan-berhasil?kode=${order.order_code || ''}&phone=${encodeURIComponent(payload.customer_phone)}`;
            }
        } catch (err) {
            errorEl.innerText = err.message || 'Gagal membuat pesanan. Coba lagi.';
            errorEl.classList.remove('hidden');
            btn.disabled = false;
            btn.innerText = 'Bayar & Buat Pesanan';
        }
    });
});
</script>
@endpush

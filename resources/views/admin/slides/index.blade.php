@extends('layouts.admin')
@section('title', 'Slideshow Beranda - Admin Cuisineria')
@section('page-title', 'Slideshow Beranda')

@section('content')
<div class="flex items-center justify-between mb-6">
    <p class="text-muted text-sm max-w-xl">Gambar di sini tampil bergantian di banner beranda. Urutan ditentukan angka "Urutan" (kecil tampil duluan). Nonaktifkan slide untuk menyembunyikan tanpa menghapus.</p>
    <button onclick="openForm()" class="bg-terracotta hover:bg-terracotta-dark text-cream px-5 py-2.5 rounded-full text-sm font-medium transition flex-shrink-0">+ Tambah Slide</button>
</div>

<div id="slide-grid" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
    <p class="text-muted text-sm col-span-full py-8 text-center">Memuat slide...</p>
</div>

<!-- Modal form -->
<div id="slide-modal" class="fixed inset-0 bg-espresso/40 hidden items-center justify-center z-50 p-4">
    <div class="bg-cream rounded-2xl w-full max-w-lg p-7">
        <h2 id="modal-title" class="font-display text-2xl mb-5">Tambah Slide</h2>
        <form id="slide-form" class="space-y-4">
            <input type="hidden" name="id">
            <div>
                <label class="text-sm font-medium block mb-1.5">Gambar slide</label>
                <input name="image" type="file" accept="image/*" id="slide-image-input"
                       class="w-full border border-espresso/15 rounded-xl px-4 py-2.5 outline-none focus:border-terracotta text-sm">
                <p class="text-xs text-muted mt-1">Maks 4MB. Kosongkan saat mengubah slide jika tidak ingin ganti gambar. Disarankan rasio potret (mis. 1200x1500px).</p>
                <img id="slide-image-preview" class="hidden mt-2 w-full h-32 rounded-lg object-cover border border-espresso/10">
            </div>
            <div>
                <label class="text-sm font-medium block mb-1.5">Caption (opsional)</label>
                <input name="caption" maxlength="255" placeholder="Contoh: Promo September — Diskon 15%" class="w-full border border-espresso/15 rounded-xl px-4 py-2.5 outline-none focus:border-terracotta">
            </div>
            <div>
                <label class="text-sm font-medium block mb-1.5">Urutan tampil</label>
                <input name="sort_order" type="number" min="0" value="0" class="w-full border border-espresso/15 rounded-xl px-4 py-2.5 outline-none focus:border-terracotta">
                <p class="text-xs text-muted mt-1">Angka lebih kecil tampil lebih dulu.</p>
            </div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" checked class="rounded"> Aktifkan slide ini</label>
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
let slides = [];

function openForm(slide = null) {
    document.getElementById('slide-modal').classList.remove('hidden');
    document.getElementById('slide-modal').classList.add('flex');
    const form = document.getElementById('slide-form');
    form.reset();
    const preview = document.getElementById('slide-image-preview');
    document.getElementById('slide-image-input').required = !slide; // wajib hanya saat tambah baru
    document.getElementById('modal-title').innerText = slide ? 'Ubah Slide' : 'Tambah Slide';
    if (slide) {
        form.id.value = slide.id;
        form.caption.value = slide.caption || '';
        form.sort_order.value = slide.sort_order;
        form.is_active.checked = !!slide.is_active;
        preview.src = slide.image;
        preview.classList.remove('hidden');
    } else {
        preview.classList.add('hidden');
    }
}
function closeForm() {
    document.getElementById('slide-modal').classList.add('hidden');
    document.getElementById('slide-modal').classList.remove('flex');
}

async function loadSlides() {
    const grid = document.getElementById('slide-grid');
    try {
        const res = await CuisineriaAPI.adminGetHeroSlides(token);
        slides = res.data || res;
        grid.innerHTML = slides.map(s => `
            <div class="bg-white rounded-2xl border border-espresso/5 overflow-hidden ${!s.is_active ? 'opacity-50' : ''}">
                <img src="${s.image}" class="w-full h-36 object-cover">
                <div class="p-4">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs text-muted">Urutan: ${s.sort_order}</span>
                        ${s.is_active ? '<span class="text-xs text-sage">Aktif</span>' : '<span class="text-xs text-muted">Nonaktif</span>'}
                    </div>
                    <p class="text-sm mb-3 truncate">${s.caption || '<span class="text-muted italic">Tanpa caption</span>'}</p>
                    <div class="flex gap-3">
                        <button onclick='openForm(${JSON.stringify(s)})' class="text-terracotta text-xs font-medium">Ubah</button>
                        <button onclick="deleteSlide(${s.id})" class="text-red-600 text-xs font-medium">Hapus</button>
                    </div>
                </div>
            </div>
        `).join('') || '<p class="text-muted text-sm col-span-full py-8 text-center">Belum ada slide. Tambahkan minimal 1 gambar supaya slideshow tampil.</p>';
    } catch (e) {
        grid.innerHTML = `<p class="text-muted text-sm col-span-full py-8 text-center">Gagal memuat (${e.message}).</p>`;
    }
}

async function deleteSlide(id) {
    if (!confirm('Hapus slide ini?')) return;
    try {
        await CuisineriaAPI.adminDeleteHeroSlide(token, id);
        loadSlides();
    } catch (e) { alert(e.message); }
}

document.addEventListener('DOMContentLoaded', () => {
    token = requireAdminAuth();
    if (!token) return;
    loadSlides();

    document.getElementById('slide-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        const fd = new FormData(e.target);
        fd.set('is_active', fd.get('is_active') === 'on' ? '1' : '0');

        const imageFile = fd.get('image');
        if (!imageFile || imageFile.size === 0) fd.delete('image');

        const id = fd.get('id');
        fd.delete('id');

        try {
            if (id) await CuisineriaAPI.adminUpdateHeroSlide(token, id, fd);
            else await CuisineriaAPI.adminCreateHeroSlide(token, fd);
            closeForm();
            loadSlides();
        } catch (err) { alert(err.message); }
    });
});
</script>
@endpush

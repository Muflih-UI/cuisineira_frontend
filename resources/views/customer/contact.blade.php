@extends('layouts.customer')
@section('title', 'Kontak - Cuisineria')

@section('content')
<section class="max-w-5xl mx-auto px-4 sm:px-6 py-16 grid md:grid-cols-2 gap-12">
    <div>
        <h1 class="font-display text-4xl mb-4">Hubungi Kami</h1>
        <p class="text-muted leading-relaxed mb-8">Ada pertanyaan soal pesanan, kustom kue, atau kerja sama? Kontak kami lewat salah satu kanal berikut.</p>

        <div class="space-y-5">
            <div class="flex items-start gap-4">
                <span class="w-10 h-10 rounded-full bg-terracotta/10 text-terracotta flex items-center justify-center flex-shrink-0">📞</span>
                <div>
                    <p class="font-medium">WhatsApp / Telpon</p>
                    <p class="text-muted text-sm">0812-3456-7890</p>
                </div>
            </div>
            <div class="flex items-start gap-4">
                <span class="w-10 h-10 rounded-full bg-terracotta/10 text-terracotta flex items-center justify-center flex-shrink-0">✉️</span>
                <div>
                    <p class="font-medium">Email</p>
                    <p class="text-muted text-sm">hello@cuisineria.id</p>
                </div>
            </div>
            <div class="flex items-start gap-4">
                <span class="w-10 h-10 rounded-full bg-terracotta/10 text-terracotta flex items-center justify-center flex-shrink-0">📍</span>
                <div>
                    <p class="font-medium">Alamat Toko</p>
                    <p class="text-muted text-sm">Jl. Jenderal Sudirman No. 10, Balikpapan</p>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-espresso/5 p-7">
        <h2 class="font-medium mb-5">Kirim Pesan</h2>
        <form id="contact-form" class="space-y-4">
            <input name="name" required placeholder="Nama" class="w-full border border-espresso/15 rounded-xl px-4 py-3 outline-none focus:border-terracotta">
            <input name="phone" required type="tel" placeholder="Nomor telpon" class="w-full border border-espresso/15 rounded-xl px-4 py-3 outline-none focus:border-terracotta">
            <textarea name="message" required rows="4" placeholder="Pesan Anda" class="w-full border border-espresso/15 rounded-xl px-4 py-3 outline-none focus:border-terracotta"></textarea>
            <button type="submit" id="contact-submit-btn" class="w-full bg-terracotta hover:bg-terracotta-dark text-cream py-3.5 rounded-full font-medium transition">Kirim</button>
            <p id="contact-success" class="text-sm text-sage text-center hidden">Pesan terkirim! Kami akan segera menghubungi Anda.</p>
            <p id="contact-error" class="text-sm text-red-600 text-center hidden"></p>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.getElementById('contact-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const fd = new FormData(form);
    const btn = document.getElementById('contact-submit-btn');
    const successEl = document.getElementById('contact-success');
    const errorEl = document.getElementById('contact-error');

    successEl.classList.add('hidden');
    errorEl.classList.add('hidden');
    btn.disabled = true;
    btn.innerText = 'Mengirim...';

    try {
        await CuisineriaAPI.sendContactMessage({
            name: fd.get('name'),
            phone: fd.get('phone'),
            message: fd.get('message'),
        });
        form.reset();
        successEl.classList.remove('hidden');
    } catch (err) {
        errorEl.innerText = err.message || 'Gagal mengirim pesan. Coba lagi.';
        errorEl.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        btn.innerText = 'Kirim';
    }
});
</script>
@endpush

@extends('layouts.admin')
@section('title', 'Pesan Kontak - Admin Cuisineria')
@section('page-title', 'Pesan Kontak')

@section('content')
<p class="text-muted text-sm mb-6">Pesan yang dikirim pelanggan lewat form di halaman Kontak. Klik pesan untuk menandainya sudah dibaca.</p>

<div id="message-list" class="space-y-3">
    <p class="text-muted text-sm text-center py-8">Memuat pesan...</p>
</div>
@endsection

@push('scripts')
<script>
let token = null;

function timeAgo(dateStr) {
    return new Date(dateStr).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
}

async function loadMessages() {
    const container = document.getElementById('message-list');
    try {
        const res = await CuisineriaAPI.adminListContactMessages(token);
        const list = res.data || res;

        container.innerHTML = list.length ? list.map(m => `
            <div class="bg-white rounded-2xl border ${m.is_read ? 'border-espresso/5' : 'border-terracotta/40'} p-5">
                <div class="flex items-start justify-between gap-4 mb-2">
                    <div>
                        <div class="flex items-center gap-2">
                            <p class="font-medium">${m.name}</p>
                            ${!m.is_read ? '<span class="text-[10px] bg-terracotta text-cream rounded-full px-2 py-0.5">Baru</span>' : ''}
                        </div>
                        <p class="text-xs text-muted mt-0.5">${m.phone} &middot; ${timeAgo(m.created_at)}</p>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        ${!m.is_read ? `<button onclick="markRead(${m.id})" class="text-terracotta text-xs font-medium">Tandai Dibaca</button>` : ''}
                        <button onclick="deleteMessage(${m.id})" class="text-red-600 text-xs font-medium">Hapus</button>
                    </div>
                </div>
                <p class="text-sm text-espresso/80 leading-relaxed">${m.message}</p>
            </div>
        `).join('') : '<p class="text-muted text-sm text-center py-8">Belum ada pesan masuk.</p>';
    } catch (e) {
        container.innerHTML = `<p class="text-muted text-sm text-center py-8">Gagal memuat pesan (${e.message}).</p>`;
    }
}

async function markRead(id) {
    try {
        await CuisineriaAPI.adminMarkContactMessageRead(token, id);
        loadMessages();
    } catch (e) { alert(e.message); }
}

async function deleteMessage(id) {
    if (!confirm('Hapus pesan ini?')) return;
    try {
        await CuisineriaAPI.adminDeleteContactMessage(token, id);
        loadMessages();
    } catch (e) { alert(e.message); }
}

document.addEventListener('DOMContentLoaded', () => {
    token = requireAdminAuth();
    if (!token) return;
    loadMessages();
});
</script>
@endpush

/**
 * Guards admin pages: redirects to /admin/login if no token is stored.
 * Call requireAdminAuth() at the top of every admin page script.
 */
function requireAdminAuth() {
    const token = localStorage.getItem('cuisineria_admin_token');
    if (!token) {
        window.location.href = '/admin/login';
        return null;
    }
    return token;
}

const STATUS_META = {
    pending:     { label: 'Menunggu Konfirmasi', color: 'bg-wheat/20 text-wheat' },
    confirmed:   { label: 'Dikonfirmasi',         color: 'bg-terracotta/15 text-terracotta' },
    processing:  { label: 'Sedang Diproses',      color: 'bg-terracotta/15 text-terracotta' },
    ready:       { label: 'Siap Diambil/Diantar', color: 'bg-sage/15 text-sage' },
    delivering:  { label: 'Sedang Diantar',       color: 'bg-sage/15 text-sage' },
    completed:   { label: 'Selesai',              color: 'bg-sage/20 text-sage' },
    cancelled:   { label: 'Dibatalkan',           color: 'bg-red-100 text-red-600' },
};

function statusBadge(status) {
    const m = STATUS_META[status] || { label: status, color: 'bg-espresso/10 text-espresso' };
    return `<span class="text-xs font-medium px-3 py-1 rounded-full ${m.color}">${m.label}</span>`;
}

async function adminLogout() {
    const token = localStorage.getItem('cuisineria_admin_token');
    try { await CuisineriaAPI.adminLogout(token); } catch (e) { /* ignore */ }
    localStorage.removeItem('cuisineria_admin_token');
    window.location.href = '/admin/login';
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[action="/admin/logout"]').forEach(f => {
        f.addEventListener('submit', (e) => { e.preventDefault(); adminLogout(); });
    });
});

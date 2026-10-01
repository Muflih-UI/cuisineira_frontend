/**
 * Cuisineria API client.
 *
 * The frontend and backend are decoupled (REST API architecture).
 * Change API_BASE_URL once the backend API is deployed — every call
 * in this file, and every page that uses CuisineriaAPI, will pick it up.
 */
const API_BASE_URL = window.CUISINERIA_API_BASE_URL || 'http://localhost:8000/api';

async function apiRequest(path, { method = 'GET', body, headers = {}, token } = {}) {
    const isFormData = body instanceof FormData;

    const finalHeaders = {
        'Accept': 'application/json',
        // Untuk FormData, JANGAN set Content-Type manual -> browser yang
        // menentukan boundary multipart-nya sendiri.
        ...(body && !isFormData ? { 'Content-Type': 'application/json' } : {}),
        ...headers,
    };
    if (token) finalHeaders['Authorization'] = `Bearer ${token}`;

    let finalMethod = method;
    let finalBody = body ? (isFormData ? body : JSON.stringify(body)) : undefined;

    // PHP tidak bisa parse body multipart/form-data pada request PUT/PATCH asli,
    // jadi untuk upload file kita kirim sebagai POST + field _method (method override
    // bawaan Laravel), route di backend tetap didefinisikan sebagai PUT/PATCH.
    if (isFormData && (method === 'PUT' || method === 'PATCH')) {
        body.append('_method', method);
        finalMethod = 'POST';
    }

    const res = await fetch(`${API_BASE_URL}${path}`, {
        method: finalMethod,
        headers: finalHeaders,
        body: finalBody,
    });

    let data = null;
    try { data = await res.json(); } catch (e) { /* empty body */ }

    if (!res.ok) {
        const message = (data && (data.message || data.error)) || `Request gagal (${res.status})`;
        throw new Error(message);
    }
    return data;
}

const CuisineriaAPI = {
    // ---- Produk (publik) ----
    getProducts: (params = {}) => {
        const qs = new URLSearchParams(params).toString();
        return apiRequest(`/products${qs ? `?${qs}` : ''}`);
    },
    // Produk dengan penjualan terbanyak 7 hari terakhir, untuk section
    // "Favorit pelanggan minggu ini" di beranda.
    getBestSellers: (limit = 4) => apiRequest(`/products/best-sellers?limit=${limit}`),
    getProduct: (id) => apiRequest(`/products/${id}`),

    // ---- Pesanan (publik, tanpa login) ----
    // createOrder sekarang bisa mengembalikan { snap_token, payment_error } untuk
    // memicu popup pembayaran Midtrans Snap di halaman checkout.
    createOrder: (payload) => apiRequest('/orders', { method: 'POST', body: payload }),

    // CHANGED: dulu GET /orders/track?phone=..., sekarang POST supaya nomor
    // telpon tidak tersimpan di URL history / server access log.
    // Status pembayaran ASLI dari backend (dicek ke Midtrans). Jangan andalkan
    // parameter URL untuk menentukan apakah pesanan sudah dibayar.
    getPaymentStatus: (orderCode, phone) =>
        apiRequest('/orders/payment-status', { method: 'POST', body: { order_code: orderCode, phone } }),
    trackOrder: (phone, orderCode = null) =>
        apiRequest('/orders/track', { method: 'POST', body: { phone, order_code: orderCode } }),

    // ---- Admin auth ----
    adminLogin: (email, password) =>
        apiRequest('/admin/login', { method: 'POST', body: { email, password } }),
    adminLogout: (token) => apiRequest('/admin/logout', { method: 'POST', token }),

    // ---- Admin: pesanan ----
    adminListOrders: (token, params = {}) => {
        const qs = new URLSearchParams(params).toString();
        return apiRequest(`/admin/orders${qs ? `?${qs}` : ''}`, { token });
    },
    adminGetOrder: (token, id) => apiRequest(`/admin/orders/${id}`, { token }),
    adminUpdateOrderStatus: (token, id, status) =>
        apiRequest(`/admin/orders/${id}/status`, { method: 'PATCH', body: { status }, token }),
    adminDeleteOrder: (token, id) => apiRequest(`/admin/orders/${id}`, { method: 'DELETE', token }),
    adminCreateOfflineOrder: (token, payload) =>
        apiRequest('/admin/orders/offline', { method: 'POST', body: payload, token }),

    // ---- Admin: produk ----
    // Beda dengan getProducts (publik, cuma produk aktif): ini mengembalikan
    // SEMUA produk termasuk yang nonaktif, supaya admin tetap bisa lihat &
    // kelola produk yang sedang "disembunyikan" dari toko.
    adminGetProducts: (token) => apiRequest('/admin/products', { token }),
    // payload di sini adalah FormData (berisi field teks + file gambar).
    adminCreateProduct: (token, formData) =>
        apiRequest('/admin/products', { method: 'POST', body: formData, token }),
    adminUpdateProduct: (token, id, formData) =>
        apiRequest(`/admin/products/${id}`, { method: 'PUT', body: formData, token }),
    adminDeleteProduct: (token, id) => apiRequest(`/admin/products/${id}`, { method: 'DELETE', token }),
    // ---- Kontak (publik) ----
    sendContactMessage: (payload) => apiRequest('/contact-messages', { method: 'POST', body: payload }),

    // ---- Slideshow beranda (publik) ----
    getHeroSlides: () => apiRequest('/hero-slides'),

    // ---- Admin: slideshow beranda ----
    adminGetHeroSlides: (token) => apiRequest('/admin/hero-slides', { token }),
    adminCreateHeroSlide: (token, formData) =>
        apiRequest('/admin/hero-slides', { method: 'POST', body: formData, token }),
    adminUpdateHeroSlide: (token, id, formData) =>
        apiRequest(`/admin/hero-slides/${id}`, { method: 'PUT', body: formData, token }),
    adminDeleteHeroSlide: (token, id) =>
        apiRequest(`/admin/hero-slides/${id}`, { method: 'DELETE', token }),

    // ---- Admin: pesan kontak ----
    adminListContactMessages: (token, params = {}) => {
        const qs = new URLSearchParams(params).toString();
        return apiRequest(`/admin/contact-messages${qs ? `?${qs}` : ''}`, { token });
    },
    adminMarkContactMessageRead: (token, id) =>
        apiRequest(`/admin/contact-messages/${id}/read`, { method: 'PATCH', token }),
    adminDeleteContactMessage: (token, id) =>
        apiRequest(`/admin/contact-messages/${id}`, { method: 'DELETE', token }),

};

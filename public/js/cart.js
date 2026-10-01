/**
 * Keranjang belanja disimpan di localStorage karena pelanggan tidak login.
 * Struktur item: { id, name, price, image, qty, is_preorder, stock }
 */
const CART_KEY = 'cuisineria_cart';

const Cart = {
    getAll() {
        try { return JSON.parse(localStorage.getItem(CART_KEY)) || []; }
        catch (e) { return []; }
    },

    save(items) {
        localStorage.setItem(CART_KEY, JSON.stringify(items));
        window.dispatchEvent(new CustomEvent('cart-updated', {
            detail: { count: items.reduce((sum, i) => sum + i.qty, 0), items }
        }));
    },

    // Mengembalikan qty final yang tersimpan (bisa lebih kecil dari yang
    // diminta kalau kena batas stok), supaya pemanggil bisa kasih tahu user.
    add(product, qty = 1) {
        const items = this.getAll();
        const existing = items.find(i => i.id === product.id);
        const stock = product.stock ?? existing?.stock ?? Infinity;

        if (existing) {
            existing.qty = Math.min(stock, existing.qty + qty);
            existing.stock = stock; // ikut segarkan batas stoknya
        } else {
            items.push({ ...product, qty: Math.min(stock, qty) });
        }
        this.save(items);
        return items.find(i => i.id === product.id).qty;
    },

    updateQty(id, qty) {
        let items = this.getAll();
        if (qty <= 0) {
            items = items.filter(i => i.id !== id);
        } else {
            items = items.map(i => i.id === id ? { ...i, qty: Math.min(qty, i.stock ?? Infinity) } : i);
        }
        this.save(items);
    },

    remove(id) {
        this.save(this.getAll().filter(i => i.id !== id));
    },

    // Dipakai halaman checkout untuk mengisi/mengubah tanggal pengambilan
    // produk pre-order yang sudah ada di keranjang.
    setPreorderDate(id, date) {
        const items = this.getAll().map(i => i.id === id ? { ...i, preorder_date: date } : i);
        this.save(items);
    },

    clear() {
        this.save([]);
    },

    subtotal() {
        return this.getAll().reduce((sum, i) => sum + (i.price * i.qty), 0);
    },

    hasPreorderItem() {
        return this.getAll().some(i => i.is_preorder);
    },

    /**
     * Menyegarkan setiap item di keranjang dengan data produk TERKINI dari
     * server (is_preorder, harga, nama, gambar, stok) — bukan data basi dari
     * saat produk pertama kali ditambahkan ke keranjang. Ini penting karena
     * admin bisa mengubah status pre-order/harga/stok/menonaktifkan produk
     * kapan saja, sementara keranjang pelanggan disimpan di localStorage.
     * Kalau stok terkini ternyata lebih kecil dari qty yang sudah ada di
     * keranjang (mis. dibeli orang lain), qty otomatis dipangkas; kalau
     * stok jadi 0, item dibuang seluruhnya dari keranjang.
     *
     * @param {Array} freshProducts - hasil CuisineriaAPI.getProducts() (data produk aktif saat ini)
     * @returns {{removed: Array, changed: boolean}} produk yang dihapus (tidak aktif/dihapus/stok habis)
     *          dan apakah ada perubahan data pada item yang masih ada.
     */
    syncWithProducts(freshProducts) {
        const byId = new Map(freshProducts.map(p => [p.id, p]));
        const removed = [];
        let changed = false;

        const items = this.getAll()
            .map(item => {
                const fresh = byId.get(item.id);
                if (!fresh || fresh.stock <= 0) {
                    // Produk sudah dihapus/dinonaktifkan admin, atau stoknya habis.
                    removed.push(item);
                    return null;
                }
                const cappedQty = Math.min(item.qty, fresh.stock);
                if (
                    fresh.is_preorder !== item.is_preorder ||
                    fresh.price !== item.price ||
                    fresh.name !== item.name ||
                    fresh.image !== item.image ||
                    fresh.stock !== item.stock ||
                    cappedQty !== item.qty
                ) {
                    changed = true;
                    // Kalau produk sekarang bukan pre-order lagi, buang preorder_date lama.
                    const preorder_date = fresh.is_preorder ? item.preorder_date : undefined;
                    return { ...item, ...fresh, qty: cappedQty, preorder_date };
                }
                return item;
            })
            .filter(Boolean);

        this.save(items);
        return { removed, changed };
    },
};

// Refresh the cart badge in the navbar on every page load.
document.addEventListener('DOMContentLoaded', () => {
    window.dispatchEvent(new CustomEvent('cart-updated', {
        detail: { count: Cart.getAll().reduce((s, i) => s + i.qty, 0), items: Cart.getAll() }
    }));
});

<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute Frontend Cuisineria
|--------------------------------------------------------------------------
| Semua halaman di sini murni tampilan (Blade + Tailwind). Data produk
| dan pesanan diambil dari REST API backend lewat public/js/api.js
| (CuisineriaAPI). Tidak ada logic database di sisi routes ini.
*/

// ---- Pelanggan (tanpa login) ----
Route::get('/', fn () => view('customer.home'));
Route::get('/produk', fn () => view('customer.products'));
Route::get('/produk/{id}', fn ($id) => view('customer.product-detail', ['id' => $id]));
Route::get('/keranjang', fn () => view('customer.cart'));
Route::get('/checkout', fn () => view('customer.checkout'));
Route::get('/pesanan-berhasil', fn () => view('customer.order-success'));
Route::get('/lacak-pesanan', fn () => view('customer.track-order'));
Route::get('/kontak', fn () => view('customer.contact'));

// ---- Admin (login terpisah dari pelanggan) ----
Route::get('/admin/login', fn () => view('admin.login'));
Route::get('/admin/dashboard', fn () => view('admin.dashboard'));
Route::get('/admin/produk', fn () => view('admin.products.index'));
Route::get('/admin/pesanan', fn () => view('admin.orders.index'));
Route::get('/admin/pesanan/offline/baru', fn () => view('admin.orders.offline-create'));
Route::get('/admin/pesan-kontak', fn () => view('admin.messages.index'));
Route::get('/admin/pesanan/{id}', fn ($id) => view('admin.orders.show', ['id' => $id]));
Route::get('/admin/slideshow', fn () => view('admin.slides.index'));

// Logout hanya membersihkan token di localStorage (lihat public/js/admin.js);
// endpoint /admin/logout di sini disiapkan untuk kompatibilitas form action.
Route::post('/admin/logout', fn () => redirect('/admin/login'));

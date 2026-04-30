# Rencana Implementasi: Catatan Keuangan Pribadi

## Ikhtisar

Dokumen ini merupakan roadmap implementasi aplikasi **Catatan Keuangan Pribadi** yang dibangun dengan arsitektur terpisah antara Backend (Laravel 12 + MySQL + Sanctum) dan Frontend (Vue 3 + Vite + Pinia + Tailwind CSS + Chart.js). Task disusun secara berurutan dari setup project hingga deployment, dibagi per modul pengembangan.

---

## Urutan Implementasi

```
Modul 1: Setup Project
    ↓
Modul 2: Backend Development
    ↓
Modul 3: Frontend Development
    ↓
Modul 4: Security Implementation
    ↓
Modul 5: Testing
    ↓
Modul 6: Deployment
```

---

## Modul 1: Setup Project

> Persiapan struktur proyek, konfigurasi environment, dan instalasi dependensi untuk backend dan frontend.

| No | Task | Deskripsi | Prioritas | Status |
|----|------|-----------|-----------|--------|
| 1.1 | Inisialisasi proyek Laravel 12 | Buat proyek Laravel 12 baru di folder `backend/` menggunakan Composer (`composer create-project laravel/laravel backend`). Pastikan versi PHP ≥ 8.2. | High | Pending |
| 1.2 | Konfigurasi file `.env` backend | Isi variabel `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `APP_URL=http://localhost:8000`, dan `FRONTEND_URL=http://localhost:5173` pada file `.env` backend. | High | Pending |
| 1.3 | Buat database MySQL | Buat database MySQL baru (misal: `catatan_keuangan`) yang sesuai dengan nilai `DB_DATABASE` di `.env`. | High | Pending |
| 1.4 | Instalasi Laravel Sanctum | Jalankan `composer require laravel/sanctum` lalu publish konfigurasi dengan `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`. | High | Pending |
| 1.5 | Inisialisasi proyek Vue 3 + Vite | Buat proyek frontend baru di folder `frontend/` menggunakan Vite (`npm create vite@latest frontend -- --template vue`). | High | Pending |
| 1.6 | Instalasi dependensi frontend | Di folder `frontend/`, jalankan `npm install` lalu install: `vue-router@4`, `pinia`, `axios`, `tailwindcss`, `@tailwindcss/vite`, `chart.js`, `vue-chartjs`. | High | Pending |
| 1.7 | Konfigurasi Tailwind CSS | Inisialisasi Tailwind CSS (`npx tailwindcss init -p`), konfigurasi `tailwind.config.js` dengan path `./src/**/*.{vue,js}`, dan import Tailwind di `src/assets/main.css`. | High | Pending |
| 1.8 | Konfigurasi Vite proxy | Tambahkan konfigurasi `server.proxy` di `vite.config.js` agar request ke `/api` diteruskan ke `http://localhost:8000` selama development. | Medium | Pending |
| 1.9 | Setup struktur folder backend | Buat folder `app/Http/Controllers/Api/`, `app/Http/Requests/Auth/`, `app/Http/Requests/Category/`, `app/Http/Requests/Transaction/`, `app/Http/Resources/`, dan `app/Policies/` sesuai desain teknis. | High | Pending |
| 1.10 | Setup struktur folder frontend | Buat folder `src/components/common/`, `src/components/category/`, `src/components/transaction/`, `src/components/dashboard/`, `src/views/auth/`, `src/stores/`, `src/services/`, dan `src/router/` sesuai desain teknis. | High | Pending |

---

## Modul 2: Backend Development

> Implementasi seluruh logika bisnis, endpoint API, model, migration, seeder, dan resource di sisi Laravel 12.

### 2A. Database & Model

| No | Task | Deskripsi | Prioritas | Status |
|----|------|-----------|-----------|--------|
| 2.1 | Buat migration tabel `categories` | Buat file migration untuk tabel `categories` dengan kolom: `id`, `user_id` (FK → users, cascade), `name` (varchar 100), `type` (enum: income/expense), `timestamps`. Tambahkan index pada `user_id`. | High | Pending |
| 2.2 | Buat migration tabel `transactions` | Buat file migration untuk tabel `transactions` dengan kolom: `id`, `user_id` (FK → users, cascade), `category_id` (FK → categories, restrict), `title`, `amount` (decimal 15,2), `type` (enum), `transaction_date` (date), `notes` (text nullable), `timestamps`. Tambahkan composite index `(user_id, transaction_date)` dan `(user_id, type)`. | High | Pending |
| 2.3 | Jalankan migration | Jalankan `php artisan migrate` untuk membuat semua tabel di database. Verifikasi tabel `users`, `personal_access_tokens`, `categories`, dan `transactions` terbentuk. | High | Pending |
| 2.4 | Buat Model `Category` | Buat `app/Models/Category.php` dengan `$fillable = ['user_id', 'name', 'type']`, definisi relasi `belongsTo(User::class)` dan `hasMany(Transaction::class)`. | High | Pending |
| 2.5 | Buat Model `Transaction` | Buat `app/Models/Transaction.php` dengan `$fillable = ['user_id', 'category_id', 'title', 'amount', 'type', 'transaction_date', 'notes']`, cast `amount` ke `decimal:2`, dan relasi `belongsTo(User::class)` serta `belongsTo(Category::class)`. | High | Pending |
| 2.6 | Update Model `User` | Tambahkan relasi `hasMany(Category::class)` dan `hasMany(Transaction::class)` pada `app/Models/User.php`. Pastikan `HasApiTokens` trait dari Sanctum sudah di-use. | High | Pending |
| 2.7 | Buat `CategorySeeder` | Buat `database/seeders/CategorySeeder.php` yang mengisi data kategori default (misal: Gaji, Freelance untuk income; Makan, Transport, Tagihan untuk expense). Daftarkan di `DatabaseSeeder.php`. | Low | Pending |

### 2B. API Resource

| No | Task | Deskripsi | Prioritas | Status |
|----|------|-----------|-----------|--------|
| 2.8 | Buat `UserResource` | Buat `app/Http/Resources/UserResource.php` yang mengembalikan field `id`, `name`, `email`. | High | Pending |
| 2.9 | Buat `CategoryResource` | Buat `app/Http/Resources/CategoryResource.php` yang mengembalikan field `id`, `user_id`, `name`, `type`, `created_at`. | High | Pending |
| 2.10 | Buat `TransactionResource` | Buat `app/Http/Resources/TransactionResource.php` yang mengembalikan field `id`, `title`, `amount`, `type`, `transaction_date`, `notes`, `created_at`, dan relasi `category` menggunakan `CategoryResource`. | High | Pending |
| 2.11 | Buat `DashboardResource` | Buat `app/Http/Resources/DashboardResource.php` yang mengembalikan `total_income`, `total_expense`, `balance`, dan array `monthly_chart`. | High | Pending |

### 2C. Form Request (Validasi)

| No | Task | Deskripsi | Prioritas | Status |
|----|------|-----------|-----------|--------|
| 2.12 | Buat `RegisterRequest` | Buat `app/Http/Requests/Auth/RegisterRequest.php` dengan aturan: `name` (required, max:255), `email` (required, email, unique:users), `password` (required, min:8, confirmed). | High | Pending |
| 2.13 | Buat `LoginRequest` | Buat `app/Http/Requests/Auth/LoginRequest.php` dengan aturan: `email` (required, email), `password` (required). | High | Pending |
| 2.14 | Buat `StoreCategoryRequest` | Buat `app/Http/Requests/Category/StoreCategoryRequest.php` dengan aturan: `name` (required, max:100), `type` (required, in:income,expense). | High | Pending |
| 2.15 | Buat `UpdateCategoryRequest` | Buat `app/Http/Requests/Category/UpdateCategoryRequest.php` dengan aturan yang sama seperti `StoreCategoryRequest`. | High | Pending |
| 2.16 | Buat `StoreTransactionRequest` | Buat `app/Http/Requests/Transaction/StoreTransactionRequest.php` dengan aturan: `title` (required, max:255), `amount` (required, numeric, gt:0), `type` (required, in:income,expense), `category_id` (required, exists:categories,id + validasi milik user), `transaction_date` (required, date_format:Y-m-d), `notes` (nullable, max:1000). | High | Pending |
| 2.17 | Buat `UpdateTransactionRequest` | Buat `app/Http/Requests/Transaction/UpdateTransactionRequest.php` dengan aturan yang sama seperti `StoreTransactionRequest`. | High | Pending |
| 2.18 | Buat `IndexTransactionRequest` | Buat `app/Http/Requests/Transaction/IndexTransactionRequest.php` untuk validasi query params: `type` (nullable, in:income,expense), `category_id` (nullable, exists:categories,id milik user), `transaction_date_from` (nullable, date_format:Y-m-d), `transaction_date_to` (nullable, date_format:Y-m-d, after_or_equal:transaction_date_from), `sort_by` (nullable, in:transaction_date,amount), `sort_order` (nullable, in:asc,desc), `per_page` (nullable, integer, min:1, max:100). | High | Pending |

### 2D. Policy (Otorisasi)

| No | Task | Deskripsi | Prioritas | Status |
|----|------|-----------|-----------|--------|
| 2.19 | Buat `CategoryPolicy` | Buat `app/Policies/CategoryPolicy.php` dengan method `view`, `update`, `delete` yang memverifikasi `$user->id === $category->user_id`. Daftarkan di `AuthServiceProvider`. | High | Pending |
| 2.20 | Buat `TransactionPolicy` | Buat `app/Policies/TransactionPolicy.php` dengan method `view`, `update`, `delete` yang memverifikasi `$user->id === $transaction->user_id`. Daftarkan di `AuthServiceProvider`. | High | Pending |

### 2E. Controller & Route

| No | Task | Deskripsi | Prioritas | Status |
|----|------|-----------|-----------|--------|
| 2.21 | Buat `AuthController` | Buat `app/Http/Controllers/Api/AuthController.php` dengan method: `register` (hash password, buat token, return 201), `login` (verifikasi kredensial, buat token, return 200 atau 401), `logout` (revoke token, return 200). | High | Pending |
| 2.22 | Buat `CategoryController` | Buat `app/Http/Controllers/Api/CategoryController.php` dengan method `index`, `store`, `show`, `update`, `destroy`. Setiap query menggunakan `where('user_id', auth()->id())`. Gunakan Policy untuk otorisasi pada `show`, `update`, `destroy`. | High | Pending |
| 2.23 | Buat `TransactionController` | Buat `app/Http/Controllers/Api/TransactionController.php` dengan method `index` (filter + sorting + pagination sesuai desain), `store`, `show`, `update`, `destroy`. Eager load relasi `category`. Gunakan Policy untuk otorisasi. | High | Pending |
| 2.24 | Buat `DashboardController` | Buat `app/Http/Controllers/Api/DashboardController.php` dengan method `index` yang menghitung `total_income`, `total_expense`, `balance`, dan `monthly_chart` (12 bulan terakhir) menggunakan `selectRaw` dan `groupBy`. | High | Pending |
| 2.25 | Definisikan route API | Buat semua route di `routes/api.php`: grup publik untuk `register` dan `login`, grup `auth:sanctum` untuk `logout`, `categories` (apiResource), `transactions` (apiResource), dan `dashboard`. | High | Pending |
| 2.26 | Checkpoint backend | Uji semua endpoint menggunakan Postman atau tool sejenis. Pastikan register, login, logout, CRUD kategori, CRUD transaksi, filter/sorting/pagination, dan dashboard berfungsi sesuai spesifikasi. Tanyakan kepada pengguna jika ada pertanyaan. | High | Pending |

---

## Modul 3: Frontend Development

> Implementasi antarmuka pengguna menggunakan Vue 3 Composition API, Pinia, Vue Router, Axios, Tailwind CSS, dan Chart.js.

### 3A. Konfigurasi Dasar Frontend

| No | Task | Deskripsi | Prioritas | Status |
|----|------|-----------|-----------|--------|
| 3.1 | Buat instance Axios (`services/api.js`) | Buat `src/services/api.js` dengan base URL `/api`, request interceptor (sisipkan `Authorization: Bearer {token}` dari localStorage), dan response interceptor (tangani 401: hapus token, redirect ke `/login`). | High | Pending |
| 3.2 | Buat `authStore` (Pinia) | Buat `src/stores/authStore.js` dengan state `user`, `token`, `isLoading`; actions `register`, `login`, `logout`, `initAuth`; getter `isAuthenticated`. | High | Pending |
| 3.3 | Konfigurasi Vue Router | Buat `src/router/index.js` dengan route: `/login`, `/register`, `/dashboard`, `/categories`, `/transactions`. Tambahkan navigation guard: redirect ke `/login` jika belum auth, redirect ke `/dashboard` jika sudah auth dan mengakses `/login` atau `/register`. | High | Pending |
| 3.4 | Setup `main.js` | Daftarkan Pinia, Vue Router, dan panggil `authStore.initAuth()` di `src/main.js`. Mount aplikasi ke `#app`. | High | Pending |
| 3.5 | Buat `App.vue` | Buat `src/App.vue` dengan `<RouterView />` sebagai root komponen. | High | Pending |

### 3B. Komponen Umum (Common)

| No | Task | Deskripsi | Prioritas | Status |
|----|------|-----------|-----------|--------|
| 3.6 | Buat `AppNavbar.vue` | Buat komponen navigasi utama dengan link ke Dashboard, Kategori, Transaksi, dan tombol Logout. Tampilkan nama pengguna dari `authStore`. Sembunyikan navbar di halaman login/register. | High | Pending |
| 3.7 | Buat `AppLoadingSpinner.vue` | Buat komponen spinner/loading indicator yang dapat digunakan di semua halaman. Terima prop `isLoading` (Boolean). | High | Pending |
| 3.8 | Buat `AppAlert.vue` | Buat komponen alert untuk menampilkan pesan sukses atau error. Terima prop `type` (success/error) dan `message` (String). | Medium | Pending |
| 3.9 | Buat `AppPagination.vue` | Buat komponen pagination reusable yang menampilkan tombol prev, next, dan nomor halaman. Terima prop `meta` (Object dari API) dan emit event `page-change`. | High | Pending |

### 3C. Halaman Autentikasi

| No | Task | Deskripsi | Prioritas | Status |
|----|------|-----------|-----------|--------|
| 3.10 | Buat `RegisterView.vue` | Buat halaman registrasi dengan form (nama, email, password, konfirmasi password). Hubungkan ke `authStore.register()`. Tampilkan error validasi per field. Tampilkan loading state saat proses berlangsung. | High | Pending |
| 3.11 | Buat `LoginView.vue` | Buat halaman login dengan form (email, password). Hubungkan ke `authStore.login()`. Tampilkan error autentikasi. Tampilkan loading state saat proses berlangsung. | High | Pending |

### 3D. Modul Kategori

| No | Task | Deskripsi | Prioritas | Status |
|----|------|-----------|-----------|--------|
| 3.12 | Buat `categoryStore` (Pinia) | Buat `src/stores/categoryStore.js` dengan state `categories`, `isLoading`; actions `fetchCategories`, `createCategory`, `updateCategory`, `deleteCategory`; getter `incomeCategories`, `expenseCategories`. | High | Pending |
| 3.13 | Buat `CategoryForm.vue` | Buat komponen form tambah/edit kategori dengan field nama dan tipe (dropdown: Pemasukan/Pengeluaran). Terima prop `category` (untuk mode edit) dan emit event `submitted`. Tampilkan error validasi. | High | Pending |
| 3.14 | Buat `CategoryTable.vue` | Buat komponen tabel yang menampilkan daftar kategori (nama, tipe, aksi edit/hapus). Terima prop `categories` (Array) dan emit event `edit`, `delete`. | High | Pending |
| 3.15 | Buat `CategoryView.vue` | Buat halaman kategori yang mengintegrasikan `CategoryTable` dan `CategoryForm` (modal atau inline). Tampilkan `AppLoadingSpinner` saat `categoryStore.isLoading`. Perbarui daftar setelah operasi CRUD berhasil tanpa reload halaman. | High | Pending |

### 3E. Modul Transaksi

| No | Task | Deskripsi | Prioritas | Status |
|----|------|-----------|-----------|--------|
| 3.16 | Buat `transactionStore` (Pinia) | Buat `src/stores/transactionStore.js` dengan state `transactions`, `meta`, `filters`, `sorting`, `isLoading`; actions `fetchTransactions`, `setFilter`, `resetFilters`, `setPage`, `setSorting`, `createTransaction`, `updateTransaction`, `deleteTransaction`. | High | Pending |
| 3.17 | Buat `TransactionFilter.vue` | Buat komponen panel filter dengan kontrol: dropdown tipe (income/expense/semua), dropdown kategori (dari `categoryStore`), input tanggal mulai & akhir, dropdown sort by & sort order, dan tombol Reset Filter. Emit event `filter-change` dan `reset`. | High | Pending |
| 3.18 | Buat `TransactionForm.vue` | Buat komponen form tambah/edit transaksi dengan field: judul, nominal, tipe, kategori (difilter berdasarkan tipe yang dipilih menggunakan getter `incomeCategories`/`expenseCategories`), tanggal transaksi, catatan. Tampilkan error validasi per field. | High | Pending |
| 3.19 | Buat `TransactionTable.vue` | Buat komponen tabel yang menampilkan daftar transaksi (judul, nominal, tipe, kategori, tanggal, aksi edit/hapus). Tampilkan badge warna berbeda untuk tipe income (hijau) dan expense (merah). | High | Pending |
| 3.20 | Buat `TransactionView.vue` | Buat halaman transaksi yang mengintegrasikan `TransactionFilter`, `TransactionTable`, `AppPagination`, dan `TransactionForm`. Tampilkan info ringkasan pagination "Menampilkan X-Y dari Z transaksi". Tampilkan `AppLoadingSpinner` saat loading. Nonaktifkan kontrol saat request berlangsung. | High | Pending |

### 3F. Modul Dashboard

| No | Task | Deskripsi | Prioritas | Status |
|----|------|-----------|-----------|--------|
| 3.21 | Buat `dashboardStore` (Pinia) | Buat `src/stores/dashboardStore.js` dengan state `summary` (total_income, total_expense, balance), `monthlyChart` (array), `isLoading`; action `fetchDashboard`. | High | Pending |
| 3.22 | Buat `SummaryCard.vue` | Buat komponen kartu ringkasan yang menampilkan total pemasukan, total pengeluaran, dan saldo. Tampilkan saldo berwarna hijau jika ≥ 0, merah jika < 0. Format nominal sebagai mata uang Rupiah. | High | Pending |
| 3.23 | Buat `MonthlyChart.vue` | Buat komponen grafik batang menggunakan Chart.js (via vue-chartjs) yang menampilkan perbandingan pemasukan dan pengeluaran per bulan selama 12 bulan terakhir. Warna hijau untuk pemasukan, merah untuk pengeluaran. | High | Pending |
| 3.24 | Buat `DashboardView.vue` | Buat halaman dashboard yang mengintegrasikan `SummaryCard` dan `MonthlyChart`. Panggil `dashboardStore.fetchDashboard()` di `onMounted`. Tampilkan `AppLoadingSpinner` saat `dashboardStore.isLoading`. | High | Pending |
| 3.25 | Checkpoint frontend | Verifikasi semua halaman dapat diakses, navigasi berfungsi, route guard bekerja, loading state tampil di semua halaman, dan data dari API ditampilkan dengan benar. Tanyakan kepada pengguna jika ada pertanyaan. | High | Pending |

---

## Modul 4: Security Implementation

> Implementasi lapisan keamanan: enkripsi password, Sanctum token, Policy, CORS, dan Form Request.

| No | Task | Deskripsi | Prioritas | Status |
|----|------|-----------|-----------|--------|
| 4.1 | Verifikasi enkripsi bcrypt | Pastikan `AuthController::register` menggunakan `Hash::make($request->password)` untuk menyimpan password. Verifikasi tidak ada plaintext password yang tersimpan di database. | High | Pending |
| 4.2 | Konfigurasi middleware Sanctum | Pastikan semua route yang memerlukan autentikasi menggunakan middleware `auth:sanctum` di `routes/api.php`. Verifikasi token dicabut saat logout menggunakan `$request->user()->currentAccessToken()->delete()`. | High | Pending |
| 4.3 | Konfigurasi CORS | Edit `config/cors.php`: set `allowed_origins` menggunakan nilai `FRONTEND_URL` dari `.env`, set `allowed_methods` ke `['*']`, `allowed_headers` ke `['*']`, dan `supports_credentials` ke `false`. | High | Pending |
| 4.4 | Verifikasi isolasi data Policy | Uji `CategoryPolicy` dan `TransactionPolicy` memastikan pengguna tidak dapat mengakses resource milik pengguna lain. Verifikasi response 403/404 dikembalikan untuk akses tidak sah. | High | Pending |
| 4.5 | Verifikasi validasi Form Request | Pastikan semua Form Request mengembalikan response 422 dengan pesan error terstruktur saat validasi gagal. Verifikasi pesan error ditampilkan dengan benar di frontend. | High | Pending |
| 4.6 | Verifikasi Axios interceptor 401 | Uji skenario token expired/invalid: pastikan Axios response interceptor menghapus token dari localStorage dan redirect ke `/login` saat menerima response 401. | High | Pending |

---

## Modul 5: Testing

> Pengujian fungsional untuk memastikan semua fitur berjalan sesuai requirements.

| No | Task | Deskripsi | Prioritas | Status |
|----|------|-----------|-----------|--------|
| 5.1 | Tulis unit test `AuthController` | Buat test untuk endpoint register (sukses, email duplikat, validasi gagal), login (sukses, kredensial salah), dan logout (sukses, tanpa token). Gunakan `RefreshDatabase` trait. | Medium | Pending |
| 5.2 | Tulis unit test `CategoryController` | Buat test untuk CRUD kategori: index (hanya milik user), store (sukses, validasi gagal), show (milik user, bukan milik user), update, destroy (dengan transaksi terkait → harus gagal). | Medium | Pending |
| 5.3 | Tulis unit test `TransactionController` | Buat test untuk CRUD transaksi, filter (type, category_id, date range), sorting (transaction_date, amount), dan pagination (page, per_page, default values). | Medium | Pending |
| 5.4 | Tulis unit test `DashboardController` | Buat test untuk endpoint dashboard: verifikasi kalkulasi total_income, total_expense, balance, dan data monthly_chart (12 bulan terakhir, format bulan benar). | Medium | Pending |
| 5.5 | Tulis unit test Policy | Buat test untuk `CategoryPolicy` dan `TransactionPolicy`: verifikasi user hanya dapat mengakses resource miliknya sendiri. | Medium | Pending |
| 5.6 | Uji loading state frontend | Verifikasi secara manual bahwa loading spinner muncul di semua halaman (dashboard, kategori, transaksi) saat request API berlangsung, dan hilang setelah response diterima. | Medium | Pending |
| 5.7 | Uji filter & sorting transaksi | Verifikasi secara manual bahwa filter (tipe, kategori, rentang tanggal) dan sorting (kolom, arah) berfungsi dengan benar, pagination reset ke halaman 1 saat filter berubah, dan tombol reset filter berfungsi. | Medium | Pending |
| 5.8 | Uji integrasi end-to-end | Lakukan pengujian alur lengkap: register → login → buat kategori → buat transaksi → lihat dashboard → filter transaksi → edit transaksi → hapus transaksi → logout. | Medium | Pending |

---

## Modul 6: Deployment

> Persiapan konfigurasi untuk deployment aplikasi ke lingkungan produksi.

| No | Task | Deskripsi | Prioritas | Status |
|----|------|-----------|-----------|--------|
| 6.1 | Build frontend untuk produksi | Jalankan `npm run build` di folder `frontend/` untuk menghasilkan file statis di folder `dist/`. Verifikasi tidak ada error build. | High | Pending |
| 6.2 | Konfigurasi `.env` produksi backend | Update `.env` backend untuk produksi: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL` (domain produksi), `FRONTEND_URL` (domain frontend produksi), dan konfigurasi database produksi. | High | Pending |
| 6.3 | Konfigurasi Nginx untuk backend | Buat konfigurasi Nginx untuk backend Laravel: set `root` ke folder `public/`, konfigurasi `try_files` untuk SPA routing, dan set header CORS jika diperlukan. | High | Pending |
| 6.4 | Konfigurasi Nginx untuk frontend | Buat konfigurasi Nginx untuk menyajikan file statis dari folder `dist/` frontend. Konfigurasi `try_files` agar Vue Router dapat menangani client-side routing. | High | Pending |
| 6.5 | Jalankan migration & seeder produksi | Jalankan `php artisan migrate --force` dan `php artisan db:seed --force` di server produksi. Verifikasi semua tabel terbentuk dan data seeder tersedia. | High | Pending |
| 6.6 | Optimasi Laravel untuk produksi | Jalankan `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`, dan `php artisan optimize` untuk meningkatkan performa backend di produksi. | Medium | Pending |
| 6.7 | Verifikasi deployment | Akses aplikasi melalui domain produksi, lakukan smoke test: register, login, buat kategori, buat transaksi, lihat dashboard. Pastikan tidak ada error CORS dan semua fitur berfungsi. | High | Pending |

---

## Catatan Implementasi

- Task yang berada dalam satu modul sebaiknya diselesaikan secara berurutan sesuai nomor task.
- Modul 2 (Backend) harus selesai sebelum memulai Modul 3 (Frontend) agar endpoint API tersedia untuk diuji.
- Modul 4 (Security) dapat dikerjakan paralel dengan Modul 3, namun verifikasi akhir dilakukan setelah keduanya selesai.
- Checkpoint (task 2.26 dan 3.25) adalah titik evaluasi penting — pastikan semua task sebelumnya berfungsi sebelum melanjutkan.
- Semua referensi ke requirements dapat dilihat di `requirements.md`, dan detail teknis implementasi ada di `design.md`.

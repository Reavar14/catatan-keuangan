# Dokumen Desain Teknis: Catatan Keuangan Pribadi

## Ikhtisar

Aplikasi **Catatan Keuangan Pribadi** merupakan sistem manajemen keuangan berbasis web yang dirancang dengan arsitektur terpisah (*decoupled*) antara Backend (Laravel 12) dan Frontend (Vue 3). Backend (Laravel 12) menggunakan MySQL sebagai basis data relasional, sedangkan Frontend (Vue 3) menggunakan Vite sebagai build tool modern. Kedua lapisan sistem berkomunikasi melalui REST API yang diamankan dengan Laravel Sanctum menggunakan mekanisme Bearer Token.

Pengguna dapat mendaftarkan akun, mencatat pemasukan dan pengeluaran, mengelola kategori transaksi, serta memantau ringkasan keuangan melalui dashboard interaktif yang dilengkapi grafik bulanan menggunakan Chart.js.

---

## 1. Arsitektur Sistem

### 1.1 Gambaran Umum Arsitektur

Sistem menggunakan arsitektur **Client-Server** dengan pemisahan penuh antara Backend (Laravel 12) dan Frontend (Vue 3):

```
+---------------------------+          HTTPS / REST API          +---------------------------+
|                           |  -------------------------------->  |                           |
|   Frontend (Vue 3 + Vite) |                                    |   Backend (Laravel 12)    |
|   - Vue Router            |  <--------------------------------  |   - MySQL Database        |
|   - Pinia Store           |       JSON Response + Token        |   - Laravel Sanctum       |
|   - Axios HTTP Client     |                                    |   - API Resource          |
|   - Tailwind CSS          |                                    |   - Query Builder         |
|   - Chart.js              |                                    |                           |
+---------------------------+                                    +---------------------------+
         |                                                                  |
         | Diakses pengguna                                                 | Koneksi DB
         v                                                                  v
  Browser Pengguna                                                  +---------------+
                                                                    |  MySQL DB     |
                                                                    |  - users      |
                                                                    |  - categories |
                                                                    |  - transactions|
                                                                    +---------------+
```

### 1.2 Alur Komunikasi

```
Browser
  |
  |-- 1. Muat SPA (HTML/JS/CSS dari static hosting / Nginx)
  |
  |-- 2. Vue Router menentukan halaman yang ditampilkan
  |
  |-- 3. Pinia Store memicu action (misal: fetchTransactions)
  |
  |-- 4. Axios mengirim HTTP Request ke Backend (Laravel 12)
  |       Header: Authorization: Bearer {token}
  |
  |-- 5. Laravel Sanctum memvalidasi token
  |
  |-- 6. Controller memproses request (validasi, query DB)
  |
  |-- 7. API Resource memformat response JSON
  |
  |-- 8. Response diterima Axios, Pinia Store diperbarui
  |
  |-- 9. Frontend (Vue 3) secara reaktif memperbarui tampilan komponen
```

### 1.3 Komponen Utama Sistem

| Komponen | Teknologi | Tanggung Jawab |
|---|---|---|
| Autentikator | Laravel Sanctum | Registrasi, login, logout, validasi token |
| Pengelola Kategori | Laravel Controller + Eloquent | CRUD kategori per pengguna |
| Pengelola Transaksi | Laravel Controller + Eloquent | CRUD transaksi, filter, sorting, pagination |
| Penghitung Dashboard | Laravel Controller + DB Aggregation | Kalkulasi saldo, agregasi bulanan |
| State Manager | Pinia | Manajemen state terpusat di Frontend (Vue 3) |
| HTTP Client | Axios | Komunikasi Frontend (Vue 3) ke Backend (Laravel 12) |
| Router | Vue Router | Navigasi halaman dan route guard |
| Visualisasi | Chart.js | Grafik batang/garis data bulanan |

---

## 1.4 Keamanan Sistem

Sistem menerapkan beberapa lapisan keamanan untuk melindungi data pengguna dan integritas API.

### Enkripsi Kata Sandi

Seluruh kata sandi pengguna disimpan dalam basis data dalam bentuk hash menggunakan algoritma **bcrypt** melalui fungsi bawaan Laravel (`Hash::make()`). Kata sandi dalam bentuk teks asli (*plaintext*) tidak pernah disimpan di sistem.

### Autentikasi API dengan Laravel Sanctum

Seluruh endpoint yang memerlukan autentikasi dilindungi oleh middleware `auth:sanctum`. Setiap permintaan ke endpoint tersebut wajib menyertakan header `Authorization: Bearer {token}`. Token diterbitkan saat login berhasil dan dicabut (*revoked*) saat logout, sehingga token yang sudah tidak valid tidak dapat digunakan kembali.

### Validasi Input dengan Form Request

Seluruh data masukan dari klien divalidasi menggunakan kelas **Form Request** Laravel sebelum diproses oleh controller. Pendekatan ini memastikan bahwa data yang tidak valid ditolak lebih awal dengan respons HTTP `422 Unprocessable Entity` beserta pesan kesalahan yang terstruktur.

### Isolasi Data Per Pengguna

Setiap query ke tabel `categories` dan `transactions` selalu disertai kondisi `WHERE user_id = auth()->id()`. Selain itu, akses ke resource milik pengguna lain diproteksi menggunakan **Laravel Policy** (`CategoryPolicy`, `TransactionPolicy`), sehingga pengguna tidak dapat mengakses atau memodifikasi data milik pengguna lain meskipun mengetahui ID resource tersebut.

### Proteksi CORS

Backend (Laravel 12) dikonfigurasi dengan middleware CORS melalui `config/cors.php` untuk membatasi origin yang diizinkan mengakses API. Hanya domain Frontend (Vue 3) yang terdaftar pada variabel `FRONTEND_URL` yang diperbolehkan melakukan permintaan lintas domain (*cross-origin request*).

---

## 2. Model Data dan Skema Database

### 2.1 Entity Relationship Diagram (ERD)

```
+------------------+          +----------------------+          +------------------------+
|      users       |          |      categories      |          |      transactions      |
+------------------+          +----------------------+          +------------------------+
| PK id            |1        *| PK id                |1        *| PK id                  |
|    name          |----------| FK user_id           |----------| FK user_id             |
|    email (unique)|          |    name              |          | FK category_id         |
|    password      |          |    type (enum)       |          |    title               |
|    created_at    |          |    created_at        |          |    amount (decimal)    |
|    updated_at    |          |    updated_at        |          |    type (enum)         |
+------------------+          +----------------------+          |    transaction_date    |
                                                                |    notes (nullable)    |
                                                                |    created_at          |
                                                                |    updated_at          |
                                                                +------------------------+
```

**Relasi:**
- `users` 1 → N `categories` (satu pengguna memiliki banyak kategori)
- `users` 1 → N `transactions` (satu pengguna memiliki banyak transaksi)
- `categories` 1 → N `transactions` (satu kategori digunakan oleh banyak transaksi)

**Kebijakan Penghapusan (Referential Integrity):**
- Apabila sebuah record `users` dihapus, seluruh record `categories` milik pengguna tersebut akan ikut dihapus secara otomatis (`onDelete('cascade')`). Hal ini menjaga konsistensi data agar tidak ada kategori yang merujuk ke pengguna yang sudah tidak ada.
- Foreign key `category_id` pada tabel `transactions` menggunakan kebijakan `onDelete('restrict')`. Artinya, sebuah kategori **tidak dapat dihapus** selama masih terdapat transaksi yang merujuk ke kategori tersebut. Pengguna harus memindahkan atau menghapus transaksi terkait terlebih dahulu sebelum dapat menghapus kategori.

### 2.2 Skema Tabel Detail

#### Tabel `users`

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Identitas unik pengguna |
| name | VARCHAR(255) | NOT NULL | Nama lengkap pengguna |
| email | VARCHAR(255) | NOT NULL, UNIQUE | Alamat email (digunakan untuk login) |
| password | VARCHAR(255) | NOT NULL | Password yang sudah di-hash (bcrypt) |
| remember_token | VARCHAR(100) | NULLABLE | Token untuk fitur "remember me" |
| created_at | TIMESTAMP | NULLABLE | Waktu pembuatan record |
| updated_at | TIMESTAMP | NULLABLE | Waktu pembaruan record terakhir |

#### Tabel `categories`

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Identitas unik kategori |
| user_id | BIGINT UNSIGNED | NOT NULL, FK → users.id | Pemilik kategori |
| name | VARCHAR(100) | NOT NULL | Nama kategori (misal: Gaji, Makan) |
| type | ENUM('income','expense') | NOT NULL | Tipe kategori: pemasukan atau pengeluaran |
| created_at | TIMESTAMP | NULLABLE | Waktu pembuatan record |
| updated_at | TIMESTAMP | NULLABLE | Waktu pembaruan record terakhir |

**Index:** `INDEX(user_id)` untuk performa query per pengguna.

#### Tabel `transactions`

| Kolom | Tipe | Constraint | Keterangan |
|---|---|---|---|
| id | BIGINT UNSIGNED | PK, AUTO_INCREMENT | Identitas unik transaksi |
| user_id | BIGINT UNSIGNED | NOT NULL, FK → users.id | Pemilik transaksi |
| category_id | BIGINT UNSIGNED | NOT NULL, FK → categories.id | Kategori transaksi |
| title | VARCHAR(255) | NOT NULL | Judul/deskripsi singkat transaksi |
| amount | DECIMAL(15,2) | NOT NULL | Nominal transaksi (maks 999 triliun) |
| type | ENUM('income','expense') | NOT NULL | Tipe: pemasukan atau pengeluaran |
| transaction_date | DATE | NOT NULL | Tanggal transaksi (YYYY-MM-DD) |
| notes | TEXT | NULLABLE | Catatan tambahan (maks 1000 karakter) |
| created_at | TIMESTAMP | NULLABLE | Waktu pembuatan record |
| updated_at | TIMESTAMP | NULLABLE | Waktu pembaruan record terakhir |

**Index:**
- `INDEX(user_id)` untuk isolasi data per pengguna
- `INDEX(user_id, transaction_date)` untuk query dashboard dan filter tanggal
- `INDEX(user_id, type)` untuk filter berdasarkan tipe
- `INDEX(category_id)` untuk filter berdasarkan kategori

### 2.3 Migration Laravel

```php
// database/migrations/xxxx_create_categories_table.php
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('name', 100);
    $table->enum('type', ['income', 'expense']);
    $table->timestamps();
    $table->index('user_id');
});

// database/migrations/xxxx_create_transactions_table.php
Schema::create('transactions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('category_id')->constrained()->onDelete('restrict');
    $table->string('title');
    $table->decimal('amount', 15, 2);
    $table->enum('type', ['income', 'expense']);
    $table->date('transaction_date');
    $table->text('notes')->nullable();
    $table->timestamps();
    $table->index('user_id');
    $table->index(['user_id', 'transaction_date']);
    $table->index(['user_id', 'type']);
});
```

---

## 3. Desain API Endpoint

### 3.1 Konvensi Umum

- Base URL: `http://localhost:8000/api`
- Format request/response: `application/json`
- Autentikasi: `Authorization: Bearer {token}` pada semua endpoint yang dilindungi
- Format tanggal: `YYYY-MM-DD`
- Format nominal: angka desimal (misal: `150000.00`)

### 3.2 Daftar Endpoint

#### Autentikasi

| Method | Endpoint | Auth | Deskripsi |
|---|---|---|---|
| POST | `/api/register` | Tidak | Registrasi pengguna baru |
| POST | `/api/login` | Tidak | Login dan mendapatkan token |
| POST | `/api/logout` | Ya | Logout dan mencabut token |

#### Kategori

| Method | Endpoint | Auth | Deskripsi |
|---|---|---|---|
| GET | `/api/categories` | Ya | Daftar semua kategori milik pengguna |
| POST | `/api/categories` | Ya | Buat kategori baru |
| GET | `/api/categories/{id}` | Ya | Detail satu kategori |
| PUT | `/api/categories/{id}` | Ya | Perbarui kategori |
| DELETE | `/api/categories/{id}` | Ya | Hapus kategori |

#### Transaksi

| Method | Endpoint | Auth | Deskripsi |
|---|---|---|---|
| GET | `/api/transactions` | Ya | Daftar transaksi (dengan pagination, filter, sorting) |
| POST | `/api/transactions` | Ya | Buat transaksi baru |
| GET | `/api/transactions/{id}` | Ya | Detail satu transaksi |
| PUT | `/api/transactions/{id}` | Ya | Perbarui transaksi |
| DELETE | `/api/transactions/{id}` | Ya | Hapus transaksi |

#### Dashboard

| Method | Endpoint | Auth | Deskripsi |
|---|---|---|---|
| GET | `/api/dashboard` | Ya | Ringkasan keuangan dan data grafik bulanan |

### 3.3 Contoh Request & Response

#### POST /api/register

Request:
```json
{
  "name": "Budi Santoso",
  "email": "budi@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

Response `201 Created`:
```json
{
  "message": "Registrasi berhasil",
  "data": {
    "user": { "id": 1, "name": "Budi Santoso", "email": "budi@example.com" },
    "token": "1|abc123xyz..."
  }
}
```

#### POST /api/login

Request:
```json
{ "email": "budi@example.com", "password": "password123" }
```

Response `200 OK`:
```json
{
  "message": "Login berhasil",
  "data": {
    "user": { "id": 1, "name": "Budi Santoso", "email": "budi@example.com" },
    "token": "2|def456uvw..."
  }
}
```

#### GET /api/transactions (dengan query parameters)

Query parameters yang didukung:
```
?page=1
&per_page=10
&type=expense
&category_id=3
&transaction_date_from=2026-01-01
&transaction_date_to=2026-04-30
&sort_by=transaction_date
&sort_order=desc
```

Response `200 OK`:
```json
{
  "data": [
    {
      "id": 10,
      "title": "Makan Siang",
      "amount": "35000.00",
      "type": "expense",
      "transaction_date": "2026-04-15",
      "notes": "Warteg dekat kantor",
      "category": { "id": 3, "name": "Makanan", "type": "expense" },
      "created_at": "2026-04-15T12:00:00.000000Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 45,
    "last_page": 5,
    "from": 1,
    "to": 10
  }
}
```

#### GET /api/dashboard

Response `200 OK`:
```json
{
  "data": {
    "total_income": "5000000.00",
    "total_expense": "3200000.00",
    "balance": "1800000.00",
    "monthly_chart": [
      { "month": "2025-05", "income": "4500000.00", "expense": "2800000.00" },
      { "month": "2025-06", "income": "5000000.00", "expense": "3100000.00" },
      { "month": "2026-04", "income": "5000000.00", "expense": "3200000.00" }
    ]
  }
}
```

---

### 3.4 Penanganan Error API

Seluruh respons error dari Backend (Laravel 12) menggunakan format JSON yang konsisten sebagai berikut:

```json
{
  "message": "Deskripsi singkat mengenai error yang terjadi.",
  "errors": {
    "field_name": ["Pesan validasi untuk field ini."]
  }
}
```

Field `errors` hanya disertakan pada error validasi (`422`). Untuk error lainnya, hanya field `message` yang dikembalikan.

**Tabel HTTP Status Code yang Digunakan:**

| HTTP Status | Keterangan | Contoh Kasus |
|---|---|---|
| `200 OK` | Permintaan berhasil diproses | GET, PUT, DELETE berhasil |
| `201 Created` | Resource baru berhasil dibuat | POST register, kategori, transaksi |
| `401 Unauthorized` | Token tidak valid atau tidak disertakan | Akses endpoint tanpa token |
| `403 Forbidden` | Token valid namun tidak memiliki izin | Akses resource milik pengguna lain |
| `404 Not Found` | Resource tidak ditemukan | ID kategori/transaksi tidak ada |
| `422 Unprocessable Entity` | Data input tidak lolos validasi | Field wajib kosong, format salah |
| `500 Internal Server Error` | Kesalahan tak terduga di sisi server | Exception yang tidak tertangani |

---

## 4. Alur Autentikasi dengan Laravel Sanctum

### 4.1 Diagram Alur

```
Tamu              Frontend (Vue 3)         Backend (Laravel 12)        Database
 |                           |                             |                        |
 |-- Isi form register ----→ |                             |                        |
 |                           |-- POST /api/register -----→ |                        |
 |                           |                             |-- Validasi data        |
 |                           |                             |-- Hash password        |
 |                           |                             |-- INSERT users ------→ |
 |                           |                             |-- Buat Sanctum token   |
 |                           |←-- 201 + {user, token} ---- |                        |
 |                           |-- Simpan token ke           |                        |
 |                           |   localStorage              |                        |
 |                           |-- Redirect ke /dashboard    |                        |
 |←-- Tampil dashboard ----- |                             |                        |
 |                           |                             |                        |
 |-- Isi form login -------→ |                             |                        |
 |                           |-- POST /api/login ---------→ |                        |
 |                           |                             |-- Verifikasi kredensial|
 |                           |                             |-- Buat token baru      |
 |                           |←-- 200 + {user, token} ---- |                        |
 |                           |-- Simpan token              |                        |
 |                           |                             |                        |
 |-- Klik logout ----------→ |                             |                        |
 |                           |-- POST /api/logout ---------→ |                        |
 |                           |   Header: Bearer {token}    |-- Hapus token dari DB  |
 |                           |←-- 200 OK ------------------ |                        |
 |                           |-- Hapus token dari          |                        |
 |                           |   localStorage              |                        |
 |                           |-- Redirect ke /login        |                        |
```

### 4.2 Route Guard di Vue Router

```
Setiap navigasi halaman:
  |
  |-- Apakah halaman memerlukan auth?
  |     |
  |     |-- Ya → Apakah token ada di localStorage?
  |     |           |-- Tidak → Redirect ke /login
  |     |           |-- Ya   → Lanjutkan ke halaman
  |     |
  |     |-- Tidak → Apakah halaman adalah /login atau /register?
  |                   |-- Ya dan token ada → Redirect ke /dashboard
  |                   |-- Tidak            → Lanjutkan ke halaman
```

### 4.3 Axios Interceptor

Axios pada Frontend (Vue 3) dikonfigurasi dengan dua interceptor:
- **Request interceptor**: Secara otomatis menyisipkan header `Authorization: Bearer {token}` yang diambil dari `localStorage` ke setiap permintaan keluar.
- **Response interceptor**: Apabila Backend (Laravel 12) mengembalikan HTTP status `401`, token dihapus dari `localStorage` dan pengguna diarahkan ke halaman `/login` secara otomatis.

---

## 5. Struktur Folder Backend (Laravel 12)

Struktur folder Backend (Laravel 12) dirancang mengikuti konvensi Laravel dengan pemisahan tanggung jawab yang jelas antara Controller, Form Request, API Resource, Model, dan Policy.

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AuthController.php          # register, login, logout
│   │   │       ├── CategoryController.php      # CRUD kategori
│   │   │       ├── TransactionController.php   # CRUD + filter + sorting + pagination
│   │   │       └── DashboardController.php     # ringkasan & data grafik
│   │   ├── Requests/
│   │   │   ├── Auth/
│   │   │   │   ├── RegisterRequest.php
│   │   │   │   └── LoginRequest.php
│   │   │   ├── Category/
│   │   │   │   ├── StoreCategoryRequest.php
│   │   │   │   └── UpdateCategoryRequest.php
│   │   │   └── Transaction/
│   │   │       ├── StoreTransactionRequest.php
│   │   │       ├── UpdateTransactionRequest.php
│   │   │       └── IndexTransactionRequest.php  # validasi query params filter/sorting
│   │   └── Resources/
│   │       ├── UserResource.php
│   │       ├── CategoryResource.php
│   │       ├── TransactionResource.php
│   │       └── DashboardResource.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Category.php
│   │   └── Transaction.php
│   └── Policies/
│       ├── CategoryPolicy.php                  # otorisasi akses kategori per user
│       └── TransactionPolicy.php               # otorisasi akses transaksi per user
├── database/
│   ├── migrations/
│   │   ├── xxxx_create_users_table.php
│   │   ├── xxxx_create_personal_access_tokens_table.php
│   │   ├── xxxx_create_categories_table.php
│   │   └── xxxx_create_transactions_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── CategorySeeder.php                  # data kategori default
├── routes/
│   └── api.php                                 # semua route API
├── config/
│   ├── cors.php                                # konfigurasi CORS
│   └── sanctum.php
└── .env                                        # konfigurasi DB, APP_URL, FRONTEND_URL
```

### 5.1 Contoh Implementasi TransactionController (Index)

```php
public function index(IndexTransactionRequest $request)
{
    $query = Transaction::with('category')
        ->where('user_id', auth()->id());

    // Filter
    if ($request->filled('type')) {
        $query->where('type', $request->type);
    }
    if ($request->filled('category_id')) {
        $query->where('category_id', $request->category_id);
    }
    if ($request->filled('transaction_date_from')) {
        $query->whereDate('transaction_date', '>=', $request->transaction_date_from);
    }
    if ($request->filled('transaction_date_to')) {
        $query->whereDate('transaction_date', '<=', $request->transaction_date_to);
    }

    // Sorting
    $sortBy    = $request->input('sort_by', 'transaction_date');
    $sortOrder = $request->input('sort_order', 'desc');
    $query->orderBy($sortBy, $sortOrder);

    // Pagination
    $perPage = $request->input('per_page', 10);
    $result  = $query->paginate($perPage);

    return TransactionResource::collection($result);
}
```

---

## 6. Struktur Folder Frontend (Vue 3 + Vite)

Struktur folder Frontend (Vue 3) mengikuti pola pemisahan antara komponen reusable, halaman (*views*), store Pinia, router, dan service layer Axios.

```
frontend/
├── public/
│   └── favicon.ico
├── src/
│   ├── assets/                         # gambar, font, ikon statis
│   ├── components/
│   │   ├── common/
│   │   │   ├── AppNavbar.vue           # navigasi utama
│   │   │   ├── AppPagination.vue       # komponen pagination reusable
│   │   │   ├── AppLoadingSpinner.vue   # indikator loading
│   │   │   └── AppAlert.vue            # pesan error/sukses
│   │   ├── category/
│   │   │   ├── CategoryForm.vue        # form tambah/edit kategori
│   │   │   └── CategoryTable.vue       # tabel daftar kategori
│   │   ├── transaction/
│   │   │   ├── TransactionForm.vue     # form tambah/edit transaksi
│   │   │   ├── TransactionTable.vue    # tabel daftar transaksi
│   │   │   ├── TransactionFilter.vue   # panel filter & sorting
│   │   │   └── TransactionPagination.vue
│   │   └── dashboard/
│   │       ├── SummaryCard.vue         # kartu total pemasukan/pengeluaran/saldo
│   │       └── MonthlyChart.vue        # grafik Chart.js bulanan
│   ├── views/
│   │   ├── auth/
│   │   │   ├── LoginView.vue
│   │   │   └── RegisterView.vue
│   │   ├── DashboardView.vue
│   │   ├── CategoryView.vue
│   │   └── TransactionView.vue
│   ├── stores/
│   │   ├── authStore.js                # state autentikasi & token
│   │   ├── categoryStore.js            # state & actions CRUD kategori
│   │   ├── transactionStore.js         # state & actions CRUD + filter + pagination
│   │   └── dashboardStore.js           # state & actions data dashboard
│   ├── router/
│   │   └── index.js                    # definisi route & navigation guard
│   ├── services/
│   │   └── api.js                      # instance Axios + interceptor
│   ├── App.vue
│   └── main.js
├── index.html
├── vite.config.js
├── tailwind.config.js
└── package.json
```

---

## 7. Desain State Management (Pinia)

### 7.1 authStore

```
State:
  - user: Object | null       → data pengguna yang login
  - token: String | null      → Bearer token dari localStorage
  - isLoading: Boolean        → loading state untuk operasi auth

Actions:
  - register(payload)         → POST /api/register, simpan token
  - login(payload)            → POST /api/login, simpan token
  - logout()                  → POST /api/logout, hapus token
  - initAuth()                → baca token dari localStorage saat app dimuat

Getters:
  - isAuthenticated           → Boolean, true jika token tidak null
```

### 7.2 categoryStore

```
State:
  - categories: Array         → daftar kategori milik pengguna
  - isLoading: Boolean        → loading state

Actions:
  - fetchCategories()         → GET /api/categories
  - createCategory(payload)   → POST /api/categories
  - updateCategory(id, data)  → PUT /api/categories/{id}
  - deleteCategory(id)        → DELETE /api/categories/{id}

Getters:
  - incomeCategories          → filter categories dengan type === 'income'
  - expenseCategories         → filter categories dengan type === 'expense'
```

### 7.3 transactionStore

```
State:
  - transactions: Array       → daftar transaksi halaman saat ini
  - meta: Object              → metadata pagination {current_page, per_page, total, last_page, from, to}
  - filters: Object           → {type, category_id, transaction_date_from, transaction_date_to}
  - sorting: Object           → {sort_by: 'transaction_date', sort_order: 'desc'}
  - isLoading: Boolean        → loading state

Actions:
  - fetchTransactions()       → GET /api/transactions dengan semua params aktif
  - setFilter(key, value)     → update filters, reset page ke 1, panggil fetchTransactions
  - resetFilters()            → reset semua filters & sorting ke default
  - setPage(page)             → update current_page, panggil fetchTransactions
  - setSorting(by, order)     → update sorting, reset page ke 1, panggil fetchTransactions
  - createTransaction(data)   → POST /api/transactions
  - updateTransaction(id,data)→ PUT /api/transactions/{id}
  - deleteTransaction(id)     → DELETE /api/transactions/{id}
```

### 7.4 dashboardStore

```
State:
  - summary: Object           → {total_income, total_expense, balance}
  - monthlyChart: Array       → [{month, income, expense}, ...]
  - isLoading: Boolean        → loading state

Actions:
  - fetchDashboard()          → GET /api/dashboard, update summary & monthlyChart
```

### 7.5 Diagram Alur State

```
View/Component
    |
    |-- dispatch action (misal: fetchTransactions)
    |
    v
Pinia Store Action
    |
    |-- set isLoading = true
    |-- panggil api.js (Axios)
    |
    v
Axios (services/api.js)
    |
    |-- tambahkan Bearer token ke header
    |-- kirim HTTP request ke Laravel API
    |
    v
Laravel API
    |
    |-- validasi token (Sanctum)
    |-- proses query
    |-- kembalikan JSON response
    |
    v
Pinia Store Action (callback)
    |
    |-- update state (transactions, meta, dll)
    |-- set isLoading = false
    |
    v
Vue Component (reaktif)
    |
    |-- tampilkan data terbaru
    |-- sembunyikan loading indicator
```

---

## 8. Dashboard Flow dan Integrasi Chart.js

### 8.1 Alur Pengambilan Data Dashboard

```
DashboardView.vue (onMounted)
    |
    |-- dashboardStore.fetchDashboard()
    |       |
    |       |-- isLoading = true
    |       |-- GET /api/dashboard
    |       |
    |       v
    |   Laravel DashboardController
    |       |
    |       |-- Hitung total_income:
    |       |   Transaction::where('user_id', id)
    |       |              ->where('type', 'income')
    |       |              ->sum('amount')
    |       |
    |       |-- Hitung total_expense (sama, type='expense')
    |       |
    |       |-- Hitung balance = total_income - total_expense
    |       |
    |       |-- Ambil data 12 bulan terakhir:
    |       |   Transaction::selectRaw(
    |       |     'DATE_FORMAT(transaction_date, "%Y-%m") as month,
    |       |      SUM(CASE WHEN type="income" THEN amount ELSE 0 END) as income,
    |       |      SUM(CASE WHEN type="expense" THEN amount ELSE 0 END) as expense'
    |       |   )->where('user_id', id)
    |       |    ->where('transaction_date', '>=', now()->subMonths(11)->startOfMonth())
    |       |    ->groupBy('month')
    |       |    ->orderBy('month')
    |       |    ->get()
    |       |
    |       |-- Return DashboardResource
    |       |
    |       v
    |   dashboardStore: update summary & monthlyChart
    |   isLoading = false
    |
    v
SummaryCard.vue → tampilkan total_income, total_expense, balance
MonthlyChart.vue → render Chart.js dengan data monthlyChart
```

### 8.2 Konfigurasi Chart.js

```javascript
// components/dashboard/MonthlyChart.vue
const chartData = computed(() => ({
  labels: dashboardStore.monthlyChart.map(item => item.month),
  datasets: [
    {
      label: 'Pemasukan',
      data: dashboardStore.monthlyChart.map(item => parseFloat(item.income)),
      backgroundColor: 'rgba(34, 197, 94, 0.6)',   // hijau
      borderColor: 'rgb(34, 197, 94)',
    },
    {
      label: 'Pengeluaran',
      data: dashboardStore.monthlyChart.map(item => parseFloat(item.expense)),
      backgroundColor: 'rgba(239, 68, 68, 0.6)',   // merah
      borderColor: 'rgb(239, 68, 68)',
    }
  ]
}))

const chartOptions = {
  responsive: true,
  plugins: { legend: { position: 'top' } },
  scales: { y: { beginAtZero: true } }
}
// Tipe grafik: 'bar' (default) atau 'line'
```

---

## 9. Pagination, Filter, Sorting, dan Loading State Flow

### 9.1 Alur Lengkap di Frontend

```
TransactionView.vue
    |
    |-- onMounted → transactionStore.fetchTransactions()
    |
    |-- TransactionFilter.vue
    |       |-- Pilih type       → setFilter('type', value)
    |       |-- Pilih category   → setFilter('category_id', value)
    |       |-- Pilih date range → setFilter('transaction_date_from', val)
    |       |                      setFilter('transaction_date_to', val)
    |       |-- Pilih sort_by    → setSorting(by, order)
    |       |-- Klik Reset       → resetFilters()
    |
    |-- AppPagination.vue
    |       |-- Klik halaman N   → setPage(N)
    |
    v
transactionStore.fetchTransactions()
    |
    |-- Bangun query params dari state:
    |   { page, per_page, type, category_id,
    |     transaction_date_from, transaction_date_to,
    |     sort_by, sort_order }
    |
    |-- isLoading = true
    |-- Kirim GET /api/transactions?{params}
    |-- Terima response → update transactions & meta
    |-- isLoading = false
```

### 9.2 Loading State

```
isLoading (per store)
    |
    |-- true  → tampilkan <AppLoadingSpinner />
    |        → nonaktifkan tombol filter, pagination, submit form
    |
    |-- false → sembunyikan spinner
    |        → tampilkan data / pesan error
```

Setiap store memiliki `isLoading` sendiri sehingga loading di halaman transaksi tidak mempengaruhi loading di halaman dashboard.

### 9.3 Alur di Backend (Query Builder)

```
TransactionController@index
    |
    |-- Base query: Transaction::with('category')->where('user_id', auth()->id())
    |
    |-- [Opsional] Filter type            → ->where('type', $request->type)
    |-- [Opsional] Filter category_id     → ->where('category_id', $request->category_id)
    |-- [Opsional] Filter date_from       → ->whereDate('transaction_date', '>=', ...)
    |-- [Opsional] Filter date_to         → ->whereDate('transaction_date', '<=', ...)
    |
    |-- Sorting                           → ->orderBy($sortBy, $sortOrder)
    |
    |-- Pagination                        → ->paginate($perPage)
    |
    v
TransactionResource::collection($result)
    → format data + meta pagination otomatis dari Laravel paginator
```

---

## 10. Pertimbangan Deployment

Bagian ini menjelaskan opsi dan konfigurasi yang direkomendasikan untuk men-deploy Backend (Laravel 12) dan Frontend (Vue 3) ke lingkungan produksi.

### 10.1 Backend (Laravel 12)

**Opsi A — VPS (Ubuntu + Nginx + PHP-FPM)**
```
Nginx (port 80/443)
    └── PHP-FPM (Laravel)
            └── MySQL (lokal atau managed DB)
```
- Konfigurasi `.env`: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://api.domain.com`
- Jalankan: `php artisan config:cache`, `php artisan route:cache`, `php artisan migrate --force`
- Konfigurasi `config/cors.php`: set `allowed_origins` ke domain frontend
- Gunakan HTTPS (Let's Encrypt / Certbot)

**Opsi B — Shared Hosting**
- Upload folder `public/` ke `public_html/`
- Sisanya di luar `public_html/`
- Buat symlink atau modifikasi `index.php` untuk menunjuk ke root Laravel

### 10.2 Frontend (Vue 3 + Vite)

**Build produksi:**
```bash
npm run build
# Output: dist/
```

**Opsi A — Static Hosting (Netlify / Vercel / GitHub Pages)**
- Upload folder `dist/` ke platform
- Konfigurasi redirect: semua path → `index.html` (untuk Vue Router history mode)
- Set environment variable: `VITE_API_BASE_URL=https://api.domain.com`

**Opsi B — Nginx (serve static files)**
```nginx
server {
    listen 80;
    root /var/www/frontend/dist;
    index index.html;

    location / {
        try_files $uri $uri/ /index.html;  # penting untuk Vue Router
    }
}
```

### 10.3 Konfigurasi CORS Backend

```php
// config/cors.php
'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:5173')],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
'supports_credentials' => false,
```

### 10.4 Environment Variables

**Backend `.env` (produksi):**
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.domain.com
FRONTEND_URL=https://app.domain.com
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=catatan_keuangan
DB_USERNAME=db_user
DB_PASSWORD=db_password
SANCTUM_STATEFUL_DOMAINS=app.domain.com
```

**Frontend `.env.production`:**
```
VITE_API_BASE_URL=https://api.domain.com
```

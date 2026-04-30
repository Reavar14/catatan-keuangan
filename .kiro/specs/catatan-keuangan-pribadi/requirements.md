# Dokumen Kebutuhan (Requirements)

## Pendahuluan

Aplikasi **Catatan Keuangan Pribadi** adalah sistem manajemen keuangan berbasis web yang memungkinkan pengguna mencatat, mengkategorikan, dan menganalisis transaksi keuangan pribadi mereka. Aplikasi ini dibangun dengan arsitektur terpisah antara backend (Laravel 12 + MySQL) dan frontend (Vue 3), berkomunikasi melalui REST API yang diamankan dengan Laravel Sanctum.

Pengguna dapat mendaftar akun, mencatat pemasukan dan pengeluaran, mengelola kategori transaksi, serta melihat ringkasan keuangan melalui dashboard interaktif dengan grafik bulanan.

## Glosarium

- **Sistem**: Keseluruhan aplikasi Catatan Keuangan Pribadi (backend + frontend)
- **API**: Backend Laravel 12 yang menyediakan endpoint REST
- **Klien**: Aplikasi frontend Vue 3 yang dikonsumsi pengguna
- **Pengguna**: Individu yang telah terdaftar dan terautentikasi dalam sistem
- **Tamu**: Individu yang belum terautentikasi
- **Token**: Bearer token yang diterbitkan oleh Laravel Sanctum setelah login berhasil
- **Transaksi**: Catatan keuangan tunggal berupa pemasukan atau pengeluaran
- **Kategori**: Pengelompokan transaksi berdasarkan nama dan tipe (pemasukan/pengeluaran)
- **Dashboard**: Halaman ringkasan yang menampilkan statistik dan grafik keuangan pengguna
- **Saldo**: Selisih antara total pemasukan dan total pengeluaran pengguna
- **Validator**: Komponen sistem yang memvalidasi data masukan dari permintaan API
- **Autentikator**: Komponen sistem yang menangani proses registrasi, login, dan logout
- **Pengelola_Kategori**: Komponen sistem yang menangani CRUD kategori transaksi
- **Pengelola_Transaksi**: Komponen sistem yang menangani CRUD transaksi
- **Penghitung_Dashboard**: Komponen sistem yang menghitung dan menyajikan data ringkasan keuangan
- **Pagination**: Mekanisme pembagian data menjadi beberapa halaman untuk efisiensi pemuatan
- **Filter**: Parameter query yang digunakan untuk menyaring data transaksi berdasarkan kriteria tertentu
- **Sorting**: Parameter query yang digunakan untuk mengurutkan data transaksi berdasarkan kolom dan arah tertentu
- **Loading State**: Kondisi antarmuka yang menunjukkan bahwa aplikasi sedang menunggu respons dari API

---

## Kebutuhan

### Kebutuhan 1: Registrasi Pengguna

**User Story:** Sebagai Tamu, saya ingin mendaftar akun baru, agar saya dapat menggunakan fitur pencatatan keuangan pribadi.

#### Kriteria Penerimaan

1. THE Autentikator SHALL menyediakan endpoint `POST /api/register` untuk pendaftaran akun baru.
2. WHEN Tamu mengirimkan nama, email, dan kata sandi yang valid, THE Autentikator SHALL membuat akun pengguna baru dan mengembalikan Token beserta data pengguna dengan HTTP status 201.
3. THE Validator SHALL memastikan field `name` wajib diisi dan memiliki panjang maksimal 255 karakter.
4. THE Validator SHALL memastikan field `email` wajib diisi, berformat email valid, dan belum terdaftar dalam sistem.
5. THE Validator SHALL memastikan field `password` wajib diisi, memiliki panjang minimal 8 karakter, dan dikonfirmasi dengan field `password_confirmation`.
6. IF data registrasi tidak memenuhi aturan validasi, THEN THE Autentikator SHALL mengembalikan pesan kesalahan validasi dengan HTTP status 422.

---

### Kebutuhan 2: Login Pengguna

**User Story:** Sebagai Tamu, saya ingin masuk ke akun saya, agar saya dapat mengakses data keuangan pribadi saya.

#### Kriteria Penerimaan

1. THE Autentikator SHALL menyediakan endpoint `POST /api/login` untuk proses autentikasi.
2. WHEN Tamu mengirimkan email dan kata sandi yang cocok dengan data terdaftar, THE Autentikator SHALL mengembalikan Token beserta data pengguna dengan HTTP status 200.
3. IF email atau kata sandi tidak cocok dengan data terdaftar, THEN THE Autentikator SHALL mengembalikan pesan kesalahan autentikasi dengan HTTP status 401.
4. THE Validator SHALL memastikan field `email` dan `password` wajib diisi pada permintaan login.

---

### Kebutuhan 3: Logout Pengguna

**User Story:** Sebagai Pengguna, saya ingin keluar dari akun saya, agar sesi saya berakhir dengan aman.

#### Kriteria Penerimaan

1. THE Autentikator SHALL menyediakan endpoint `POST /api/logout` yang dilindungi autentikasi Sanctum.
2. WHEN Pengguna mengirimkan permintaan logout dengan Token yang valid, THE Autentikator SHALL mencabut Token aktif dan mengembalikan pesan konfirmasi dengan HTTP status 200.
3. IF permintaan logout dikirim tanpa Token yang valid, THEN THE Autentikator SHALL mengembalikan respons tidak terautentikasi dengan HTTP status 401.

---

### Kebutuhan 4: Pengelolaan Kategori Transaksi

**User Story:** Sebagai Pengguna, saya ingin mengelola kategori transaksi saya sendiri, agar saya dapat mengorganisir transaksi sesuai kebutuhan.

#### Kriteria Penerimaan

1. THE Pengelola_Kategori SHALL menyediakan endpoint CRUD kategori di bawah prefix `/api/categories` yang dilindungi autentikasi Sanctum.
2. WHEN Pengguna mengirimkan permintaan `GET /api/categories`, THE Pengelola_Kategori SHALL mengembalikan daftar seluruh kategori milik Pengguna tersebut dengan HTTP status 200.
3. WHEN Pengguna mengirimkan nama kategori dan tipe kategori yang valid melalui `POST /api/categories`, THE Pengelola_Kategori SHALL membuat kategori baru dan mengembalikan data kategori dengan HTTP status 201.
4. WHEN Pengguna mengirimkan permintaan `GET /api/categories/{id}`, THE Pengelola_Kategori SHALL mengembalikan detail kategori dengan HTTP status 200.
5. WHEN Pengguna mengirimkan data yang valid melalui `PUT /api/categories/{id}`, THE Pengelola_Kategori SHALL memperbarui kategori dan mengembalikan data terbaru dengan HTTP status 200.
6. WHEN Pengguna mengirimkan permintaan `DELETE /api/categories/{id}`, THE Pengelola_Kategori SHALL menghapus kategori dan mengembalikan respons sukses dengan HTTP status 200.
7. THE Validator SHALL memastikan field `name` wajib diisi dan memiliki panjang maksimal 100 karakter pada operasi buat dan perbarui kategori.
8. THE Validator SHALL memastikan field `type` wajib diisi dan hanya menerima nilai `income` atau `expense` pada operasi buat dan perbarui kategori.
9. IF Pengguna mengakses atau memodifikasi kategori yang bukan miliknya, THEN THE Pengelola_Kategori SHALL mengembalikan respons tidak ditemukan dengan HTTP status 404.
10. THE Pengelola_Kategori SHALL memastikan setiap kategori hanya dapat diakses oleh Pengguna yang membuatnya (isolasi data per pengguna).

---

### Kebutuhan 5: Pengelolaan Transaksi

**User Story:** Sebagai Pengguna, saya ingin mencatat dan mengelola transaksi keuangan saya, agar saya dapat melacak pemasukan dan pengeluaran secara akurat.

#### Kriteria Penerimaan

1. THE Pengelola_Transaksi SHALL menyediakan endpoint CRUD transaksi di bawah prefix `/api/transactions` yang dilindungi autentikasi Sanctum.
2. WHEN Pengguna mengirimkan permintaan `GET /api/transactions`, THE Pengelola_Transaksi SHALL mengembalikan daftar seluruh transaksi milik Pengguna tersebut beserta data kategori terkait dengan HTTP status 200.
3. WHEN Pengguna mengirimkan data transaksi yang valid melalui `POST /api/transactions`, THE Pengelola_Transaksi SHALL membuat transaksi baru dan mengembalikan data transaksi dengan HTTP status 201.
4. WHEN Pengguna mengirimkan permintaan `GET /api/transactions/{id}`, THE Pengelola_Transaksi SHALL mengembalikan detail transaksi beserta data kategori terkait dengan HTTP status 200.
5. WHEN Pengguna mengirimkan data yang valid melalui `PUT /api/transactions/{id}`, THE Pengelola_Transaksi SHALL memperbarui transaksi dan mengembalikan data terbaru dengan HTTP status 200.
6. WHEN Pengguna mengirimkan permintaan `DELETE /api/transactions/{id}`, THE Pengelola_Transaksi SHALL menghapus transaksi dan mengembalikan respons sukses dengan HTTP status 200.
7. THE Validator SHALL memastikan field `title` wajib diisi dan memiliki panjang maksimal 255 karakter.
8. THE Validator SHALL memastikan field `amount` wajib diisi, bertipe numerik, dan bernilai lebih dari 0.
9. THE Validator SHALL memastikan field `type` wajib diisi dan hanya menerima nilai `income` atau `expense`.
10. THE Validator SHALL memastikan field `category_id` wajib diisi, merujuk pada kategori yang ada, dan merupakan milik Pengguna yang sedang terautentikasi.
11. THE Validator SHALL memastikan field `transaction_date` wajib diisi dan berformat tanggal valid (YYYY-MM-DD).
12. THE Validator SHALL memastikan field `notes` bersifat opsional dan memiliki panjang maksimal 1000 karakter.
13. IF Pengguna mengakses atau memodifikasi transaksi yang bukan miliknya, THEN THE Pengelola_Transaksi SHALL mengembalikan respons tidak ditemukan dengan HTTP status 404.
14. THE Pengelola_Transaksi SHALL memastikan setiap transaksi hanya dapat diakses oleh Pengguna yang membuatnya (isolasi data per pengguna).

---

### Kebutuhan 6: Dashboard Ringkasan Keuangan

**User Story:** Sebagai Pengguna, saya ingin melihat ringkasan keuangan saya di dashboard, agar saya dapat memahami kondisi keuangan pribadi secara cepat.

#### Kriteria Penerimaan

1. THE Penghitung_Dashboard SHALL menyediakan endpoint `GET /api/dashboard` yang dilindungi autentikasi Sanctum.
2. WHEN Pengguna mengakses endpoint dashboard, THE Penghitung_Dashboard SHALL mengembalikan total pemasukan, total pengeluaran, dan saldo milik Pengguna tersebut dengan HTTP status 200.
3. THE Penghitung_Dashboard SHALL menghitung saldo sebagai selisih antara total pemasukan dan total pengeluaran milik Pengguna.
4. WHEN Pengguna mengakses endpoint dashboard, THE Penghitung_Dashboard SHALL mengembalikan data agregat transaksi bulanan selama 12 bulan terakhir, dikelompokkan per bulan dengan total pemasukan dan pengeluaran per bulan.
5. THE Penghitung_Dashboard SHALL memastikan seluruh kalkulasi hanya menggunakan data transaksi milik Pengguna yang sedang terautentikasi.

---

### Kebutuhan 7: Tampilan Antarmuka Autentikasi (Frontend)

**User Story:** Sebagai Tamu, saya ingin menggunakan antarmuka web untuk mendaftar dan masuk, agar saya dapat mengakses aplikasi dengan mudah.

#### Kriteria Penerimaan

1. THE Klien SHALL menyediakan halaman registrasi dengan formulir yang memuat field nama, email, kata sandi, dan konfirmasi kata sandi.
2. THE Klien SHALL menyediakan halaman login dengan formulir yang memuat field email dan kata sandi.
3. WHEN Tamu berhasil login, THE Klien SHALL menyimpan Token ke dalam penyimpanan lokal (localStorage) dan mengarahkan Tamu ke halaman dashboard.
4. WHEN Pengguna berhasil logout, THE Klien SHALL menghapus Token dari penyimpanan lokal dan mengarahkan Pengguna ke halaman login.
5. WHILE Pengguna belum terautentikasi, THE Klien SHALL mencegah akses ke halaman yang memerlukan autentikasi dan mengarahkan ke halaman login.
6. WHILE Pengguna sudah terautentikasi, THE Klien SHALL mencegah akses ke halaman login dan registrasi dan mengarahkan ke halaman dashboard.
7. IF permintaan API mengembalikan HTTP status 401, THEN THE Klien SHALL menghapus Token dari penyimpanan lokal dan mengarahkan Pengguna ke halaman login.

---

### Kebutuhan 8: Tampilan Antarmuka Kategori (Frontend)

**User Story:** Sebagai Pengguna, saya ingin mengelola kategori melalui antarmuka web, agar saya dapat mengatur pengelompokan transaksi dengan mudah.

#### Kriteria Penerimaan

1. THE Klien SHALL menyediakan halaman daftar kategori yang menampilkan seluruh kategori milik Pengguna beserta nama dan tipe kategori.
2. THE Klien SHALL menyediakan formulir tambah kategori dengan field nama dan tipe (pemasukan/pengeluaran).
3. THE Klien SHALL menyediakan formulir edit kategori yang menampilkan data kategori yang dipilih.
4. WHEN Pengguna berhasil membuat, memperbarui, atau menghapus kategori, THE Klien SHALL memperbarui daftar kategori yang ditampilkan tanpa memuat ulang halaman penuh.
5. IF operasi CRUD kategori gagal karena kesalahan validasi, THEN THE Klien SHALL menampilkan pesan kesalahan yang sesuai di dekat field yang bermasalah.

---

### Kebutuhan 9: Tampilan Antarmuka Transaksi (Frontend)

**User Story:** Sebagai Pengguna, saya ingin mengelola transaksi melalui antarmuka web, agar saya dapat mencatat keuangan dengan mudah.

#### Kriteria Penerimaan

1. THE Klien SHALL menyediakan halaman daftar transaksi yang menampilkan seluruh transaksi milik Pengguna beserta judul, nominal, tipe, kategori, dan tanggal transaksi.
2. THE Klien SHALL menyediakan formulir tambah transaksi dengan field judul, nominal, tipe, kategori, tanggal transaksi, dan catatan.
3. THE Klien SHALL menyediakan formulir edit transaksi yang menampilkan data transaksi yang dipilih.
4. WHEN Pengguna memilih tipe transaksi pada formulir, THE Klien SHALL memfilter daftar pilihan kategori sehingga hanya menampilkan kategori dengan tipe yang sesuai.
5. WHEN Pengguna berhasil membuat, memperbarui, atau menghapus transaksi, THE Klien SHALL memperbarui daftar transaksi yang ditampilkan tanpa memuat ulang halaman penuh.
6. IF operasi CRUD transaksi gagal karena kesalahan validasi, THEN THE Klien SHALL menampilkan pesan kesalahan yang sesuai di dekat field yang bermasalah.

---

### Kebutuhan 10: Tampilan Antarmuka Dashboard (Frontend)

**User Story:** Sebagai Pengguna, saya ingin melihat dashboard keuangan yang informatif, agar saya dapat memantau kondisi keuangan saya secara visual.

#### Kriteria Penerimaan

1. THE Klien SHALL menyediakan halaman dashboard yang menampilkan kartu ringkasan berisi total pemasukan, total pengeluaran, dan saldo.
2. THE Klien SHALL menampilkan nilai saldo dengan warna berbeda: hijau jika saldo bernilai positif atau nol, merah jika saldo bernilai negatif.
3. THE Klien SHALL menyediakan grafik batang atau grafik garis bulanan menggunakan Chart.js yang menampilkan perbandingan total pemasukan dan pengeluaran per bulan selama 12 bulan terakhir.
4. WHEN halaman dashboard dimuat, THE Klien SHALL mengambil data terbaru dari endpoint `/api/dashboard` dan memperbarui seluruh komponen tampilan.
5. THE Klien SHALL menggunakan Pinia store untuk menyimpan dan mengelola state data dashboard secara terpusat.

---

### Kebutuhan 11: Pagination pada Daftar Transaksi

**User Story:** Sebagai Pengguna, saya ingin daftar transaksi ditampilkan secara bertahap, agar halaman tidak lambat meskipun data transaksi sangat banyak.

#### Kriteria Penerimaan

1. THE Pengelola_Transaksi SHALL mendukung pagination pada endpoint `GET /api/transactions` melalui query parameter `page` dan `per_page`.
2. WHEN Pengguna mengirimkan permintaan `GET /api/transactions` dengan parameter `page`, THE Pengelola_Transaksi SHALL mengembalikan data transaksi sesuai halaman yang diminta beserta metadata pagination (total data, halaman saat ini, jumlah halaman, jumlah data per halaman) dengan HTTP status 200.
3. THE Pengelola_Transaksi SHALL menggunakan nilai default `per_page` sebesar 10 jika parameter `per_page` tidak disertakan dalam permintaan.
4. IF parameter `page` yang dikirimkan melebihi jumlah halaman yang tersedia, THEN THE Pengelola_Transaksi SHALL mengembalikan data kosong dengan metadata pagination yang tetap akurat.
5. THE Klien SHALL menyediakan komponen navigasi pagination pada halaman daftar transaksi yang menampilkan tombol halaman sebelumnya, halaman berikutnya, dan nomor halaman.
6. WHEN Pengguna berpindah halaman melalui navigasi pagination, THE Klien SHALL mengambil data transaksi halaman yang dipilih dari API dan memperbarui daftar yang ditampilkan tanpa memuat ulang halaman penuh.
7. THE Klien SHALL menampilkan informasi ringkasan pagination seperti "Menampilkan X-Y dari Z transaksi" pada halaman daftar transaksi.

---

### Kebutuhan 12: Filter dan Sorting Transaksi

**User Story:** Sebagai Pengguna, saya ingin memfilter dan mengurutkan daftar transaksi berdasarkan kriteria tertentu, agar saya dapat menemukan dan menganalisis transaksi yang relevan dengan cepat.

#### Kriteria Penerimaan

1. THE Pengelola_Transaksi SHALL mendukung filter berdasarkan tipe transaksi pada endpoint `GET /api/transactions` melalui query parameter `type` dengan nilai `income` atau `expense`.
2. THE Pengelola_Transaksi SHALL mendukung filter berdasarkan kategori pada endpoint `GET /api/transactions` melalui query parameter `category_id`.
3. THE Pengelola_Transaksi SHALL mendukung filter berdasarkan rentang tanggal pada endpoint `GET /api/transactions` melalui query parameter `transaction_date_from` dan `transaction_date_to` dengan format tanggal YYYY-MM-DD.
4. THE Pengelola_Transaksi SHALL mendukung sorting pada endpoint `GET /api/transactions` melalui query parameter `sort_by` dengan nilai `transaction_date` atau `amount`, dan query parameter `sort_order` dengan nilai `asc` atau `desc`.
5. WHEN Pengguna mengirimkan satu atau lebih parameter filter, THE Pengelola_Transaksi SHALL mengembalikan hanya transaksi yang memenuhi seluruh kriteria filter yang diberikan dengan HTTP status 200.
6. WHEN Pengguna mengirimkan parameter sorting, THE Pengelola_Transaksi SHALL mengembalikan daftar transaksi yang diurutkan sesuai kolom dan arah yang ditentukan dengan HTTP status 200.
7. THE Pengelola_Transaksi SHALL menggunakan nilai default `sort_by` sebesar `transaction_date` dan `sort_order` sebesar `desc` jika parameter sorting tidak disertakan dalam permintaan.
8. THE Validator SHALL memastikan parameter `type` jika disertakan hanya menerima nilai `income` atau `expense`.
9. THE Validator SHALL memastikan parameter `category_id` jika disertakan merujuk pada kategori yang dimiliki oleh Pengguna yang sedang terautentikasi.
10. THE Validator SHALL memastikan parameter `transaction_date_from` dan `transaction_date_to` jika disertakan berformat tanggal valid (YYYY-MM-DD), dan `transaction_date_from` tidak boleh lebih besar dari `transaction_date_to`.
11. THE Validator SHALL memastikan parameter `sort_by` jika disertakan hanya menerima nilai `transaction_date` atau `amount`.
12. THE Validator SHALL memastikan parameter `sort_order` jika disertakan hanya menerima nilai `asc` atau `desc`.
13. IF parameter filter atau sorting tidak valid, THEN THE Pengelola_Transaksi SHALL mengembalikan pesan kesalahan validasi dengan HTTP status 422.
14. THE Klien SHALL menyediakan panel atau area filter pada halaman daftar transaksi dengan kontrol untuk memilih tipe transaksi, kategori, tanggal mulai, dan tanggal akhir.
15. THE Klien SHALL menyediakan kontrol sorting pada halaman daftar transaksi untuk memilih kolom pengurutan (`transaction_date` atau `amount`) dan arah pengurutan (`asc` atau `desc`).
16. WHEN Pengguna menerapkan filter atau mengubah sorting, THE Klien SHALL mengirimkan parameter yang sesuai ke API dan memperbarui daftar transaksi yang ditampilkan serta mereset pagination ke halaman pertama.
17. THE Klien SHALL menyediakan tombol atau aksi untuk mereset seluruh filter dan sorting aktif sehingga daftar transaksi kembali menampilkan semua data dengan urutan default.

---

### Kebutuhan 13: Loading State pada Frontend

**User Story:** Sebagai Pengguna, saya ingin melihat indikator loading saat data sedang dimuat, agar saya mengetahui bahwa aplikasi sedang memproses permintaan dan tidak mengira aplikasi berhenti.

#### Kriteria Penerimaan

1. THE Klien SHALL menampilkan loading indicator pada halaman dashboard selama permintaan ke endpoint `/api/dashboard` belum mendapatkan respons.
2. THE Klien SHALL menampilkan loading indicator pada halaman daftar kategori selama permintaan ke endpoint `/api/categories` belum mendapatkan respons.
3. THE Klien SHALL menampilkan loading indicator pada halaman daftar transaksi selama permintaan ke endpoint `/api/transactions` belum mendapatkan respons, termasuk saat berpindah halaman pagination dan saat menerapkan filter.
4. WHEN permintaan API sedang berlangsung, THE Klien SHALL menonaktifkan tombol atau kontrol yang dapat memicu permintaan baru untuk mencegah permintaan ganda.
5. WHEN respons API diterima (baik berhasil maupun gagal), THE Klien SHALL menyembunyikan loading indicator dan menampilkan konten atau pesan kesalahan yang sesuai.
6. THE Klien SHALL menggunakan Pinia store untuk menyimpan state loading secara terpusat sehingga komponen yang berbeda dapat mengakses status loading yang konsisten.

---

### Kebutuhan 14: Struktur Proyek dan Konfigurasi Teknis

**User Story:** Sebagai pengembang, saya ingin proyek memiliki struktur yang rapi dan mengikuti best practice, agar pengembangan dan pemeliharaan dapat dilakukan dengan efisien.

#### Kriteria Penerimaan

1. THE Sistem SHALL menggunakan Laravel 12 sebagai framework backend dengan MySQL sebagai basis data.
2. THE Sistem SHALL menggunakan Laravel Sanctum untuk mekanisme autentikasi API berbasis token.
3. THE Sistem SHALL menggunakan API Resource Laravel untuk memformat seluruh respons JSON dari endpoint API.
4. THE Sistem SHALL menyediakan migration database untuk tabel `users`, `categories`, dan `transactions`.
5. THE Sistem SHALL menyediakan seeder database untuk mengisi data awal kategori default.
6. THE Sistem SHALL menggunakan Vue 3 Composition API dengan Vite sebagai build tool frontend.
7. THE Sistem SHALL menggunakan Vue Router untuk manajemen navigasi halaman pada frontend.
8. THE Sistem SHALL menggunakan Pinia sebagai state management pada frontend.
9. THE Sistem SHALL menggunakan Axios untuk seluruh komunikasi HTTP antara Klien dan API.
10. THE Sistem SHALL menggunakan Tailwind CSS untuk seluruh styling antarmuka pengguna.
11. THE Sistem SHALL menggunakan Chart.js untuk rendering grafik pada halaman dashboard.
12. THE Sistem SHALL mengonfigurasi CORS pada backend agar Klien dapat berkomunikasi dengan API dari origin yang berbeda.

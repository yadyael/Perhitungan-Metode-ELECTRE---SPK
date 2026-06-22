# 🍁 SPK ELECTRE I — Prioritas Pembangunan Manusia Jawa Timur & Jawa Tengah

> Sistem Pendukung Keputusan berbasis metode **ELECTRE I** dan **Nilai Phi (Φ)** untuk menentukan prioritas daerah dalam peningkatan Indeks Pembangunan Manusia (IPM) di Provinsi Jawa Timur dan Jawa Tengah Tahun 2024.

---

## ✨ Fitur Utama

- 🔐 **Autentikasi** — Login admin dengan session PHP native
- 📋 **Data Kriteria** — CRUD kriteria dinamis (kode, nama, bobot, tipe benefit/cost)
- 🗺️ **Data Alternatif** — CRUD kabupaten/kota dengan filter provinsi
- 🔢 **Input Nilai** — Modal per-daerah dengan dropdown skala 1–5, status kelengkapan otomatis
- ⚙️ **Perhitungan ELECTRE I** — Satu tombol hitung untuk seluruh tahapan:
  - Matriks Keputusan
  - Normalisasi (R)
  - Matriks Ternormalisasi Terbobot (V)
  - Himpunan Concordance C(i,j) & Discordance D(i,j) — seluruh pasangan
  - Matriks Concordance & Discordance
  - Matriks Dominan Concordance (F) & Discordance (G)
  - Matriks Agregat Dominan (E)
  - Nilai Phi (Φ) & Ranking Akhir
- 📊 **Ranking Prioritas** — 4 kategori (Utama/Tinggi/Sedang/Cukup Baik), filter provinsi, diagram sorotan, dan breakdown nilai per kriteria vs rata-rata
- 📥 **Export Excel** — Satu file `.xlsx` dengan 13 sheet lengkap

---

## 🛠️ Teknologi

| Layer | Stack |
|---|---|
| Backend | PHP Native (tanpa framework) |
| Database | MySQL / MariaDB via MySQLi |
| Frontend | HTML, CSS, JavaScript |
| UI | Glassmorphism — Font: Outfit + Cormorant Garamond |
| Alert | SweetAlert2 |
| Chart | Chart.js |
| Excel | PhpSpreadsheet 1.30.x |

---

## 🗂️ Struktur Folder

```
Metode-ELECTRE/
├── auth/
│   └── login.php
├── config/
│   └── db.php
├── includes/
│   ├── sidebar.php
│   ├── header.php
│   └── electre_engine.php       ← seluruh logika kalkulasi ELECTRE
├── assets/
│   └── maple-leaf.png
├── kriteria/
│   └── index.php
├── alternatif/
│   └── index.php
├── nilai/
│   └── index.php
├── perhitungan_electre/
│   └── index.php
├── hasil/
│   ├── index.php                ← halaman hasil (existing)
│   └── ranking.php              ← halaman ranking prioritas
├── export_excel/
│   ├── index.php                ← UI tombol export
│   └── generate.php             ← generator file .xlsx
├── vendor/                      ← hasil composer install (tidak di-commit)
├── composer.json
├── composer.lock
└── dashboard.php
```

---

## ⚡ Instalasi & Setup

### Prasyarat
- PHP >= 8.0
- MySQL / MariaDB
- XAMPP / Laragon / server lokal lainnya
- [Composer](https://getcomposer.org/)

### Langkah

**1. Clone repositori**
```bash
git clone https://github.com/username/nama-repo.git
cd nama-repo
```

**2. Install dependensi PHP**
```bash
composer require "phpoffice/phpspreadsheet:^1.29"
```

> ⚠️ Jika menggunakan PHP 8.0, pastikan ekstensi `gd` aktif di `php.ini` (hapus `;` di depan `extension=gd`).

**3. Import database**

Buat database baru di phpMyAdmin, lalu import file `electre_spk.sql` yang tersedia di folder `database/`.

**4. Konfigurasi koneksi**

Edit `config/db.php` sesuai environment lokal:
```php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'electre-spk';
```

**5. Jalankan**

Akses lewat browser: `http://localhost/nama-repo/`

---

## 🗄️ Struktur Database

| Tabel | Fungsi |
|---|---|
| `tbl_users` | Data akun admin |
| `tbl_kriteria` | Kriteria beserta bobot dan tipe (benefit/cost) |
| `tbl_alternatif` | Daerah (kabupaten/kota) beserta provinsi |
| `tbl_nilai` | Nilai skala 1–5 per alternatif per kriteria |
| `tbl_normalisasi` | Hasil matriks normalisasi R |
| `tbl_terbobot` | Hasil matriks terbobot V |
| `tbl_concordance` | Nilai concordance c(i,j) seluruh pasangan |
| `tbl_discordance` | Nilai discordance d(i,j) seluruh pasangan |
| `tbl_dominan_concordance` | Matriks dominan F |
| `tbl_dominan_discordance` | Matriks dominan G |
| `tbl_agregat` | Matriks agregat E |
| `tbl_hasil` | Nilai Phi (Φ) dan ranking akhir |

---

## 📐 Rumus & Metode

**Normalisasi:**

$$r_{ij} = \frac{x_{ij}}{\sqrt{\sum_{i=1}^{m} x_{ij}^2}}$$

**Matriks Terbobot:**

$$v_{ij} = w_j \cdot r_{ij}$$

**Concordance & Discordance:**

$$C(k,l) = \{j \mid v_{kj} \geq v_{lj}\} \text{ (benefit)}, \quad \{j \mid v_{kj} \leq v_{lj}\} \text{ (cost)}$$

**Nilai Phi:**

$$\Phi(k) = \sum_l E(k,l) - \sum_l E(l,k)$$

Ranking diurutkan dari Φ **terbesar → terkecil**.

---

## 🎨 Skala Penilaian

| Skor | Keterangan |
|:---:|---|
| 1 | Sangat Rendah |
| 2 | Rendah |
| 3 | Sedang |
| 4 | Tinggi |
| 5 | Sangat Tinggi |

---

## 📊 Kriteria IPM

| Kode | Nama | Tipe | Bobot |
|---|---|:---:|:---:|
| C1 | Umur Harapan Hidup (UHH) | Benefit | 0.14 |
| C2 | Harapan Lama Sekolah (HLS) | Benefit | 0.20 |
| C3 | Rata-rata Lama Sekolah (RLS) | Benefit | 0.26 |
| C4 | Pengeluaran Per Kapita | Benefit | 0.10 |
| C5 | Persentase Penduduk Miskin | Cost | 0.30 |

> Kriteria bersifat **dinamis** — admin dapat menambah, mengubah, atau menghapus kriteria. Seluruh sistem menyesuaikan secara otomatis.

---

## 📦 Sheet Export Excel

File `.xlsx` yang dihasilkan terdiri dari 13 sheet:

1. Ringkasan
2. Data Alternatif
3. Data Kriteria
4. Matriks Keputusan
5. Normalisasi (R)
6. Terbobot (V)
7. Himpunan C & D
8. Matriks Concordance
9. Matriks Discordance
10. Dominan F
11. Dominan G
12. Agregat E
13. Hasil & Ranking

---

## 🔒 Catatan Keamanan

- Seluruh query database menggunakan **prepared statement** (MySQLi)
- Input nilai disimpan sebagai angka integer — tidak ada string bebas dari user yang masuk ke perhitungan
- PhpSpreadsheet hanya digunakan untuk **menulis** file Excel (tidak ada fitur baca/upload file Excel dari user) — sebagian besar CVE PhpSpreadsheet terkait fungsi `IOFactory::load()` yang tidak dipakai di sistem ini

---

## 👩‍💻 Pengembang

Dibuat sebagai Final Project mata kuliah **Sistem Pendukung Keputusan**.

---

## 📄 Lisensi

Repositori ini dibuat untuk keperluan akademis.

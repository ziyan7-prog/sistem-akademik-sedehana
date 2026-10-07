# 📚 SIAKAD — Sistem Informasi Akademik

Proyek PHP sederhana untuk mengelola data mahasiswa, dosen, matakuliah, dan nilai akademik.

---

## 🗂️ Struktur Direktori

```
akademik/
│
├── login.php               → Halaman login untuk semua role
├── logout.php              → Proses logout (hapus session)
├── akademik.sql            → File SQL untuk setup database
│
├── assets/
│   └── style.css           → Stylesheet global (dark theme)
│
├── includes/
│   ├── koneksi.php         → Koneksi ke database MySQL
│   ├── fungsi.php          → Fungsi helper (hitungGrade, hashPassword, dll)
│   ├── header.php          → Template HTML head + pembuka layout
│   └── footer.php          → Penutup layout + JavaScript global
│
├── admin/
│   ├── index.php           → Dashboard utama admin (entry point)
│   ├── aksi.php            → Proses semua aksi POST (tambah/edit/hapus)
│   ├── tab_dashboard.php   → Konten tab Dashboard
│   ├── tab_form.php        → Form tambah/edit (mahasiswa, dosen, MK, nilai)
│   └── tab_tabel.php       → Tabel data (mahasiswa, dosen, MK, nilai)
│
├── mahasiswa/
│   ├── index.php           → Dashboard utama mahasiswa (entry point)
│   ├── hal_dashboard.php   → Konten halaman Dashboard mahasiswa
│   ├── hal_matakuliah.php  → Halaman pilih & kelola matakuliah
│   └── hal_nilai.php       → Halaman rekap nilai & IP mahasiswa
│
└── dosen/
    ├── index.php           → Dashboard utama dosen (entry point)
    ├── hal_dashboard.php   → Konten halaman Dashboard dosen
    ├── hal_input_nilai.php → Halaman input nilai mahasiswa
    └── hal_rekap.php       → Halaman rekap nilai yang sudah diinput
```

---

## 🗄️ Struktur Database

Database: `akademik`

| Tabel               | Fungsi                                                        |
|---------------------|---------------------------------------------------------------|
| `admin`             | Akun administrator sistem                                     |
| `mahasiswa`         | Akun dan profil mahasiswa (NIM)                               |
| `dosen`             | Akun dan profil dosen (NIDN)                                  |
| `matakuliah`        | Daftar mata kuliah (kode, nama, SKS, semester)                |
| `dosen_matakuliah`  | Relasi dosen pengampu per matakuliah (1 MK = 1 dosen)         |
| `nilai`             | Nilai mahasiswa per MK; juga sebagai tanda terdaftar di MK    |

> ⚠️ Tabel `users` (lama) sudah **dipecah** menjadi 3 tabel terpisah.
> Tabel `enrollment` **dihapus** — pendaftaran MK direpresentasikan oleh record di tabel `nilai` (nilai NULL = terdaftar tapi belum dinilai).

---

## 🔑 Akun Default

Password semua akun: **`password123`**

| Role       | Username               | MK Diampu (khusus dosen)        |
|------------|------------------------|---------------------------------|
| Admin      | `admin`                | —                               |
| Mahasiswa  | `mhs001`, `mhs002`     | —                               |
| Dosen      | `dosen001`             | Pemrograman Web, Basis Data, Algoritma |
| Dosen      | `dosen002`             | Jaringan Komputer, Sistem Operasi |

---

## ⚙️ Cara Instalasi

1. Copy seluruh folder `akademik/` ke dalam folder `htdocs` (XAMPP) atau `www` (WAMP)
2. Buka **phpMyAdmin**, buat database baru bernama `akademik`
3. Import file `akademik.sql` ke database tersebut
4. Sesuaikan konfigurasi di `includes/koneksi.php` jika perlu (user/password MySQL)
5. Buka browser, akses `http://localhost/akademik/login.php`

---

## 🔄 Alur Sistem

```
Login → Cek tabel (admin / mahasiswa / dosen)
      ↓
  Admin     → CRUD mahasiswa, dosen, matakuliah, nilai
            → Tab Pengampuan: assign dosen pengampu ke setiap MK

  Mahasiswa → Pilih MK (hanya MK yang sudah punya dosen pengampu)
            → Dosen pengampu otomatis tersambung saat daftar
            → Batal pendaftaran MK (hanya jika nilai masih kosong/pending)
            → Lihat nilai & IP

  Dosen     → Hanya melihat MK yang dia ampu
            → Input nilai mahasiswa per MK yang diampu
            → Edit nilai inline langsung dari halaman Rekap
            → Reset nilai ke kosong (Pending) jika perlu koreksi
```

---

## ✨ Fitur Utama

### Admin
- CRUD mahasiswa, dosen, matakuliah, nilai
- **Tab Pengampuan**: tetapkan dosen pengampu untuk setiap MK (bisa diubah kapan saja)
- Form tambah/edit MK sudah include dropdown pilih dosen pengampu

### Mahasiswa
- Pilih matakuliah dari daftar yang sudah ada dosen pengampunya
- MK tanpa pengampu tidak bisa dipilih (disabled + visual abu-abu)
- **Batalkan pendaftaran**: hanya bisa jika nilai masih kosong (tombol terkunci jika sudah dinilai)
- Konfirmasi 2 langkah saat batal (klik OK + ketik "BATAL")
- Lihat nilai, grade, dan IP sementara

### Dosen
- Dashboard hanya menampilkan MK yang diampu
- Input nilai per MK → hanya mahasiswa terdaftar di MK itu yang tampil
- **Edit nilai inline** langsung dari halaman Rekap (klik ✏️ Edit)
- **Reset nilai** ke Pending jika perlu koreksi
- Rekap nilai dikelompokkan per matakuliah dengan rata-rata per MK

---

## 📐 Konversi Grade

| Nilai Angka | Grade |
|-------------|-------|
| 85 – 100    | A     |
| 75 – 84     | B     |
| 65 – 74     | C     |
| 55 – 64     | D     |
| 0 – 54      | E     |

---

## 💡 Catatan Teknis

- Password disimpan menggunakan **bcrypt** (`password_hash` / `password_verify`)
- Semua input di-escape dengan `mysqli_real_escape_string` sebelum masuk ke query
- Hapus data bersifat **CASCADE** — menghapus mahasiswa/dosen/MK akan otomatis menghapus nilai terkait
- Tidak menggunakan framework — murni PHP native + MySQLi

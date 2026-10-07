<?php
// ============================================================
// FILE: includes/fungsi.php
// Fungsi: Kumpulan fungsi helper yang dipakai di seluruh project
// Di-include oleh admin.php, mahasiswa.php, dosen.php
// ============================================================

// ------------------------------------------------------------
// Menghitung grade huruf berdasarkan nilai angka (0–100)
// A = 85–100 | B = 75–84 | C = 65–74 | D = 55–64 | E = 0–54
// ------------------------------------------------------------
function hitungGrade($nilai) {
    if ($nilai >= 85) return 'A';
    if ($nilai >= 75) return 'B';
    if ($nilai >= 65) return 'C';
    if ($nilai >= 55) return 'D';
    return 'E';
}

// ------------------------------------------------------------
// Mengembalikan nama class CSS badge berdasarkan grade
// Digunakan untuk mewarnai badge grade di tabel
// ------------------------------------------------------------
function warnaBadge($grade) {
    $map = [
        'A' => 'grade-a',
        'B' => 'grade-b',
        'C' => 'grade-c',
        'D' => 'grade-d',
        'E' => 'grade-e',
    ];
    return $map[$grade] ?? 'grade-e';
}

// ------------------------------------------------------------
// Mengembalikan warna hex berdasarkan grade
// Digunakan untuk bar chart distribusi grade
// ------------------------------------------------------------
function warnaHex($grade) {
    $map = [
        'A' => '#10b981', // hijau
        'B' => '#3b82f6', // biru
        'C' => '#f59e0b', // kuning
        'D' => '#f97316', // oranye
        'E' => '#ef4444', // merah
    ];
    return $map[$grade] ?? '#ef4444';
}

// ------------------------------------------------------------
// Meng-hash password menggunakan algoritma bcrypt (PASSWORD_DEFAULT)
// Wajib dipakai saat menyimpan/mengubah password ke database
// ------------------------------------------------------------
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

// ------------------------------------------------------------
// Membersihkan input dari karakter berbahaya untuk query SQL
// Selalu gunakan fungsi ini sebelum menyisipkan string ke query
// ------------------------------------------------------------
function bersihkan($conn, $input) {
    return mysqli_real_escape_string($conn, trim($input));
}

// ------------------------------------------------------------
// Mengecek apakah user sudah login dan rolenya sesuai
// Jika tidak, redirect ke halaman login
// Parameter $role: 'admin' | 'mahasiswa' | 'dosen'
// ------------------------------------------------------------
function cekSesi($role) {
    // Mulai sesi jika belum aktif
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Jika belum login atau role tidak cocok, arahkan ke login
    if (!isset($_SESSION['id']) || $_SESSION['role'] !== $role) {
        header("Location: ../login.php");
        exit();
    }
}
?>

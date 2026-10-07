<?php
// ============================================================
// FILE: includes/koneksi.php
// Fungsi: Membuat koneksi ke database MySQL
// Di-include oleh semua file yang butuh akses database
// ============================================================

// Konfigurasi koneksi database — sesuaikan dengan server Anda
$host     = "localhost";
$user     = "root";        // Username MySQL
$password = "";            // Password MySQL (kosong jika tidak ada)
$database = "akademik";

// Buat koneksi menggunakan MySQLi
$conn = mysqli_connect($host, $user, $password, $database);

// Cek apakah koneksi berhasil
if (!$conn) {
    die("<h3 style='color:red;font-family:sans-serif;padding:20px;'>
        ❌ Koneksi database gagal!<br>
        Error: " . mysqli_connect_error() . "<br><br>
        <small>Pastikan MySQL aktif dan database 'akademik' sudah dibuat dari file akademik.sql</small>
    </h3>");
}

// Set charset ke UTF-8 agar karakter Indonesia tampil benar
mysqli_set_charset($conn, "utf8");
?>

<?php
// ============================================================
// FILE: includes/header.php
// Fungsi: Template HTML head + awal layout (dipanggil oleh setiap halaman)
// Cara pakai: require_once '../includes/header.php';
//             sebelum konten, setelah $judul dan $role sudah diset
// ============================================================

// Variabel yang harus diset sebelum include file ini:
// $judul   = judul halaman (string)
// $role    = 'admin' | 'mahasiswa' | 'dosen'
// $nama    = nama user yang login (dari session)
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($judul ?? 'SiAkad') ?> — SiAkad</title>

  <!-- Google Fonts: Plus Jakarta Sans & Syne -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Syne:wght@700;800&display=swap" rel="stylesheet">

  <!-- Stylesheet utama -->
  <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

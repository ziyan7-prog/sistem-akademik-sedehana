<?php
// ============================================================
// FILE: logout.php
// Fungsi: Menghapus semua data session dan mengarahkan ke login
// ============================================================

session_start();
session_destroy(); // Hapus semua data session yang tersimpan
header("Location: login.php");
exit();
?>

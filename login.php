<?php
// ============================================================
// FILE: login.php
// Fungsi: Halaman login untuk semua role (admin, mahasiswa, dosen)
//         Login dicek dari tabel terpisah sesuai role
// ============================================================

session_start();
require_once 'includes/koneksi.php';
require_once 'includes/fungsi.php';

// Jika sudah login, langsung arahkan ke halaman masing-masing
if (isset($_SESSION['role'])) {
    $tujuan = ['admin' => 'admin/index.php', 'mahasiswa' => 'mahasiswa/index.php', 'dosen' => 'dosen/index.php'];
    header("Location: " . ($tujuan[$_SESSION['role']] ?? 'login.php'));
    exit();
}

$pesan = "";

// ======================== PROSES LOGIN ========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = bersihkan($conn, $_POST['username']);
    $password = $_POST['password']; // password asli untuk di-verify dengan bcrypt

    $user   = null;
    $role   = null;

    // Cek di tabel admin terlebih dahulu
    $q = mysqli_query($conn, "SELECT *, 'admin' AS role FROM admin WHERE username='$username' LIMIT 1");
    $user = mysqli_fetch_assoc($q);

    // Jika tidak ada di admin, cek di tabel mahasiswa
    if (!$user) {
        $q = mysqli_query($conn, "SELECT *, 'mahasiswa' AS role FROM mahasiswa WHERE username='$username' LIMIT 1");
        $user = mysqli_fetch_assoc($q);
    }

    // Jika tidak ada di mahasiswa, cek di tabel dosen
    if (!$user) {
        $q = mysqli_query($conn, "SELECT *, 'dosen' AS role FROM dosen WHERE username='$username' LIMIT 1");
        $user = mysqli_fetch_assoc($q);
    }

    // Verifikasi password dengan bcrypt password_verify()
    if ($user && password_verify($password, $user['password'])) {
        // Login berhasil — simpan data ke session
        $_SESSION['id']       = $user['id'];
        $_SESSION['nama']     = $user['nama'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role']     = $user['role'];

        // Arahkan ke dashboard sesuai role
        if ($user['role'] === 'admin')     header("Location: admin/index.php");
        elseif ($user['role'] === 'mahasiswa') header("Location: mahasiswa/index.php");
        elseif ($user['role'] === 'dosen')     header("Location: dosen/index.php");
        exit();
    } else {
        // Login gagal — username tidak ditemukan atau password salah
        $pesan = "Username atau password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login — SiAkad</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
    /* Reset & layout halaman login */
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      background: linear-gradient(135deg, #1a365d 0%, #2a4a7f 50%, #1a365d 100%);
      min-height: 100vh;
      display: flex; justify-content: center; align-items: center;
    }
    .login-box {
      background: white; border-radius: 14px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      padding: 48px 40px; width: 100%; max-width: 400px;
    }
    .logo { text-align: center; margin-bottom: 32px; }
    .logo .icon { font-size: 48px; display: block; margin-bottom: 10px; }
    .logo h1 { font-size: 22px; font-weight: 800; color: #1a365d; }
    .logo p  { color: #718096; font-size: 13px; margin-top: 4px; }
    .form-group { margin-bottom: 18px; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; color: #4a5568; margin-bottom: 7px; }
    .form-group input {
      width: 100%; padding: 11px 14px;
      border: 2px solid #e2e8f0; border-radius: 7px;
      font-size: 14px; transition: border-color 0.2s;
      font-family: inherit;
    }
    .form-group input:focus { outline: none; border-color: #3182ce; }
    .btn-login {
      width: 100%; padding: 13px;
      background: #1a365d; color: white;
      border: none; border-radius: 7px;
      font-size: 16px; font-weight: 700;
      cursor: pointer; transition: background 0.2s;
      margin-top: 8px; font-family: inherit;
    }
    .btn-login:hover { background: #2b6cb0; }
    .alert-error {
      background: #fed7d7; color: #9b2c2c;
      border-left: 4px solid #e53e3e;
      padding: 10px 14px; border-radius: 6px;
      margin-bottom: 18px; font-size: 13px;
    }
    /* Tabel info akun demo */
    .info-akun {
      margin-top: 28px; background: #f7fafc;
      border-radius: 8px; padding: 14px;
      border: 1px solid #e2e8f0;
    }
    .info-akun p { font-size: 12px; color: #4a5568; font-weight: 700; margin-bottom: 8px; }
    .info-akun table { width: 100%; font-size: 12px; color: #4a5568; border-collapse: collapse; }
    .info-akun td { padding: 3px 6px; }
    .info-akun td:first-child { font-weight: 600; }
    .badge-role {
      background: #ebf8ff; color: #2b6cb0;
      padding: 1px 7px; border-radius: 10px; font-size: 11px;
    }
  </style>
</head>
<body>
  <div class="login-box">
    <div class="logo">
      <span class="icon">🎓</span>
      <h1>SISTEM AKADEMIK</h1>
      <p>Manajemen Nilai & Matakuliah</p>
    </div>

    <!-- Tampilkan pesan error jika login gagal -->
    <?php if ($pesan): ?>
      <div class="alert-error">❌ <?= htmlspecialchars($pesan) ?></div>
    <?php endif; ?>

    <!-- Form login -->
    <form method="POST">
      <div class="form-group">
        <label>Username</label>
        <input type="text" name="username" placeholder="Masukkan username..."
               value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Masukkan password..." required>
      </div>
      <button type="submit" class="btn-login">🔐 Masuk</button>
    </form>
  </div>
</body>
</html>

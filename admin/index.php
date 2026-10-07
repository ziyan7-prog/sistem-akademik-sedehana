<?php
// ============================================================
// FILE: admin/index.php
// Fungsi: Dashboard utama admin — menampilkan semua tab
//         (dashboard, mahasiswa, dosen, matakuliah, nilai)
// ============================================================

session_start();
require_once '../includes/koneksi.php';
require_once '../includes/fungsi.php';

// Cek sesi — hanya admin yang boleh akses
cekSesi('admin');

$nama_admin = $_SESSION['nama'];

// Arahkan aksi POST ke admin/aksi.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'aksi.php';
    exit();
}

// Ambil pesan notifikasi dari redirect
$pesan     = isset($_GET['pesan']) ? urldecode($_GET['pesan']) : '';
$tipe_pesan = $_GET['tipe'] ?? 'sukses';

// Tab aktif saat ini (default: dashboard)
$tab = $_GET['tab'] ?? 'dashboard';

// ============================================================
// MODE EDIT — ambil data yang akan diedit
// ============================================================
$edit_id   = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;
$edit_type = $_GET['edit_type'] ?? '';
$data_edit = null;

if ($edit_id > 0) {
    // Ambil data dari tabel yang sesuai
    if ($edit_type === 'mahasiswa') {
        $data_edit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM mahasiswa WHERE id=$edit_id"));
    } elseif ($edit_type === 'dosen') {
        $data_edit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM dosen WHERE id=$edit_id"));
    } elseif ($edit_type === 'matakuliah') {
        $data_edit = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM matakuliah WHERE id=$edit_id"));
    } elseif ($edit_type === 'nilai') {
        // Join untuk mendapat nama mahasiswa dan matakuliah
        $data_edit = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT n.*, mhs.nama AS nm_mhs, mk.nama AS nm_mk
             FROM nilai n
             JOIN mahasiswa mhs ON n.mahasiswa_id = mhs.id
             JOIN matakuliah mk  ON n.matakuliah_id = mk.id
             WHERE n.id=$edit_id"));
    }
}

// ============================================================
// AMBIL DATA UNTUK DITAMPILKAN DI TABEL
// ============================================================

// Daftar semua mahasiswa, diurutkan nama
$list_mhs   = mysqli_query($conn, "SELECT * FROM mahasiswa ORDER BY nama");
// Daftar semua dosen, diurutkan nama
$list_dosen = mysqli_query($conn, "SELECT * FROM dosen ORDER BY nama");
// Daftar semua matakuliah, diurutkan semester lalu nama
$list_mk    = mysqli_query($conn,
    "SELECT mk.*, d.nama AS nama_dosen, d.id AS dosen_id_ampu
     FROM matakuliah mk
     LEFT JOIN dosen_matakuliah dm ON mk.id = dm.matakuliah_id
     LEFT JOIN dosen d ON dm.dosen_id = d.id
     ORDER BY mk.semester, mk.nama");
// Daftar semua nilai dengan join ke tabel terkait
$list_nilai = mysqli_query($conn,
    "SELECT n.*, mhs.nama nm_mhs, mhs.nim, mk.nama nm_mk, mk.kode, dsn.nama nm_dosen
     FROM nilai n
     JOIN mahasiswa mhs ON n.mahasiswa_id  = mhs.id
     JOIN matakuliah mk  ON n.matakuliah_id = mk.id
     JOIN dosen dsn      ON n.dosen_id      = dsn.id
     ORDER BY mhs.nama, mk.nama");

// Ambil semua dosen untuk dropdown (di form MK dan tab pengampuan)
$list_dosen_all = mysqli_query($conn, "SELECT * FROM dosen ORDER BY nama");

// Hitung jumlah masing-masing untuk ditampilkan di badge/stat
$jml_mhs   = mysqli_num_rows($list_mhs);
$jml_dosen = mysqli_num_rows($list_dosen);
$jml_mk    = mysqli_num_rows($list_mk);
$jml_nilai = mysqli_num_rows($list_nilai);

// ============================================================
// STATISTIK DISTRIBUSI GRADE (untuk dashboard)
// ============================================================
$dist_grade = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0];
$h_dist = mysqli_query($conn, "SELECT grade, COUNT(*) c FROM nilai GROUP BY grade");
while ($r = mysqli_fetch_assoc($h_dist)) {
    $dist_grade[$r['grade']] = $r['c'];
}

// Aktivitas nilai terbaru (6 terakhir)
$h_akt = mysqli_query($conn,
    "SELECT n.*, mhs.nama nm_mhs, mk.nama nm_mk, dsn.nama nm_dosen
     FROM nilai n
     JOIN mahasiswa mhs ON n.mahasiswa_id  = mhs.id
     JOIN matakuliah mk  ON n.matakuliah_id = mk.id
     JOIN dosen dsn      ON n.dosen_id      = dsn.id
     ORDER BY n.updated_at DESC LIMIT 6");
$aktivitas = [];
while ($r = mysqli_fetch_assoc($h_akt)) $aktivitas[] = $r;

// ============================================================
// TAMPILKAN HALAMAN
// ============================================================
$judul = 'Dashboard Admin';
require_once '../includes/header.php';
?>

<!-- ==================== SIDEBAR ==================== -->
<div class="sidebar">
  <div class="sidebar-brand">
    <div class="logo-icon">🎓</div>
    <h1>SIAKAD</h1>
    <p>Sistem Informasi Akademik</p>
  </div>

  <!-- Info user yang sedang login -->
  <div class="sidebar-user">
    <div style="display:flex;align-items:center;gap:10px;">
      <div class="avatar" style="background:linear-gradient(135deg,#6366f1,#ec4899);">
        <?= strtoupper(substr($nama_admin, 0, 1)) ?>
      </div>
      <div>
        <div class="user-nama"><?= htmlspecialchars($nama_admin) ?></div>
        <div class="user-role">🛡️ Administrator</div>
      </div>
    </div>
  </div>

  <!-- Menu navigasi admin -->
  <nav class="sidebar-nav">
    <div class="nav-label">Utama</div>
    <a href="?tab=dashboard" class="nav-item <?= $tab==='dashboard' ? 'aktif' : '' ?>">
      <span class="icon"></span> Dashboard
    </a>

    <div class="nav-label" style="margin-top:8px;">Manajemen</div>
    <a href="?tab=mahasiswa" class="nav-item <?= $tab==='mahasiswa' ? 'aktif' : '' ?>">
      <span class="icon"></span> Mahasiswa
      <span class="badge-count"><?= $jml_mhs ?></span>
    </a>
    <a href="?tab=dosen" class="nav-item <?= $tab==='dosen' ? 'aktif' : '' ?>">
      <span class="icon"></span> Dosen
      <span class="badge-count"><?= $jml_dosen ?></span>
    </a>
    <a href="?tab=matakuliah" class="nav-item <?= $tab==='matakuliah' ? 'aktif' : '' ?>">
      <span class="icon"></span> Matakuliah
      <span class="badge-count"><?= $jml_mk ?></span>
    </a>
    <a href="?tab=nilai" class="nav-item <?= $tab==='nilai' ? 'aktif' : '' ?>">
      <span class="icon"></span> Nilai
      <span class="badge-count"><?= $jml_nilai ?></span>
    </a>
    <a href="?tab=pengampuan" class="nav-item <?= $tab==='pengampuan' ? 'aktif' : '' ?>">
      <span class="icon"></span> Pengampuan
    </a>
  </nav>

  <div class="sidebar-footer">
    <a href="../logout.php" class="btn-logout"><span></span> Keluar</a>
  </div>
</div>

<!-- ==================== MAIN CONTENT ==================== -->
<div class="main">
  <div class="topbar">
    <h2>
      <?php
      // Judul halaman sesuai tab aktif
      $judul_tab = ['dashboard'=>'Dashboard','mahasiswa'=>'Manajemen Mahasiswa',
                    'dosen'=>'Manajemen Dosen','matakuliah'=>'Manajemen Matakuliah',
                    'nilai'=>'Manajemen Nilai','pengampuan'=>'Penugasan Dosen Pengampu'];
      echo $judul_tab[$tab] ?? 'Dashboard';
      ?>
    </h2>
    <div class="time-badge">📅 <?= date('d F Y') ?></div>
  </div>

  <div class="content">

    <!-- Notifikasi sukses/error -->
    <?php if ($pesan): ?>
      <div class="alert alert-<?= $tipe_pesan === 'sukses' ? 'sukses' : 'error' ?>">
        <?= $tipe_pesan === 'sukses' ? '✅' : '❌' ?> <?= htmlspecialchars($pesan) ?>
      </div>
    <?php endif; ?>

    <?php
    // Muat konten tab yang sesuai
    if ($tab === 'dashboard') {
        require_once 'tab_dashboard.php';
    } elseif ($tab === 'pengampuan') {
        // Tab pengampuan: full width, tidak pakai dua kolom
        require_once 'tab_pengampuan.php';
    } else {
        // Tab CRUD: form + tabel dalam 2 kolom
        echo '<div style="display:grid;grid-template-columns:1fr 1.5fr;gap:20px;align-items:start;">';
        // Kolom kiri: form tambah/edit
        echo '<div class="card" style="position:sticky;top:80px;">';
        require_once 'tab_form.php';
        echo '</div>';
        // Kolom kanan: tabel data
        echo '<div class="card">';
        require_once 'tab_tabel.php';
        echo '</div>';
        echo '</div>';
    }
    ?>


<?php require_once '../includes/footer.php'; ?>

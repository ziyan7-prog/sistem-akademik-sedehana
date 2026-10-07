<?php
// ============================================================
// FILE: dosen/index.php
// Fungsi: Dashboard dosen — input nilai, lihat rekap
// Hanya bisa diakses oleh user dengan role 'dosen'
// ============================================================

session_start();
require_once '../includes/koneksi.php';
require_once '../includes/fungsi.php';

// Cek sesi — hanya dosen yang boleh akses
cekSesi('dosen');

$did   = $_SESSION['id'];   // ID dosen yang login
$nama  = $_SESSION['nama'];
$pesan = '';
$tipe_pesan = '';
$halaman = $_GET['hal'] ?? 'dashboard';

// ============================================================
// PROSES AKSI POST (simpan nilai, edit nilai, hapus nilai)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi'])) {

    if ($_POST['aksi'] === 'simpan_nilai') {
        $mhs_id = (int)$_POST['mahasiswa_id'];
        $mk_id  = (int)$_POST['matakuliah_id'];
        $nilai  = (float)$_POST['nilai_angka'];

        // Validasi: pastikan MK ini memang diampu oleh dosen yang login
        $cek_ampu = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT id FROM dosen_matakuliah WHERE dosen_id=$did AND matakuliah_id=$mk_id"));
        if (!$cek_ampu) {
            $pesan = "Anda tidak berwenang menginput nilai untuk matakuliah ini!";
            $tipe_pesan = "error";
        } elseif ($nilai < 0 || $nilai > 100) {
            $pesan = "Nilai harus antara 0–100!";
            $tipe_pesan = "error";
        } else {
            // Hitung grade otomatis dari nilai angka
            $grade = hitungGrade($nilai);

            // Upsert: insert jika belum ada, update jika sudah
            $q = "INSERT INTO nilai (mahasiswa_id, matakuliah_id, dosen_id, nilai_angka, grade)
                  VALUES ($mhs_id, $mk_id, $did, $nilai, '$grade')
                  ON DUPLICATE KEY UPDATE nilai_angka=$nilai, grade='$grade', dosen_id=$did";

            if (mysqli_query($conn, $q)) {
                $pesan = "Nilai $nilai (Grade $grade) berhasil disimpan!";
                $tipe_pesan = "sukses";
            } else {
                $pesan = "Gagal menyimpan nilai!";
                $tipe_pesan = "error";
            }
        }
        $halaman = 'input_nilai';
    }

    elseif ($_POST['aksi'] === 'edit_nilai') {
        // Dosen mengedit nilai dari halaman rekap
        $nilai_id = (int)$_POST['nilai_id'];
        $nilai    = (float)$_POST['nilai_angka'];

        // Validasi: pastikan record nilai ini memang milik dosen yang login
        $cek = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT n.id, n.matakuliah_id FROM nilai n
             JOIN dosen_matakuliah dm ON n.matakuliah_id = dm.matakuliah_id
             WHERE n.id=$nilai_id AND dm.dosen_id=$did LIMIT 1"));

        if (!$cek) {
            $pesan = "Anda tidak berwenang mengubah nilai ini!";
            $tipe_pesan = "error";
        } elseif ($nilai < 0 || $nilai > 100) {
            $pesan = "Nilai harus antara 0–100!";
            $tipe_pesan = "error";
        } else {
            // Hitung ulang grade berdasarkan nilai baru
            $grade = hitungGrade($nilai);
            mysqli_query($conn,
                "UPDATE nilai SET nilai_angka=$nilai, grade='$grade', dosen_id=$did
                 WHERE id=$nilai_id");
            $pesan = "Nilai berhasil diubah menjadi $nilai (Grade $grade)!";
            $tipe_pesan = "sukses";
        }
        $halaman = 'rekap';
    }

    elseif ($_POST['aksi'] === 'hapus_nilai') {
        // Dosen menghapus nilai (reset ke kosong, bukan hapus record)
        // Record tetap ada agar mahasiswa masih terdaftar di MK
        $nilai_id = (int)$_POST['nilai_id'];

        // Validasi kepemilikan: hanya dosen pengampu yang bisa reset nilai
        $cek = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT n.id FROM nilai n
             JOIN dosen_matakuliah dm ON n.matakuliah_id = dm.matakuliah_id
             WHERE n.id=$nilai_id AND dm.dosen_id=$did LIMIT 1"));

        if (!$cek) {
            $pesan = "Anda tidak berwenang menghapus nilai ini!";
            $tipe_pesan = "error";
        } else {
            // Set nilai dan grade ke NULL (reset, bukan hapus record)
            mysqli_query($conn,
                "UPDATE nilai SET nilai_angka=NULL, grade=NULL WHERE id=$nilai_id");
            $pesan = "Nilai berhasil direset ke kosong.";
            $tipe_pesan = "sukses";
        }
        $halaman = 'rekap';
    }
}

// ============================================================
// AMBIL DATA UNTUK DITAMPILKAN
// ============================================================

// Ambil HANYA matakuliah yang diampu oleh dosen ini (dari dosen_matakuliah)
$semua_mk  = mysqli_query($conn,
    "SELECT mk.*
     FROM matakuliah mk
     JOIN dosen_matakuliah dm ON mk.id = dm.matakuliah_id
     WHERE dm.dosen_id = $did
     ORDER BY mk.semester, mk.nama");

// Filter matakuliah yang dipilih dosen (via GET parameter)
$filter_mk = isset($_GET['mk']) ? (int)$_GET['mk'] : 0;
$nama_mk_filter = '';
if ($filter_mk) {
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama FROM matakuliah WHERE id=$filter_mk"));
    $nama_mk_filter = $r ? $r['nama'] : '';
}

// Ambil mahasiswa yang terdaftar di MK terpilih
// Mahasiswa dianggap terdaftar jika ada record di tabel nilai
$list_mhs = [];
if ($filter_mk) {
    $q = "SELECT mhs.id, mhs.nama, mhs.nim,
                 n.nilai_angka, n.grade, n.id AS nilai_id
          FROM nilai n
          JOIN mahasiswa mhs ON n.mahasiswa_id = mhs.id
          WHERE n.matakuliah_id = $filter_mk
          ORDER BY mhs.nama";
    $h = mysqli_query($conn, $q);
    while ($r = mysqli_fetch_assoc($h)) $list_mhs[] = $r;
}

// Statistik ringkasan dosen ini
// Hitung nilai yang sudah terisi (bukan NULL) dari MK yang diampu
$total_nilai_input = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) c FROM nilai n
     JOIN dosen_matakuliah dm ON n.matakuliah_id = dm.matakuliah_id
     WHERE dm.dosen_id=$did AND n.nilai_angka IS NOT NULL"))['c'];

// Jumlah MK yang diampu dosen ini
$total_mk = mysqli_num_rows($semua_mk);

// Rata-rata nilai dari semua MK yang diampu dosen ini
$avg_r     = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT AVG(n.nilai_angka) avg FROM nilai n
     JOIN dosen_matakuliah dm ON n.matakuliah_id = dm.matakuliah_id
     WHERE dm.dosen_id=$did AND n.nilai_angka IS NOT NULL"));
$avg_nilai = $avg_r['avg'] ? round($avg_r['avg'], 1) : 0;

// Distribusi grade dari semua MK yang diampu dosen ini
$dist_grade = ['A'=>0, 'B'=>0, 'C'=>0, 'D'=>0, 'E'=>0];
$h_dist = mysqli_query($conn,
    "SELECT n.grade, COUNT(*) c FROM nilai n
     JOIN dosen_matakuliah dm ON n.matakuliah_id = dm.matakuliah_id
     WHERE dm.dosen_id=$did AND n.grade IS NOT NULL
     GROUP BY n.grade");
while ($r = mysqli_fetch_assoc($h_dist)) $dist_grade[$r['grade']] = $r['c'];

// Nilai terbaru yang diinput pada MK yang diampu dosen ini (8 terakhir)
$h_recent = mysqli_query($conn,
    "SELECT n.*, mhs.nama AS nama_mhs, mk.nama AS nama_mk, mk.kode
     FROM nilai n
     JOIN mahasiswa mhs ON n.mahasiswa_id  = mhs.id
     JOIN matakuliah mk  ON n.matakuliah_id = mk.id
     JOIN dosen_matakuliah dm ON mk.id      = dm.matakuliah_id
     WHERE dm.dosen_id = $did AND n.nilai_angka IS NOT NULL
     ORDER BY n.updated_at DESC LIMIT 8");
$recent_nilai = [];
while ($r = mysqli_fetch_assoc($h_recent)) $recent_nilai[] = $r;

// ============================================================
// TAMPILKAN HALAMAN
// ============================================================
$judul = 'Dashboard Dosen';
require_once '../includes/header.php';
?>

<!-- ==================== SIDEBAR ==================== -->
<div class="sidebar">
  <div class="sidebar-brand">
    <div class="logo-icon">🎓</div>
    <h1>SIAKAD</h1>
    <p>Sistem Informasi Akademik</p>
  </div>

  <!-- Info dosen yang login -->
  <div class="sidebar-user">
    <div style="display:flex;align-items:center;gap:10px;">
      <div class="avatar" style="background:linear-gradient(135deg,#14b8a6,#3b82f6);">
        <?= strtoupper(substr($nama, 0, 1)) ?>
      </div>
      <div>
        <div class="user-nama"><?= htmlspecialchars($nama) ?></div>
        <div class="user-role">👨‍🏫 Dosen</div>
      </div>
    </div>
  </div>

  <!-- Menu navigasi dosen -->
  <nav class="sidebar-nav">
    <div class="nav-label">Menu</div>
    <a href="?hal=dashboard" class="nav-item <?= $halaman==='dashboard' ? 'aktif' : '' ?>">
      <span class="icon"></span> Dashboard
    </a>
    <a href="?hal=input_nilai" class="nav-item <?= $halaman==='input_nilai' ? 'aktif' : '' ?>">
      <span class="icon"></span> Input Nilai
    </a>
    <a href="?hal=rekap" class="nav-item <?= $halaman==='rekap' ? 'aktif' : '' ?>">
      <span class="icon"></span> Rekap Nilai
      <?php if ($total_nilai_input > 0): ?>
        <span class="badge-count"><?= $total_nilai_input ?></span>
      <?php endif; ?>
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
      $judul_tab = ['dashboard'=>'Dashboard','input_nilai'=>'Input Nilai','rekap'=>'Rekap Nilai'];
      echo $judul_tab[$halaman] ?? 'Dashboard';
      ?>
    </h2>
    <div class="time-badge">📅 <?= date('d F Y') ?></div>
  </div>

  <div class="content">
    <!-- Notifikasi -->
    <?php if ($pesan): ?>
      <div class="alert alert-<?= $tipe_pesan === 'sukses' ? 'sukses' : 'error' ?>">
        <?= $tipe_pesan === 'sukses' ? '✅' : '❌' ?> <?= htmlspecialchars($pesan) ?>
      </div>
    <?php endif; ?>

    <?php
    // Muat konten halaman sesuai pilihan
    if ($halaman === 'dashboard')    require_once 'hal_dashboard.php';
    elseif ($halaman === 'input_nilai') require_once 'hal_input_nilai.php';
    elseif ($halaman === 'rekap')       require_once 'hal_rekap.php';
    ?>


<?php require_once '../includes/footer.php'; ?>

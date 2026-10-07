<?php
// ============================================================
// FILE: mahasiswa/index.php
// Fungsi: Dashboard mahasiswa — lihat nilai, pilih matakuliah
// Hanya bisa diakses oleh user dengan role 'mahasiswa'
// ============================================================

session_start();
require_once '../includes/koneksi.php';
require_once '../includes/fungsi.php';

// Cek sesi — hanya mahasiswa yang boleh akses
cekSesi('mahasiswa');

$mid      = $_SESSION['id'];   // ID mahasiswa yang login
$nama     = $_SESSION['nama'];
$pesan    = '';
$tipe_pesan = '';
$halaman  = $_GET['hal'] ?? 'dashboard';

// ============================================================
// PROSES AKSI POST (daftar / batal matakuliah)
// ============================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'daftar_satu') {
        // Daftarkan SATU matakuliah yang dipilih mahasiswa
        $mk_id = (int)($_POST['matakuliah_id'] ?? 0);

        if (!$mk_id) {
            $pesan = "MK tidak ditemukan.";
            $tipe_pesan = "error";
        } else {
            // Ambil dosen pengampu dari tabel dosen_matakuliah
            $r_dosen = mysqli_fetch_assoc(mysqli_query($conn,
                "SELECT dm.dosen_id, d.nama AS nama_dosen, mk.nama AS nama_mk
                 FROM dosen_matakuliah dm
                 JOIN dosen d ON dm.dosen_id = d.id
                 JOIN matakuliah mk ON dm.matakuliah_id = mk.id
                 WHERE dm.matakuliah_id = $mk_id LIMIT 1"));

            if (!$r_dosen) {
                $pesan = "Matakuliah ini belum memiliki dosen pengampu, tidak bisa didaftarkan.";
                $tipe_pesan = "error";
            } else {
                $dosen_id = (int)$r_dosen['dosen_id'];
                $nama_mk  = $r_dosen['nama_mk'];

                // Cek apakah sudah terdaftar sebelumnya
                $cek = mysqli_fetch_assoc(mysqli_query($conn,
                    "SELECT id FROM nilai WHERE mahasiswa_id=$mid AND matakuliah_id=$mk_id"));
                if ($cek) {
                    $pesan = "Anda sudah terdaftar di matakuliah \"$nama_mk\".";
                    $tipe_pesan = "error";
                } else {
                    // Insert record nilai kosong — sebagai tanda terdaftar
                    mysqli_query($conn,
                        "INSERT INTO nilai (mahasiswa_id, matakuliah_id, dosen_id)
                         VALUES ($mid, $mk_id, $dosen_id)");
                    $pesan = "Berhasil mengambil matakuliah \"$nama_mk\"!";
                    $tipe_pesan = "sukses";
                }
            }
        }
        $halaman = 'matakuliah';
    }

    elseif ($aksi === 'daftar') {
        // Mahasiswa mendaftar matakuliah
        // Dosen pengampu diambil dari tabel dosen_matakuliah (ditetapkan oleh admin)
        $dipilih = $_POST['matakuliah'] ?? [];
        if (empty($dipilih)) {
            $pesan = "Pilih minimal 1 matakuliah!";
            $tipe_pesan = "error";
        } else {
            $ok = 0; $gagal_dosen = [];
            foreach ($dipilih as $mk_id) {
                $mk_id = (int)$mk_id;

                // Ambil dosen pengampu dari tabel dosen_matakuliah
                $r_dosen = mysqli_fetch_assoc(mysqli_query($conn,
                    "SELECT dm.dosen_id, d.nama AS nama_dosen
                     FROM dosen_matakuliah dm
                     JOIN dosen d ON dm.dosen_id = d.id
                     WHERE dm.matakuliah_id = $mk_id LIMIT 1"));

                if (!$r_dosen) {
                    // MK belum punya dosen pengampu — tidak bisa didaftarkan
                    $r_mk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama FROM matakuliah WHERE id=$mk_id"));
                    $gagal_dosen[] = $r_mk['nama'] ?? "MK#$mk_id";
                    continue;
                }

                $dosen_id = (int)$r_dosen['dosen_id'];

                // Insert record nilai kosong dengan dosen pengampu yang benar
                // INSERT IGNORE agar tidak error jika mahasiswa sudah terdaftar
                if (mysqli_query($conn,
                    "INSERT IGNORE INTO nilai (mahasiswa_id, matakuliah_id, dosen_id)
                     VALUES ($mid, $mk_id, $dosen_id)")) {
                    $ok++;
                }
            }

            if ($ok > 0) {
                $pesan = "Berhasil mendaftar $ok matakuliah!";
                $tipe_pesan = "sukses";
            }
            if (!empty($gagal_dosen)) {
                $nama_gagal = implode(', ', $gagal_dosen);
                $pesan .= ($pesan ? ' ' : '') . "⚠️ MK berikut belum ada dosen pengampu: $nama_gagal.";
                $tipe_pesan = $ok > 0 ? "sukses" : "error";
            }
        }
        $halaman = 'matakuliah';
    }
    elseif ($aksi === 'batal') {
        // Batalkan pendaftaran matakuliah
        // Cek apakah mahasiswa sudah punya nilai — jika sudah, tidak boleh dibatalkan
        $mk_id    = (int)$_POST['matakuliah_id'];
        $cek_nilai = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT nilai_angka FROM nilai
             WHERE mahasiswa_id=$mid AND matakuliah_id=$mk_id LIMIT 1"));

        if ($cek_nilai && $cek_nilai['nilai_angka'] !== null) {
            // Nilai sudah diisi dosen — pembatalan ditolak
            $pesan = "Tidak bisa membatalkan! Matakuliah ini sudah memiliki nilai yang diinput dosen.";
            $tipe_pesan = "error";
        } else {
            // Nilai masih kosong (pending) — boleh dibatalkan
            mysqli_query($conn,
                "DELETE FROM nilai WHERE mahasiswa_id=$mid AND matakuliah_id=$mk_id");
            $pesan = "Pendaftaran matakuliah berhasil dibatalkan.";
            $tipe_pesan = "sukses";
        }
        $halaman = 'matakuliah';
    }
}

// ============================================================
// AMBIL DATA UNTUK DITAMPILKAN
// ============================================================

// Semua matakuliah yang tersedia + info dosen pengampunya
$semua_mk = mysqli_query($conn,
    "SELECT mk.*, d.nama AS nama_dosen_ampu, d.id AS dosen_id_ampu
     FROM matakuliah mk
     LEFT JOIN dosen_matakuliah dm ON mk.id = dm.matakuliah_id
     LEFT JOIN dosen d ON dm.dosen_id = d.id
     ORDER BY mk.semester, mk.nama");

// MK yang sudah diambil mahasiswa ini (ada recordnya di tabel nilai)
$h_diambil = mysqli_query($conn, "SELECT matakuliah_id FROM nilai WHERE mahasiswa_id=$mid");
$mk_diambil = [];
while ($r = mysqli_fetch_assoc($h_diambil)) $mk_diambil[] = $r['matakuliah_id'];

// Data nilai lengkap milik mahasiswa ini
$h_nilai = mysqli_query($conn,
    "SELECT n.*, mk.nama AS nama_mk, mk.kode, mk.sks, mk.semester, dsn.nama AS nama_dosen
     FROM nilai n
     JOIN matakuliah mk ON n.matakuliah_id = mk.id
     JOIN dosen dsn     ON n.dosen_id      = dsn.id
     WHERE n.mahasiswa_id = $mid
     ORDER BY mk.semester, mk.nama");

// Hitung IP (Indeks Prestasi)
$data_nilai  = [];
$total_bobot = 0; $total_sks = 0; $sudah_nilai = 0;
$mutu_map    = ['A'=>4, 'B'=>3, 'C'=>2, 'D'=>1, 'E'=>0];

while ($n = mysqli_fetch_assoc($h_nilai)) {
    $data_nilai[] = $n;
    // Hanya hitung nilai yang sudah ada angkanya
    if ($n['nilai_angka'] !== null) {
        $sudah_nilai++;
        $mutu          = $mutu_map[$n['grade']] ?? 0;
        $total_bobot  += $mutu * $n['sks'];  // Bobot = mutu × SKS
        $total_sks    += $n['sks'];
    }
}

// Rumus IP: jumlah (mutu × SKS) dibagi total SKS
$ip        = $total_sks > 0 ? round($total_bobot / $total_sks, 2) : 0;
$ip_persen = min(($ip / 4) * 100, 100); // untuk visualisasi lingkaran
$predikat  = $ip >= 3.5 ? 'Cumlaude 🌟' : ($ip >= 3.0 ? 'Sangat Memuaskan ✨' :
             ($ip >= 2.75 ? 'Memuaskan 👍' : ($ip >= 2.0 ? 'Cukup 📚' : 'Perlu Peningkatan')));

// Hitung MK yang belum dinilai
$belum_nilai = count($mk_diambil) - $sudah_nilai;

// Distribusi grade untuk grafik batang
$dist_grade = ['A'=>0, 'B'=>0, 'C'=>0, 'D'=>0, 'E'=>0];
foreach ($data_nilai as $n) {
    if ($n['grade']) $dist_grade[$n['grade']]++;
}

// ============================================================
// TAMPILKAN HALAMAN
// ============================================================
$judul = 'Dashboard Mahasiswa';
require_once '../includes/header.php';
?>

<!-- ==================== SIDEBAR ==================== -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="logo-icon">🎓</div>
    <h1>SIAKAD</h1>
    <p>Sistem Informasi Akademik</p>
  </div>

  <!-- Info user mahasiswa yang login -->
  <div class="sidebar-user">
    <div style="display:flex;align-items:center;gap:10px;">
      <div class="avatar"><?= strtoupper(substr($nama, 0, 1)) ?></div>
      <div>
        <div class="user-nama"><?= htmlspecialchars($nama) ?></div>
        <div class="user-role">🎓 Mahasiswa</div>
      </div>
    </div>
  </div>

  <!-- Menu navigasi -->
  <nav class="sidebar-nav">
    <div class="nav-label">Menu</div>
    <a href="?hal=dashboard" class="nav-item <?= $halaman==='dashboard' ? 'aktif' : '' ?>">
      <span class="icon"></span> Dashboard
    </a>
    <a href="?hal=matakuliah" class="nav-item <?= $halaman==='matakuliah' ? 'aktif' : '' ?>">
      <span class="icon"></span> Pilih Matakuliah
      <?php if (count($mk_diambil) > 0): ?>
        <span class="badge-count"><?= count($mk_diambil) ?></span>
      <?php endif; ?>
    </a>
    <a href="?hal=nilai" class="nav-item <?= $halaman==='nilai' ? 'aktif' : '' ?>">
      <span class="icon"></span> Nilai Saya
      <?php if ($sudah_nilai > 0): ?>
        <span class="badge-count"><?= $sudah_nilai ?></span>
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
      $judul_halaman = ['dashboard'=>'Dashboard','matakuliah'=>'Pilih Matakuliah','nilai'=>'Nilai Saya'];
      echo $judul_halaman[$halaman] ?? 'Dashboard';
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
    elseif ($halaman === 'matakuliah') require_once 'hal_matakuliah.php';
    elseif ($halaman === 'nilai')      require_once 'hal_nilai.php';
    ?>


<?php require_once '../includes/footer.php'; ?>

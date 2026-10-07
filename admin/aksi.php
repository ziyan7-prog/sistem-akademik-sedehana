<?php
// ============================================================
// FILE: admin/aksi.php
// Fungsi: Menangani semua aksi POST dari halaman admin
//         (tambah/edit/hapus mahasiswa, dosen, matakuliah, nilai)
// Dipanggil oleh: admin/index.php
// Setelah selesai, redirect kembali ke tab yang sesuai
// ============================================================

// File ini hanya boleh dipanggil dari admin/index.php
// Bukan diakses langsung

require_once '../includes/koneksi.php';
require_once '../includes/fungsi.php';

$aksi      = $_POST['aksi'] ?? '';
$pesan     = '';
$tipe      = 'sukses';
$tab       = 'dashboard'; // tab tujuan setelah redirect

// ============================================================
// AKSI MAHASISWA
// ============================================================

if ($aksi === 'tambah_mahasiswa') {
    // Tambah data mahasiswa baru ke tabel mahasiswa
    $username = bersihkan($conn, $_POST['username']);
    $nama     = bersihkan($conn, $_POST['nama']);
    $nim      = bersihkan($conn, $_POST['nim']);
    $password = hashPassword($_POST['password']); // Hash bcrypt

    $q = "INSERT INTO mahasiswa (username, password, nama, nim)
          VALUES ('$username', '$password', '$nama', '$nim')";

    if (mysqli_query($conn, $q)) {
        $pesan = "Mahasiswa '$nama' berhasil ditambahkan!";
    } else {
        $pesan = "Gagal! Username atau NIM mungkin sudah terdaftar.";
        $tipe  = 'error';
    }
    $tab = 'mahasiswa';
}

elseif ($aksi === 'edit_mahasiswa') {
    // Update data mahasiswa yang sudah ada
    $id       = (int)$_POST['id'];
    $username = bersihkan($conn, $_POST['username']);
    $nama     = bersihkan($conn, $_POST['nama']);
    $nim      = bersihkan($conn, $_POST['nim']);

    // Cek apakah password diisi (jika kosong, password tidak diubah)
    if (!empty($_POST['password'])) {
        $password = hashPassword($_POST['password']);
        $q = "UPDATE mahasiswa SET nama='$nama', nim='$nim', username='$username', password='$password' WHERE id=$id";
    } else {
        $q = "UPDATE mahasiswa SET nama='$nama', nim='$nim', username='$username' WHERE id=$id";
    }

    mysqli_query($conn, $q);
    $pesan = "Data mahasiswa berhasil diupdate!";
    $tab   = 'mahasiswa';
}

elseif ($aksi === 'hapus_mahasiswa') {
    // Hapus mahasiswa — nilai terkait otomatis terhapus (CASCADE)
    $id = (int)$_POST['id'];
    mysqli_query($conn, "DELETE FROM mahasiswa WHERE id=$id");
    $pesan = "Mahasiswa berhasil dihapus!";
    $tab   = 'mahasiswa';
}

// ============================================================
// AKSI DOSEN
// ============================================================

elseif ($aksi === 'tambah_dosen') {
    // ── 1. Simpan data dosen ke tabel dosen ──────────────────
    $username = bersihkan($conn, $_POST['username']);
    $nama     = bersihkan($conn, $_POST['nama']);
    $nidn     = bersihkan($conn, $_POST['nidn']);
    $password = hashPassword($_POST['password']);

    $q = "INSERT INTO dosen (username, password, nama, nidn)
          VALUES ('$username', '$password', '$nama', '$nidn')";

    if (mysqli_query($conn, $q)) {
        $dosen_baru_id = mysqli_insert_id($conn); // ID dosen yang baru dibuat
        $pesan         = "Dosen '$nama' berhasil ditambahkan!";

        // ── 2. Simpan pengampuan MK yang dicentang ────────────
        // mk_ids[] berisi array ID matakuliah yang dipilih admin
        $mk_ids = $_POST['mk_ids'] ?? [];
        $jml_mk = 0;
        foreach ($mk_ids as $mk_id) {
            $mk_id = (int)$mk_id;
            if ($mk_id <= 0) continue;

            // Upsert: jika MK sudah punya pengampu lain, ganti dengan dosen baru ini
            // ON DUPLICATE KEY UPDATE menangani constraint UNIQUE(matakuliah_id)
            if (mysqli_query($conn,
                "INSERT INTO dosen_matakuliah (dosen_id, matakuliah_id)
                 VALUES ($dosen_baru_id, $mk_id)
                 ON DUPLICATE KEY UPDATE dosen_id = $dosen_baru_id")) {
                $jml_mk++;
            }
        }

        if ($jml_mk > 0) {
            $pesan .= " Mengampu $jml_mk matakuliah.";
        }
    } else {
        $pesan = "Gagal! Username atau NIDN mungkin sudah terdaftar.";
        $tipe  = 'error';
    }
    $tab = 'dosen';
}

elseif ($aksi === 'edit_dosen') {
    // ── 1. Update data profil dosen ───────────────────────────
    $id       = (int)$_POST['id'];
    $username = bersihkan($conn, $_POST['username']);
    $nama     = bersihkan($conn, $_POST['nama']);
    $nidn     = bersihkan($conn, $_POST['nidn']);

    if (!empty($_POST['password'])) {
        $password = hashPassword($_POST['password']);
        $q = "UPDATE dosen SET nama='$nama', nidn='$nidn', username='$username', password='$password' WHERE id=$id";
    } else {
        // Password kosong = tidak diubah
        $q = "UPDATE dosen SET nama='$nama', nidn='$nidn', username='$username' WHERE id=$id";
    }
    mysqli_query($conn, $q);

    // ── 2. Sync pengampuan MK ─────────────────────────────────
    // Ambil MK yang dicentang dari form
    $mk_ids_baru = array_map('intval', $_POST['mk_ids'] ?? []);

    // Ambil MK yang sedang diampu dosen ini (sebelum update)
    $h_lama   = mysqli_query($conn,
        "SELECT matakuliah_id FROM dosen_matakuliah WHERE dosen_id=$id");
    $mk_ids_lama = [];
    while ($r = mysqli_fetch_assoc($h_lama)) $mk_ids_lama[] = (int)$r['matakuliah_id'];

    // MK yang perlu DIHAPUS (ada di lama tapi tidak dicentang lagi)
    $hapus = array_diff($mk_ids_lama, $mk_ids_baru);
    foreach ($hapus as $mk_id) {
        mysqli_query($conn,
            "DELETE FROM dosen_matakuliah WHERE dosen_id=$id AND matakuliah_id=$mk_id");
    }

    // MK yang perlu DITAMBAH (dicentang tapi belum ada di tabel)
    $tambah = array_diff($mk_ids_baru, $mk_ids_lama);
    foreach ($tambah as $mk_id) {
        if ($mk_id <= 0) continue;
        // Upsert: handle jika MK sudah diampu dosen lain
        mysqli_query($conn,
            "INSERT INTO dosen_matakuliah (dosen_id, matakuliah_id)
             VALUES ($id, $mk_id)
             ON DUPLICATE KEY UPDATE dosen_id = $id");
    }

    $pesan = "Data dosen berhasil diupdate! Mengampu " . count($mk_ids_baru) . " matakuliah.";
    $tab   = 'dosen';
}

elseif ($aksi === 'hapus_dosen') {
    // Hapus dosen — nilai yang diinput dosen ini otomatis terhapus (CASCADE)
    $id = (int)$_POST['id'];
    mysqli_query($conn, "DELETE FROM dosen WHERE id=$id");
    $pesan = "Dosen berhasil dihapus!";
    $tab   = 'dosen';
}

// ============================================================
// AKSI MATAKULIAH
// ============================================================

elseif ($aksi === 'tambah_mk') {
    // Tambah matakuliah baru
    $kode     = bersihkan($conn, $_POST['kode']);
    $nama     = bersihkan($conn, $_POST['nama']);
    $sks      = (int)$_POST['sks'];
    $semester = (int)$_POST['semester'];
    $dosen_id = (int)($_POST['dosen_id'] ?? 0);

    $q = "INSERT INTO matakuliah (kode, nama, sks, semester)
          VALUES ('$kode', '$nama', $sks, $semester)";

    if (mysqli_query($conn, $q)) {
        $mk_id = mysqli_insert_id($conn); // ID MK yang baru dibuat

        // Jika dosen pengampu dipilih, simpan ke tabel dosen_matakuliah
        if ($dosen_id > 0) {
            mysqli_query($conn,
                "INSERT INTO dosen_matakuliah (dosen_id, matakuliah_id)
                 VALUES ($dosen_id, $mk_id)
                 ON DUPLICATE KEY UPDATE dosen_id=$dosen_id");
        }
        $pesan = "Matakuliah '$nama' berhasil ditambahkan!";
    } else {
        $pesan = "Gagal! Kode matakuliah mungkin sudah ada.";
        $tipe  = 'error';
    }
    $tab = 'matakuliah';
}

elseif ($aksi === 'edit_mk') {
    // Update data matakuliah beserta dosen pengampunya
    $id       = (int)$_POST['id'];
    $kode     = bersihkan($conn, $_POST['kode']);
    $nama     = bersihkan($conn, $_POST['nama']);
    $sks      = (int)$_POST['sks'];
    $semester = (int)$_POST['semester'];
    $dosen_id = (int)($_POST['dosen_id'] ?? 0);

    mysqli_query($conn,
        "UPDATE matakuliah SET kode='$kode', nama='$nama', sks=$sks, semester=$semester WHERE id=$id");

    // Update atau hapus assignment dosen pengampu
    if ($dosen_id > 0) {
        // Upsert: update jika sudah ada, insert jika belum
        mysqli_query($conn,
            "INSERT INTO dosen_matakuliah (dosen_id, matakuliah_id)
             VALUES ($dosen_id, $id)
             ON DUPLICATE KEY UPDATE dosen_id=$dosen_id");
    } else {
        // Jika dosen dikosongkan, hapus assignment
        mysqli_query($conn, "DELETE FROM dosen_matakuliah WHERE matakuliah_id=$id");
    }

    $pesan = "Matakuliah berhasil diupdate!";
    $tab   = 'matakuliah';
}

elseif ($aksi === 'hapus_mk') {
    // Hapus matakuliah — nilai & assignment dosen otomatis terhapus (CASCADE)
    $id = (int)$_POST['id'];
    mysqli_query($conn, "DELETE FROM matakuliah WHERE id=$id");
    $pesan = "Matakuliah berhasil dihapus!";
    $tab   = 'matakuliah';
}

// ============================================================
// AKSI NILAI
// ============================================================

elseif ($aksi === 'edit_nilai') {
    // Update nilai mahasiswa, grade dihitung otomatis
    $id          = (int)$_POST['id'];
    $nilai_angka = (float)$_POST['nilai_angka'];
    $grade       = hitungGrade($nilai_angka); // hitung grade dari nilai angka

    mysqli_query($conn, "UPDATE nilai SET nilai_angka=$nilai_angka, grade='$grade' WHERE id=$id");
    $pesan = "Nilai diupdate! Grade: $grade";
    $tab   = 'nilai';
}

elseif ($aksi === 'hapus_nilai') {
    // Hapus satu record nilai
    $id = (int)$_POST['id'];
    mysqli_query($conn, "DELETE FROM nilai WHERE id=$id");
    $pesan = "Nilai berhasil dihapus!";
    $tab   = 'nilai';
}

// ============================================================
// AKSI PENGAMPUAN (assign dosen ke matakuliah)
// ============================================================

elseif ($aksi === 'assign_dosen_mk') {
    // Admin menetapkan dosen pengampu untuk sebuah matakuliah
    $mk_id    = (int)$_POST['matakuliah_id'];
    $dosen_id = (int)$_POST['dosen_id'];

    if ($dosen_id > 0) {
        // Upsert: insert baru atau update dosen jika MK sudah punya pengampu
        mysqli_query($conn,
            "INSERT INTO dosen_matakuliah (dosen_id, matakuliah_id)
             VALUES ($dosen_id, $mk_id)
             ON DUPLICATE KEY UPDATE dosen_id=$dosen_id");
        $pesan = "Dosen pengampu berhasil ditetapkan!";
    } else {
        // Hapus pengampuan jika pilihan "Tidak ada" dipilih
        mysqli_query($conn, "DELETE FROM dosen_matakuliah WHERE matakuliah_id=$mk_id");
        $pesan = "Pengampu matakuliah berhasil dihapus.";
    }
    $tab = 'pengampuan';
}

// Redirect ke halaman admin dengan pesan notifikasi via GET
header("Location: index.php?tab=$tab&pesan=" . urlencode($pesan) . "&tipe=$tipe");
exit();
?>

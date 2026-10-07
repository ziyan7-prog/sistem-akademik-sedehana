<?php
// ============================================================
// FILE: dosen/hal_rekap.php
// Fungsi: Rekap semua nilai dosen + edit/reset langsung dari tabel
// Di-include oleh: dosen/index.php
// Variabel tersedia: $did, $conn, $total_nilai_input
// ============================================================

// Ambil semua nilai dari MK yang diampu dosen ini,
// dikelompokkan per MK agar mudah dibaca
$h_all = mysqli_query($conn,
    "SELECT n.id AS nilai_id, n.nilai_angka, n.grade, n.updated_at,
            mhs.id AS mhs_id, mhs.nama AS nama_mhs, mhs.nim,
            mk.id AS mk_id, mk.nama AS nama_mk, mk.kode, mk.sks
     FROM nilai n
     JOIN mahasiswa mhs ON n.mahasiswa_id  = mhs.id
     JOIN matakuliah mk  ON n.matakuliah_id = mk.id
     JOIN dosen_matakuliah dm ON mk.id = dm.matakuliah_id
     WHERE dm.dosen_id = $did
     ORDER BY mk.nama, mhs.nama");

// Kelompokkan hasil query per matakuliah untuk tampilan yang rapi
$per_mk = [];
while ($r = mysqli_fetch_assoc($h_all)) {
    $per_mk[$r['mk_id']]['info'] = ['kode' => $r['kode'], 'nama' => $r['nama_mk'], 'sks' => $r['sks']];
    $per_mk[$r['mk_id']]['mahasiswa'][] = $r;
}

// ID nilai yang sedang dalam mode edit (dari GET parameter)
$edit_nilai_id = isset($_GET['edit_nilai']) ? (int)$_GET['edit_nilai'] : 0;
?>

<!-- Statistik ringkasan rekap -->
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:20px;">
  <div class="stat-card c1">
    <span class="stat-icon">📚</span>
    <div class="stat-val"><?= count($per_mk) ?></div>
    <div class="stat-label">MK Diampu</div>
  </div>
  <div class="stat-card c2">
    <span class="stat-icon">✅</span>
    <div class="stat-val"><?= $total_nilai_input ?></div>
    <div class="stat-label">Nilai Sudah Diisi</div>
  </div>
  <div class="stat-card c3">
    <?php
    // Hitung total mahasiswa terdaftar di semua MK yang diampu
    $r_tot = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT COUNT(*) c FROM nilai n
         JOIN dosen_matakuliah dm ON n.matakuliah_id = dm.matakuliah_id
         WHERE dm.dosen_id = $did"));
    $total_terdaftar = $r_tot['c'];
    $belum_diisi = $total_terdaftar - $total_nilai_input;
    ?>
    <span class="stat-icon">⏳</span>
    <div class="stat-val"><?= $belum_diisi ?></div>
    <div class="stat-label">Belum Diisi</div>
  </div>
</div>

<?php if (empty($per_mk)): ?>
  <div class="card">
    <div class="empty">
      <div class="empty-icon">📋</div>
      <p>Belum ada data nilai.<br>
        <a href="?hal=input_nilai" style="color:var(--accent)">Input nilai sekarang →</a>
      </p>
    </div>
  </div>

<?php else: ?>

  <?php foreach ($per_mk as $mk_id => $data): ?>
    <?php
    $info     = $data['info'];
    $mhs_list = $data['mahasiswa'];

    // Hitung rata-rata nilai untuk MK ini (hanya yang sudah ada nilainya)
    $nilai_ada = array_filter($mhs_list, fn($m) => $m['nilai_angka'] !== null);
    $avg_mk    = count($nilai_ada)
                 ? round(array_sum(array_column($nilai_ada, 'nilai_angka')) / count($nilai_ada), 1)
                 : null;
    ?>

    <div class="card" style="margin-bottom:20px;">
      <div class="card-header">
        <div>
          <div class="card-title">
            <span class="pill pill-purple" style="margin-right:8px;"><?= $info['kode'] ?></span>
            <?= htmlspecialchars($info['nama']) ?>
          </div>
          <div style="font-size:12px;color:var(--muted);margin-top:4px;">
            <?= $info['sks'] ?> SKS &nbsp;·&nbsp;
            <?= count($mhs_list) ?> mahasiswa &nbsp;·&nbsp;
            <?= count($nilai_ada) ?> sudah dinilai
            <?php if ($avg_mk !== null): ?>
              &nbsp;·&nbsp; Rata-rata: <strong style="color:var(--text)"><?= $avg_mk ?></strong>
            <?php endif; ?>
          </div>
        </div>
        <!-- Shortcut ke halaman input nilai untuk MK ini -->
        <a href="?hal=input_nilai&mk=<?= $mk_id ?>" class="btn btn-teal btn-sm">✏️ Input Nilai</a>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>NIM</th>
              <th>Nama Mahasiswa</th>
              <th>Nilai</th>
              <th>Grade</th>
              <th>Diperbarui</th>
              <th style="min-width:220px;">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($mhs_list as $no => $mhs): ?>
              <!-- Beri highlight biru pada baris yang sedang diedit -->
              <tr <?= ($edit_nilai_id == $mhs['nilai_id'])
                      ? 'style="background:rgba(99,102,241,0.08);border-left:3px solid var(--accent);"'
                      : '' ?>>
                <td style="color:var(--muted)"><?= $no+1 ?></td>
                <td><span class="pill pill-purple"><?= htmlspecialchars($mhs['nim']) ?></span></td>
                <td><strong><?= htmlspecialchars($mhs['nama_mhs']) ?></strong></td>
                <td>
                  <?php if ($mhs['nilai_angka'] !== null): ?>
                    <strong style="font-size:15px;"><?= number_format($mhs['nilai_angka'], 1) ?></strong>
                  <?php else: ?>
                    <span style="color:var(--muted);font-style:italic;">—</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($mhs['grade']): ?>
                    <span class="badge <?= warnaBadge($mhs['grade']) ?>"><?= $mhs['grade'] ?></span>
                  <?php else: ?>
                    <span class="pill pill-muted">Pending</span>
                  <?php endif; ?>
                </td>
                <td style="font-size:12px;color:var(--muted);">
                  <?= ($mhs['nilai_angka'] !== null)
                      ? date('d M Y H:i', strtotime($mhs['updated_at']))
                      : '—' ?>
                </td>
                <td>
                  <?php if ($edit_nilai_id == $mhs['nilai_id']): ?>
                    <!-- ===== MODE EDIT INLINE ===== -->
                    <!-- Form ini muncul saat tombol "✏️ Edit" diklik -->
                    <form method="POST" style="display:flex;gap:6px;align-items:center;">
                      <input type="hidden" name="aksi"     value="edit_nilai">
                      <input type="hidden" name="nilai_id" value="<?= $mhs['nilai_id'] ?>">
                      <!-- Input langsung fokus di baris ini -->
                      <input type="number" name="nilai_angka"
                             min="0" max="100" step="0.5"
                             value="<?= $mhs['nilai_angka'] ?? '' ?>"
                             placeholder="0–100"
                             style="width:80px;"
                             autofocus>
                      <button type="submit" class="btn btn-primary btn-sm">💾 Simpan</button>
                      <!-- Batal edit tanpa perubahan -->
                      <a href="?hal=rekap" class="btn btn-ghost btn-sm">✕</a>
                    </form>
                  <?php else: ?>
                    <!-- ===== TOMBOL NORMAL ===== -->
                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                      <!-- Klik Edit → tambah ?edit_nilai=ID ke URL, buka form inline -->
                      <a href="?hal=rekap&edit_nilai=<?= $mhs['nilai_id'] ?>"
                         class="btn btn-teal btn-sm">✏️ Edit</a>

                      <?php if ($mhs['nilai_angka'] !== null): ?>
                        <!-- Reset: kembalikan nilai ke NULL (status Pending) -->
                        <!-- Record mahasiswa tetap ada, hanya nilainya dikosongkan -->
                        <form method="POST" style="display:inline"
                              onsubmit="return confirm('Reset nilai <?= addslashes(htmlspecialchars($mhs['nama_mhs'])) ?>?\nNilai akan dikosongkan dan menjadi Pending.')">
                          <input type="hidden" name="aksi"     value="hapus_nilai">
                          <input type="hidden" name="nilai_id" value="<?= $mhs['nilai_id'] ?>">
                          <button type="submit" class="btn btn-red btn-sm">🔄 Reset</button>
                        </form>
                      <?php endif; ?>
                    </div>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  <?php endforeach; ?>
<?php endif; ?>

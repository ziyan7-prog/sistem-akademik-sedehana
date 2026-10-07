<?php
// ============================================================
// FILE: admin/tab_pengampuan.php
// Fungsi: Tab penugasan dosen pengampu per matakuliah
//         Admin menetapkan dosen mana yang mengajar MK mana
//         Relasi disimpan di tabel dosen_matakuliah
// Di-include oleh: admin/index.php
// Variabel tersedia: $conn, $list_dosen_all, $list_mk
// ============================================================

// Ambil ulang data MK + dosen pengampunya (join lengkap)
$h_mk_dosen = mysqli_query($conn,
    "SELECT mk.*, d.id AS dosen_id_ampu, d.nama AS nama_dosen_ampu
     FROM matakuliah mk
     LEFT JOIN dosen_matakuliah dm ON mk.id = dm.matakuliah_id
     LEFT JOIN dosen d ON dm.dosen_id = d.id
     ORDER BY mk.semester, mk.nama");

// Ambil semua dosen untuk dropdown
$h_dosen_all = mysqli_query($conn, "SELECT * FROM dosen ORDER BY nama");
$semua_dosen = [];
while ($d = mysqli_fetch_assoc($h_dosen_all)) $semua_dosen[] = $d;

// Hitung statistik
$total_diampu   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM dosen_matakuliah"))['c'];
$total_mk_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) c FROM matakuliah"))['c'];
$tidak_diampu   = $total_mk_total - $total_diampu;
?>

<!-- Statistik Ringkasan Pengampuan -->
<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);">
  <div class="stat-card c1">
    <span class="stat-icon">🔗</span>
    <div class="stat-val"><?= $total_diampu ?></div>
    <div class="stat-label">MK Sudah Diampu</div>
  </div>
  <div class="stat-card c3">
    <span class="stat-icon">⚠️</span>
    <div class="stat-val"><?= $tidak_diampu ?></div>
    <div class="stat-label">MK Belum Diampu</div>
    <div class="stat-sub">perlu assign dosen</div>
  </div>
  <div class="stat-card c2">
    <span class="stat-icon">👨‍🏫</span>
    <div class="stat-val"><?= count($semua_dosen) ?></div>
    <div class="stat-label">Total Dosen</div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title">🔗 Penugasan Dosen Pengampu</div>
    <span class="pill pill-blue"><?= $total_mk_total ?> matakuliah</span>
  </div>
  <p style="font-size:13px;color:var(--muted);margin-bottom:20px;">
    Tetapkan dosen pengampu untuk setiap matakuliah. Satu matakuliah hanya bisa diampu oleh satu dosen.
    Mahasiswa yang mendaftar MK akan otomatis mendapat dosen pengampu yang sesuai.
  </p>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Kode</th>
          <th>Matakuliah</th>
          <th>SKS</th>
          <th>Semester</th>
          <th>Dosen Pengampu</th>
          <th style="min-width:200px;">Ubah Pengampu</th>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1; while ($mk = mysqli_fetch_assoc($h_mk_dosen)): ?>
          <tr>
            <td style="color:var(--muted)"><?= $no++ ?></td>
            <td><span class="pill pill-purple"><?= htmlspecialchars($mk['kode']) ?></span></td>
            <td><strong><?= htmlspecialchars($mk['nama']) ?></strong></td>
            <td><?= $mk['sks'] ?></td>
            <td><?= $mk['semester'] ?></td>
            <td>
              <?php if ($mk['nama_dosen_ampu']): ?>
                <!-- Tampilkan badge dosen yang sudah ditugaskan -->
                <span class="pill pill-green">👨‍🏫 <?= htmlspecialchars($mk['nama_dosen_ampu']) ?></span>
              <?php else: ?>
                <!-- Peringatan jika belum ada dosen -->
                <span class="pill pill-muted" style="color:#f59e0b;background:rgba(245,158,11,0.1);">
                  ⚠️ Belum ada
                </span>
              <?php endif; ?>
            </td>
            <td>
              <!-- Form inline: langsung update dosen pengampu saat dropdown berubah -->
              <form method="POST" action="aksi.php" style="display:flex;gap:8px;align-items:center;">
                <input type="hidden" name="aksi" value="assign_dosen_mk">
                <input type="hidden" name="matakuliah_id" value="<?= $mk['id'] ?>">
                <select name="dosen_id" style="font-size:12px;padding:7px 10px;">
                  <!-- Opsi kosong = hapus pengampu -->
                  <option value="0">— Tidak ada —</option>
                  <?php foreach ($semua_dosen as $d): ?>
                    <option value="<?= $d['id'] ?>"
                      <?= $mk['dosen_id_ampu'] == $d['id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars($d['nama']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-teal btn-sm">💾</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

  <!-- Catatan penting -->
  <div style="margin-top:16px;padding:14px 16px;background:rgba(99,102,241,0.08);
              border:1px solid rgba(99,102,241,0.2);border-radius:10px;font-size:12px;color:var(--muted);">
    💡 <strong style="color:var(--text)">Catatan:</strong>
    Saat mahasiswa mendaftar matakuliah, sistem akan otomatis menggunakan dosen pengampu yang telah ditetapkan di sini.
    Pastikan setiap matakuliah sudah memiliki dosen pengampu sebelum mahasiswa mendaftar.
  </div>
</div>

<!-- Ringkasan MK per Dosen -->
<div class="card">
  <div class="card-header">
    <div class="card-title">📋 Ringkasan MK per Dosen</div>
  </div>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;">
    <?php foreach ($semua_dosen as $d):
      // Ambil MK yang diampu oleh dosen ini
      $h_mk_ampu = mysqli_query($conn,
          "SELECT mk.kode, mk.nama, mk.sks, mk.semester
           FROM dosen_matakuliah dm
           JOIN matakuliah mk ON dm.matakuliah_id = mk.id
           WHERE dm.dosen_id = {$d['id']}
           ORDER BY mk.semester, mk.nama");
      $mk_ampu = [];
      while ($r = mysqli_fetch_assoc($h_mk_ampu)) $mk_ampu[] = $r;
    ?>
      <div style="padding:16px;background:var(--surface2);border-radius:10px;border:1px solid var(--border);">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
          <div class="avatar" style="background:linear-gradient(135deg,#14b8a6,#3b82f6);font-size:13px;">
            <?= strtoupper(substr($d['nama'], 0, 1)) ?>
          </div>
          <div>
            <div style="font-weight:700;font-size:13px;"><?= htmlspecialchars($d['nama']) ?></div>
            <div style="font-size:11px;color:var(--muted);">NIDN: <?= $d['nidn'] ?></div>
          </div>
        </div>
        <?php if (empty($mk_ampu)): ?>
          <p style="font-size:12px;color:var(--muted);font-style:italic;">Belum mengampu matakuliah apapun</p>
        <?php else: ?>
          <?php foreach ($mk_ampu as $m): ?>
            <div style="display:flex;align-items:center;justify-content:space-between;
                        padding:6px 0;border-bottom:1px solid var(--border);font-size:12px;">
              <span><?= htmlspecialchars($m['nama']) ?></span>
              <div style="display:flex;gap:4px;">
                <span class="pill pill-purple" style="font-size:10px;"><?= $m['kode'] ?></span>
                <span class="pill pill-muted"  style="font-size:10px;"><?= $m['sks'] ?> SKS</span>
              </div>
            </div>
          <?php endforeach; ?>
          <div style="margin-top:8px;font-size:11px;color:var(--muted);">
            Total: <strong style="color:var(--text)"><?= count($mk_ampu) ?> matakuliah</strong>
          </div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>

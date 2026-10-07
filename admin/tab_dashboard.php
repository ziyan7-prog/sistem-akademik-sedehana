<?php
// ============================================================
// FILE: admin/tab_dashboard.php
// Fungsi: Konten tab Dashboard — statistik dan aktivitas terbaru
// Di-include oleh: admin/index.php
// Variabel yang tersedia: $jml_mhs, $jml_dosen, $jml_mk,
//                         $jml_nilai, $dist_grade, $aktivitas
// ============================================================
?>

<!-- Kartu Statistik Ringkasan -->
<div class="stats-grid">
  <div class="stat-card c1">
    <span class="stat-icon">👨‍🎓</span>
    <div class="stat-val"><?= $jml_mhs ?></div>
    <div class="stat-label">Mahasiswa</div>
    <div class="stat-sub"><a href="?tab=mahasiswa" style="color:var(--accent)">Kelola →</a></div>
  </div>
  <div class="stat-card c2">
    <span class="stat-icon">👨‍🏫</span>
    <div class="stat-val"><?= $jml_dosen ?></div>
    <div class="stat-label">Dosen</div>
    <div class="stat-sub"><a href="?tab=dosen" style="color:var(--teal)">Kelola →</a></div>
  </div>
  <div class="stat-card c3">
    <span class="stat-icon">📚</span>
    <div class="stat-val"><?= $jml_mk ?></div>
    <div class="stat-label">Matakuliah</div>
    <div class="stat-sub"><a href="?tab=matakuliah" style="color:var(--yellow)">Kelola →</a></div>
  </div>
  <div class="stat-card c4">
    <span class="stat-icon">✅</span>
    <div class="stat-val"><?= $jml_nilai ?></div>
    <div class="stat-label">Total Nilai</div>
    <div class="stat-sub"><a href="?tab=nilai" style="color:var(--pink)">Lihat →</a></div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

  <!-- Distribusi Grade Semua Mahasiswa -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">📊 Distribusi Grade</div>
      <span class="pill pill-purple"><?= $jml_nilai ?> nilai</span>
    </div>
    <?php if ($jml_nilai > 0): ?>
      <?php foreach ($dist_grade as $g => $jml):
        if (!$jml) continue;
        // Hitung persentase tiap grade
        $pct = round($jml / $jml_nilai * 100);
      ?>
      <div style="margin-bottom:14px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
          <div style="display:flex;align-items:center;gap:8px;">
            <span class="badge <?= warnaBadge($g) ?>"><?= $g ?></span>
            <span style="font-size:13px;color:var(--muted)"><?= $jml ?> mahasiswa</span>
          </div>
          <strong style="font-size:13px;"><?= $pct ?>%</strong>
        </div>
        <!-- Bar progress sesuai persentase -->
        <div class="nilai-bar">
          <div class="nilai-bar-fill" style="width:<?= $pct ?>%;background:<?= warnaHex($g) ?>;"></div>
        </div>
      </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty"><div class="empty-icon">📊</div><p>Belum ada data nilai</p></div>
    <?php endif; ?>
  </div>

  <!-- Aksi Cepat Admin -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">⚡ Aksi Cepat</div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
      <a href="?tab=mahasiswa" class="btn btn-primary" style="justify-content:center;flex-direction:column;padding:16px;gap:4px;text-align:center;">
        <span style="font-size:20px;">👨‍🎓</span><span>Tambah Mahasiswa</span>
      </a>
      <a href="?tab=dosen" class="btn btn-teal" style="justify-content:center;flex-direction:column;padding:16px;gap:4px;text-align:center;">
        <span style="font-size:20px;">👨‍🏫</span><span>Tambah Dosen</span>
      </a>
      <a href="?tab=matakuliah" class="btn btn-ghost" style="justify-content:center;flex-direction:column;padding:16px;gap:4px;text-align:center;">
        <span style="font-size:20px;">📚</span><span>Tambah MK</span>
      </a>
      <a href="?tab=nilai" class="btn btn-ghost" style="justify-content:center;flex-direction:column;padding:16px;gap:4px;text-align:center;">
        <span style="font-size:20px;">📊</span><span>Kelola Nilai</span>
      </a>
    </div>
  </div>
</div>

<!-- Tabel Aktivitas Nilai Terbaru -->
<div class="card">
  <div class="card-header">
    <div class="card-title">🕐 Aktivitas Nilai Terbaru</div>
    <a href="?tab=nilai" class="btn btn-ghost btn-sm">Lihat Semua →</a>
  </div>
  <?php if (empty($aktivitas)): ?>
    <div class="empty"><div class="empty-icon">📭</div><p>Belum ada aktivitas</p></div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>Mahasiswa</th><th>Matakuliah</th><th>Dosen</th><th>Nilai</th><th>Grade</th><th>Waktu</th></tr>
        </thead>
        <tbody>
          <?php foreach ($aktivitas as $a): ?>
            <tr>
              <td><strong><?= htmlspecialchars($a['nm_mhs']) ?></strong></td>
              <td><?= htmlspecialchars($a['nm_mk']) ?></td>
              <td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($a['nm_dosen']) ?></td>
              <td><strong><?= number_format($a['nilai_angka'], 1) ?></strong></td>
              <td><span class="badge <?= warnaBadge($a['grade']) ?>"><?= $a['grade'] ?></span></td>
              <td style="font-size:12px;color:var(--muted)"><?= date('d M Y H:i', strtotime($a['updated_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

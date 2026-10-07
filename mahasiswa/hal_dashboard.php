<?php
// ============================================================
// FILE: mahasiswa/hal_dashboard.php
// Fungsi: Konten halaman Dashboard untuk mahasiswa
// Di-include oleh: mahasiswa/index.php
// Variabel tersedia: $mk_diambil, $sudah_nilai, $belum_nilai,
//                   $ip, $ip_persen, $predikat, $total_sks,
//                   $dist_grade, $data_nilai, $semua_mk
// ============================================================
?>

<!-- Kartu Statistik Ringkasan -->
<div class="stats-grid">
  <div class="stat-card c1">
    <span class="stat-icon">📚</span>
    <div class="stat-val"><?= count($mk_diambil) ?></div>
    <div class="stat-label">MK Diambil</div>
    <div class="stat-sub">dari <?= mysqli_num_rows($semua_mk) ?> tersedia</div>
  </div>
  <div class="stat-card c2">
    <span class="stat-icon">✅</span>
    <div class="stat-val"><?= $sudah_nilai ?></div>
    <div class="stat-label">Sudah Dinilai</div>
    <div class="stat-sub"><?= $belum_nilai ?> menunggu nilai</div>
  </div>
  <div class="stat-card c3">
    <span class="stat-icon">🎯</span>
    <div class="stat-val"><?= $ip ?></div>
    <div class="stat-label">Indeks Prestasi</div>
    <div class="stat-sub"><?= $predikat ?></div>
  </div>
  <div class="stat-card c4">
    <span class="stat-icon">📖</span>
    <div class="stat-val"><?= $total_sks ?></div>
    <div class="stat-label">Total SKS</div>
    <div class="stat-sub">SKS bernilai</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

  <!-- Card Indeks Prestasi dengan visualisasi lingkaran -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">🏆 Indeks Prestasi Sementara</div>
    </div>
    <?php if ($ip > 0): ?>
      <div class="ip-ring">
        <!-- Lingkaran IP menggunakan conic-gradient -->
        <div style="width:80px;height:80px;flex-shrink:0;border-radius:50%;
                    background:conic-gradient(#6366f1 <?= round($ip_persen * 3.6) ?>deg, #1a2234 0deg);
                    display:flex;align-items:center;justify-content:center;position:relative;">
          <div style="width:58px;height:58px;border-radius:50%;background:var(--surface);
                      position:absolute;display:flex;align-items:center;justify-content:center;">
            <span style="font-family:'Syne',sans-serif;font-size:17px;font-weight:800;color:var(--accent);"><?= $ip ?></span>
          </div>
        </div>
        <div class="ip-info">
          <h3><?= $ip ?> / 4.00</h3>
          <p>Berdasarkan <?= $sudah_nilai ?> nilai yang masuk</p>
          <div class="predikat"><?= $predikat ?></div>
        </div>
      </div>
    <?php else: ?>
      <div class="empty"><div class="empty-icon">📊</div><p>Belum ada nilai masuk</p></div>
    <?php endif; ?>
  </div>

  <!-- Distribusi Grade -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">📈 Distribusi Grade</div>
    </div>
    <?php if ($sudah_nilai > 0): ?>
      <?php foreach ($dist_grade as $g => $jml): ?>
        <?php if ($jml > 0): ?>
          <?php $pct = round($jml / $sudah_nilai * 100); ?>
          <div style="margin-bottom:12px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
              <div style="display:flex;align-items:center;gap:8px;">
                <span class="badge <?= warnaBadge($g) ?>"><?= $g ?></span>
                <span style="font-size:13px;color:var(--muted)"><?= $jml ?> matakuliah</span>
              </div>
              <span style="font-size:12px;font-weight:700;"><?= $pct ?>%</span>
            </div>
            <div class="nilai-bar">
              <div class="nilai-bar-fill" style="width:<?= $pct ?>%;background:<?= warnaHex($g) ?>;"></div>
            </div>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty"><div class="empty-icon">📈</div><p>Belum ada data grade</p></div>
    <?php endif; ?>
  </div>
</div>

<!-- Tabel Nilai Terbaru (5 teratas) -->
<div class="card">
  <div class="card-header">
    <div class="card-title">🕐 Nilai Terbaru</div>
    <a href="?hal=nilai" class="btn btn-ghost btn-sm">Lihat Semua →</a>
  </div>
  <?php if (empty($data_nilai)): ?>
    <div class="empty"><div class="empty-icon">📭</div><p>Belum ada nilai masuk</p></div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead><tr><th>Matakuliah</th><th>SKS</th><th>Dosen</th><th>Nilai</th><th>Grade</th></tr></thead>
        <tbody>
          <?php foreach (array_slice($data_nilai, 0, 5) as $n): ?>
            <tr>
              <td>
                <strong><?= htmlspecialchars($n['nama_mk']) ?></strong><br>
                <span style="font-size:11px;color:var(--muted)"><?= $n['kode'] ?></span>
              </td>
              <td><?= $n['sks'] ?></td>
              <td style="font-size:13px;"><?= htmlspecialchars($n['nama_dosen']) ?></td>
              <td>
                <strong><?= $n['nilai_angka'] !== null ? number_format($n['nilai_angka'],1) : '<span style="color:var(--muted)">—</span>' ?></strong>
              </td>
              <td>
                <?php if ($n['grade']): ?>
                  <span class="badge <?= warnaBadge($n['grade']) ?>"><?= $n['grade'] ?></span>
                <?php else: ?>
                  <span class="pill pill-muted">Pending</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

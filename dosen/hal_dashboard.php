<?php
// ============================================================
// FILE: dosen/hal_dashboard.php
// Fungsi: Konten halaman Dashboard untuk dosen
// Di-include oleh: dosen/index.php
// Variabel tersedia: $total_mk, $total_nilai_input, $avg_nilai,
//                   $dist_grade, $recent_nilai
// ============================================================
?>

<!-- Kartu Statistik Ringkasan -->
<div class="stats-grid">
  <div class="stat-card c1">
    <span class="stat-icon">📚</span>
    <div class="stat-val"><?= $total_mk ?></div>
    <div class="stat-label">Total Matakuliah</div>
    <div class="stat-sub">tersedia di sistem</div>
  </div>
  <div class="stat-card c2">
    <span class="stat-icon">✅</span>
    <div class="stat-val"><?= $total_nilai_input ?></div>
    <div class="stat-label">Nilai Diinput</div>
    <div class="stat-sub">oleh Anda</div>
  </div>
  <div class="stat-card c3">
    <span class="stat-icon">📊</span>
    <div class="stat-val"><?= $avg_nilai ?: '—' ?></div>
    <div class="stat-label">Rata-rata Nilai</div>
    <div class="stat-sub">yang Anda berikan</div>
  </div>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

  <!-- Distribusi Grade yang Diberikan Dosen Ini -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">📊 Distribusi Grade Anda</div>
      <span class="pill pill-purple"><?= $total_nilai_input ?> nilai</span>
    </div>
    <?php if ($total_nilai_input > 0): ?>
      <?php foreach ($dist_grade as $g => $jml):
        if ($jml === 0) continue;
        // Hitung persentase tiap grade dari total yang diinput
        $pct = round($jml / $total_nilai_input * 100);
      ?>
        <div style="margin-bottom:14px;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:5px;">
            <div style="display:flex;align-items:center;gap:8px;">
              <span class="badge <?= warnaBadge($g) ?>"><?= $g ?></span>
              <span style="font-size:13px;color:var(--muted)"><?= $jml ?> mahasiswa</span>
            </div>
            <span style="font-size:13px;font-weight:700;"><?= $pct ?>%</span>
          </div>
          <div class="nilai-bar">
            <div class="nilai-bar-fill" style="width:<?= $pct ?>%;background:<?= warnaHex($g) ?>;"></div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="empty"><div class="empty-icon">📊</div><p>Belum ada nilai diinput</p></div>
    <?php endif; ?>
  </div>

  <!-- Aksi Cepat + Info Konversi Grade -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">⚡ Aksi Cepat</div>
    </div>
    <div style="display:flex;flex-direction:column;gap:12px;">
      <a href="?hal=input_nilai" class="btn btn-primary" style="justify-content:center;padding:14px;">
        ✏️ Input Nilai Mahasiswa
      </a>
      <a href="?hal=rekap" class="btn btn-teal" style="justify-content:center;padding:14px;">
        📋 Lihat Rekap Nilai
      </a>
    </div>

    <!-- Tabel konversi grade sebagai referensi dosen -->
    <div class="divider"></div>
    <div style="font-size:12px;color:var(--muted);line-height:1.9;">
      <div style="margin-bottom:4px;"><strong style="color:var(--text)">📐 Tabel Konversi Grade:</strong></div>
      <div>🟢 A = 85 – 100 &nbsp;&nbsp; 🔵 B = 75 – 84</div>
      <div>🟡 C = 65 – 74 &nbsp;&nbsp; 🟠 D = 55 – 64</div>
      <div>🔴 E = 0 – 54</div>
    </div>
  </div>
</div>

<!-- Tabel 8 Nilai Terbaru yang Diinput Dosen Ini -->
<div class="card">
  <div class="card-header">
    <div class="card-title">🕐 Nilai Terbaru Diinput</div>
    <a href="?hal=rekap" class="btn btn-ghost btn-sm">Lihat Semua →</a>
  </div>
  <?php if (empty($recent_nilai)): ?>
    <div class="empty"><div class="empty-icon">📝</div><p>Belum ada nilai diinput</p></div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>Mahasiswa</th><th>Matakuliah</th><th>Nilai</th><th>Grade</th><th>Waktu</th></tr>
        </thead>
        <tbody>
          <?php foreach ($recent_nilai as $n): ?>
            <tr>
              <td><strong><?= htmlspecialchars($n['nama_mhs']) ?></strong></td>
              <td>
                <?= htmlspecialchars($n['nama_mk']) ?><br>
                <span style="font-size:11px;color:var(--muted)"><?= $n['kode'] ?></span>
              </td>
              <td><strong><?= number_format($n['nilai_angka'], 1) ?></strong></td>
              <td><span class="badge <?= warnaBadge($n['grade']) ?>"><?= $n['grade'] ?></span></td>
              <td style="font-size:12px;color:var(--muted)"><?= date('d M Y', strtotime($n['updated_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php
// ============================================================
// FILE: mahasiswa/hal_nilai.php
// Fungsi: Halaman rekap nilai lengkap mahasiswa beserta IP
// Di-include oleh: mahasiswa/index.php
// Variabel tersedia: $ip, $ip_persen, $predikat, $total_sks,
//                   $sudah_nilai, $mk_diambil, $belum_nilai,
//                   $data_nilai
// ============================================================
?>

<!-- Ringkasan IP di bagian atas -->
<?php if ($ip > 0): ?>
  <div class="ip-ring">
    <!-- Lingkaran IP visual -->
    <div style="width:80px;height:80px;flex-shrink:0;border-radius:50%;
                background:conic-gradient(#6366f1 <?= round($ip_persen * 3.6) ?>deg, #1a2234 0deg);
                display:flex;align-items:center;justify-content:center;position:relative;">
      <div style="width:58px;height:58px;border-radius:50%;background:var(--surface);
                  position:absolute;display:flex;align-items:center;justify-content:center;">
        <span style="font-family:'Syne',sans-serif;font-size:17px;font-weight:800;color:var(--accent);"><?= $ip ?></span>
      </div>
    </div>
    <div class="ip-info">
      <h3>IP: <?= $ip ?> / 4.00</h3>
      <p><?= $total_sks ?> SKS bernilai dari <?= count($mk_diambil) ?> matakuliah diambil</p>
      <div class="predikat"><?= $predikat ?></div>
    </div>
  </div>
<?php endif; ?>

<!-- Tabel Rekap Nilai Lengkap -->
<div class="card">
  <div class="card-header">
    <div class="card-title">📊 Rekap Nilai Lengkap</div>
    <span class="pill pill-blue"><?= count($data_nilai) ?> matakuliah</span>
  </div>

  <?php if (empty($data_nilai)): ?>
    <div class="empty">
      <div class="empty-icon">📭</div>
      <p>Belum ada matakuliah yang Anda ikuti.<br>
        <a href="?hal=matakuliah" style="color:var(--accent)">Pilih matakuliah sekarang →</a>
      </p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr><th>#</th><th>Matakuliah</th><th>SKS</th><th>Dosen</th><th>Nilai</th><th>Grade</th><th>Status</th></tr>
        </thead>
        <tbody>
          <?php foreach ($data_nilai as $no => $n): ?>
            <tr>
              <td style="color:var(--muted)"><?= $no+1 ?></td>
              <td>
                <strong><?= htmlspecialchars($n['nama_mk']) ?></strong><br>
                <span style="font-size:11px;color:var(--muted)"><?= $n['kode'] ?> · Sem <?= $n['semester'] ?></span>
              </td>
              <td><?= $n['sks'] ?></td>
              <td style="font-size:13px;"><?= htmlspecialchars($n['nama_dosen']) ?></td>
              <td>
                <?php if ($n['nilai_angka'] !== null): ?>
                  <strong style="font-size:16px;"><?= number_format($n['nilai_angka'],1) ?></strong>
                  <!-- Progress bar nilai relatif terhadap 100 -->
                  <div class="nilai-bar" style="width:80px;">
                    <div class="nilai-bar-fill"
                         style="width:<?= $n['nilai_angka'] ?>%;background:<?= warnaHex($n['grade']) ?>;"></div>
                  </div>
                <?php else: ?>
                  <span style="color:var(--muted);font-style:italic;">Belum dinilai</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($n['grade']): ?>
                  <span class="badge <?= warnaBadge($n['grade']) ?>"><?= $n['grade'] ?></span>
                <?php else: ?>
                  <span style="color:var(--muted)">—</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($n['nilai_angka'] !== null): ?>
                  <!-- Tentukan status lulus/tidak lulus berdasarkan grade -->
                  <?php $g = $n['grade']; ?>
                  <span class="pill <?= $g === 'A' ? 'pill-green' : ($g === 'B' ? 'pill-blue' : ($g === 'E' ? 'pill-muted' : 'pill-yellow')) ?>">
                    <?= $g === 'E' ? 'Tidak Lulus' : 'Lulus' ?>
                  </span>
                <?php else: ?>
                  <span class="pill pill-muted">Menunggu</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Ringkasan statistik di bawah tabel -->
    <div style="margin-top:20px;padding:16px;background:var(--surface2);border-radius:10px;
                display:flex;gap:24px;flex-wrap:wrap;font-size:13px;">
      <div>📚 <strong><?= count($mk_diambil) ?></strong> MK Diambil</div>
      <div>✅ <strong><?= $sudah_nilai ?></strong> Sudah Dinilai</div>
      <div>⏳ <strong><?= $belum_nilai ?></strong> Menunggu</div>
      <div>📖 <strong><?= $total_sks ?></strong> SKS</div>
      <div>🎯 IP: <strong><?= $ip ?></strong></div>
    </div>
  <?php endif; ?>
</div>

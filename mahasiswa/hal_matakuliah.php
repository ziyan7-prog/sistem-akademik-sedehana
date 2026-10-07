<?php
// ============================================================
// FILE: mahasiswa/hal_matakuliah.php
// Fungsi: Halaman pilih dan kelola matakuliah mahasiswa
//         Setiap MK punya tombol "Ambil" sendiri — satu per satu
//         Termasuk fitur batal (jika nilai masih kosong)
// Di-include oleh: mahasiswa/index.php
// Variabel tersedia: $mid, $semua_mk, $mk_diambil, $data_nilai
// ============================================================

// Hitung MK yang tersedia (belum diambil & ada pengampu)
$jml_tersedia = 0;
mysqli_data_seek($semua_mk, 0);
while ($r = mysqli_fetch_assoc($semua_mk)) {
    if (!in_array($r['id'], $mk_diambil) && !empty($r['dosen_id_ampu'])) $jml_tersedia++;
}
mysqli_data_seek($semua_mk, 0);
?>

<!-- ===== SECTION 1: DAFTAR SEMUA MK ===== -->
<div class="card">
  <div class="card-header">
    <div class="card-title">📚 Pilih Matakuliah</div>
    <div style="display:flex;gap:8px;align-items:center;">
      <span class="pill pill-green"><?= $jml_tersedia ?> tersedia</span>
      <span class="pill pill-blue"><?= count($mk_diambil) ?> diambil</span>
    </div>
  </div>
  <p style="font-size:13px;color:var(--muted);margin-bottom:20px;">
    Klik tombol <strong style="color:var(--text)">Ambil</strong> pada matakuliah yang ingin Anda ikuti.
    MK yang abu-abu belum memiliki dosen pengampu.
  </p>

  <div class="mk-grid">
    <?php
    mysqli_data_seek($semua_mk, 0);
    while ($mk = mysqli_fetch_assoc($semua_mk)):
      $sudah_diambil = in_array($mk['id'], $mk_diambil);
      $ada_pengampu  = !empty($mk['dosen_id_ampu']);
      // Kelas visual card: terpilih=hijau, nonaktif=abu-abu, default=normal
      $kelas = $sudah_diambil ? 'terpilih' : (!$ada_pengampu ? 'mk-nonaktif' : '');
    ?>
      <!-- Setiap card adalah satu unit form sendiri — daftar satu per satu -->
      <div class="mk-item <?= $kelas ?>">

        <!-- Bagian atas card: info MK -->
        <div class="mk-nama"><?= htmlspecialchars($mk['nama']) ?></div>

        <div class="mk-meta">
          <span class="pill pill-purple" style="font-size:10px;padding:2px 7px;"><?= $mk['kode'] ?></span>
          &nbsp;<?= $mk['sks'] ?> SKS &nbsp;·&nbsp; Semester <?= $mk['semester'] ?>
        </div>

        <!-- Info dosen pengampu -->
        <div class="mk-dosen">
          <?php if ($ada_pengampu): ?>
            <span style="color:var(--teal);">👨‍🏫 <?= htmlspecialchars($mk['nama_dosen_ampu']) ?></span>
          <?php else: ?>
            <span style="color:#f59e0b;">⚠️ Belum ada dosen pengampu</span>
          <?php endif; ?>
        </div>

        <!-- Bagian bawah card: tombol aksi -->
        <div class="mk-aksi">
          <?php if ($sudah_diambil): ?>
            <!-- Status: sudah terdaftar -->
            <span class="pill pill-green" style="font-size:11px;padding:5px 12px;">✓ Sudah Diambil</span>

          <?php elseif (!$ada_pengampu): ?>
            <!-- Status: tidak tersedia karena belum ada dosen pengampu -->
            <span class="pill pill-muted" style="font-size:11px;padding:5px 12px;opacity:0.6;">
              Tidak Tersedia
            </span>

          <?php else: ?>
            <!-- Tombol Ambil — form POST satu MK saja -->
            <form method="POST"
                  onsubmit="return confirm('Ambil matakuliah\n\"<?= addslashes(htmlspecialchars($mk['nama'])) ?>\"?')">
              <input type="hidden" name="aksi"          value="daftar_satu">
              <input type="hidden" name="matakuliah_id" value="<?= $mk['id'] ?>">
              <button type="submit" class="btn btn-primary btn-sm" style="width:100%;justify-content:center;">
                ➕ Ambil
              </button>
            </form>

          <?php endif; ?>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
</div>

<!-- ===== SECTION 2: TABEL MATAKULIAH YANG SUDAH DIAMBIL ===== -->
<?php if (!empty($data_nilai)): ?>
<div class="card">
  <div class="card-header">
    <div class="card-title">📋 Matakuliah yang Diambil</div>
    <span class="pill pill-blue"><?= count($data_nilai) ?> matakuliah</span>
  </div>
  <p style="font-size:13px;color:var(--muted);margin-bottom:16px;">
    Pembatalan hanya bisa dilakukan jika <strong style="color:var(--text)">nilai belum diinput</strong> oleh dosen.
  </p>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>#</th>
          <th>Matakuliah</th>
          <th>Dosen Pengampu</th>
          <th>SKS</th>
          <th>Sem</th>
          <th>Nilai</th>
          <th>Grade</th>
          <th>Status</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($data_nilai as $no => $n):
          // Tentukan apakah MK ini bisa dibatalkan (nilai masih NULL)
          $bisa_batal = ($n['nilai_angka'] === null);
        ?>
          <tr>
            <td style="color:var(--muted)"><?= $no+1 ?></td>
            <td>
              <strong><?= htmlspecialchars($n['nama_mk']) ?></strong><br>
              <span style="font-size:11px;color:var(--muted);"><?= $n['kode'] ?></span>
            </td>
            <td style="font-size:13px;color:var(--muted);">
              👨‍🏫 <?= htmlspecialchars($n['nama_dosen']) ?>
            </td>
            <td><?= $n['sks'] ?></td>
            <td><?= $n['semester'] ?></td>
            <td>
              <?php if ($n['nilai_angka'] !== null): ?>
                <strong style="font-size:15px;"><?= number_format($n['nilai_angka'], 1) ?></strong>
              <?php else: ?>
                <span style="color:var(--muted);">—</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($n['grade']): ?>
                <span class="badge <?= warnaBadge($n['grade']) ?>"><?= $n['grade'] ?></span>
              <?php else: ?>
                <span class="pill pill-muted">—</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($n['nilai_angka'] !== null): ?>
                <!-- Sudah ada nilai dari dosen -->
                <span class="pill pill-green">✅ Sudah dinilai</span>
              <?php else: ?>
                <!-- Nilai masih pending -->
                <span class="pill" style="background:rgba(245,158,11,0.12);color:#f59e0b;">
                  ⏳ Menunggu nilai
                </span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($bisa_batal): ?>
                <!-- Tombol batal: hanya tampil jika nilai masih kosong -->
                <form method="POST" style="display:inline"
                      onsubmit="return konfirmasiBatal('<?= addslashes(htmlspecialchars($n['nama_mk'])) ?>')">
                  <input type="hidden" name="aksi"         value="batal">
                  <input type="hidden" name="matakuliah_id" value="<?= $n['matakuliah_id'] ?>">
                  <button type="submit" class="btn btn-red btn-sm">✕ Batalkan</button>
                </form>
              <?php else: ?>
                <!-- Tidak bisa batal karena sudah ada nilai -->
                <span style="font-size:11px;color:var(--muted);" title="Nilai sudah diinput dosen">
                  🔒 Terkunci
                </span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Keterangan di bawah tabel -->
  <div style="margin-top:14px;padding:12px 16px;background:var(--surface2);border-radius:8px;font-size:12px;color:var(--muted);">
    🔒 <strong style="color:var(--text)">Terkunci</strong> = nilai sudah diinput dosen, pendaftaran tidak bisa dibatalkan.<br>
    ✕ <strong style="color:var(--text)">Batalkan</strong> = nilai masih kosong, pendaftaran bisa dibatalkan.
  </div>
</div>
<?php endif; ?>


<script>
/**
 * konfirmasiBatal() — dialog konfirmasi 2 langkah sebelum batal
 * Minta user mengetik nama MK untuk konfirmasi pembatalan
 */
function konfirmasiBatal(namaMk) {
  // Langkah 1: konfirmasi umum
  if (!confirm('Batalkan pendaftaran "' + namaMk + '"?\n\nTindakan ini tidak bisa dibatalkan.')) {
    return false;
  }
  // Langkah 2: konfirmasi tambahan agar tidak tertekan tombol tidak sengaja
  var input = prompt('Ketik "BATAL" untuk mengkonfirmasi pembatalan:');
  return input !== null && input.trim().toUpperCase() === 'BATAL';
}
</script>

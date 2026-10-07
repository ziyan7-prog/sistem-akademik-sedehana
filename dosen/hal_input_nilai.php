<?php
// ============================================================
// FILE: dosen/hal_input_nilai.php
// Fungsi: Halaman input nilai mahasiswa per matakuliah
// Di-include oleh: dosen/index.php
// Variabel tersedia: $semua_mk, $filter_mk, $nama_mk_filter,
//                   $list_mhs (mahasiswa di MK terpilih)
// ============================================================
?>

<!-- Form Pilih Matakuliah -->
<div class="card">
  <div class="card-header">
    <div class="card-title">📚 Pilih Matakuliah</div>
  </div>
  <!-- Form GET untuk memfilter mahasiswa berdasarkan MK -->
  <form method="GET" style="display:flex;gap:12px;align-items:flex-end;">
    <input type="hidden" name="hal" value="input_nilai">
    <div class="form-group" style="flex:1;margin:0;">
      <label>Matakuliah</label>
      <select name="mk">
        <option value="">— Pilih Matakuliah —</option>
        <?php
        // Tampilkan semua MK sebagai pilihan dropdown
        mysqli_data_seek($semua_mk, 0);
        while ($mk = mysqli_fetch_assoc($semua_mk)):
        ?>
          <option value="<?= $mk['id'] ?>" <?= $filter_mk == $mk['id'] ? 'selected' : '' ?>>
            <?= $mk['kode'] ?> — <?= htmlspecialchars($mk['nama']) ?> (Sem <?= $mk['semester'] ?>)
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">🔍 Tampilkan</button>
  </form>
</div>

<!-- Tabel Mahasiswa + Form Input Nilai (muncul jika MK sudah dipilih) -->
<?php if ($filter_mk): ?>
<div class="card">
  <div class="card-header">
    <div class="card-title">👥 <?= htmlspecialchars($nama_mk_filter) ?></div>
    <span class="pill pill-blue"><?= count($list_mhs) ?> mahasiswa</span>
  </div>

  <?php if (empty($list_mhs)): ?>
    <div class="empty">
      <div class="empty-icon">👥</div>
      <p>Belum ada mahasiswa yang terdaftar di matakuliah ini.<br>
         <small style="color:var(--muted)">Mahasiswa perlu mendaftar terlebih dahulu melalui halaman mahasiswa.</small>
      </p>
    </div>
  <?php else: ?>
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>NIM</th>
            <th>Nama Mahasiswa</th>
            <th>Nilai Saat Ini</th>
            <th>Grade</th>
            <th style="min-width:200px">Input / Update Nilai</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($list_mhs as $no => $mhs): ?>
            <tr>
              <td style="color:var(--muted)"><?= $no+1 ?></td>
              <td><span class="pill pill-purple"><?= htmlspecialchars($mhs['nim']) ?></span></td>
              <td><strong><?= htmlspecialchars($mhs['nama']) ?></strong></td>
              <td>
                <?php if ($mhs['nilai_angka'] !== null): ?>
                  <!-- Tampilkan nilai yang sudah ada -->
                  <strong style="font-size:16px;"><?= number_format($mhs['nilai_angka'], 1) ?></strong>
                <?php else: ?>
                  <span style="color:var(--muted);font-style:italic">Belum dinilai</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($mhs['grade']): ?>
                  <span class="badge <?= warnaBadge($mhs['grade']) ?>"><?= $mhs['grade'] ?></span>
                <?php else: ?>
                  <span style="color:var(--muted)">—</span>
                <?php endif; ?>
              </td>
              <td>
                <!-- Form input/update nilai per mahasiswa -->
                <form method="POST" style="display:flex;gap:8px;align-items:center;">
                  <input type="hidden" name="aksi"          value="simpan_nilai">
                  <input type="hidden" name="mahasiswa_id"  value="<?= $mhs['id'] ?>">
                  <input type="hidden" name="matakuliah_id" value="<?= $filter_mk ?>">
                  <!-- Input nilai angka 0–100, langkah 0.5 -->
                  <input type="number" name="nilai_angka"
                         min="0" max="100" step="0.5"
                         placeholder="0–100"
                         value="<?= $mhs['nilai_angka'] !== null ? $mhs['nilai_angka'] : '' ?>"
                         style="width:88px;">
                  <button type="submit" class="btn btn-teal btn-sm">💾 Simpan</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <!-- Catatan untuk dosen -->
    <div style="margin-top:14px;padding:12px 16px;background:var(--surface2);border-radius:8px;font-size:12px;color:var(--muted);">
      💡 Grade dihitung otomatis setelah nilai disimpan. Nilai dapat diupdate kapan saja.
    </div>
  <?php endif; ?>
</div>
<?php endif; ?>

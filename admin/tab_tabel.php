<?php
// ============================================================
// FILE: admin/tab_tabel.php
// Fungsi: Tabel data untuk tab mahasiswa, dosen, matakuliah, nilai
// Di-include oleh: admin/index.php (kolom kanan)
// Variabel yang tersedia: $tab, $list_mhs, $list_dosen, $list_mk, $list_nilai
// ============================================================
?>

<?php if ($tab === 'mahasiswa'): ?>
<!-- ==================== TABEL MAHASISWA ==================== -->
<div class="card-header">
  <div class="card-title">📋 Daftar Mahasiswa</div>
  <span class="pill pill-blue"><?= $jml_mhs ?> orang</span>
</div>
<!-- Input pencarian real-time -->
<div class="search-wrap">
  <span class="search-icon">🔍</span>
  <input type="text" id="cariData" placeholder="Cari mahasiswa..." onkeyup="filterData()">
</div>
<div class="table-wrap">
  <table id="tabelData">
    <thead><tr><th>#</th><th>Nama</th><th>NIM</th><th>Username</th><th>Aksi</th></tr></thead>
    <tbody>
      <?php mysqli_data_seek($list_mhs, 0); $no = 1; ?>
      <?php while ($r = mysqli_fetch_assoc($list_mhs)): ?>
        <tr>
          <td style="color:var(--muted)"><?= $no++ ?></td>
          <td><strong><?= htmlspecialchars($r['nama']) ?></strong></td>
          <td><span class="pill pill-blue"><?= $r['nim'] ?></span></td>
          <td style="color:var(--muted)"><?= $r['username'] ?></td>
          <td>
            <!-- Tombol edit -->
            <a href="?tab=mahasiswa&edit_id=<?= $r['id'] ?>&edit_type=mahasiswa" class="btn btn-teal btn-sm">✏️</a>
            <!-- Tombol hapus dengan konfirmasi -->
            <form method="POST" action="aksi.php" style="display:inline"
                  onsubmit="return confirm('Hapus mahasiswa <?= addslashes($r['nama']) ?>? Nilai terkait akan ikut terhapus.')">
              <input type="hidden" name="aksi" value="hapus_mahasiswa">
              <input type="hidden" name="id"   value="<?= $r['id'] ?>">
              <button type="submit" class="btn btn-red btn-sm">🗑️</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php elseif ($tab === 'dosen'): ?>
<!-- ==================== TABEL DOSEN ==================== -->
<div class="card-header">
  <div class="card-title">📋 Daftar Dosen</div>
  <span class="pill pill-yellow"><?= $jml_dosen ?> orang</span>
</div>
<div class="search-wrap">
  <span class="search-icon">🔍</span>
  <!-- Pencarian bisa berdasarkan nama, NIDN, atau nama MK yang diampu -->
  <input type="text" id="cariData" placeholder="Cari nama, NIDN, atau matakuliah..." onkeyup="filterData()">
</div>
<div class="table-wrap">
  <table id="tabelData">
    <thead>
      <tr>
        <th>#</th>
        <th>Nama Dosen</th>
        <th>NIDN</th>
        <th>Username</th>
        <th>Matakuliah Diampu</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php mysqli_data_seek($list_dosen, 0); $no = 1; ?>
      <?php while ($r = mysqli_fetch_assoc($list_dosen)):
        // Ambil semua MK yang diampu dosen ini beserta SKS & semester-nya
        $h_mk_d = mysqli_query($conn,
            "SELECT mk.kode, mk.nama, mk.sks, mk.semester
              FROM dosen_matakuliah dm
              JOIN matakuliah mk ON dm.matakuliah_id = mk.id
              WHERE dm.dosen_id = {$r['id']}
              ORDER BY mk.semester, mk.nama");
        $mk_list = [];
        while ($mk_r = mysqli_fetch_assoc($h_mk_d)) $mk_list[] = $mk_r;
      ?>
        <tr>
          <td style="color:var(--muted)"><?= $no++ ?></td>
          <td><strong><?= htmlspecialchars($r['nama']) ?></strong></td>
          <td><span class="pill pill-yellow"><?= $r['nidn'] ?: '—' ?></span></td>
          <td style="color:var(--muted);font-size:12px;"><?= $r['username'] ?></td>
          <td>
            <?php if (empty($mk_list)): ?>
              <span style="font-size:12px;color:var(--muted);font-style:italic;">Belum ada</span>
            <?php else: ?>
              <div style="display:flex;flex-direction:column;gap:5px;">
              <?php foreach ($mk_list as $mk_d): ?>
                <div style="font-size:12px;">
                  <!-- Nama MK -->
                  <div style="font-weight:600;color:var(--text);margin-bottom:3px;">
                    <?= htmlspecialchars($mk_d['nama']) ?>
                  </div>
                  <!-- Kode, SKS, dan Semester ditampilkan sebagai badge mini -->
                  <div style="display:flex;gap:4px;flex-wrap:wrap;">
                    <span style="background:rgba(99,102,241,.15);color:#a5b4fc;
                                  padding:1px 7px;border-radius:8px;font-size:10px;font-weight:600;">
                      <?= $mk_d['kode'] ?>
                    </span>
                    <span style="background:rgba(20,184,166,.12);color:#5eead4;
                                  padding:1px 7px;border-radius:8px;font-size:10px;">
                      <?= $mk_d['sks'] ?> SKS
                    </span>
                    <span style="background:rgba(245,158,11,.12);color:#fcd34d;
                                  padding:1px 7px;border-radius:8px;font-size:10px;">
                      Sem <?= $mk_d['semester'] ?>
                    </span>
                  </div>
                </div>
              <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </td>
          <td>
            <div style="display:flex;flex-direction:column;gap:5px;">
              <a href="?tab=dosen&edit_id=<?= $r['id'] ?>&edit_type=dosen" class="btn btn-teal btn-sm">✏️ Edit</a>
              <form method="POST" action="aksi.php" style="display:inline"
                    onsubmit="return confirm('Hapus dosen <?= addslashes($r['nama']) ?>? Nilai yang diinput dosen ini juga akan terhapus.')">
                <input type="hidden" name="aksi" value="hapus_dosen">
                <input type="hidden" name="id"   value="<?= $r['id'] ?>">
                <button type="submit" class="btn btn-red btn-sm">🗑️ Hapus</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php elseif ($tab === 'matakuliah'): ?>
<!-- ==================== TABEL MATAKULIAH ==================== -->
<div class="card-header">
  <div class="card-title">📋 Daftar Matakuliah</div>
  <span class="pill pill-green"><?= $jml_mk ?> MK</span>
</div>
<div class="search-wrap">
  <span class="search-icon">🔍</span>
  <input type="text" id="cariData" placeholder="Cari matakuliah..." onkeyup="filterData()">
</div>
<div class="table-wrap">
  <table id="tabelData">
    <thead><tr><th>#</th><th>Kode</th><th>Nama</th><th>SKS</th><th>Sem</th><th>Pengampu</th><th>Aksi</th></tr></thead>
    <tbody>
      <?php mysqli_data_seek($list_mk, 0); $no = 1; ?>
      <?php while ($r = mysqli_fetch_assoc($list_mk)): ?>
        <tr>
          <td style="color:var(--muted)"><?= $no++ ?></td>
          <td><span class="pill pill-purple"><?= $r['kode'] ?></span></td>
          <td><strong><?= htmlspecialchars($r['nama']) ?></strong></td>
          <td><?= $r['sks'] ?></td>
          <td><?= $r['semester'] ?></td>
          <td>
            <?php if ($r['nama_dosen']): ?>
              <!-- Tampilkan nama dosen pengampu -->
              <span style="font-size:12px;color:var(--teal);">👨‍🏫 <?= htmlspecialchars($r['nama_dosen']) ?></span>
            <?php else: ?>
              <!-- Peringatan belum ada pengampu -->
              <span style="font-size:11px;color:#f59e0b;">⚠️ Belum ada</span>
            <?php endif; ?>
          </td>
          <td>
            <a href="?tab=matakuliah&edit_id=<?= $r['id'] ?>&edit_type=matakuliah" class="btn btn-teal btn-sm">✏️</a>
            <form method="POST" action="aksi.php" style="display:inline"
                  onsubmit="return confirm('Hapus MK ini? Semua nilai terkait ikut terhapus!')">
              <input type="hidden" name="aksi" value="hapus_mk">
              <input type="hidden" name="id"   value="<?= $r['id'] ?>">
              <button type="submit" class="btn btn-red btn-sm">🗑️</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php elseif ($tab === 'nilai'): ?>
<!-- ==================== TABEL NILAI ==================== -->
<div class="card-header">
  <div class="card-title">📋 Semua Data Nilai</div>
  <span class="pill pill-purple"><?= $jml_nilai ?> entri</span>
</div>
<div class="search-wrap">
  <span class="search-icon">🔍</span>
  <input type="text" id="cariData" placeholder="Cari nama atau matakuliah..." onkeyup="filterData()">
</div>
<div class="table-wrap">
  <table id="tabelData">
    <thead><tr><th>#</th><th>Mahasiswa</th><th>Matakuliah</th><th>Dosen</th><th>Nilai</th><th>Grade</th><th>Aksi</th></tr></thead>
    <tbody>
      <?php mysqli_data_seek($list_nilai, 0); $no = 1; ?>
      <?php while ($r = mysqli_fetch_assoc($list_nilai)): ?>
        <tr>
          <td style="color:var(--muted)"><?= $no++ ?></td>
          <td>
            <strong><?= htmlspecialchars($r['nm_mhs']) ?></strong><br>
            <span style="font-size:11px;color:var(--muted)"><?= $r['nim'] ?></span>
          </td>
          <td>
            <?= htmlspecialchars($r['nm_mk']) ?><br>
            <span style="font-size:11px;color:var(--muted)"><?= $r['kode'] ?></span>
          </td>
          <td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($r['nm_dosen']) ?></td>
          <td><strong style="font-size:15px;"><?= number_format($r['nilai_angka'], 1) ?></strong></td>
          <td><span class="badge <?= warnaBadge($r['grade']) ?>"><?= $r['grade'] ?></span></td>
          <td>
            <a href="?tab=nilai&edit_id=<?= $r['id'] ?>&edit_type=nilai" class="btn btn-teal btn-sm">✏️</a>
            <form method="POST" action="aksi.php" style="display:inline"
                  onsubmit="return confirm('Hapus nilai ini?')">
              <input type="hidden" name="aksi" value="hapus_nilai">
              <input type="hidden" name="id"   value="<?= $r['id'] ?>">
              <button type="submit" class="btn btn-red btn-sm">🗑️</button>
            </form>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

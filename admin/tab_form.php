<?php
// ============================================================
// FILE: admin/tab_form.php
// Fungsi: Form tambah/edit untuk tab mahasiswa, dosen, matakuliah, nilai
// Di-include oleh: admin/index.php (kolom kiri)
// Variabel yang tersedia: $tab, $edit_id, $edit_type, $data_edit
// ============================================================
?>

<?php if ($tab === 'mahasiswa'): ?>
<!-- ==================== FORM MAHASISWA ==================== -->
<div class="card-header">
  <div class="card-title">
    <?= ($edit_id && $edit_type==='mahasiswa') ? '✏️ Edit Mahasiswa' : '➕ Tambah Mahasiswa' ?>
  </div>
  <?php if ($edit_id && $edit_type==='mahasiswa'): ?>
    <a href="?tab=mahasiswa" class="btn btn-ghost btn-sm">✕ Batal</a>
  <?php endif; ?>
</div>
<form method="POST" action="aksi.php">
  <!-- Tentukan aksi: tambah atau edit -->
  <input type="hidden" name="aksi" value="<?= ($edit_id && $edit_type==='mahasiswa') ? 'edit_mahasiswa' : 'tambah_mahasiswa' ?>">
  <?php if ($edit_id && $edit_type==='mahasiswa'): ?>
    <input type="hidden" name="id" value="<?= $edit_id ?>">
  <?php endif; ?>

  <div class="form-group" style="margin-bottom:14px;">
    <label>Nama Lengkap</label>
    <input type="text" name="nama" placeholder="Contoh: Budi Santoso" required
           value="<?= $data_edit ? htmlspecialchars($data_edit['nama']) : '' ?>">
  </div>
  <div class="form-group" style="margin-bottom:14px;">
    <label>NIM</label>
    <input type="text" name="nim" placeholder="Contoh: 11222001" required
           value="<?= $data_edit ? htmlspecialchars($data_edit['nim']) : '' ?>">
  </div>
  <div class="form-group" style="margin-bottom:14px;">
    <label>Username</label>
    <input type="text" name="username" placeholder="Contoh: mhs001" required
           value="<?= $data_edit ? htmlspecialchars($data_edit['username']) : '' ?>">
  </div>
  <div class="form-group" style="margin-bottom:18px;">
    <label>Password <?= $data_edit ? '<span style="color:var(--muted);font-size:10px;">(kosong = tidak berubah)</span>' : '' ?></label>
    <input type="password" name="password"
           placeholder="<?= $data_edit ? 'Isi jika ingin ganti' : 'Min 6 karakter' ?>"
           <?= !$data_edit ? 'required' : '' ?>>
  </div>
  <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
    <?= ($edit_id && $edit_type==='mahasiswa') ? '💾 Simpan Perubahan' : '➕ Tambah Mahasiswa' ?>
  </button>
</form>

<?php elseif ($tab === 'dosen'): ?>
<!-- ==================== FORM DOSEN ==================== -->
<div class="card-header">
  <div class="card-title">
    <?= ($edit_id && $edit_type==='dosen') ? '✏️ Edit Dosen' : '➕ Tambah Dosen' ?>
  </div>
  <?php if ($edit_id && $edit_type==='dosen'): ?>
    <a href="?tab=dosen" class="btn btn-ghost btn-sm">✕ Batal</a>
  <?php endif; ?>
</div>

<form method="POST" action="aksi.php">
  <input type="hidden" name="aksi" value="<?= ($edit_id && $edit_type==='dosen') ? 'edit_dosen' : 'tambah_dosen' ?>">
  <?php if ($edit_id && $edit_type==='dosen'): ?>
    <input type="hidden" name="id" value="<?= $edit_id ?>">
  <?php endif; ?>

  <!-- ── Data Dosen ──────────────────────────────────── -->
  <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;
               letter-spacing:.8px;margin-bottom:10px;padding-bottom:6px;
               border-bottom:1px solid var(--border);">
    👤 Data Dosen
  </div>

  <div class="form-group" style="margin-bottom:12px;">
    <label>Nama Lengkap</label>
    <input type="text" name="nama" placeholder="Contoh: Dr. Ahmad Fauzi" required
           value="<?= $data_edit ? htmlspecialchars($data_edit['nama']) : '' ?>">
  </div>
  <div class="form-group" style="margin-bottom:12px;">
    <label>NIDN</label>
    <input type="text" name="nidn" placeholder="Contoh: 19855001"
           value="<?= $data_edit ? htmlspecialchars($data_edit['nidn']) : '' ?>">
  </div>
  <div class="form-group" style="margin-bottom:12px;">
    <label>Username</label>
    <input type="text" name="username" placeholder="Contoh: dosen001" required
           value="<?= $data_edit ? htmlspecialchars($data_edit['username']) : '' ?>">
  </div>
  <div class="form-group" style="margin-bottom:16px;">
    <label>Password <?= $data_edit ? '<span style="color:var(--muted);font-size:10px;">(kosong = tidak berubah)</span>' : '' ?></label>
    <input type="password" name="password"
           placeholder="<?= $data_edit ? 'Isi jika ingin ganti' : 'Min 6 karakter' ?>"
           <?= !$data_edit ? 'required' : '' ?>>
  </div>

  <!-- ── Matakuliah yang Diampu ─────────────────────── -->
  <div style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;
               letter-spacing:.8px;margin-bottom:10px;padding-bottom:6px;
               border-bottom:1px solid var(--border);">
    📚 Matakuliah Diampu
    <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--muted);font-size:10px;">
      (centang MK yang diajar — bisa lebih dari satu)
    </span>
  </div>

  <?php
  // Ambil ID matakuliah yang sudah diampu dosen ini (untuk mode edit)
  // agar checkbox sudah tercentang sesuai data yang ada
  $mk_diampu_ids = [];
  if ($edit_id && $edit_type === 'dosen') {
      $h_ampu = mysqli_query($conn,
          "SELECT matakuliah_id FROM dosen_matakuliah WHERE dosen_id=$edit_id");
      while ($r = mysqli_fetch_assoc($h_ampu)) {
          $mk_diampu_ids[] = (int)$r['matakuliah_id'];
      }
  }

  // Ambil semua matakuliah, kelompokkan per semester
  $semua_mk_form = mysqli_query($conn,
      "SELECT * FROM matakuliah ORDER BY semester, nama");
  $mk_per_sem = [];
  while ($mk = mysqli_fetch_assoc($semua_mk_form)) {
      $mk_per_sem[$mk['semester']][] = $mk;
  }
  ?>

  <div style="max-height:260px;overflow-y:auto;padding-right:4px;margin-bottom:16px;">
  <?php foreach ($mk_per_sem as $sem => $mks): ?>
    <!-- Label semester sebagai pemisah kelompok -->
    <div style="font-size:10px;font-weight:700;color:var(--accent);letter-spacing:.6px;
                 text-transform:uppercase;margin:10px 0 6px;padding:4px 8px;
                 background:rgba(99,102,241,.08);border-radius:6px;">
      Semester <?= $sem ?>
    </div>

    <?php foreach ($mks as $mk):
      // Cek apakah MK ini sudah punya dosen pengampu LAIN (bukan dosen yang sedang diedit)
      $pengampu_row = mysqli_fetch_assoc(mysqli_query($conn,
          "SELECT dosen_id FROM dosen_matakuliah WHERE matakuliah_id={$mk['id']}"));
      $sudah_diampu_lain = $pengampu_row
                           && $pengampu_row['dosen_id'] != $edit_id
                           && $edit_id > 0;
      $pengampu_lain_nama = '';
      if ($sudah_diampu_lain) {
          $row_nama = mysqli_fetch_assoc(mysqli_query($conn,
              "SELECT nama FROM dosen WHERE id={$pengampu_row['dosen_id']}"));
          $pengampu_lain_nama = $row_nama ? $row_nama['nama'] : '';
      }
      // Checkbox dicentang jika MK ini ada di daftar MK diampu dosen ini
      $checked = in_array((int)$mk['id'], $mk_diampu_ids) ? 'checked' : '';
    ?>
    <label style="display:flex;align-items:flex-start;gap:10px;padding:9px 10px;
                   border-radius:8px;cursor:pointer;margin-bottom:4px;transition:.15s;
                   border:1px solid <?= $checked ? 'rgba(99,102,241,.35)' : 'var(--border)' ?>;
                   background:<?= $checked ? 'rgba(99,102,241,.08)' : 'var(--surface2)' ?>;">
      <input type="checkbox" name="mk_ids[]" value="<?= $mk['id'] ?>" <?= $checked ?>
             onchange="highlightCheckbox(this)"
             <?= $sudah_diampu_lain ? 'data-warning="1"' : '' ?>
             style="margin-top:2px;accent-color:#6366f1;width:14px;height:14px;flex-shrink:0;">
      <div style="flex:1;">
        <div style="font-size:13px;font-weight:600;color:var(--text);"><?= htmlspecialchars($mk['nama']) ?></div>
        <div style="font-size:11px;color:var(--muted);margin-top:2px;display:flex;gap:8px;flex-wrap:wrap;">
          <span style="background:rgba(99,102,241,.15);color:#a5b4fc;padding:1px 7px;border-radius:10px;">
            <?= $mk['kode'] ?>
          </span>
          <!-- SKS ditampilkan langsung -->
          <span><?= $mk['sks'] ?> SKS</span>
          <!-- Semester MK ditampilkan langsung -->
          <span>Sem <?= $mk['semester'] ?></span>
        </div>
        <?php if ($sudah_diampu_lain): ?>
          <!-- Peringatan jika MK ini sudah diampu dosen lain -->
          <div style="font-size:10px;color:#f59e0b;margin-top:3px;">
            ⚠️ Saat ini diampu: <?= htmlspecialchars($pengampu_lain_nama) ?> — akan digantikan jika dicentang
          </div>
        <?php endif; ?>
      </div>
    </label>
    <?php endforeach; ?>
  <?php endforeach; ?>

  <?php if (empty($mk_per_sem)): ?>
    <div style="text-align:center;padding:20px;color:var(--muted);font-size:13px;">
      Belum ada matakuliah. <a href="?tab=matakuliah" style="color:var(--accent);">Tambah dahulu →</a>
    </div>
  <?php endif; ?>
  </div>

  <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:11px;">
    <?= ($edit_id && $edit_type==='dosen') ? '💾 Simpan Perubahan' : '➕ Tambah Dosen' ?>
  </button>
</form>

<script>
// Highlight label checkbox saat dicentang/dilepas
function highlightCheckbox(cb) {
  var label = cb.closest('label');
  if (cb.checked) {
    label.style.borderColor  = 'rgba(99,102,241,.35)';
    label.style.background   = 'rgba(99,102,241,.08)';
    // Tampilkan peringatan konflik dosen jika ada
    if (cb.dataset.warning) {
      if (!confirm('MK ini sudah diampu dosen lain. Tetap assign ke dosen ini?')) {
        cb.checked = false;
        label.style.borderColor = 'var(--border)';
        label.style.background  = 'var(--surface2)';
      }
    }
  } else {
    label.style.borderColor = 'var(--border)';
    label.style.background  = 'var(--surface2)';
  }
}
</script>

<?php elseif ($tab === 'matakuliah'): ?>
<!-- ==================== FORM MATAKULIAH ==================== -->
<div class="card-header">
  <div class="card-title">
    <?= ($edit_id && $edit_type==='matakuliah') ? '✏️ Edit Matakuliah' : '➕ Tambah Matakuliah' ?>
  </div>
  <?php if ($edit_id && $edit_type==='matakuliah'): ?>
    <a href="?tab=matakuliah" class="btn btn-ghost btn-sm">✕ Batal</a>
  <?php endif; ?>
</div>
<form method="POST" action="aksi.php">
  <input type="hidden" name="aksi" value="<?= ($edit_id && $edit_type==='matakuliah') ? 'edit_mk' : 'tambah_mk' ?>">
  <?php if ($edit_id && $edit_type==='matakuliah'): ?>
    <input type="hidden" name="id" value="<?= $edit_id ?>">
  <?php endif; ?>

  <div class="form-group" style="margin-bottom:14px;">
    <label>Kode</label>
    <input type="text" name="kode" placeholder="Contoh: MK006" required
           value="<?= $data_edit ? htmlspecialchars($data_edit['kode']) : '' ?>">
  </div>
  <div class="form-group" style="margin-bottom:14px;">
    <label>Nama Matakuliah</label>
    <input type="text" name="nama" placeholder="Contoh: Pemrograman Mobile" required
           value="<?= $data_edit ? htmlspecialchars($data_edit['nama']) : '' ?>">
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
    <div class="form-group" style="margin-bottom:0;">
      <label>SKS</label>
      <select name="sks" required>
        <?php for ($s = 1; $s <= 6; $s++): ?>
          <option value="<?= $s ?>" <?= ($data_edit && $data_edit['sks']==$s) ? 'selected' : '' ?>><?= $s ?> SKS</option>
        <?php endfor; ?>
      </select>
    </div>
    <div class="form-group" style="margin-bottom:0;">
      <label>Semester</label>
      <select name="semester" required>
        <?php for ($s = 1; $s <= 8; $s++): ?>
          <option value="<?= $s ?>" <?= ($data_edit && $data_edit['semester']==$s) ? 'selected' : '' ?>>Sem <?= $s ?></option>
        <?php endfor; ?>
      </select>
    </div>
  </div>

  <!-- Dropdown pilih dosen pengampu saat tambah/edit MK -->
  <div class="form-group" style="margin-bottom:18px;">
    <label>Dosen Pengampu</label>
    <select name="dosen_id">
      <option value="0">— Belum ditentukan —</option>
      <?php
      // Reset pointer list_dosen_all untuk iterasi ulang
      mysqli_data_seek($list_dosen_all, 0);
      while ($d = mysqli_fetch_assoc($list_dosen_all)):
        // Tandai dosen yang sedang jadi pengampu (saat mode edit)
        $selected = ($data_edit && isset($data_edit['dosen_id_ampu']) && $data_edit['dosen_id_ampu'] == $d['id'])
                    ? 'selected' : '';
      ?>
        <option value="<?= $d['id'] ?>" <?= $selected ?>>
          <?= htmlspecialchars($d['nama']) ?> (NIDN: <?= $d['nidn'] ?>)
        </option>
      <?php endwhile; ?>
    </select>
  </div>

  <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
    <?= ($edit_id && $edit_type==='matakuliah') ? '💾 Simpan Perubahan' : '➕ Tambah Matakuliah' ?>
  </button>
</form>

<?php elseif ($tab === 'nilai'): ?>
<!-- ==================== PANEL NILAI ==================== -->
<div class="card-header">
  <div class="card-title">
    <?= ($edit_id && $edit_type==='nilai') ? '✏️ Edit Nilai' : '📊 Panel Nilai' ?>
  </div>
  <?php if ($edit_id && $edit_type==='nilai'): ?>
    <a href="?tab=nilai" class="btn btn-ghost btn-sm">✕ Batal</a>
  <?php endif; ?>
</div>

<?php if ($edit_id && $edit_type==='nilai' && $data_edit): ?>
  <!-- Form edit nilai yang dipilih -->
  <p style="font-size:13px;color:var(--muted);margin-bottom:16px;">
    Mahasiswa: <strong style="color:var(--text)"><?= htmlspecialchars($data_edit['nm_mhs']) ?></strong><br>
    MK: <strong style="color:var(--text)"><?= htmlspecialchars($data_edit['nm_mk']) ?></strong>
  </p>
  <form method="POST" action="aksi.php">
    <input type="hidden" name="aksi" value="edit_nilai">
    <input type="hidden" name="id"   value="<?= $edit_id ?>">
    <div class="form-group" style="margin-bottom:18px;">
      <label>Nilai Angka (0–100)</label>
      <input type="number" name="nilai_angka" min="0" max="100" step="0.5"
             value="<?= $data_edit['nilai_angka'] ?>" required
             style="font-size:20px;font-weight:700;text-align:center;">
    </div>
    <div style="margin-bottom:14px;padding:12px;background:var(--surface2);border-radius:8px;font-size:12px;color:var(--muted);">
      Grade dihitung otomatis berdasarkan nilai angka yang dimasukkan.
    </div>
    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
      💾 Simpan Nilai
    </button>
  </form>
<?php else: ?>
  <!-- Tampilkan statistik jika tidak ada yang diedit -->
  <div style="text-align:center;padding:20px;">
    <div style="font-size:36px;margin-bottom:12px;">📊</div>
    <p style="font-size:13px;color:var(--muted);">Klik tombol ✏️ pada tabel untuk mengedit nilai</p>
  </div>
  <div class="divider"></div>
  <div style="font-size:12px;color:var(--muted);">
    <strong style="color:var(--text);">Statistik Nilai:</strong><br>
    Total: <strong style="color:var(--text)"><?= $jml_nilai ?></strong><br>
    Grade A: <strong style="color:#10b981"><?= $dist_grade['A'] ?></strong> &nbsp;
    Grade B: <strong style="color:#3b82f6"><?= $dist_grade['B'] ?></strong><br>
    Grade C: <strong style="color:#f59e0b"><?= $dist_grade['C'] ?></strong> &nbsp;
    Grade E: <strong style="color:#ef4444"><?= $dist_grade['E'] ?></strong>
  </div>
<?php endif; ?>
<?php endif; ?>

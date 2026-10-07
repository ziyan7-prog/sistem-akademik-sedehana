<?php
// ============================================================
// FILE: includes/footer.php
// Fungsi: Penutup layout HTML + JavaScript global
// Di-include di akhir setiap halaman
// ============================================================
?>

  </div><!-- /content -->
</div><!-- /main -->

<!-- JavaScript: Fungsi pencarian tabel secara real-time -->
<script>
/**
 * filterData() — menyaring baris tabel berdasarkan input pencarian
 * Dipanggil via onkeyup pada input#cariData
 */
function filterData() {
  var keyword = document.getElementById("cariData").value.toLowerCase();
  // Loop semua baris tabel dan tampilkan/sembunyikan sesuai keyword
  document.querySelectorAll("#tabelData tbody tr").forEach(function(baris) {
    baris.style.display = baris.innerText.toLowerCase().includes(keyword) ? "" : "none";
  });
}
</script>

</body>
</html>

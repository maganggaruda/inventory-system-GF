<?php
include "../koneksi.php";
include "../template/header.php";

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

// Menggunakan Prepared Statement untuk pencarian aman
if (!empty($keyword)) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM mesin WHERE (nama_mesin LIKE ? OR serial_number LIKE ? OR lokasi LIKE ?) ORDER BY id DESC");
    $kw = "%" . $keyword . "%";
    mysqli_stmt_bind_param($stmt, "sss", $kw, $kw, $kw);
    mysqli_stmt_execute($stmt);
    $sql = mysqli_stmt_get_result($stmt);
} else {
    $sql = mysqli_query($conn, "SELECT * FROM mesin ORDER BY id DESC");
}
?>

<div class="container-fluid p-0">

  <!-- PAGE HEADER -->
  <div class="dashboard-header mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div>
        <h2 class="dashboard-title m-0">Data Mesin Induk</h2>
        <p class="dashboard-subtitle m-0">Kelola daftar mesin utama dan fasilitas produksi pabrik</p>
      </div>
      <a href="tambah.php" class="btn btn-primary px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
        <i class="bi bi-plus-lg fs-6"></i>
        <span>Tambah Mesin</span>
      </a>
    </div>
  </div>

  <!-- FILTER & SEARCH CARD -->
  <div class="content-card mb-4">
    <div class="card-body-custom py-3">
      <form method="GET" class="row g-2 align-items-center">
        <div class="col-md-10">
          <div class="input-group">
            <span class="input-group-text bg-light text-muted border-end-0">
              <i class="bi bi-search"></i>
            </span>
            <input type="text" name="keyword" class="form-control border-start-0 ps-0" placeholder="Cari nama mesin, serial number, atau lokasi area..." value="<?= htmlspecialchars($keyword) ?>">
          </div>
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-primary w-100 fw-medium">
            Cari
          </button>
          <?php if (!empty($keyword)): ?>
            <a href="index.php" class="btn btn-outline-secondary" title="Reset Pencarian">
              <i class="bi bi-arrow-counterclockwise"></i>
            </a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <!-- TABLE CARD -->
  <div class="content-card">
    <div class="card-header-custom d-flex align-items-center justify-content-between">
      <h5 class="card-title-custom m-0">
        <i class="bi bi-gear-wide-connected me-2"></i>Daftar Mesin Terdaftar
      </h5>
      <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-7">
        Total: <?= $sql ? mysqli_num_rows($sql) : 0 ?> Mesin
      </span>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th width="50" class="text-center">No</th>
            <th width="80" class="text-center">Foto</th>
            <th width="160">Serial Number</th>
            <th>Nama Mesin</th>
            <th>Lokasi Area</th>
            <th>Keterangan</th>
            <th width="140" class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $no = 1;
          if ($sql && mysqli_num_rows($sql) > 0) :
            while ($d = mysqli_fetch_assoc($sql)) :
              // Penanganan File Gambar
              $gambarPath = (!empty($d['gambar']) && file_exists("../uploads/mesin/" . $d['gambar'])) 
                            ? "../uploads/mesin/" . $d['gambar'] 
                            : "../assets/img/no-image.png";
          ?>
              <tr>
                <td class="text-center fw-medium text-muted"><?= $no++ ?></td>
                <td class="text-center">
                  <?php if (!empty($d['gambar']) && file_exists("../uploads/mesin/" . $d['gambar'])): ?>
                    <img src="../uploads/mesin/<?= htmlspecialchars($d['gambar']) ?>" alt="Foto Mesin" class="rounded border object-fit-cover" width="48" height="48">
                  <?php else: ?>
                    <div class="bg-light border rounded d-inline-flex align-items-center justify-content-center text-secondary" style="width: 48px; height: 48px;">
                      <i class="bi bi-image fs-5"></i>
                    </div>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="badge bg-light text-dark border font-monospace">
                    <i class="bi bi-qr-code me-1 text-primary"></i><?= htmlspecialchars($d['serial_number'] ?? '-') ?>
                  </span>
                </td>
                <td>
                  <strong class="text-dark d-block"><?= htmlspecialchars($d['nama_mesin'] ?? '-') ?></strong>
                </td>
                <td>
                  <span class="text-secondary small">
                    <i class="bi bi-geo-alt text-danger me-1"></i><?= htmlspecialchars($d['lokasi'] ?? '-') ?>
                  </span>
                </td>
                <td>
                  <small class="text-muted"><?= htmlspecialchars($d['keterangan'] ?? '-') ?></small>
                </td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm" role="group">
                    <!-- Tombol Detail (Memicu Modal) -->
                    <button type="button" 
                            class="btn btn-outline-info btn-detail" 
                            title="Lihat Detail"
                            data-bs-toggle="modal" 
                            data-bs-target="#modalDetailMesin"
                            data-id="<?= $d['id'] ?>"
                            data-sn="<?= htmlspecialchars($d['serial_number'] ?? '-') ?>"
                            data-nama="<?= htmlspecialchars($d['nama_mesin'] ?? '-') ?>"
                            data-lokasi="<?= htmlspecialchars($d['lokasi'] ?? '-') ?>"
                            data-keterangan="<?= htmlspecialchars($d['keterangan'] ?? '-') ?>"
                            data-gambar="<?= $gambarPath ?>">
                      <i class="bi bi-eye"></i>
                    </button>

                    <a href="edit.php?id=<?= $d['id'] ?>" class="btn btn-outline-warning" title="Edit Data">
                      <i class="bi bi-pencil-square"></i>
                    </a>
                    <a href="hapus.php?id=<?= $d['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus mesin ini?')" class="btn btn-outline-danger" title="Hapus Data">
                      <i class="bi bi-trash"></i>
                    </a>
                  </div>
                </td>
              </tr>
            <?php
            endwhile;
          else :
            ?>
            <tr>
              <td colspan="7" class="text-center py-5">
                <div class="text-muted">
                  <i class="bi bi-inbox display-6 d-block mb-2 text-secondary"></i>
                  <p class="mb-0 fw-medium">Data mesin tidak ditemukan.</p>
                  <small>Coba gunakan kata kunci pencarian yang lain.</small>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- MODAL DETAIL MESIN -->
<div class="modal fade" id="modalDetailMesin" tabindex="-1" aria-labelledby="modalDetailMesinLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-light">
        <h5 class="modal-title fw-bold text-dark" id="modalDetailMesinLabel">
          <i class="bi bi-info-circle text-primary me-2"></i>Detail Spesifikasi Mesin
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-4">
        <div class="row g-4">
          <!-- Preview Foto Gambar Ukuran Besar -->
          <div class="col-md-5 text-center">
            <div class="p-2 border rounded bg-light d-flex align-items-center justify-content-center" style="min-height: 220px; max-height: 280px; overflow: hidden;">
              <img id="detail-gambar" src="" class="img-fluid rounded object-fit-contain" style="max-height: 260px;" alt="Foto Mesin">
            </div>
          </div>
          
          <!-- Informasi Rincian Data -->
          <div class="col-md-7">
            <h4 class="fw-bold text-primary mb-1" id="detail-nama">-</h4>
            <div class="mb-3">
              <span class="badge bg-secondary-subtle text-dark border font-monospace fs-6" id="detail-sn">
                <i class="bi bi-qr-code me-1 text-primary"></i>-
              </span>
            </div>

            <hr class="my-3 opacity-25">

            <div class="mb-3">
              <label class="text-muted small d-block fw-semibold text-uppercase">Lokasi Penempatan / Area</label>
              <div class="fw-medium text-dark" id="detail-lokasi">
                <i class="bi bi-geo-alt text-danger me-1"></i>-
              </div>
            </div>

            <div class="mb-3">
              <label class="text-muted small d-block fw-semibold text-uppercase">Keterangan / Deskripsi</label>
              <p class="text-secondary small mb-0 bg-light p-2 rounded border" id="detail-keterangan">-</p>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light py-2">
        <button type="button" class="btn btn-secondary btn-sm px-3 fw-semibold" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- SCRIPT UNTUK MEMASUKKAN DATA SECARA DINAMIS KE MODAL -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const modalDetail = document.getElementById('modalDetailMesin');
  modalDetail.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    
    // Ambil atribut data dari tombol
    const sn = button.getAttribute('data-sn');
    const nama = button.getAttribute('data-nama');
    const lokasi = button.getAttribute('data-lokasi');
    const keterangan = button.getAttribute('data-keterangan');
    const gambar = button.getAttribute('data-gambar');

    // Masukkan data ke dalam elemen Modal
    document.getElementById('detail-sn').innerHTML = '<i class="bi bi-qr-code me-1 text-primary"></i>' + sn;
    document.getElementById('detail-nama').textContent = nama;
    document.getElementById('detail-lokasi').innerHTML = '<i class="bi bi-geo-alt text-danger me-1"></i>' + lokasi;
    document.getElementById('detail-keterangan').textContent = keterangan !== '' ? keterangan : 'Tidak ada keterangan tambahan.';
    document.getElementById('detail-gambar').src = gambar;
  });
});
</script>

<?php include "../template/footer.php"; ?>
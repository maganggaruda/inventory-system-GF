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
            <th width="60" class="text-center">No</th>
            <th width="180">Serial Number</th>
            <th>Nama Mesin</th>
            <th>Lokasi Area</th>
            <th>Keterangan</th>
            <th width="120" class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $no = 1;
          if ($sql && mysqli_num_rows($sql) > 0) :
            while ($d = mysqli_fetch_assoc($sql)) :
          ?>
              <tr>
                <td class="text-center fw-medium text-muted"><?= $no++ ?></td>
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
              <td colspan="6" class="text-center py-5">
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

<?php include "../template/footer.php"; ?>
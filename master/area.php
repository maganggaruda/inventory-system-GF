<?php
include "../koneksi.php";
include "../template/header.php";

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

if (!empty($keyword)) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM area_bagian WHERE nama_area LIKE ? ORDER BY id DESC");
    $kw = "%" . $keyword . "%";
    mysqli_stmt_bind_param($stmt, "s", $kw);
    mysqli_stmt_execute($stmt);
    $sql = mysqli_stmt_get_result($stmt);
} else {
    $sql = mysqli_query($conn, "SELECT * FROM area_bagian ORDER BY id DESC");
}
?>

<div class="container-fluid p-0">

  <!-- PAGE HEADER -->
  <div class="dashboard-header mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div>
        <h2 class="dashboard-title m-0">Data Area Bagian</h2>
        <p class="dashboard-subtitle m-0">Kelola daftar area atau lokasi pabrik untuk penempatan mesin</p>
      </div>
      <a href="tambah_area.php" class="btn btn-primary px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
        <i class="bi bi-plus-lg fs-6"></i>
        <span>Tambah Area</span>
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
            <input type="text" name="keyword" class="form-control border-start-0 ps-0" placeholder="Cari nama area bagian..." value="<?= htmlspecialchars($keyword) ?>">
          </div>
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-primary w-100 fw-medium">
            Cari
          </button>
          <?php if (!empty($keyword)): ?>
            <a href="area.php" class="btn btn-outline-secondary" title="Reset Pencarian">
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
        <i class="bi bi-geo-alt me-2"></i>Daftar Area Terdaftar
      </h5>
      <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-7">
        Total: <?= $sql ? mysqli_num_rows($sql) : 0 ?> Area
      </span>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th width="60" class="text-center">No</th>
            <th>Nama Area / Bagian</th>
            <th width="150" class="text-center">Aksi</th>
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
                  <strong class="text-dark"><?= htmlspecialchars($d['nama_area'] ?? '-') ?></strong>
                </td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm" role="group">
                    <a href="edit_area.php?id=<?= $d['id'] ?>" class="btn btn-outline-warning" title="Edit Data">
                      <i class="bi bi-pencil-square"></i>
                    </a>
                    <a href="hapus_area.php?id=<?= $d['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus area ini?')" class="btn btn-outline-danger" title="Hapus Data">
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
              <td colspan="3" class="text-center py-5">
                <div class="text-muted">
                  <i class="bi bi-inbox display-6 d-block mb-2 text-secondary"></i>
                  <p class="mb-0 fw-medium">Data area tidak ditemukan.</p>
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
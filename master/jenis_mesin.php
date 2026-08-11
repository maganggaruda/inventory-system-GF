<?php
include "../koneksi.php";
include "../template/header.php";

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

// Query dengan JOIN ke tabel area_bagian
$query_str = "SELECT jm.*, a.nama_area 
              FROM jenis_mesin jm
              LEFT JOIN area_bagian a ON jm.id_area = a.id";

if (!empty($keyword)) {
    $kw = "%" . $keyword . "%";
    $query_str .= " WHERE (jm.nama_jenis_mesin LIKE ? OR a.nama_area LIKE ?)";
    $stmt = mysqli_prepare($conn, $query_str . " ORDER BY jm.id DESC");
    mysqli_stmt_bind_param($stmt, "ss", $kw, $kw);
    mysqli_stmt_execute($stmt);
    $sql = mysqli_stmt_get_result($stmt);
} else {
    $sql = mysqli_query($conn, $query_str . " ORDER BY jm.id DESC");
}
?>

<div class="container-fluid p-0">

  <!-- PAGE HEADER -->
  <div class="dashboard-header mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div>
        <h2 class="dashboard-title m-0">Data Jenis Mesin</h2>
        <p class="dashboard-subtitle m-0">Kelola daftar kategori atau jenis mesin berdasarkan area pabrik</p>
      </div>
      <a href="tambah_jenis_mesin.php" class="btn btn-primary px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
        <i class="bi bi-plus-lg fs-6"></i>
        <span>Tambah Jenis Mesin</span>
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
            <input type="text" name="keyword" class="form-control border-start-0 ps-0" placeholder="Cari nama jenis mesin atau area bagian..." value="<?= htmlspecialchars($keyword) ?>">
          </div>
        </div>
        <div class="col-md-2 d-flex gap-2">
          <button type="submit" class="btn btn-primary w-100 fw-medium">
            Cari
          </button>
          <?php if (!empty($keyword)): ?>
            <a href="jenis_mesin.php" class="btn btn-outline-secondary" title="Reset Pencarian">
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
        <i class="bi bi-tags me-2"></i>Daftar Jenis Mesin Terdaftar
      </h5>
      <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-7">
        Total: <?= $sql ? mysqli_num_rows($sql) : 0 ?> Jenis Mesin
      </span>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th width="60" class="text-center">No</th>
            <th>Nama Jenis Mesin</th>
            <th>Area / Bagian</th>
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
                  <strong class="text-dark"><?= htmlspecialchars($d['nama_jenis_mesin'] ?? '-') ?></strong>
                </td>
                <td>
                  <span class="text-secondary">
                    <i class="bi bi-geo-alt text-danger me-1"></i><?= htmlspecialchars($d['nama_area'] ?? '-') ?>
                  </span>
                </td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm" role="group">
                    <a href="edit_jenis_mesin.php?id=<?= $d['id'] ?>" class="btn btn-outline-warning" title="Edit Data">
                      <i class="bi bi-pencil-square"></i>
                    </a>
                    <a href="hapus_jenis_mesin.php?id=<?= $d['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus jenis mesin ini?')" class="btn btn-outline-danger" title="Hapus Data">
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
              <td colspan="4" class="text-center py-5">
                <div class="text-muted">
                  <i class="bi bi-inbox display-6 d-block mb-2 text-secondary"></i>
                  <p class="mb-0 fw-medium">Data jenis mesin tidak ditemukan.</p>
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
<?php
include "../koneksi.php";
include "../template/header.php";

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

// Query JOIN: Mengambil serial_number dan nama_mesin dari tabel mesin induk
if (!empty($keyword)) {
    $stmt = mysqli_prepare($conn, "SELECT sub_mesin.*, 
                                          mesin.nama_mesin, 
                                          mesin.serial_number AS sn_mesin_induk 
                                   FROM sub_mesin 
                                   LEFT JOIN mesin ON sub_mesin.id_mesin = mesin.id 
                                   WHERE (sub_mesin.nama_sub_mesin LIKE ? 
                                      OR sub_mesin.serial_number LIKE ? 
                                      OR mesin.nama_mesin LIKE ? 
                                      OR mesin.serial_number LIKE ?) 
                                   ORDER BY sub_mesin.id DESC");
    $kw = "%" . $keyword . "%";
    mysqli_stmt_bind_param($stmt, "ssss", $kw, $kw, $kw, $kw);
    mysqli_stmt_execute($stmt);
    $sql = mysqli_stmt_get_result($stmt);
} else {
    $sql = mysqli_query($conn, "SELECT sub_mesin.*, 
                                       mesin.nama_mesin, 
                                       mesin.serial_number AS sn_mesin_induk 
                                FROM sub_mesin 
                                LEFT JOIN mesin ON sub_mesin.id_mesin = mesin.id 
                                ORDER BY sub_mesin.id DESC");
}
?>

<div class="container-fluid p-0">

  <!-- PAGE HEADER -->
  <div class="dashboard-header mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div>
        <h2 class="dashboard-title m-0">Data Sub Mesin</h2>
        <p class="dashboard-subtitle m-0">Kelola daftar bagian/komponen sub-sistem mesin produksi</p>
      </div>
      <a href="tambah.php" class="btn btn-primary px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
        <i class="bi bi-plus-lg fs-6"></i>
        <span>Tambah Sub Mesin</span>
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
            <input type="text" name="keyword" class="form-control border-start-0 ps-0" placeholder="Cari sub mesin, serial number, nama mesin, atau SN mesin induk..." value="<?= htmlspecialchars($keyword) ?>">
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
        <i class="bi bi-diagram-3 me-2"></i>Daftar Sub Mesin Terdaftar
      </h5>
      <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-7">
        Total: <?= $sql ? mysqli_num_rows($sql) : 0 ?> Sub Mesin
      </span>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th width="50" class="text-center">NO</th>
            <th width="80" class="text-center">FOTO</th>
            <th width="160">SERIAL NUMBER</th>
            <th>NAMA SUB MESIN</th>
            <th>MESIN INDUK</th>
            <th>KETERANGAN</th>
            <th width="110" class="text-center">AKSI</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $no = 1;
          if ($sql && mysqli_num_rows($sql) > 0) :
            while ($d = mysqli_fetch_assoc($sql)) :
              // Cek file foto sub mesin
              $foto_path = "../uploads/sub_mesin/" . ($d['gambar'] ?? '');
              $has_foto  = !empty($d['gambar']) && file_exists($foto_path);
          ?>
              <tr>
                <td class="text-center fw-medium text-muted"><?= $no++ ?></td>

                <!-- KOLOM FOTO -->
                <td class="text-center">
                  <?php if ($has_foto) : ?>
                    <img src="<?= $foto_path ?>" alt="<?= htmlspecialchars($d['nama_sub_mesin']) ?>" 
                         class="rounded border object-fit-cover cursor-pointer" 
                         width="48" height="48"
                         data-bs-toggle="modal" 
                         data-bs-target="#modalFoto<?= $d['id'] ?>"
                         title="Klik untuk memperbesar">
                  <?php else : ?>
                    <div class="bg-light rounded border d-flex align-items-center justify-content-center mx-auto text-muted" style="width: 48px; height: 48px;">
                      <i class="bi bi-image fs-5"></i>
                    </div>
                  <?php endif; ?>
                </td>

                <!-- KOLOM SERIAL NUMBER -->
                <td>
                  <span class="badge bg-light text-dark border font-monospace d-block text-truncate mb-1" style="max-width: 150px;" title="SN Sub Mesin">
                    <?= htmlspecialchars(!empty($d['serial_number']) ? $d['serial_number'] : '-') ?>
                  </span>
                  <?php if (!empty($d['sn_mesin_induk'])) : ?>
                    <small class="text-muted d-block text-truncate" style="font-size: 0.75rem;" title="SN Mesin Induk">
                      Induk: <?= htmlspecialchars($d['sn_mesin_induk']) ?>
                    </small>
                  <?php endif; ?>
                </td>

                <!-- NAMA SUB MESIN -->
                <td>
                  <strong class="text-dark d-block"><?= htmlspecialchars($d['nama_sub_mesin'] ?? '-') ?></strong>
                </td>

                <!-- MESIN INDUK -->
                <td>
                  <?php if (!empty($d['nama_mesin'])) : ?>
                    <span class="fw-semibold text-dark">
                      <i class="bi bi-building me-1 text-primary"></i><?= htmlspecialchars($d['nama_mesin']) ?>
                    </span>
                  <?php else : ?>
                    <span class="badge bg-secondary-subtle text-secondary border">Tidak Terkait</span>
                  <?php endif; ?>
                </td>

                <!-- KETERANGAN -->
                <td>
                  <small class="text-muted"><?= htmlspecialchars($d['keterangan'] ?? '-') ?></small>
                </td>

                <!-- AKSI -->
                <td class="text-center">
                  <div class="btn-group btn-group-sm" role="group">
                    <a href="edit.php?id=<?= $d['id'] ?>" class="btn btn-outline-warning" title="Edit Data">
                      <i class="bi bi-pencil-square"></i>
                    </a>
                    <a href="hapus.php?id=<?= $d['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus sub mesin ini?')" class="btn btn-outline-danger" title="Hapus Data">
                      <i class="bi bi-trash"></i>
                    </a>
                  </div>
                </td>
              </tr>

              <!-- MODAL PREVIEW FOTO DETAIL -->
              <?php if ($has_foto) : ?>
                <div class="modal fade" id="modalFoto<?= $d['id'] ?>" tabindex="-1" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                      <div class="modal-header border-0 pb-0">
                        <h6 class="modal-title fw-bold text-dark"><?= htmlspecialchars($d['nama_sub_mesin']) ?></h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body text-center p-4">
                        <img src="<?= $foto_path ?>" class="img-fluid rounded border shadow-sm mb-3" style="max-height: 400px; object-fit: contain;">
                        <p class="text-muted small mb-0">
                          <i class="bi bi-building me-1"></i> Mesin Induk: <strong><?= htmlspecialchars($d['nama_mesin'] ?? '-') ?></strong>
                        </p>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endif; ?>

            <?php
            endwhile;
          else :
            ?>
            <tr>
              <td colspan="7" class="text-center py-5">
                <div class="text-muted">
                  <i class="bi bi-inbox display-6 d-block mb-2 text-secondary"></i>
                  <p class="mb-0 fw-medium">Data sub mesin tidak ditemukan.</p>
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
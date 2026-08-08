<?php
include "../koneksi.php";

// Tangkap keyword pencarian jika ada
$keyword = isset($_GET['search']) ? mysqli_real_escape_string($conn, trim($_GET['search'])) : '';

// Buat query pencarian dinamis
$query_str = "SELECT sm.*, m.kode_mesin, m.nama_mesin 
              FROM sub_mesin sm 
              LEFT JOIN mesin m ON sm.id_mesin = m.id";

if (!empty($keyword)) {
    $query_str .= " WHERE m.kode_mesin LIKE '%$keyword%' 
                       OR m.nama_mesin LIKE '%$keyword%' 
                       OR sm.nama_sub_mesin LIKE '%$keyword%' 
                       OR sm.keterangan LIKE '%$keyword%'";
}

$query_str .= " ORDER BY sm.id DESC";
$result = mysqli_query($conn, $query_str);

include "../template/header.php";
?>

<div class="container-fluid p-0">

  <!-- CARD 1: Header (Judul & Tombol Tambah) -->
  <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-3">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h2 class="fw-bold text-dark m-0">Data Sub Mesin</h2>
        <p class="text-muted small m-0 mt-1">Daftar unit sub-sistem dari mesin induk</p>
      </div>
      <a href="tambah.php" class="btn btn-primary fw-semibold px-4 py-2 rounded-3 d-flex align-items-center gap-2" style="background-color: #0056a6; border: none;">
        <i class="bi bi-plus-lg fs-5"></i> Tambah Sub Mesin
      </a>
    </div>
  </div>

  <!-- CARD 2: Bar Pencarian (Search Bar Terpisah) -->
  <div class="card border-0 shadow-sm rounded-4 bg-white p-3 mb-3">
    <form method="GET" action="" class="row g-2 align-items-center">
      <div class="col">
        <div class="input-group">
          <span class="input-group-text bg-white border-end-0 text-muted ps-3">
            <i class="bi bi-search"></i>
          </span>
          <input type="text" name="search" class="form-control border-start-0 py-2" placeholder="Cari nama sub mesin, mesin induk, atau keterangan..." value="<?= htmlspecialchars($keyword); ?>">
        </div>
      </div>
      <div class="col-auto d-flex gap-2">
        <button type="submit" class="btn btn-primary fw-semibold px-4 py-2 rounded-3" style="background-color: #0056a6; border: none;">
          Cari
        </button>
        <?php if (!empty($keyword)) : ?>
          <a href="index.php" class="btn btn-outline-secondary px-3 py-2 rounded-3" title="Reset Search">
            <i class="bi bi-x-lg me-1"></i> Reset
          </a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- CARD 3: Tabel Data Sub Mesin -->
  <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
    
    <!-- Top Table Header: List Title & Badge Total -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-diagram-3 text-primary fs-5"></i>
        <h5 class="fw-bold text-dark m-0">List Sub Mesin</h5>
      </div>

      <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-3 fw-semibold">
        Total: <?= mysqli_num_rows($result); ?> Sub Mesin
      </span>
    </div>

    <!-- Table -->
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="table-light">
          <tr class="text-uppercase small text-muted fw-bold">
            <th style="width: 50px;">NO</th>
            <th>KODE MESIN</th>
            <th>MESIN INDUK</th>
            <th>NAMA SUB MESIN</th>
            <th>KETERANGAN</th>
            <th class="text-center" style="width: 100px;">AKSI</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $no = 1;
          if (mysqli_num_rows($result) > 0) :
            while ($row = mysqli_fetch_assoc($result)) : 
          ?>
            <tr>
              <td class="fw-semibold text-muted"><?= $no++; ?></td>
              <td>
                <span class="badge bg-light text-dark border px-2 py-1 font-monospace">
                  <i class="bi bi-qr-code me-1 text-primary"></i><?= htmlspecialchars($row['kode_mesin'] ?: '-'); ?>
                </span>
              </td>
              <td class="fw-bold text-primary" style="color: #0056a6 !important;">
                <?= htmlspecialchars($row['nama_mesin'] ?: '-'); ?>
              </td>
              <td class="fw-bold text-dark">
                <?= htmlspecialchars($row['nama_sub_mesin']); ?>
              </td>
              <td class="text-muted small">
                <?= htmlspecialchars($row['keterangan'] ?: '-'); ?>
              </td>
              <td class="text-center">
                <div class="d-flex justify-content-center gap-1">
                  <a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-outline-warning rounded-2 px-2" title="Edit">
                    <i class="bi bi-pencil-square"></i>
                  </a>
                  <a href="hapus.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-outline-danger rounded-2 px-2" onclick="return confirm('Yakin ingin menghapus sub mesin ini?')" title="Hapus">
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
              <td colspan="6" class="text-center py-4 text-muted">
                <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary"></i>
                Data sub mesin tidak ditemukan<?= !empty($keyword) ? " untuk kata kunci \"<b>" . htmlspecialchars($keyword) . "</b>\"" : "" ?>.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

  </div>

</div>

<?php include "../template/footer.php"; ?>
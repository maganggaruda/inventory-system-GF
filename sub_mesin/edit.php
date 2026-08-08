<?php
include "../koneksi.php";

$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

$data = mysqli_query($conn, "SELECT * FROM sub_mesin WHERE id='$id'");
$d    = mysqli_fetch_assoc($data);

if (!$d) {
    header("Location: index.php");
    exit;
}

$error = "";

if (isset($_POST['update'])) {
    $id_mesin       = mysqli_real_escape_string($conn, $_POST['id_mesin']);
    $nama_sub_mesin = mysqli_real_escape_string($conn, trim($_POST['nama_sub_mesin']));
    $keterangan     = mysqli_real_escape_string($conn, trim($_POST['keterangan']));

    if (empty($id_mesin) || empty($nama_sub_mesin)) {
        $error = "Mesin Induk dan Nama Sub Mesin wajib diisi!";
    } else {
        $query_update = "UPDATE sub_mesin SET 
                            id_mesin='$id_mesin',
                            nama_sub_mesin='$nama_sub_mesin',
                            keterangan='$keterangan'
                         WHERE id='$id'";

        if (mysqli_query($conn, $query_update)) {
            header("Location: index.php");
            exit;
        } else {
            $error = "Gagal memperbarui data: " . mysqli_error($conn);
        }
    }
}

// Ambil daftar Mesin untuk Dropdown Relasi
$q_mesin = mysqli_query($conn, "SELECT * FROM mesin ORDER BY nama_mesin ASC");

include "../template/header.php";
?>

<div class="container-fluid p-0">

  <!-- HEADER (Lebar 100%) -->
  <div class="dashboard-header mb-3 py-3 px-4">
    <div class="d-flex align-items-center gap-3">
      <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px; flex-shrink: 0;">
        <i class="bi bi-arrow-left fs-5"></i>
      </a>
      <div>
        <h3 class="dashboard-title m-0 fs-4 fw-bold">Edit Sub Mesin</h3>
        <p class="dashboard-subtitle m-0 small text-muted">Perbarui rincian data unit sub mesin yang terdaftar</p>
      </div>
    </div>
  </div>

  <!-- FORM CARD (Lebar 100% Disamakan dengan Header) -->
  <div class="content-card mb-3">
    <div class="card-header-custom py-2 px-3">
      <h6 class="card-title-custom m-0 fw-bold">
        <i class="bi bi-pencil-square me-2"></i>Form Edit Sub Mesin
      </h6>
    </div>
    
    <div class="card-body-custom p-3">
      <?php if (!empty($error)) : ?>
        <div class="alert alert-danger border-0 d-flex align-items-center py-2 px-3 mb-3" role="alert">
          <i class="bi bi-exclamation-triangle-fill fs-6 me-2"></i>
          <div class="small"><?= $error; ?></div>
        </div>
      <?php endif; ?>

      <form method="POST">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold text-dark small mb-1">Pilih Mesin Induk <span class="text-danger">*</span></label>
            <select name="id_mesin" class="form-select form-select-sm" required>
              <option value="">-- Pilih Mesin --</option>
              <?php while ($m = mysqli_fetch_assoc($q_mesin)) : ?>
                <option value="<?= $m['id']; ?>" <?= ($m['id'] == $d['id_mesin']) ? 'selected' : ''; ?>>
                  <?= htmlspecialchars($m['nama_mesin']); ?> (<?= htmlspecialchars($m['lokasi'] ?: 'No Location'); ?>)
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold text-dark small mb-1">Nama Sub Mesin <span class="text-danger">*</span></label>
            <input type="text" name="nama_sub_mesin" class="form-control form-control-sm" value="<?= htmlspecialchars($d['nama_sub_mesin']) ?>" required>
          </div>

          <div class="col-12">
            <label class="form-label fw-semibold text-dark small mb-1">Keterangan / Deskripsi</label>
            <textarea name="keterangan" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($d['keterangan']) ?></textarea>
          </div>
        </div>

        <div class="border-top mt-3 pt-3 d-flex align-items-center gap-2">
          <button type="submit" name="update" class="btn btn-warning btn-sm fw-semibold text-dark px-4" style="background-color: var(--warning); border: none;">
            <i class="bi bi-check-lg me-1"></i> Update Data
          </button>
          <a href="index.php" class="btn btn-light border px-4 btn-sm fw-semibold text-secondary">
            Batal
          </a>
        </div>
      </form>

    </div>
  </div>

</div>

<?php include "../template/footer.php"; ?>
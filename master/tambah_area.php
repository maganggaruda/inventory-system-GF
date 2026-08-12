<?php
include "../koneksi.php";

$error = "";

if (isset($_POST['simpan'])) {
    $nama_area = trim($_POST['nama_area']);
    $lokasi = trim($_POST['lokasi']);

    if (empty($nama_area)) {
        $error = "Nama Area / Bagian wajib diisi!";
    }

    if (empty($error)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO area_bagian (nama_area, lokasi) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "ss", $nama_area, $lokasi);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: area.php");
            exit;
        } else {
            $error = "Gagal menyimpan data: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
}

include "../template/header.php";
?>

<div class="container-fluid p-0">

  <!-- HEADER -->
  <div class="dashboard-header mb-3 py-3 px-4">
    <div class="d-flex align-items-center gap-3">
      <a href="area.php" class="btn btn-outline-secondary btn-sm rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px; flex-shrink: 0;">
        <i class="bi bi-arrow-left fs-5"></i>
      </a>
      <div>
        <h3 class="dashboard-title m-0 fs-4 fw-bold">Tambah Area Baru</h3>
        <p class="dashboard-subtitle m-0 small text-muted">Isi formulir berikut untuk mendaftarkan area atau bagian pabrik baru</p>
      </div>
    </div>
  </div>

  <!-- FORM CARD -->
  <div class="content-card mb-3">
    <div class="card-header-custom py-2 px-3">
      <h6 class="card-title-custom m-0 fw-bold">
        <i class="bi bi-plus-circle me-2"></i>Form Tambah Area
      </h6>
    </div>
    
    <div class="card-body-custom p-3">
      <?php if (!empty($error)) : ?>
        <div class="alert alert-danger border-0 d-flex align-items-center py-2 px-3 mb-3" role="alert">
          <i class="bi bi-exclamation-triangle-fill fs-6 me-2"></i>
          <div class="small"><?= htmlspecialchars($error); ?></div>
        </div>
      <?php endif; ?>

      <form method="POST">
        <div class="row g-3">
          <div class="col-12">
            <label class="form-label fw-semibold text-dark small mb-1">Nama Area / Bagian <span class="text-danger">*</span></label>
            <input type="text" name="nama_area" class="form-control form-control-sm" placeholder="Contoh: Gedung A - Area Produksi" value="<?= isset($_POST['nama_area']) ? htmlspecialchars($_POST['nama_area']) : '' ?>" required>
            <div class="form-text mt-1 style-subtext">Nama lokasi atau bagian pabrik tempat mesin ditempatkan.</div>
          </div>

          <div class="col-12">
            <label class="form-label fw-semibold text-dark small mb-1">Lokasi Area</label>
            <input type="text" name="lokasi" class="form-control form-control-sm" placeholder="Contoh: Lantai 2, Zona Barat / Sektor B" value="<?= isset($_POST['lokasi']) ? htmlspecialchars($_POST['lokasi']) : '' ?>">
            <div class="form-text mt-1 style-subtext">Keterangan detail posisi atau gedung spesifik area tersebut.</div>
          </div>
        </div>

        <div class="border-top mt-3 pt-3 d-flex align-items-center gap-2">
          <button type="submit" name="simpan" class="btn btn-primary px-4 btn-sm fw-semibold">
            <i class="bi bi-check-lg me-1"></i> Simpan Data
          </button>
          <a href="area.php" class="btn btn-light border px-4 btn-sm fw-semibold text-secondary">
            Batal
          </a>
        </div>
      </form>

    </div>
  </div>

</div>

<style>
  .style-subtext {
    font-size: 11px;
    color: #64748b;
  }
</style>

<?php include "../template/footer.php"; ?>
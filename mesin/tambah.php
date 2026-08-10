<?php
include "../koneksi.php";

$error = "";

if (isset($_POST['simpan'])) {
    $serial_number = trim($_POST['serial_number']);
    $nama_mesin    = trim($_POST['nama_mesin']);
    $lokasi        = trim($_POST['lokasi']);
    $keterangan    = trim($_POST['keterangan']);

    if (empty($nama_mesin)) {
        $error = "Nama Mesin wajib diisi!";
    } else {
        // Menggunakan Prepared Statement untuk keamanan
        $stmt = mysqli_prepare($conn, "INSERT INTO mesin (serial_number, nama_mesin, lokasi, keterangan) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $serial_number, $nama_mesin, $lokasi, $keterangan);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: index.php");
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

  <!-- HEADER (Lebar 100%) -->
  <div class="dashboard-header mb-3 py-3 px-4">
    <div class="d-flex align-items-center gap-3">
      <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px; flex-shrink: 0;">
        <i class="bi bi-arrow-left fs-5"></i>
      </a>
      <div>
        <h3 class="dashboard-title m-0 fs-4 fw-bold">Tambah Mesin Baru</h3>
        <p class="dashboard-subtitle m-0 small text-muted">Isi formulir berikut untuk mendaftarkan mesin baru ke dalam sistem</p>
      </div>
    </div>
  </div>

  <!-- FORM CARD (Lebar 100% Disamakan dengan Header) -->
  <div class="content-card mb-3">
    <div class="card-header-custom py-2 px-3">
      <h6 class="card-title-custom m-0 fw-bold">
        <i class="bi bi-plus-circle me-2"></i>Form Tambah Mesin
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
          <div class="col-md-6">
            <label class="form-label fw-semibold text-dark small mb-1">Serial Number</label>
            <input type="text" name="serial_number" class="form-control form-control-sm" placeholder="Contoh: SN-2024-001" value="<?= isset($_POST['serial_number']) ? htmlspecialchars($_POST['serial_number']) : '' ?>">
            <div class="form-text mt-1 style-subtext">Nomor seri unik identifikasi mesin.</div>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold text-dark small mb-1">Nama Mesin <span class="text-danger">*</span></label>
            <input type="text" name="nama_mesin" class="form-control form-control-sm" placeholder="Contoh: Mesin Packing Line 1" value="<?= isset($_POST['nama_mesin']) ? htmlspecialchars($_POST['nama_mesin']) : '' ?>" required>
            <div class="form-text mt-1 style-subtext">Nama resmi atau sebutan unit mesin.</div>
          </div>

          <div class="col-12">
            <label class="form-label fw-semibold text-dark small mb-1">Lokasi Area</label>
            <input type="text" name="lokasi" class="form-control form-control-sm" placeholder="Contoh: Gedung A - Area Produksi Line 1" value="<?= isset($_POST['lokasi']) ? htmlspecialchars($_POST['lokasi']) : '' ?>">
          </div>

          <div class="col-12">
            <label class="form-label fw-semibold text-dark small mb-1">Keterangan / Deskripsi</label>
            <textarea name="keterangan" class="form-control form-control-sm" rows="2" placeholder="Tambahkan deskripsi atau catatan khusus tentang kondisi awal mesin..."><?= isset($_POST['keterangan']) ? htmlspecialchars($_POST['keterangan']) : '' ?></textarea>
          </div>
        </div>

        <div class="border-top mt-3 pt-3 d-flex align-items-center gap-2">
          <button type="submit" name="simpan" class="btn btn-primary px-4 btn-sm fw-semibold">
            <i class="bi bi-check-lg me-1"></i> Simpan Data
          </button>
          <a href="index.php" class="btn btn-light border px-4 btn-sm fw-semibold text-secondary">
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
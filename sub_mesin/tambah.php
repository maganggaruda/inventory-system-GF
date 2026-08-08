<?php
include "../koneksi.php";

$error = "";

if (isset($_POST['simpan'])) {
    $id_mesin       = mysqli_real_escape_string($conn, $_POST['id_mesin']);
    $nama_sub_mesin = mysqli_real_escape_string($conn, trim($_POST['nama_sub_mesin']));
    $keterangan     = mysqli_real_escape_string($conn, trim($_POST['keterangan']));

    if (empty($id_mesin) || empty($nama_sub_mesin)) {
        $error = "Mesin Induk dan Nama Sub Mesin wajib diisi!";
    } else {
        $query = "INSERT INTO sub_mesin (id_mesin, nama_sub_mesin, keterangan) VALUES ('$id_mesin', '$nama_sub_mesin', '$keterangan')";
        if (mysqli_query($conn, $query)) {
            header("Location: index.php");
            exit;
        } else {
            $error = "Gagal menyimpan data: " . mysqli_error($conn);
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
        <h3 class="dashboard-title m-0 fs-4 fw-bold">Tambah Sub Mesin</h3>
        <p class="dashboard-subtitle m-0 small text-muted">Isi formulir berikut untuk mendaftarkan sub mesin baru ke dalam sistem</p>
      </div>
    </div>
  </div>

  <!-- FORM CARD (Lebar 100% Disamakan dengan Header) -->
  <div class="content-card mb-3">
    <div class="card-header-custom py-2 px-3">
      <h6 class="card-title-custom m-0 fw-bold">
        <i class="bi bi-plus-circle me-2"></i>Form Sub Mesin Baru
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
                <option value="<?= $m['id']; ?>"><?= htmlspecialchars($m['nama_mesin']); ?> (<?= htmlspecialchars($m['lokasi'] ?: 'No Location'); ?>)</option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold text-dark small mb-1">Nama Sub Mesin <span class="text-danger">*</span></label>
            <input type="text" name="nama_sub_mesin" class="form-control form-control-sm" placeholder="Contoh: Conveyor Feeder / Motor Drive" required>
          </div>

          <div class="col-12">
            <label class="form-label fw-semibold text-dark small mb-1">Keterangan / Deskripsi</label>
            <textarea name="keterangan" class="form-control form-control-sm" rows="2" placeholder="Catatan atau fungsi sub mesin ini..."></textarea>
          </div>
        </div>

        <div class="border-top mt-3 pt-3 d-flex align-items-center gap-2">
          <button type="submit" name="simpan" class="btn btn-primary px-4 btn-sm fw-semibold">
            <i class="bi bi-check-lg me-1"></i> Simpan
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
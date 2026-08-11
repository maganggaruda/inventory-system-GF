<?php
include "../koneksi.php";

$error = "";
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: jenis_mesin.php");
    exit;
}

// Ambil data lama berdasarkan ID
$stmt_get = mysqli_prepare($conn, "SELECT * FROM jenis_mesin WHERE id = ?");
mysqli_stmt_bind_param($stmt_get, "i", $id);
mysqli_stmt_execute($stmt_get);
$result = mysqli_stmt_get_result($stmt_get);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    header("Location: jenis_mesin.php");
    exit;
}

if (isset($_POST['update'])) {
    $id_area          = intval($_POST['id_area']);
    $nama_jenis_mesin = trim($_POST['nama_jenis_mesin']);

    if (empty($id_area)) {
        $error = "Area Bagian wajib dipilih!";
    } elseif (empty($nama_jenis_mesin)) {
        $error = "Nama Jenis Mesin wajib diisi!";
    }

    if (empty($error)) {
        $stmt = mysqli_prepare($conn, "UPDATE jenis_mesin SET id_area = ?, nama_jenis_mesin = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt, "isi", $id_area, $nama_jenis_mesin, $id);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: jenis_mesin.php");
            exit;
        } else {
            $error = "Gagal memperbarui data: " . mysqli_error($conn);
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
      <a href="jenis_mesin.php" class="btn btn-outline-secondary btn-sm rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px; flex-shrink: 0;">
        <i class="bi bi-arrow-left fs-5"></i>
      </a>
      <div>
        <h3 class="dashboard-title m-0 fs-4 fw-bold">Edit Jenis Mesin</h3>
        <p class="dashboard-subtitle m-0 small text-muted">Perbarui informasi kategori atau jenis mesin</p>
      </div>
    </div>
  </div>

  <!-- FORM CARD -->
  <div class="content-card mb-3">
    <div class="card-header-custom py-2 px-3">
      <h6 class="card-title-custom m-0 fw-bold">
        <i class="bi bi-pencil-square me-2"></i>Form Edit Jenis Mesin
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
            <label class="form-label fw-semibold text-dark small mb-1">Area Bagian <span class="text-danger">*</span></label>
            <select name="id_area" id="id_area" class="form-select form-select-sm" required>
              <option value="">-- Pilih Area --</option>
              <?php
              $q_area = mysqli_query($conn, "SELECT * FROM area_bagian ORDER BY nama_area ASC");
              while ($a = mysqli_fetch_assoc($q_area)) {
                  $selected_area = isset($_POST['id_area']) ? $_POST['id_area'] : $data['id_area'];
                  $selected = ($selected_area == $a['id']) ? 'selected' : '';
                  echo "<option value='".$a['id']."' ".$selected.">".$a['nama_area']."</option>";
              }
              ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold text-dark small mb-1">Nama Jenis Mesin <span class="text-danger">*</span></label>
            <input type="text" name="nama_jenis_mesin" class="form-control form-control-sm" placeholder="Contoh: Conveyor, Mixer, Filling Machine" value="<?= isset($_POST['nama_jenis_mesin']) ? htmlspecialchars($_POST['nama_jenis_mesin']) : htmlspecialchars($data['nama_jenis_mesin']) ?>" required>
            <div class="form-text mt-1 style-subtext">Kategori atau kelompok jenis unit mesin.</div>
          </div>
        </div>

        <div class="border-top mt-3 pt-3 d-flex align-items-center gap-2">
          <button type="submit" name="update" class="btn btn-primary px-4 btn-sm fw-semibold">
            <i class="bi bi-check-lg me-1"></i> Perbarui Data
          </button>
          <a href="jenis_mesin.php" class="btn btn-light border px-4 btn-sm fw-semibold text-secondary">
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
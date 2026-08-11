<?php
include "../koneksi.php";

$error = "";
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data sub mesin berdasarkan ID
$stmt_get = mysqli_prepare($conn, "SELECT * FROM sub_mesin WHERE id = ?");
mysqli_stmt_bind_param($stmt_get, "i", $id);
mysqli_stmt_execute($stmt_get);
$result = mysqli_stmt_get_result($stmt_get);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt_get);

if (!$data) {
    echo "<script>alert('Data sub mesin tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

$val_id_mesin       = $data['id_mesin'];
$val_nama_sub_mesin = $data['nama_sub_mesin'];
$val_keterangan     = $data['keterangan'];
$nama_gambar        = $data['gambar'];

if (isset($_POST['update'])) {
    $val_id_mesin       = intval($_POST['id_mesin'] ?? 0);
    $val_nama_sub_mesin = trim($_POST['nama_sub_mesin'] ?? '');
    $val_keterangan     = trim($_POST['keterangan'] ?? '');

    // Logika Ganti Foto Sub Mesin
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath   = $_FILES['gambar']['tmp_name'];
        $fileName      = $_FILES['gambar']['name'];
        $fileSize      = $_FILES['gambar']['size'];

        $fileExtension     = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($fileExtension, $allowedExtensions)) {
            if ($fileSize <= 2 * 1024 * 1024) { // Maksimal 2MB
                $foto_baru = "submesin_" . time() . "_" . uniqid() . "." . $fileExtension;
                $targetDir = "../uploads/sub_mesin/";

                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                if (move_uploaded_file($fileTmpPath, $targetDir . $foto_baru)) {
                    // Hapus foto lama jika ada
                    if (!empty($nama_gambar) && file_exists($targetDir . $nama_gambar)) {
                        @unlink($targetDir . $nama_gambar);
                    }
                    $nama_gambar = $foto_baru;
                } else {
                    $error = "Gagal mengunggah foto baru ke server.";
                }
            } else {
                $error = "Ukuran gambar terlalu besar! Maksimal 2MB.";
            }
        } else {
            $error = "Format gambar tidak valid! Hanya diperbolehkan JPG, JPEG, PNG, dan WEBP.";
        }
    }

    // Validasi Form Wajib
    if (empty($val_id_mesin) || empty($val_nama_sub_mesin)) {
        $error = "Mesin Induk dan Nama Sub Mesin wajib diisi!";
    }

    // Jika Tidak Ada Error, Lakukan Update
    if (empty($error)) {
        $stmt_update = mysqli_prepare($conn, "UPDATE sub_mesin SET id_mesin = ?, nama_sub_mesin = ?, keterangan = ?, gambar = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt_update, "isssi", $val_id_mesin, $val_nama_sub_mesin, $val_keterangan, $nama_gambar, $id);

        if (mysqli_stmt_execute($stmt_update)) {
            mysqli_stmt_close($stmt_update);
            echo "<script>alert('Data sub mesin berhasil diperbarui!'); window.location='index.php';</script>";
            exit;
        } else {
            $error = "Gagal memperbarui data: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt_update);
    }
}

// Ambil daftar Mesin Induk untuk Dropdown Relasi
$q_mesin = mysqli_query($conn, "SELECT id, nama_mesin, lokasi FROM mesin ORDER BY nama_mesin ASC");

include "../template/header.php";
?>

<div class="container-fluid p-0">

  <!-- HEADER -->
  <div class="dashboard-header mb-3 py-3 px-4">
    <div class="d-flex align-items-center gap-3">
      <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px; flex-shrink: 0;">
        <i class="bi bi-arrow-left fs-5"></i>
      </a>
      <div>
        <h3 class="dashboard-title m-0 fs-4 fw-bold">Edit Sub Mesin</h3>
        <p class="dashboard-subtitle m-0 small text-muted">Perbarui informasi data sub mesin</p>
      </div>
    </div>
  </div>

  <!-- FORM CARD -->
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
          <div class="small"><?= htmlspecialchars($error); ?></div>
        </div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data">
        <div class="row g-3">
          <!-- MESIN INDUK -->
          <div class="col-md-6">
            <label class="form-label fw-semibold text-dark small mb-1">Pilih Mesin Induk <span class="text-danger">*</span></label>
            <select name="id_mesin" class="form-select form-select-sm" required>
              <option value="">-- Pilih Mesin --</option>
              <?php while ($m = mysqli_fetch_assoc($q_mesin)) : ?>
                <option value="<?= $m['id']; ?>" <?= ($val_id_mesin == $m['id']) ? 'selected' : ''; ?>>
                  <?= htmlspecialchars($m['nama_mesin']); ?> (<?= htmlspecialchars($m['lokasi'] ?: 'No Location'); ?>)
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <!-- NAMA SUB MESIN -->
          <div class="col-md-6">
            <label class="form-label fw-semibold text-dark small mb-1">Nama Sub Mesin <span class="text-danger">*</span></label>
            <input type="text" name="nama_sub_mesin" class="form-control form-control-sm" value="<?= htmlspecialchars($val_nama_sub_mesin) ?>" required>
          </div>

          <!-- UPLOAD FOTO SUB MESIN -->
          <div class="col-md-6">
            <label class="form-label fw-semibold text-dark small mb-1">Ganti Foto Sub Mesin</label>
            <input type="file" name="gambar" class="form-control form-control-sm" accept="image/png, image/jpeg, image/jpg, image/webp">
            <div class="form-text mt-1 text-muted small" style="font-size: 0.75rem;">Biarkan kosong jika tidak ingin mengubah foto.</div>
            <?php if (!empty($data['gambar']) && file_exists("../uploads/sub_mesin/" . $data['gambar'])) : ?>
              <div class="mt-2">
                <img src="../uploads/sub_mesin/<?= htmlspecialchars($data['gambar']); ?>" alt="Foto Sub Mesin" class="img-thumbnail" style="max-height: 100px;">
              </div>
            <?php endif; ?>
          </div>

          <!-- KETERANGAN / DESKRIPSI -->
          <div class="col-12">
            <label class="form-label fw-semibold text-dark small mb-1">Keterangan / Deskripsi</label>
            <textarea name="keterangan" class="form-control form-control-sm" rows="3"><?= htmlspecialchars($val_keterangan) ?></textarea>
          </div>
        </div>

        <div class="border-top mt-3 pt-3 d-flex align-items-center gap-2">
          <button type="submit" name="update" class="btn btn-primary px-4 btn-sm fw-semibold">
            <i class="bi bi-save me-1"></i> Perbarui
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
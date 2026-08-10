<?php
include "../koneksi.php";

$error = "";

if (isset($_POST['simpan'])) {
    $serial_number = trim($_POST['serial_number']);
    $nama_mesin    = trim($_POST['nama_mesin']);
    $lokasi        = trim($_POST['lokasi']);
    $keterangan    = trim($_POST['keterangan']);
    
    // Logika Handling Upload Gambar
    $nama_gambar = NULL;
    
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath   = $_FILES['gambar']['tmp_name'];
        $fileName      = $_FILES['gambar']['name'];
        $fileSize      = $_FILES['gambar']['size'];
        
        $fileExtension     = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($fileExtension, $allowedExtensions)) {
            if ($fileSize <= 2 * 1024 * 1024) { // Maksimal 2MB
                // Buat nama unik agar file tidak saling tumpang tindih
                $nama_gambar = "mesin_" . time() . "_" . uniqid() . "." . $fileExtension;
                $targetDir   = "../uploads/mesin/";
                
                // Buat folder otomatis jika belum ada
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                
                $targetPath = $targetDir . $nama_gambar;

                if (!move_uploaded_file($fileTmpPath, $targetPath)) {
                    $error = "Gagal mengunggah file gambar ke server.";
                }
            } else {
                $error = "Ukuran gambar terlalu besar! Maksimal 2MB.";
            }
        } else {
            $error = "Format gambar tidak valid! Hanya diperbolehkan JPG, JPEG, PNG, dan WEBP.";
        }
    }

    if (empty($nama_mesin)) {
        $error = "Nama Mesin wajib diisi!";
    }

    // Jika tidak ada error validasi upload/form, simpan ke database
    if (empty($error)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO mesin (serial_number, nama_mesin, lokasi, keterangan, gambar) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssss", $serial_number, $nama_mesin, $lokasi, $keterangan, $nama_gambar);

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

  <!-- HEADER -->
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

  <!-- FORM CARD -->
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

      <!-- Penting: enctype="multipart/form-data" ditambahkan agar file dapat diupload -->
      <form method="POST" enctype="multipart/form-data">
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

          <!-- Field Tambahan Upload Gambar -->
          <div class="col-12">
            <label class="form-label fw-semibold text-dark small mb-1">Foto Mesin</label>
            <input type="file" name="gambar" class="form-control form-control-sm" accept="image/png, image/jpeg, image/jpg, image/webp">
            <div class="form-text mt-1 style-subtext">Format yang didukung: JPG, JPEG, PNG, WEBP (Maksimal 2MB).</div>
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
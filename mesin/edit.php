<?php
include "../koneksi.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data mesin berdasarkan ID menggunakan Prepared Statement
$stmt_get = mysqli_prepare($conn, "SELECT * FROM mesin WHERE id = ?");
mysqli_stmt_bind_param($stmt_get, "i", $id);
mysqli_stmt_execute($stmt_get);
$result = mysqli_stmt_get_result($stmt_get);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt_get);

if (!$data) {
    header("Location: index.php");
    exit;
}

$error = "";

// Set nilai awal dari database
$val_serial_number = $data['serial_number'] ?? '';
$val_nama_mesin    = $data['nama_mesin'] ?? '';
$val_lokasi        = $data['lokasi'] ?? '';
$val_keterangan    = $data['keterangan'] ?? '';

// Proses Update Data
if (isset($_POST['update'])) {
    $val_serial_number = trim($_POST['serial_number'] ?? '');
    $val_nama_mesin    = trim($_POST['nama_mesin'] ?? '');
    $val_lokasi        = trim($_POST['lokasi'] ?? '');
    $val_keterangan    = trim($_POST['keterangan'] ?? '');

    if (empty($val_nama_mesin)) {
        $error = "Nama Mesin wajib diisi!";
    } else {
        // Prepared Statement untuk Update Data
        $stmt_update = mysqli_prepare($conn, "UPDATE mesin SET serial_number = ?, nama_mesin = ?, lokasi = ?, keterangan = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt_update, "ssssi", $val_serial_number, $val_nama_mesin, $val_lokasi, $val_keterangan, $id);

        if (mysqli_stmt_execute($stmt_update)) {
            mysqli_stmt_close($stmt_update);
            header("Location: index.php");
            exit;
        } else {
            $error = "Gagal memperbarui data: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt_update);
    }
}

// Render HTML Header setelah pemrosesan redirect selesai
include "../template/header.php";
?>

<div class="container-fluid p-0">

    <!-- HEADER TITLE DENGAN BOX PUTIH -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3 d-flex align-items-center gap-3">
            <a href="index.php" class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center p-0" style="width: 42px; height: 42px;">
                <i class="bi bi-arrow-left fs-5"></i>
            </a>
            <div>
                <h3 class="fw-bold text-dark mb-0">Edit Data Mesin</h3>
                <p class="text-muted small mb-0">Perbarui rincian data unit mesin yang terdaftar</p>
            </div>
        </div>
    </div>

    <!-- FORM EDIT MESIN -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <h5 class="fw-bold text-primary mb-4">
                <i class="bi bi-pencil-square me-1"></i> Form Edit Mesin
            </h5>

            <?php if (!empty($error)) : ?>
                <div class="alert alert-danger border-0 d-flex align-items-center py-2 px-3 mb-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-6 me-2"></i>
                    <div class="small"><?= htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Serial Number (SN)</label>
                    <input type="text" name="serial_number" class="form-control rounded-3" value="<?= htmlspecialchars($val_serial_number) ?>" placeholder="Contoh: SN-2024-001">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Mesin <span class="text-danger">*</span></label>
                    <input type="text" name="nama_mesin" class="form-control rounded-3" value="<?= htmlspecialchars($val_nama_mesin) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Lokasi Area</label>
                    <input type="text" name="lokasi" class="form-control rounded-3" value="<?= htmlspecialchars($val_lokasi) ?>" placeholder="Contoh: Gedung A - Area Produksi Line 1">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Keterangan / Deskripsi</label>
                    <textarea name="keterangan" class="form-control rounded-3" rows="4"><?= htmlspecialchars($val_keterangan) ?></textarea>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" name="update" class="btn btn-warning rounded-3 px-4 fw-semibold">
                        <i class="bi bi-check-lg me-1"></i> Update Data
                    </button>
                    <a href="index.php" class="btn btn-light border rounded-3 px-4">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

</div>

<?php include "../template/footer.php"; ?>
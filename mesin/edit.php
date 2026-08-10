<?php
include "../koneksi.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil data mesin berdasarkan ID
$query = mysqli_query($conn, "SELECT * FROM mesin WHERE id = $id");
$data = mysqli_fetch_assoc($query);

if (!$data) {
    header("Location: index.php");
    exit;
}

// Proses Update Data
if (isset($_POST['update'])) {
    $kode_mesin  = mysqli_real_escape_string($conn, $_POST['kode_mesin'] ?? '');
    $nama_mesin  = mysqli_real_escape_string($conn, $_POST['nama_mesin'] ?? '');
    
    // Mengecek apakah menggunakan nama kolom 'lokasi_area' atau 'lokasi'
    $lokasi_area = mysqli_real_escape_string($conn, $_POST['lokasi_area'] ?? $_POST['lokasi'] ?? '');
    $keterangan  = mysqli_real_escape_string($conn, $_POST['keterangan'] ?? '');

    // Cari nama kolom lokasi yang dipakai di database
    $kolom_lokasi = isset($data['lokasi_area']) ? 'lokasi_area' : (isset($data['lokasi']) ? 'lokasi' : 'lokasi_area');

    $update = mysqli_query($conn, "UPDATE mesin SET 
        kode_mesin = '$kode_mesin',
        nama_mesin = '$nama_mesin',
        $kolom_lokasi = '$lokasi_area',
        keterangan = '$keterangan'
        WHERE id = $id");

    if ($update) {
        header("Location: index.php");
        exit;
    }
}

include "../template/header.php";

// Penanganan aman untuk value input (Mencegah Undefined Array Key Warning)
$val_kode_mesin = $data['kode_mesin'] ?? '';
$val_nama_mesin = $data['nama_mesin'] ?? '';
$val_lokasi     = $data['lokasi_area'] ?? $data['lokasi'] ?? '';
$val_keterangan = $data['keterangan'] ?? '';
?>

<div class="container-fluid">

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

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Serial Number(SN)</label>
                    <input type="text" name="kode_mesin" class="form-control rounded-3" value="<?= htmlspecialchars($val_kode_mesin) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Mesin <span class="text-danger">*</span></label>
                    <input type="text" name="nama_mesin" class="form-control rounded-3" value="<?= htmlspecialchars($val_nama_mesin) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Lokasi Area</label>
                    <input type="text" name="lokasi_area" class="form-control rounded-3" value="<?= htmlspecialchars($val_lokasi) ?>">
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
                        <i class="bi bi me-1"></i> Batal
                    </a>
            </form>
        </div>
    </div>

</div>

<?php include "../template/footer.php"; ?>
<?php
include "../koneksi.php";

// 1. Ambil ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 2. Query data maintenance berdasarkan ID
$query = mysqli_query($conn, "SELECT * FROM riwayat_maintenance WHERE id = '$id'");
$data  = mysqli_fetch_assoc($query);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

// 3. Query daftar komponen untuk dropdown
$list_komponen = mysqli_query($conn, "
    SELECT k.id, k.nama_bagian, m.nama_mesin, m.kode_mesin 
    FROM komponen k 
    LEFT JOIN mesin m ON k.id_mesin = m.id 
    ORDER BY k.nama_bagian ASC
");

// 4. Proses Update Data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_komponen  = mysqli_real_escape_string($conn, $_POST['id_komponen']);
    $tanggal      = mysqli_real_escape_string($conn, $_POST['tanggal']);
    $status       = mysqli_real_escape_string($conn, $_POST['status']);
    $teknisi      = mysqli_real_escape_string($conn, $_POST['teknisi']);
    $tindakan     = mysqli_real_escape_string($conn, $_POST['tindakan']);

    $update = mysqli_query($conn, "
        UPDATE riwayat_maintenance SET 
            id_komponen = '$id_komponen',
            tanggal     = '$tanggal',
            status      = '$status',
            teknisi     = '$teknisi',
            tindakan    = '$tindakan'
        WHERE id = '$id'
    ");

    if ($update) {
        echo "<script>alert('Data berhasil diperbarui!'); window.location='index.php';</script>";
    } else {
        $error = "Gagal memperbarui data: " . mysqli_error($conn);
    }
}

include "../template/header.php";
?>

<div class="container-fluid mb-4 px-3 py-2">

    <!-- HEADER BAR -->
<div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
    <div class="d-flex align-items-center gap-3">
        <!-- Tombol Kembali Bulat Lingkaran -->
        <a href="index.php" class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center p-0" style="width: 42px; height: 42px; flex-shrink: 0;" title="Kembali">
            <i class="bi bi-arrow-left fs-5"></i>
        </a>
        <div>
            <h3 class="fw-bold text-dark mb-0 fs-4">Edit Log Maintenance</h3>
            <p class="text-muted small mb-0">Perbarui catatan atau status pemeliharaan mesin</p>
        </div>
    </div>
</div>

    <!-- CARD FORM EDIT -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="fw-bold text-primary mb-0 fs-6 d-flex align-items-center gap-2">
                <i class="bi bi-pencil-square"></i> Form Edit Maintenance
            </h5>
        </div>
        <div class="card-body p-4 pt-2">
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-3" role="alert">
                    <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                
                <!-- Pilih Komponen -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Pilih Komponen <span class="text-danger">*</span></label>
                    <select name="id_komponen" class="form-select rounded-3" required>
                        <option value="">-- Pilih Komponen --</option>
                        <?php if ($list_komponen): ?>
                            <?php while ($k = mysqli_fetch_assoc($list_komponen)): ?>
                                <option value="<?= $k['id'] ?>" <?= ($data['id_komponen'] == $k['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($k['nama_bagian']) ?> — <?= htmlspecialchars($k['nama_mesin']) ?> (<?= htmlspecialchars($k['kode_mesin']) ?>)
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="row g-3 mb-3">
                    <!-- Tanggal Maintenance -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Tanggal Maintenance <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control rounded-3" value="<?= htmlspecialchars($data['tanggal']) ?>" required>
                    </div>

                    <!-- Status Pengerjaan -->
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">Status Pengerjaan <span class="text-danger">*</span></label>
                        <select name="status" class="form-select rounded-3" required>
                            <option value="Selesai" <?= ($data['status'] == 'Selesai') ? 'selected' : '' ?>>Selesai</option>
                            <option value="Proses" <?= ($data['status'] == 'Proses') ? 'selected' : '' ?>>Proses</option>
                            <option value="Pending" <?= ($data['status'] == 'Pending') ? 'selected' : '' ?>>Pending</option>
                        </select>
                    </div>
                </div>

                <!-- Teknisi / Penanggung Jawab -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">Teknisi / Penanggung Jawab</label>
                    <input type="text" name="teknisi" class="form-control rounded-3" placeholder="Masukkan nama teknisi" value="<?= htmlspecialchars($data['teknisi'] ?? '') ?>">
                </div>

                <!-- Tindakan / Detail Perbaikan -->
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">Tindakan / Detail Perbaikan <span class="text-danger">*</span></label>
                    <textarea name="tindakan" class="form-control rounded-3" rows="4" placeholder="Tuliskan tindakan perbaikan..." required><?= htmlspecialchars($data['tindakan'] ?? '') ?></textarea>
                </div>

                <!-- Tombol Aksi -->
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary fw-semibold rounded-3 px-4 shadow-sm d-inline-flex align-items-center gap-2">
                        <i class="bi bi-check-circle"></i> Update Maintenance
                    </button>
                    <a href="index.php" class="btn btn-light border fw-semibold rounded-3 px-4 text-secondary">
                        Batal
                    </a>
                </div>

            </form>
        </div>
    </div>

</div>

<?php include "../template/footer.php"; ?>
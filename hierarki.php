<?php
include "koneksi.php";
include "template/header.php";

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$kw = "%" . $keyword . "%";

// Query Lokasi (Bisa dicari berdasarkan nama lokasi)
if (!empty($keyword)) {
    $query_lokasi = mysqli_prepare($conn, "SELECT DISTINCT 
            CASE WHEN lokasi IS NULL OR lokasi = '' THEN 'Lain-lain' ELSE lokasi END as lokasi_group 
        FROM area_bagian 
        WHERE lokasi LIKE ? OR nama_area LIKE ?
        ORDER BY lokasi_group ASC");
    mysqli_stmt_bind_param($query_lokasi, "ss", $kw, $kw);
    mysqli_stmt_execute($query_lokasi);
    $query_lokasi = mysqli_stmt_get_result($query_lokasi);
} else {
    $query_lokasi = mysqli_query($conn, "SELECT DISTINCT 
            CASE WHEN lokasi IS NULL OR lokasi = '' THEN 'Lain-lain' ELSE lokasi END as lokasi_group 
        FROM area_bagian 
        ORDER BY lokasi_group ASC");
}
?>

<div class="container-fluid mb-4 px-3 py-2">
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
            <div>
                <h2 class="fw-bold text-dark m-0"><i class="bi bi-diagram-3-fill me-2 text-primary" style="color: #0056a6 !important;"></i> Direktori Mesin & Komponen</h2>
                <p class="text-muted small m-0 mt-1">Pilih Lokasi Folder PT Garudafood</p>
            </div>
        </div>

        <!-- Form Pencarian Lokasi -->
        <form method="GET" action="" class="row g-2">
            <div class="col-md-10">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="keyword" class="form-control border-start-0 ps-0" placeholder="Cari berdasarkan Lokasi atau Area..." value="<?= htmlspecialchars($keyword); ?>">
                </div>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-semibold" style="background-color: #0056a6; border: none;">Cari</button>
                <?php if (!empty($keyword)) : ?>
                    <a href="hierarki.php" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- DAFTAR FOLDER LOKASI -->
    <div class="row g-3">
        <?php if ($query_lokasi && mysqli_num_rows($query_lokasi) > 0) : ?>
            <?php while ($lokasi = mysqli_fetch_assoc($query_lokasi)) : ?>
                <?php $lokasi_name = $lokasi['lokasi_group']; ?>
                <div class="col-md-4">
                    <a href="detail_lokasi.php?lokasi=<?= urlencode($lokasi_name); ?>" class="text-decoration-none">
                        <div class="card border border-primary-subtle shadow-sm rounded-4 bg-white p-4 h-100 hover-shadow transition">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 55px; height: 55px; flex-shrink: 0; background-color: #e6f0fa; color: #0056a6;">
                                    <i class="bi bi-folder-fill fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark m-0">Lokasi: <?= htmlspecialchars($lokasi_name); ?></h5>
                                    <small class="text-muted"><i class="bi bi-arrow-right-circle me-1"></i> Klik untuk buka folder</small>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else : ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-5 text-center">
                    <p class="text-muted m-0">Tidak ditemukan lokasi dengan kata kunci "<strong><?= htmlspecialchars($keyword); ?></strong>".</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include "template/footer.php"; ?>
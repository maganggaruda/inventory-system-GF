<?php
include "koneksi.php";
include "template/header.php";

$lokasi = isset($_GET['lokasi']) ? trim($_GET['lokasi']) : '';
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$kw = "%" . $keyword . "%";

if (empty($lokasi)) {
    echo "<script>window.location='hierarki.php';</script>";
    exit;
}

// Query Area dalam Lokasi ini dengan fitur pencarian mandiri
if ($lokasi == 'Lain-lain') {
    $q_str = "SELECT * FROM area_bagian WHERE (lokasi IS NULL OR lokasi = '') AND (nama_area LIKE ? OR keterangan LIKE ?) ORDER BY nama_area ASC";
    $stmt = mysqli_prepare($conn, $q_str);
    mysqli_stmt_bind_param($stmt, "ss", $kw, $kw);
} else {
    $q_str = "SELECT * FROM area_bagian WHERE lokasi = ? AND (nama_area LIKE ? OR keterangan LIKE ?) ORDER BY nama_area ASC";
    $stmt = mysqli_prepare($conn, $q_str);
    mysqli_stmt_bind_param($stmt, "sss", $lokasi, $kw, $kw);
}
mysqli_stmt_execute($stmt);
$query_area = mysqli_stmt_get_result($stmt);
?>

<div class="container-fluid mb-4 px-3 py-2">
    <!-- Breadcrumb Navigasi -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="hierarki.php" class="text-decoration-none">Direktori Utama</a></li>
            <li class="breadcrumb-item active" aria-current="page">Lokasi: <?= htmlspecialchars($lokasi); ?></li>
        </ol>
    </nav>

    <!-- Header & Search Bar Khusus Area -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="fw-bold text-dark m-0"><i class="bi bi-pin-map-fill me-2 text-primary" style="color: #0056a6 !important;"></i> Lokasi: <?= htmlspecialchars($lokasi); ?></h2>
                <p class="text-muted small m-0 mt-1">Daftar Area Bagian dalam lokasi ini</p>
            </div>
            <a href="hierarki.php" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
        </div>

        <form method="GET" action="" class="row g-2">
            <input type="hidden" name="lokasi" value="<?= htmlspecialchars($lokasi); ?>">
            <div class="col-md-10">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="keyword" class="form-control border-start-0 ps-0" placeholder="Cari nama area di lokasi ini..." value="<?= htmlspecialchars($keyword); ?>">
                </div>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-semibold" style="background-color: #0056a6; border: none;">Cari</button>
                <?php if (!empty($keyword)) : ?>
                    <a href="detail_lokasi.php?lokasi=<?= urlencode($lokasi); ?>" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- DAFTAR FOLDER AREA -->
    <div class="row g-3">
        <?php if ($query_area && mysqli_num_rows($query_area) > 0) : ?>
            <?php while ($area = mysqli_fetch_assoc($query_area)) : ?>
                <div class="col-md-4">
                    <a href="detail_area.php?id_area=<?= $area['id']; ?>" class="text-decoration-none">
                        <div class="card border border-warning-subtle shadow-sm rounded-4 bg-white p-4 h-100 hover-shadow transition">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 55px; height: 55px; flex-shrink: 0; background-color: #fff8e1; color: #d39e00;">
                                    <i class="bi bi-folder2-open fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark m-0">Area: <?= htmlspecialchars($area['nama_area']); ?></h5>
                                    <small class="text-muted"><i class="bi bi-arrow-right-circle me-1"></i> Lihat Mesin & Komponen</small>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else : ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-5 text-center">
                    <p class="text-muted m-0">Tidak ada area yang ditemukan pada lokasi ini.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include "template/footer.php"; ?>
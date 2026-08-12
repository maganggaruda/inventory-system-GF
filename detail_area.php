<?php
include "koneksi.php";
include "template/header.php";

$id_area = isset($_GET['id_area']) ? intval($_GET['id_area']) : 0;
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$kw = "%" . $keyword . "%";

if ($id_area <= 0) {
    echo "<script>window.location='hierarki.php';</script>";
    exit;
}

// Ambil data nama area & lokasi untuk breadcrumb
$q_info = mysqli_query($conn, "SELECT * FROM area_bagian WHERE id = $id_area");
$data_area = mysqli_fetch_assoc($q_info);
if (!$data_area) {
    echo "<script>window.location='hierarki.php';</script>";
    exit;
}

// Query Jenis Mesin dalam Area ini
if (!empty($keyword)) {
    $q_jenis_str = "SELECT DISTINCT jm.* FROM jenis_mesin jm 
        LEFT JOIN mesin m ON m.id_jenis_mesin = jm.id
        LEFT JOIN sub_mesin sm ON sm.id_mesin = m.id
        LEFT JOIN komponen k ON k.id_sub_mesin = sm.id
        WHERE jm.id_area = ? AND (jm.nama_jenis_mesin LIKE ? OR m.nama_mesin LIKE ? OR m.serial_number LIKE ? OR sm.nama_sub_mesin LIKE ? OR k.nama_bagian LIKE ? OR k.serial_number LIKE ?)
        ORDER BY jm.nama_jenis_mesin ASC";
    $stmt_j = mysqli_prepare($conn, $q_jenis_str);
    mysqli_stmt_bind_param($stmt_j, "issssss", $id_area, $kw, $kw, $kw, $kw, $kw, $kw);
    mysqli_stmt_execute($stmt_j);
    $query_jenis = mysqli_stmt_get_result($stmt_j);
} else {
    $query_jenis = mysqli_query($conn, "SELECT * FROM jenis_mesin WHERE id_area = $id_area ORDER BY nama_jenis_mesin ASC");
}
?>

<div class="container-fluid mb-4 px-3 py-2">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="hierarki.php" class="text-decoration-none">Direktori Utama</a></li>
            <li class="breadcrumb-item"><a href="detail_lokasi.php?lokasi=<?= urlencode($data_area['lokasi']); ?>" class="text-decoration-none">Lokasi: <?= htmlspecialchars($data_area['lokasi']); ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Area: <?= htmlspecialchars($data_area['nama_area']); ?></li>
        </ol>
    </nav>

    <!-- Header & Search Bar Khusus Area Ini -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="fw-bold text-dark m-0"><i class="bi bi-folder2-open me-2 text-primary" style="color: #0056a6 !important;"></i> Area: <?= htmlspecialchars($data_area['nama_area']); ?></h2>
                <p class="text-muted small m-0 mt-1">Pilih Jenis Mesin di Area ini</p>
            </div>
            <a href="detail_lokasi.php?lokasi=<?= urlencode($data_area['lokasi']); ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
        </div>

        <form method="GET" action="" class="row g-2">
            <input type="hidden" name="id_area" value="<?= $id_area; ?>">
            <div class="col-md-10">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="keyword" class="form-control border-start-0 ps-0" placeholder="Cari Jenis Mesin, Nama Mesin, Serial Number, Sub Mesin, atau Komponen di area ini..." value="<?= htmlspecialchars($keyword); ?>">
                </div>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-semibold" style="background-color: #0056a6; border: none;">Cari</button>
                <?php if (!empty($keyword)) : ?>
                    <a href="detail_area.php?id_area=<?= $id_area; ?>" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- DAFTAR FOLDER KOTAK-KOTAK (GRID) UNTUK JENIS MESIN -->
    <div class="row g-3">
        <?php if ($query_jenis && mysqli_num_rows($query_jenis) > 0) : ?>
            <?php while ($jenis = mysqli_fetch_assoc($query_jenis)) : ?>
                <div class="col-md-4">
                    <!-- Anda bisa membuat file baru misal detail_jenis.php untuk level selanjutnya, atau arahkan ke halaman detail mesin -->
                    <a href="detail_jenis.php?id_jenis=<?= $jenis['id']; ?>" class="text-decoration-none">
                        <div class="card border border-warning-subtle shadow-sm rounded-4 bg-white p-4 h-100 hover-shadow transition">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 55px; height: 55px; flex-shrink: 0; background-color: #fff8e1; color: #d39e00;">
                                    <i class="bi bi-folder-fill fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark m-0">Jenis: <?= htmlspecialchars($jenis['nama_jenis_mesin']); ?></h5>
                                    <small class="text-muted"><i class="bi bi-arrow-right-circle me-1"></i> Lihat Daftar Mesin</small>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else : ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-5 text-center">
                    <p class="text-muted m-0">Tidak ditemukan jenis mesin yang sesuai dengan kata kunci di area ini.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include "template/footer.php"; ?>
<?php
include "koneksi.php";
include "template/header.php";

$id_jenis = isset($_GET['id_jenis']) ? intval($_GET['id_jenis']) : 0;
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$kw = "%" . $keyword . "%";

if ($id_jenis <= 0) {
    echo "<script>window.location='hierarki.php';</script>";
    exit;
}

// Ambil data Jenis Mesin, Area, dan Lokasi untuk keperluan breadcrumb
$q_info = mysqli_query($conn, "SELECT jm.*, a.nama_area, a.lokasi, a.id as id_area 
    FROM jenis_mesin jm 
    JOIN area_bagian a ON jm.id_area = a.id 
    WHERE jm.id = $id_jenis");
$data_jenis = mysqli_fetch_assoc($q_info);

if (!$data_jenis) {
    echo "<script>window.location='hierarki.php';</script>";
    exit;
}

// Query Mesin berdasarkan Jenis Mesin dengan fitur pencarian mandiri
if (!empty($keyword)) {
    $q_mesin_str = "SELECT DISTINCT m.* FROM mesin m 
        LEFT JOIN sub_mesin sm ON sm.id_mesin = m.id
        LEFT JOIN komponen k ON k.id_sub_mesin = sm.id
        WHERE m.id_jenis_mesin = ? AND (m.nama_mesin LIKE ? OR m.serial_number LIKE ? OR sm.nama_sub_mesin LIKE ? OR k.nama_bagian LIKE ? OR k.serial_number LIKE ?)
        ORDER BY m.nama_mesin ASC";
    $stmt_m = mysqli_prepare($conn, $q_mesin_str);
    mysqli_stmt_bind_param($stmt_m, "isssss", $id_jenis, $kw, $kw, $kw, $kw, $kw);
    mysqli_stmt_execute($stmt_m);
    $query_mesin = mysqli_stmt_get_result($stmt_m);
} else {
    $query_mesin = mysqli_query($conn, "SELECT * FROM mesin WHERE id_jenis_mesin = $id_jenis ORDER BY nama_mesin ASC");
}
?>

<div class="container-fluid mb-4 px-3 py-2">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="hierarki.php" class="text-decoration-none">Direktori Utama</a></li>
            <li class="breadcrumb-item"><a href="detail_lokasi.php?lokasi=<?= urlencode($data_jenis['lokasi']); ?>" class="text-decoration-none">Lokasi: <?= htmlspecialchars($data_jenis['lokasi']); ?></a></li>
            <li class="breadcrumb-item"><a href="detail_area.php?id_area=<?= $data_jenis['id_area']; ?>" class="text-decoration-none">Area: <?= htmlspecialchars($data_jenis['nama_area']); ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Jenis: <?= htmlspecialchars($data_jenis['nama_jenis_mesin']); ?></li>
        </ol>
    </nav>

    <!-- Header & Search Bar Khusus Jenis Mesin Ini -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="fw-bold text-dark m-0"><i class="bi bi-folder2-open me-2 text-primary" style="color: #0056a6 !important;"></i> Jenis Mesin: <?= htmlspecialchars($data_jenis['nama_jenis_mesin']); ?></h2>
                <p class="text-muted small m-0 mt-1">Daftar Mesin dalam jenis ini</p>
            </div>
            <a href="detail_area.php?id_area=<?= $data_jenis['id_area']; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
        </div>

        <form method="GET" action="" class="row g-2">
            <input type="hidden" name="id_jenis" value="<?= $id_jenis; ?>">
            <div class="col-md-10">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="keyword" class="form-control border-start-0 ps-0" placeholder="Cari Nama Mesin, Serial Number, Sub Mesin, atau Komponen..." value="<?= htmlspecialchars($keyword); ?>">
                </div>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-semibold" style="background-color: #0056a6; border: none;">Cari</button>
                <?php if (!empty($keyword)) : ?>
                    <a href="detail_jenis.php?id_jenis=<?= $id_jenis; ?>" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- DAFTAR FOLDER KOTAK-KOTAK (GRID) UNTUK MESIN -->
    <div class="row g-3">
        <?php if ($query_mesin && mysqli_num_rows($query_mesin) > 0) : ?>
            <?php while ($mesin = mysqli_fetch_assoc($query_mesin)) : ?>
                <div class="col-md-4">
                    <a href="detail_mesin.php?id_mesin=<?= $mesin['id']; ?>" class="text-decoration-none">
                        <div class="card border border-info-subtle shadow-sm rounded-4 bg-white p-4 h-100 hover-shadow transition">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 55px; height: 55px; flex-shrink: 0; background-color: #e0f7fa; color: #00acc1;">
                                    <i class="bi bi-cpu fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark m-0"><?= htmlspecialchars($mesin['nama_mesin']); ?></h5>
                                    <?php if (!empty($mesin['serial_number'])) : ?>
                                        <code class="small text-muted">SN: <?= htmlspecialchars($mesin['serial_number']); ?></code><br>
                                    <?php endif; ?>
                                    <small class="text-muted"><i class="bi bi-arrow-right-circle me-1"></i> Lihat Sub Mesin & Komponen</small>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else : ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-5 text-center">
                    <p class="text-muted m-0">Tidak ditemukan mesin yang sesuai dengan kata kunci pada jenis mesin ini.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include "template/footer.php"; ?>
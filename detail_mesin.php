<?php
include "koneksi.php";
include "template/header.php";

$id_mesin = isset($_GET['id_mesin']) ? intval($_GET['id_mesin']) : 0;
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$kw = "%" . $keyword . "%";

if ($id_mesin <= 0) {
    echo "<script>window.location='hierarki.php';</script>";
    exit;
}

// Ambil data Mesin, Jenis Mesin, Area, dan Lokasi
$q_info = mysqli_query($conn, "SELECT m.*, jm.id as id_jenis, jm.nama_jenis_mesin, a.id as id_area, a.nama_area, a.lokasi 
    FROM mesin m 
    JOIN jenis_mesin jm ON m.id_jenis_mesin = jm.id 
    JOIN area_bagian a ON jm.id_area = a.id 
    WHERE m.id = $id_mesin");
$data_mesin = mysqli_fetch_assoc($q_info);

if (!$data_mesin) {
    echo "<script>window.location='hierarki.php';</script>";
    exit;
}

// Query Sub Mesin
if (!empty($keyword)) {
    $q_sub_str = "SELECT DISTINCT sm.* FROM sub_mesin sm 
        LEFT JOIN komponen k ON k.id_sub_mesin = sm.id
        WHERE sm.id_mesin = ? AND (sm.nama_sub_mesin LIKE ? OR k.nama_bagian LIKE ? OR k.serial_number LIKE ? OR k.kategori LIKE ?)
        ORDER BY sm.nama_sub_mesin ASC";
    $stmt_s = mysqli_prepare($conn, $q_sub_str);
    mysqli_stmt_bind_param($stmt_s, "issss", $id_mesin, $kw, $kw, $kw, $kw);
    mysqli_stmt_execute($stmt_s);
    $query_sub = mysqli_stmt_get_result($stmt_s);
} else {
    $query_sub = mysqli_query($conn, "SELECT * FROM sub_mesin WHERE id_mesin = $id_mesin ORDER BY nama_sub_mesin ASC");
}
?>

<div class="container-fluid mb-4 px-3 py-2">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="hierarki.php" class="text-decoration-none">Direktori Utama</a></li>
            <li class="breadcrumb-item"><a href="detail_lokasi.php?lokasi=<?= urlencode($data_mesin['lokasi']); ?>" class="text-decoration-none">Lokasi: <?= htmlspecialchars($data_mesin['lokasi']); ?></a></li>
            <li class="breadcrumb-item"><a href="detail_area.php?id_area=<?= $data_mesin['id_area']; ?>" class="text-decoration-none">Area: <?= htmlspecialchars($data_mesin['nama_area']); ?></a></li>
            <li class="breadcrumb-item"><a href="detail_jenis.php?id_jenis=<?= $data_mesin['id_jenis']; ?>" class="text-decoration-none">Jenis: <?= htmlspecialchars($data_mesin['nama_jenis_mesin']); ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Mesin: <?= htmlspecialchars($data_mesin['nama_mesin']); ?></li>
        </ol>
    </nav>

    <!-- Header & Search Bar -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2 class="fw-bold text-dark m-0"><i class="bi bi-cpu me-2 text-primary" style="color: #0056a6 !important;"></i> Mesin: <?= htmlspecialchars($data_mesin['nama_mesin']); ?></h2>
                <?php if (!empty($data_mesin['serial_number'])) : ?>
                    <code class="text-muted">Serial Number: <?= htmlspecialchars($data_mesin['serial_number']); ?></code>
                <?php endif; ?>
                <p class="text-muted small m-0 mt-1">Pilih Sub Mesin di dalam mesin ini</p>
            </div>
            <a href="detail_jenis.php?id_jenis=<?= $data_mesin['id_jenis']; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
        </div>

        <form method="GET" action="" class="row g-2">
            <input type="hidden" name="id_mesin" value="<?= $id_mesin; ?>">
            <div class="col-md-10">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="keyword" class="form-control border-start-0 ps-0" placeholder="Cari Sub Mesin atau Komponen..." value="<?= htmlspecialchars($keyword); ?>">
                </div>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-semibold" style="background-color: #0056a6; border: none;">Cari</button>
                <?php if (!empty($keyword)) : ?>
                    <a href="detail_mesin.php?id_mesin=<?= $id_mesin; ?>" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- DAFTAR FOLDER KOTAK-KOTAK (GRID) UNTUK SUB MESIN -->
    <div class="row g-3">
        <?php if ($query_sub && mysqli_num_rows($query_sub) > 0) : ?>
            <?php while ($sub = mysqli_fetch_assoc($query_sub)) : ?>
                <div class="col-md-4">
                    <a href="detail_sub_mesin.php?id_sub=<?= $sub['id']; ?>" class="text-decoration-none">
                        <div class="card border border-warning-subtle shadow-sm rounded-4 bg-white p-4 h-100 hover-shadow transition">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 55px; height: 55px; flex-shrink: 0; background-color: #fff8e1; color: #d39e00;">
                                    <i class="bi bi-folder-fill fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark m-0">Sub Mesin: <?= htmlspecialchars($sub['nama_sub_mesin']); ?></h5>
                                    <small class="text-muted"><i class="bi bi-arrow-right-circle me-1"></i> Lihat Komponen</small>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        <?php else : ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-5 text-center">
                    <p class="text-muted m-0">Tidak ditemukan sub mesin atau komponen pada mesin ini.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include "template/footer.php"; ?>
<?php
include "koneksi.php";
include "template/header.php";

$id_sub = isset($_GET['id_sub']) ? intval($_GET['id_sub']) : 0;
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$kw = "%" . $keyword . "%";

if ($id_sub <= 0) {
    echo "<script>window.location='hierarki.php';</script>";
    exit;
}

// Ambil data Sub Mesin, Mesin, Jenis Mesin, Area, dan Lokasi untuk breadcrumb
$q_info = mysqli_query($conn, "SELECT sm.*, m.id as id_mesin, m.nama_mesin, m.serial_number as sn_mesin, jm.id as id_jenis, jm.nama_jenis_mesin, a.id as id_area, a.nama_area, a.lokasi 
    FROM sub_mesin sm 
    JOIN mesin m ON sm.id_mesin = m.id 
    JOIN jenis_mesin jm ON m.id_jenis_mesin = jm.id 
    JOIN area_bagian a ON jm.id_area = a.id 
    WHERE sm.id = $id_sub");
$data_sub = mysqli_fetch_assoc($q_info);

if (!$data_sub) {
    echo "<script>window.location='hierarki.php';</script>";
    exit;
}

// Query Komponen dalam Sub Mesin ini
if (!empty($keyword)) {
    $q_k_str = "SELECT * FROM komponen WHERE id_sub_mesin = ? AND (nama_bagian LIKE ? OR serial_number LIKE ? OR kategori LIKE ? OR brand LIKE ? OR tipe LIKE ?) ORDER BY nama_bagian ASC";
    $stmt_k = mysqli_prepare($conn, $q_k_str);
    mysqli_stmt_bind_param($stmt_k, "isssss", $id_sub, $kw, $kw, $kw, $kw, $kw);
    mysqli_stmt_execute($stmt_k);
    $query_komponen = mysqli_stmt_get_result($stmt_k);
} else {
    $query_komponen = mysqli_query($conn, "SELECT * FROM komponen WHERE id_sub_mesin = $id_sub ORDER BY nama_bagian ASC");
}
?>

<div class="container-fluid mb-4 px-3 py-2">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="hierarki.php" class="text-decoration-none">Direktori Utama</a></li>
            <li class="breadcrumb-item"><a href="detail_lokasi.php?lokasi=<?= urlencode($data_sub['lokasi']); ?>" class="text-decoration-none">Lokasi: <?= htmlspecialchars($data_sub['lokasi']); ?></a></li>
            <li class="breadcrumb-item"><a href="detail_area.php?id_area=<?= $data_sub['id_area']; ?>" class="text-decoration-none">Area: <?= htmlspecialchars($data_sub['nama_area']); ?></a></li>
            <li class="breadcrumb-item"><a href="detail_jenis.php?id_jenis=<?= $data_sub['id_jenis']; ?>" class="text-decoration-none">Jenis: <?= htmlspecialchars($data_sub['nama_jenis_mesin']); ?></a></li>
            <li class="breadcrumb-item"><a href="detail_mesin.php?id_mesin=<?= $data_sub['id_mesin']; ?>" class="text-decoration-none">Mesin: <?= htmlspecialchars($data_sub['nama_mesin']); ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Sub Mesin: <?= htmlspecialchars($data_sub['nama_sub_mesin']); ?></li>
        </ol>
    </nav>

    <!-- Header & Search Bar -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <!-- Nama Mesin di atas Sub Mesin -->
                <div class="mb-2">
                    <span class="badge bg-light text-dark border px-2 py-1 fw-normal">
                        <i class="bi bi-cpu me-1 text-primary"></i> Mesin: <strong><?= htmlspecialchars($data_sub['nama_mesin']); ?></strong>
                        <?php if (!empty($data_sub['sn_mesin'])) : ?>
                            <span class="text-muted ms-1">(SN: <?= htmlspecialchars($data_sub['sn_mesin']); ?>)</span>
                        <?php endif; ?>
                    </span>
                </div>
                
                <h2 class="fw-bold text-dark m-0"><i class="bi bi-folder2-open me-2 text-primary" style="color: #0056a6 !important;"></i> Sub Mesin: <?= htmlspecialchars($data_sub['nama_sub_mesin']); ?></h2>
                <p class="text-muted small m-0 mt-1">Daftar Komponen di dalam Sub Mesin ini</p>
            </div>
            <a href="detail_mesin.php?id_mesin=<?= $data_sub['id_mesin']; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Kembali</a>
        </div>

        <form method="GET" action="" class="row g-2">
            <input type="hidden" name="id_sub" value="<?= $id_sub; ?>">
            <div class="col-md-10">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="keyword" class="form-control border-start-0 ps-0" placeholder="Cari Nama Bagian, Serial Number, Kategori, Brand, atau Tipe..." value="<?= htmlspecialchars($keyword); ?>">
                </div>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-semibold" style="background-color: #0056a6; border: none;">Cari</button>
                <?php if (!empty($keyword)) : ?>
                    <a href="detail_sub_mesin.php?id_sub=<?= $id_sub; ?>" class="btn btn-outline-secondary" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- TABEL KOMPONEN -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
        <h5 class="fw-bold text-dark mb-3"><i class="bi bi-gear-fill me-2 text-primary"></i> Daftar Komponen</h5>
        <?php if ($query_komponen && mysqli_num_rows($query_komponen) > 0) : ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Bagian / Komponen</th>
                            <th>Serial Number (SN)</th>
                            <th>Kategori</th>
                            <th>Brand / Tipe</th>
                            <th>Kondisi</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($k = mysqli_fetch_assoc($query_komponen)) : ?>
                            <tr>
                                <td class="fw-semibold"><i class="bi bi-gear me-1 text-muted"></i><?= htmlspecialchars($k['nama_bagian']); ?></td>
                                <td><code><?= htmlspecialchars($k['serial_number'] ?: '-'); ?></code></td>
                                <td><?= htmlspecialchars($k['kategori'] ?: '-'); ?></td>
                                <td><?= htmlspecialchars($k['brand'] ?: '-'); ?> <?= htmlspecialchars($k['tipe'] ? '(' . $k['tipe'] . ')' : ''); ?></td>
                                <td>
                                    <?php if (($k['kondisi'] ?? '') == 'Baik') : ?>
                                        <span class="badge bg-success">Baik</span>
                                    <?php elseif (($k['kondisi'] ?? '') == 'Rusak') : ?>
                                        <span class="badge bg-danger">Rusak</span>
                                    <?php else : ?>
                                        <span class="badge bg-warning text-dark">Perbaikan</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="komponen/detail.php?id=<?= $k['id']; ?>" class="btn btn-sm btn-outline-info px-2 py-1" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else : ?>
            <div class="text-center py-4">
                <p class="text-muted m-0">Belum ada komponen terdaftar pada sub mesin ini.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include "template/footer.php"; ?>
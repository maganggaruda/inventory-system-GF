<?php
include "koneksi.php"; 
$active_page = 'hierarki';
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Deteksi Base URL otomatis secara dinamis
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$base_url = $protocol . "://" . $_SERVER['HTTP_HOST'] . "/inventory_mesin";

// Query mengambil data relasi mesin, sub mesin, dan komponen (ditambahkan k.id AS id_komponen)
$query = mysqli_query($conn, "
    SELECT 
        k.id AS id_komponen,
        COALESCE(m.nama_mesin, 'Tanpa Mesin Induk') AS nama_mesin, 
        m.serial_number AS sn_mesin,
        COALESCE(sm.nama_sub_mesin, 'Tanpa Sub Mesin') AS nama_sub_mesin, 
        k.nama_bagian, 
        k.serial_number, 
        k.kategori,
        k.kondisi,
        k.brand,
        k.tipe,
        k.part_number
    FROM komponen k
    LEFT JOIN mesin m ON k.id_mesin = m.id
    LEFT JOIN sub_mesin sm ON k.id_sub_mesin = sm.id
    ORDER BY m.nama_mesin ASC, sm.nama_sub_mesin ASC, k.nama_bagian ASC
");

$hierarki = [];
while ($row = mysqli_fetch_assoc($query)) {
    $mesin = $row['nama_mesin'];
    $sub_mesin = $row['nama_sub_mesin'];
    $hierarki[$mesin]['sn'] = $row['sn_mesin'];
    $hierarki[$mesin]['sub_mesin'][$sub_mesin][] = $row;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hierarki Mesin - Inventory Maintenance System</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icon -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- CSS Utama -->
    <link rel="stylesheet" href="<?= $base_url; ?>/assets/css/style.css">
</head>
<body>

<div class="app-wrapper">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="<?= $base_url; ?>/assets/img/logo-garudafood.png" alt="Logo">
            <h4>GarudaFood</h4>
            <span>Inventory Maintenance</span>
        </div>
        <nav class="sidebar-menu">
            <a href="<?= $base_url; ?>/dashboard/index.php" class="<?= ($current_dir == 'dashboard') ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
            <a href="<?= $base_url; ?>/hierarki.php" class="nav-link <?= ($active_page == 'hierarki') ? 'active' : ''; ?>">
                <i class="bi bi-diagram-3 me-2"></i> Hierarki Mesin
            </a>
            <a href="<?= $base_url; ?>/mesin/index.php" class="<?= ($current_dir == 'mesin') ? 'active' : '' ?>">
                <i class="bi bi-building"></i> Data Mesin
            </a>
            <a href="<?= $base_url; ?>/sub_mesin/index.php" class="<?= ($current_dir == 'sub_mesin') ? 'active' : '' ?>">
                <i class="bi bi-diagram-3"></i> Sub Mesin
            </a>
            <a href="<?= $base_url; ?>/komponen/index.php" class="<?= ($current_dir == 'komponen') ? 'active' : '' ?>">
                <i class="bi bi-cpu"></i> Data Komponen
            </a>
            <a href="<?= $base_url; ?>/maintenance/index.php" class="<?= ($current_dir == 'maintenance') ? 'active' : '' ?>">
                <i class="bi bi-tools"></i> Maintenance
            </a>
            <a href="<?= $base_url; ?>/laporan/index.php" class="<?= ($current_dir == 'laporan') ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-text"></i> Laporan
            </a>
        </nav>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container-fluid mb-4 px-4 py-3">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h2 class="fw-bold text-dark m-0">Hierarki Aset Pabrik</h2>
                        <p class="text-muted small m-0 mt-1">Pemetaan relasi menyeluruh dari Mesin Induk, Sub Mesin, hingga Komponen/Part</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= $base_url; ?>/mesin/index.php" class="btn btn-outline-primary rounded-pill px-3">
                            <i class="bi bi-cpu me-1"></i> Data Mesin
                        </a>
                        <a href="<?= $base_url; ?>/komponen/index.php" class="btn btn-outline-secondary rounded-pill px-3">
                            <i class="bi bi-tools me-1"></i> Data Komponen
                        </a>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <?php if (empty($hierarki)) : ?>
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
                            <div class="py-4">
                                <i class="bi bi-diagram-3 text-muted fs-1 d-block mb-3"></i>
                                <h5 class="fw-bold text-dark">Belum Ada Data Hierarki</h5>
                                <p class="text-muted small mb-0">Pastikan komponen sudah dihubungkan ke Mesin Induk dan Sub Mesin.</p>
                            </div>
                        </div>
                    </div>
                <?php else : ?>
                    <?php foreach ($hierarki as $nama_mesin => $data_mesin) : ?>
                        <div class="col-12">
                            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3">
                                <div class="card-header text-white py-3 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2" style="background-color: #0056a6 !important;">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-white bg-opacity-25 p-2 rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                            <i class="bi bi-cpu fs-4 text-white"></i>
                                        </div>
                                        <div>
                                            <h4 class="fw-bold m-0 text-white"><?= htmlspecialchars($nama_mesin); ?></h4>
                                            <?php if (!empty($data_mesin['sn'])) : ?>
                                                <small class="text-white-50"><i class="bi bi-upc-scan me-1"></i>SN Mesin: <?= htmlspecialchars($data_mesin['sn']); ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <span class="badge bg-white text-primary fw-semibold px-3 py-2 rounded-pill shadow-sm">
                                        Mesin Induk
                                    </span>
                                </div>
                                
                                <div class="card-body p-4 bg-light">
                                    <div class="row g-3">
                                        <?php foreach ($data_mesin['sub_mesin'] as $nama_sub_mesin => $komponens) : ?>
                                            <div class="col-md-6 col-xl-4">
                                                <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                                                    <div class="card-header bg-white border-bottom py-3 px-3 d-flex justify-content-between align-items-center">
                                                        <div class="d-flex align-items-center">
                                                            <i class="bi bi-gear-wide-connected text-primary fs-5 me-2"></i>
                                                            <span class="fw-bold text-dark small text-uppercase">
                                                                <?= htmlspecialchars($nama_sub_mesin); ?>
                                                            </span>
                                                        </div>
                                                        <span class="badge bg-light text-secondary border rounded-pill"><?= count($komponens); ?> Part</span>
                                                    </div>
                                                    
                                                    <div class="card-body p-3">
                                                        <div class="list-group list-group-flush">
                                                            <?php foreach ($komponens as $k) : ?>
                                                                <div class="list-group-item px-2 py-2 border-bottom d-flex justify-content-between align-items-start bg-transparent">
                                                                    <div class="pe-2">
                                                                        <!-- Nama Komponen dengan Link ke Detail -->
                                                                        <a href="<?= $base_url; ?>/komponen/detail.php?id=<?= $k['id_komponen']; ?>" class="fw-semibold text-dark small d-block mb-1 text-decoration-none">
                                                                            <i class="bi bi-dot text-primary"></i><?= htmlspecialchars($k['nama_bagian']); ?>
                                                                        </a>
                                                                        <div class="text-muted" style="font-size: 11px; line-height: 1.4;">
                                                                            <?php if (!empty($k['serial_number'])) : ?>
                                                                                <span>SN: <?= htmlspecialchars($k['serial_number']); ?></span><br>
                                                                            <?php endif; ?>
                                                                            <?php if (!empty($k['brand']) || !empty($k['tipe'])) : ?>
                                                                                <span class="text-secondary"><?= htmlspecialchars($k['brand'] . ' ' . $k['tipe']); ?></span>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                    <div class="text-end flex-shrink-0 d-flex flex-column align-items-end gap-1">
                                                                        <?php 
                                                                            $badge_bg = 'bg-success';
                                                                            if ($k['kondisi'] == 'Rusak') $badge_bg = 'bg-danger';
                                                                            elseif ($k['kondisi'] == 'Perbaikan') $badge_bg = 'bg-warning text-dark';
                                                                        ?>
                                                                        <span class="badge <?= $badge_bg; ?> font-monospace" style="font-size: 9px;">
                                                                            <?= htmlspecialchars($k['kondisi'] ?: 'Baik'); ?>
                                                                        </span>
                                                                        <?php if (!empty($k['kategori'])) : ?>
                                                                            <div class="text-muted" style="font-size: 10px;"><?= htmlspecialchars($k['kategori']); ?></div>
                                                                        <?php endif; ?>
                                                                        
                                                                        <!-- Tombol Aksi Lihat Detail Komponen -->
                                                                        <a href="<?= $base_url; ?>/komponen/detail.php?id=<?= $k['id_komponen']; ?>" class="btn btn-sm btn-light text-primary border py-0 px-2 mt-1" title="Lihat Detail Komponen" style="font-size: 10px;">
                                                                            <i class="bi bi-eye"></i> Detail
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- Bootstrap JS Bundle CDN -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
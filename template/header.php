<?php
// Tentukan base URL project Anda agar aman diakses dari folder manapun
$base_url = "/inventory_mesin/";
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inventory Maintenance System</title>

    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icon -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS Utama (Gunakan Root-Relative agar tidak broken link) -->
    <link rel="stylesheet" href="/inventory_mesin/assets/css/style.css">
</head>

<body>

<div class="app-wrapper">

    <!-- Sidebar -->
    <aside class="sidebar">

        <div class="sidebar-header">
            <img src="/inventory_mesin/assets/img/logo-garudafood.png" alt="Logo">
            <h4>GarudaFood</h4>
            <span>Inventory Maintenance</span>
        </div>

        <nav class="sidebar-menu">

            <a href="/inventory_mesin/dashboard/index.php" class="<?= (strpos($_SERVER['REQUEST_URI'], '/dashboard/') !== false) ? 'active' : '' ?>">
                <i class="bi bi-speedometer2"></i>
                Dashboard
            </a>

            <a href="/inventory_mesin/hierarki.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'hierarki.php') ? 'active' : ''; ?>">
                <i class="bi bi-diagram-3 me-2"></i> Daftar Mesin
            </a>

            <!-- SECTION MASTER DATA -->
            <div class="px-3 py-2 text-muted text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Master Data</div>

            <a href="/inventory_mesin/master/area.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'area.php') ? 'active' : '' ?>">
                <i class="bi bi-geo-alt"></i>
                Data Area
            </a>

            <a href="/inventory_mesin/master/jenis_mesin.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'jenis_mesin.php') ? 'active' : '' ?>">
                <i class="bi bi-grid"></i>
                Jenis Mesin
            </a>

            <a href="/inventory_mesin/mesin/index.php" class="<?= (strpos($_SERVER['REQUEST_URI'], '/mesin/') !== false) ? 'active' : '' ?>">
                <i class="bi bi-building"></i>
                Data Mesin
            </a>

            <a href="/inventory_mesin/sub_mesin/index.php" class="<?= (strpos($_SERVER['REQUEST_URI'], '/sub_mesin/') !== false) ? 'active' : '' ?>">
                <i class="bi bi-diagram-3"></i>
                Sub Mesin
            </a>

            <a href="/inventory_mesin/komponen/index.php" class="<?= (strpos($_SERVER['REQUEST_URI'], '/komponen/') !== false) ? 'active' : '' ?>">
                <i class="bi bi-cpu"></i>
                Data Komponen
            </a>

            <!-- SECTION TRANSAKSI & LAPORAN -->
            <div class="px-3 py-2 text-muted text-uppercase fw-bold mt-2" style="font-size: 10px; letter-spacing: 0.5px;">Transaksi & Lainnya</div>

            <a href="/inventory_mesin/maintenance/index.php" class="<?= (strpos($_SERVER['REQUEST_URI'], '/maintenance/') !== false) ? 'active' : '' ?>">
                <i class="bi bi-tools"></i>
                Maintenance
            </a>

            <a href="/inventory_mesin/laporan/index.php" class="<?= (strpos($_SERVER['REQUEST_URI'], '/laporan/') !== false) ? 'active' : '' ?>">
                <i class="bi bi-file-earmark-text"></i>
                Laporan
            </a>

        </nav>
    </aside>

    <!-- Main Content Wrapper Start -->
    <main class="main-content">
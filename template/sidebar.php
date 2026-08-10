<?php
$current_page = $_SERVER['REQUEST_URI'];
?>

<div class="sidebar">
  <div class="logo">
    <h3>PT GARUDAFOOD PUTRA PUTRI JAYA Tbk</h3>
    <p>Inventory Maintenance</p>
  </div>

  <div class="sidebar-menu">
    <a href="/inventory_mesin/dashboard/index.php" class="nav-item-link <?= (strpos($current_page, '/dashboard/') !== false) ? 'active' : ''; ?>">
      <i class="bi bi-speedometer2"></i>
      <span>Dashboard</span>
    </a>

    <a href="/inventory_mesin/hierarki.php" class="nav-item-link <?= (basename($current_page) == 'hierarki.php') ? 'active' : ''; ?>">
      <i class="bi bi-diagram-3"></i>
      <span>Hierarki Mesin</span>
    </a>

    <a href="/inventory_mesin/mesin/index.php" class="nav-item-link <?= (strpos($current_page, '/mesin/') !== false) ? 'active' : ''; ?>">
      <i class="bi bi-building"></i>
      <span>Data Mesin</span>
    </a>

    <a href="/inventory_mesin/sub_mesin/index.php" class="nav-item-link <?= (strpos($current_page, '/sub_mesin/') !== false) ? 'active' : ''; ?>">
      <i class="bi bi-diagram-3"></i>
      <span>Sub Mesin</span>
    </a>

    <a href="/inventory_mesin/komponen/index.php" class="nav-item-link <?= (strpos($current_page, '/komponen/') !== false) ? 'active' : ''; ?>">
      <i class="bi bi-cpu"></i>
      <span>Data Komponen</span>
    </a>

    <a href="/inventory_mesin/maintenance/index.php" class="nav-item-link <?= (strpos($current_page, '/maintenance/') !== false) ? 'active' : ''; ?>">
      <i class="bi bi-tools"></i>
      <span>Maintenance</span>
    </a>

    <a href="/inventory_mesin/laporan/index.php" class="nav-item-link <?= (strpos($current_page, '/laporan/') !== false) ? 'active' : ''; ?>">
      <i class="bi bi-file-earmark-text"></i>
      <span>Laporan</span>
    </a>
  </div>
</div>
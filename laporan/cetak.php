<?php
include "../koneksi.php";

$tgl_mulai   = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : date('Y-m-01');
$tgl_selesai = isset($_GET['tgl_selesai']) ? $_GET['tgl_selesai'] : date('Y-m-d');
$jenis       = isset($_GET['jenis']) ? $_GET['jenis'] : 'maintenance';

if ($jenis == 'maintenance') {
    // Query Maintenance (k.part_number diganti k.kode_mesin)
    $sql = mysqli_query($conn, "
        SELECT rm.*, k.nama_bagian, k.kode_mesin, m.nama_mesin 
        FROM riwayat_maintenance rm
        LEFT JOIN komponen k ON rm.id_komponen = k.id
        LEFT JOIN mesin m ON k.id_mesin = m.id
        WHERE rm.tanggal BETWEEN '$tgl_mulai' AND '$tgl_selesai'
        ORDER BY rm.tanggal DESC
    ");
} else {
    // Query Inventaris Komponen
    $sql = mysqli_query($conn, "
        SELECT k.*, m.nama_mesin, sm.nama_sub_mesin
        FROM komponen k
        LEFT JOIN mesin m ON k.id_mesin = m.id
        LEFT JOIN sub_mesin sm ON k.id_sub_mesin = sm.id
        ORDER BY k.id DESC
    ");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Cetak Laporan - Garudafood</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <style>
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #fff; }
    .header-cetak { border-bottom: 3px double #004085; padding-bottom: 12px; margin-bottom: 25px; }
    @media print {
      .no-print { display: none !important; }
      @page { margin: 1cm; }
    }
  </style>
</head>
<body onload="window.print()">

  <div class="container-fluid py-3">
    <!-- Header Kop Laporan dengan Logo Garudafood -->
    <div class="header-cetak d-flex align-items-center justify-content-between">
      <div style="width: 130px;">
        <img src="../assets/img/logo-garudafood.png" alt="Logo Garudafood" style="max-width: 120px; height: auto;">
      </div>
      
      <div class="text-center flex-grow-1">
        <h3 class="fw-bold mb-0 text-uppercase" style="color: #004085;">PT GARUDAFOOD PUTRA PUTRI JAYA Tbk</h3>
        <h5 class="fw-semibold text-secondary mb-1">LAPORAN <?= strtoupper($jenis) ?> INVENTARIS MESIN</h5>
        <?php if ($jenis == 'maintenance') : ?>
          <small class="text-muted">Periode: <?= date('d/m/Y', strtotime($tgl_mulai)) ?> s/d <?= date('d/m/Y', strtotime($tgl_selesai)) ?></small>
        <?php endif; ?>
      </div>

      <div style="width: 130px;"></div>
    </div>

    <!-- Tombol Aksi di Layar -->
    <div class="no-print mb-3 text-end">
      <button onclick="window.print()" class="btn btn-sm btn-primary"><i class="bi bi-printer me-1"></i> Cetak Sekarang</button>
      <button onclick="window.close()" class="btn btn-sm btn-secondary me-1"><i class="bi bi-x-circle me-1"></i> Tutup Halaman</button>
    </div>

    <!-- Tabel Data -->
    <table class="table table-bordered align-middle text-sm">
      <thead class="table-light border-dark">
        <?php if ($jenis == 'maintenance') : ?>
          <tr>
            <th width="30">No</th>
            <th>Tanggal</th>
            <th>Mesin</th>
            <th>Komponen & Kode Mesin</th>
            <th>Tindakan / Detail Perbaikan</th>
            <th>Teknisi</th>
            <th>Status</th>
          </tr>
        <?php else : ?>
          <tr>
            <th width="30">No</th>
            <th>Nama Komponen</th>
            <th>Kode Mesin</th>
            <th>Mesin Induk</th>
            <th>Sub Mesin</th>
            <th>Kondisi</th>
          </tr>
        <?php endif; ?>
      </thead>
      <tbody>
        <?php
        $no = 1;
        if ($sql && mysqli_num_rows($sql) > 0) :
          while ($d = mysqli_fetch_assoc($sql)) :
        ?>
            <?php if ($jenis == 'maintenance') : ?>
              <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td><?= date('d/m/Y', strtotime($d['tanggal'])) ?></td>
                <td><?= htmlspecialchars($d['nama_mesin'] ?: '-') ?></td>
                <td>
                  <strong><?= htmlspecialchars($d['nama_bagian'] ?: '-') ?></strong><br>
                  <small class="text-muted">Kode Mesin: <?= htmlspecialchars($d['kode_mesin'] ?: '-') ?></small>
                </td>
                <td><?= htmlspecialchars($d['tindakan']) ?></td>
                <td><?= htmlspecialchars($d['teknisi'] ?? '-') ?></td>
                <td><?= htmlspecialchars($d['status']) ?></td>
              </tr>
            <?php else : ?>
              <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td><strong><?= htmlspecialchars($d['nama_bagian']) ?></strong></td>
                <td><?= htmlspecialchars($d['kode_mesin'] ?: '-') ?></td>
                <td><?= htmlspecialchars($d['nama_mesin'] ?: '-') ?></td>
                <td><?= htmlspecialchars($d['nama_sub_mesin'] ?: '-') ?></td>
                <td><?= htmlspecialchars($d['kondisi']) ?></td>
              </tr>
            <?php endif; ?>
          <?php endwhile;
        else : ?>
          <tr><td colspan="7" class="text-center py-3">Tidak ada data untuk dicetak.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div class="row mt-5 text-center">
      <div class="col-8"></div>
      <div class="col-4">
        <p class="mb-1">Gresik, <?= date('d F Y') ?></p>
        <p class="mb-5">Supervisor / Manager Maintenance</p>
        <br><br>
        <p class="fw-bold text-decoration-underline mb-0">( .................................... )</p>
      </div>
    </div>
  </div>

</body>
</html>
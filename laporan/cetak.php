<?php
include "../koneksi.php";

$tgl_mulai   = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : date('Y-m-01');
$tgl_selesai = isset($_GET['tgl_selesai']) ? $_GET['tgl_selesai'] : date('Y-m-d');
$jenis       = isset($_GET['jenis']) ? $_GET['jenis'] : 'maintenance';
$id          = isset($_GET['id']) ? intval($_GET['id']) : 0;
$id_mesin     = isset($_GET['id_mesin']) ? $_GET['id_mesin'] : '';
$id_sub_mesin = isset($_GET['id_sub_mesin']) ? $_GET['id_sub_mesin'] : '';
$cari_komp    = isset($_GET['cari_komponen']) ? trim($_GET['cari_komponen']) : '';

// Query Data Berdasarkan Jenis Laporan (Lengkap dengan spesifikasi teknis & foto)
if ($jenis == 'single_maintenance') {
    $stmt = mysqli_prepare($conn, "
        SELECT rm.*, k.*, sm.nama_sub_mesin, m.serial_number as sn_mesin, m.nama_mesin 
        FROM riwayat_maintenance rm
        LEFT JOIN komponen k ON rm.id_komponen = k.id
        LEFT JOIN sub_mesin sm ON k.id_sub_mesin = sm.id
        LEFT JOIN mesin m ON k.id_mesin = m.id
        WHERE rm.id = ?
    ");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $sql = mysqli_stmt_get_result($stmt);
} elseif ($jenis == 'single_komponen') {
    $stmt = mysqli_prepare($conn, "
        SELECT k.*, m.nama_mesin, m.serial_number as sn_mesin, sm.nama_sub_mesin
        FROM komponen k
        LEFT JOIN mesin m ON k.id_mesin = m.id
        LEFT JOIN sub_mesin sm ON k.id_sub_mesin = sm.id
        WHERE k.id = ?
    ");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $sql = mysqli_stmt_get_result($stmt);
} elseif ($jenis == 'maintenance') {
    $query = "
        SELECT rm.*, k.*, sm.nama_sub_mesin, m.serial_number as sn_mesin, m.nama_mesin 
        FROM riwayat_maintenance rm
        LEFT JOIN komponen k ON rm.id_komponen = k.id
        LEFT JOIN sub_mesin sm ON k.id_sub_mesin = sm.id
        LEFT JOIN mesin m ON k.id_mesin = m.id
        WHERE rm.tanggal BETWEEN ? AND ?
    ";
    $params = [$tgl_mulai, $tgl_selesai];
    $types  = "ss";

    if (!empty($id_mesin)) {
        $query .= " AND k.id_mesin = ?";
        $params[] = $id_mesin;
        $types .= "i";
    }
    if (!empty($id_sub_mesin)) {
        $query .= " AND k.id_sub_mesin = ?";
        $params[] = $id_sub_mesin;
        $types .= "i";
    }
    if (!empty($cari_komp)) {
        $query .= " AND (k.nama_bagian LIKE ? OR m.serial_number LIKE ? OR k.brand LIKE ? OR k.tipe LIKE ?)";
        $params[] = "%$cari_komp%";
        $params[] = "%$cari_komp%";
        $params[] = "%$cari_komp%";
        $params[] = "%$cari_komp%";
        $types .= "ssss";
    }
    $query .= " ORDER BY rm.tanggal DESC";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $sql = mysqli_stmt_get_result($stmt);
} else {
    $query = "
        SELECT k.*, m.nama_mesin, m.serial_number as sn_mesin, sm.nama_sub_mesin
        FROM komponen k
        LEFT JOIN mesin m ON k.id_mesin = m.id
        LEFT JOIN sub_mesin sm ON k.id_sub_mesin = sm.id
        WHERE 1=1
    ";
    $params = [];
    $types  = "";

    if (!empty($id_mesin)) {
        $query .= " AND k.id_mesin = ?";
        $params[] = $id_mesin;
        $types .= "i";
    }
    if (!empty($id_sub_mesin)) {
        $query .= " AND k.id_sub_mesin = ?";
        $params[] = $id_sub_mesin;
        $types .= "i";
    }
    if (!empty($cari_komp)) {
        $query .= " AND (k.nama_bagian LIKE ? OR m.serial_number LIKE ? OR k.brand LIKE ? OR k.tipe LIKE ?)";
        $params[] = "%$cari_komp%";
        $params[] = "%$cari_komp%";
        $params[] = "%$cari_komp%";
        $params[] = "%$cari_komp%";
        $types .= "ssss";
    }
    $query .= " ORDER BY k.id DESC";

    $stmt = mysqli_prepare($conn, $query);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $sql = mysqli_stmt_get_result($stmt);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Cetak Laporan Lengkap - Garudafood</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <style>
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #fff; font-size: 10px; }
    .header-cetak { border-bottom: 3px double #004085; padding-bottom: 12px; margin-bottom: 20px; }
    .img-komponen { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
    @media print {
      .no-print { display: none !important; }
      @page { margin: 0.6cm; size: landscape; }
    }
  </style>
</head>
<body onload="window.print()">

  <div class="container-fluid py-2">
    <!-- Header Kop Laporan dengan Logo Garudafood -->
    <div class="header-cetak d-flex align-items-center justify-content-between">
      <div style="width: 130px;">
        <img src="../assets/img/logo-garudafood.png" alt="Logo Garudafood" style="max-width: 110px; height: auto;">
      </div>
      
      <div class="text-center flex-grow-1">
        <h3 class="fw-bold mb-0 text-uppercase fs-5" style="color: #004085;">PT GARUDAFOOD PUTRA PUTRI JAYA Tbk</h3>
        <h6 class="fw-semibold text-secondary mb-1">LAPORAN <?= strtoupper(str_replace('single_', '', $jenis)) ?> INVENTARIS MESIN & SPESIFIKASI TEKNIS LENGKAP</h6>
        <?php if ($jenis == 'maintenance' || $jenis == 'single_maintenance') : ?>
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
    <table class="table table-bordered align-middle">
      <thead class="table-light border-dark text-uppercase text-center align-middle">
        <?php if ($jenis == 'maintenance' || $jenis == 'single_maintenance') : ?>
          <tr>
            <th width="25">No</th>
            <th width="45">Foto</th>
            <th width="70">Tanggal</th>
            <th>Mesin & Sub Mesin</th>
            <th>Komponen</th>
            <th>Spesifikasi Teknis Lengkap</th>
            <th>Tindakan / Perbaikan</th>
            <th width="65">Teknisi</th>
            <th width="55">Status</th>
          </tr>
        <?php else : ?>
          <tr>
            <th width="25">No</th>
            <th width="45">Foto</th>
            <th>Mesin Induk & SN Mesin</th>
            <th>Sub Mesin</th>
            <th>Nama Komponen</th>
            <th>Spesifikasi Teknis Lengkap (Brand, Tipe, Part No, Daya, Voltage, Arus, Frekuensi, IP)</th>
            <th width="55">Kondisi</th>
          </tr>
        <?php endif; ?>
      </thead>
      <tbody>
        <?php
        $no = 1;
        if ($sql && mysqli_num_rows($sql) > 0) :
          while ($d = mysqli_fetch_assoc($sql)) :
            // Cek nama kolom foto di database Anda, sesuaikan misal 'foto', 'gambar', atau 'image'
            $foto = !empty($d['foto']) ? "../assets/img/komponen/" . $d['foto'] : "";
        ?>
            <?php if ($jenis == 'maintenance' || $jenis == 'single_maintenance') : ?>
              <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td class="text-center">
                  <?php if (!empty($d['foto']) && file_exists("../assets/img/komponen/" . $d['foto'])) : ?>
                    <img src="<?= $foto ?>" class="img-komponen" alt="Foto">
                  <?php else : ?>
                    <span class="text-muted" style="font-size: 9px;">No Image</span>
                  <?php endif; ?>
                </td>
                <td><?= !empty($d['tanggal']) ? date('d/m/Y', strtotime($d['tanggal'])) : '-' ?></td>
                <td>
                  <strong><?= htmlspecialchars($d['nama_mesin'] ?? '-') ?></strong><br>
                  <small class="text-muted">Sub: <?= htmlspecialchars($d['nama_sub_mesin'] ?? '-') ?> | SN: <?= htmlspecialchars($d['sn_mesin'] ?? '-') ?></small>
                </td>
                <td>
                  <strong><?= htmlspecialchars($d['nama_bagian'] ?? '-') ?></strong><br>
                  <small class="text-muted">SN Komp: <?= htmlspecialchars($d['sn_komponen'] ?? $d['serial_number'] ?? '-') ?></small>
                </td>
                <td>
                  <div class="row g-0">
                    <div class="col-6">
                      <b>Brand:</b> <?= htmlspecialchars($d['brand'] ?? '-') ?><br>
                      <b>Tipe:</b> <?= htmlspecialchars($d['tipe'] ?? '-') ?><br>
                      <b>Part No:</b> <?= htmlspecialchars($d['part_number'] ?? '-') ?><br>
                      <b>Daya:</b> <?= htmlspecialchars($d['daya'] ?? '-') ?>
                    </div>
                    <div class="col-6">
                      <b>Voltage:</b> <?= htmlspecialchars($d['input_voltage'] ?? '-') ?><br>
                      <b>Arus:</b> <?= htmlspecialchars($d['arus_input'] ?? '-') ?><br>
                      <b>Freq:</b> <?= htmlspecialchars($d['frekuensi_input'] ?? '-') ?><br>
                      <b>IP:</b> <?= htmlspecialchars($d['ip_rating'] ?? '-') ?>
                    </div>
                  </div>
                </td>
                <td><?= htmlspecialchars($d['tindakan'] ?? '-') ?></td>
                <td><?= htmlspecialchars($d['teknisi'] ?? '-') ?></td>
                <td class="text-center"><?= htmlspecialchars($d['status'] ?? '-') ?></td>
              </tr>
            <?php else : ?>
              <tr>
                <td class="text-center"><?= $no++ ?></td>
                <td class="text-center">
                  <?php if (!empty($d['foto']) && file_exists("../assets/img/komponen/" . $d['foto'])) : ?>
                    <img src="<?= $foto ?>" class="img-komponen" alt="Foto">
                  <?php else : ?>
                    <span class="text-muted" style="font-size: 9px;">No Image</span>
                  <?php endif; ?>
                </td>
                <td>
                  <strong><?= htmlspecialchars($d['nama_mesin'] ?? '-') ?></strong><br>
                  <small class="text-muted">SN: <?= htmlspecialchars($d['sn_mesin'] ?? '-') ?></small>
                </td>
                <td><?= htmlspecialchars($d['nama_sub_mesin'] ?? '-') ?></td>
                <td>
                  <strong><?= htmlspecialchars($d['nama_bagian'] ?? '-') ?></strong><br>
                  <small class="text-muted">SN Komp: <?= htmlspecialchars($d['sn_komponen'] ?? '-') ?></small>
                </td>
                <td>
                  <div class="row g-0">
                    <div class="col-6">
                      <b>Brand/Merk:</b> <?= htmlspecialchars($d['brand'] ?? '-') ?><br>
                      <b>Tipe:</b> <?= htmlspecialchars($d['tipe'] ?? '-') ?><br>
                      <b>Part Number:</b> <?= htmlspecialchars($d['part_number'] ?? '-') ?><br>
                      <b>Daya:</b> <?= htmlspecialchars($d['daya'] ?? '-') ?> | <b>IO:</b> <?= htmlspecialchars($d['io_address'] ?? '-') ?>
                    </div>
                    <div class="col-6">
                      <b>Input Voltage:</b> <?= htmlspecialchars($d['input_voltage'] ?? '-') ?><br>
                      <b>Frekuensi:</b> <?= htmlspecialchars($d['frekuensi_input'] ?? '-') ?><br>
                      <b>Arus Input:</b> <?= htmlspecialchars($d['arus_input'] ?? '-') ?><br>
                      <b>Output / Freq Out:</b> <?= htmlspecialchars($d['output'] ?? '-') ?> / <?= htmlspecialchars($d['frekuensi_output'] ?? '-') ?> <br>
                      <b>IP Rating:</b> <?= htmlspecialchars($d['ip_rating'] ?? '-') ?>
                    </div>
                  </div>
                </td>
                <td class="text-center"><?= htmlspecialchars($d['kondisi'] ?? 'Baik') ?></td>
              </tr>
            <?php endif; ?>
          <?php endwhile;
        else : ?>
          <tr><td colspan="7" class="text-center py-3 text-muted">Tidak ada data untuk dicetak.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div class="row mt-4 text-center">
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
<?php 
if (isset($stmt)) {
    mysqli_stmt_close($stmt);
}
?>
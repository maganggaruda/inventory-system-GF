<?php
include "../koneksi.php";

$tgl_mulai   = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : date('Y-m-01');
$tgl_selesai = isset($_GET['tgl_selesai']) ? $_GET['tgl_selesai'] : date('Y-m-d');
$jenis       = isset($_GET['jenis']) ? $_GET['jenis'] : 'maintenance';
$id_mesin     = isset($_GET['id_mesin']) ? $_GET['id_mesin'] : '';
$id_sub_mesin = isset($_GET['id_sub_mesin']) ? $_GET['id_sub_mesin'] : '';
$cari_komp    = isset($_GET['cari_komponen']) ? trim($_GET['cari_komponen']) : '';

// Set Header Http untuk Download File Excel (.xls)
$filename = "Laporan_" . strtoupper($jenis) . "_" . date('Ymd_His') . ".xls";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Query Data Berdasarkan Jenis Laporan & Filter Spesifik
if ($jenis == 'maintenance') {
    $query = "
        SELECT rm.*, k.nama_bagian, m.serial_number, m.nama_mesin 
        FROM riwayat_maintenance rm
        LEFT JOIN komponen k ON rm.id_komponen = k.id
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
        $query .= " AND (k.nama_bagian LIKE ? OR m.serial_number LIKE ?)";
        $params[] = "%$cari_komp%";
        $params[] = "%$cari_komp%";
        $types .= "ss";
    }
    $query .= " ORDER BY rm.tanggal DESC";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    mysqli_stmt_execute($stmt);
    $sql = mysqli_stmt_get_result($stmt);
} else {
    $query = "
        SELECT k.*, m.nama_mesin, m.serial_number, sm.nama_sub_mesin
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
        $query .= " AND (k.nama_bagian LIKE ? OR m.serial_number LIKE ?)";
        $params[] = "%$cari_komp%";
        $params[] = "%$cari_komp%";
        $types .= "ss";
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
<html>
<head>
  <meta charset="utf-8">
  <style>
    body { font-family: sans-serif; }
    table { border-collapse: collapse; width: 100%; }
    th, td { border: 1px solid #000; padding: 6px; text-align: left; }
    th { background-color: #f2f2f2; font-weight: bold; }
    .title { font-size: 14pt; font-weight: bold; }
    .subtitle { font-size: 11pt; font-weight: bold; }
  </style>
</head>
<body>

  <!-- Header Laporan dengan Logo Garudafood Format PNG -->
  <table style="border: none; margin-bottom: 15px;">
    <tr style="border: none;">
      <td width="120" style="border: none; vertical-align: middle; text-align: center;">
        <img src="https://i.ibb.co/680324k/garudafood.png" width="100" height="40" alt="Garudafood Logo">
      </td>
      <td style="border: none; vertical-align: middle;">
        <div class="title">PT GARUDAFOOD PUTRA PUTRI JAYA Tbk</div>
        <div class="subtitle">LAPORAN <?= strtoupper(htmlspecialchars($jenis)) ?> INVENTARIS MESIN</div>
        <?php if ($jenis == 'maintenance') : ?>
          <div style="font-size: 9pt;">Periode: <?= date('d/m/Y', strtotime($tgl_mulai)) ?> s/d <?= date('d/m/Y', strtotime($tgl_selesai)) ?></div>
        <?php endif; ?>
      </td>
    </tr>
  </table>

  <br>

  <!-- Tabel Data -->
  <table>
    <thead>
      <?php if ($jenis == 'maintenance') : ?>
        <tr>
          <th width="30">No</th>
          <th>Tanggal</th>
          <th>Nama Mesin</th>
          <th>Nama Komponen</th>
          <th>Serial Number (SN)</th>
          <th>Tindakan / Detail Perbaikan</th>
          <th>Teknisi</th>
          <th>Status</th>
        </tr>
      <?php else : ?>
        <tr>
          <th width="30">No</th>
          <th>Nama Komponen</th>
          <th>Serial Number (SN)</th>
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
              <td style="text-align: center;"><?= $no++ ?></td>
              <td><?= !empty($d['tanggal']) ? date('d/m/Y', strtotime($d['tanggal'])) : '-' ?></td>
              <td><?= htmlspecialchars(!empty($d['nama_mesin']) ? $d['nama_mesin'] : '-') ?></td>
              <td><?= htmlspecialchars(!empty($d['nama_bagian']) ? $d['nama_bagian'] : '-') ?></td>
              <!-- mso-number-format:'\@' agar Excel membaca string text & angka nol depan tidak hilang -->
              <td style="mso-number-format:'\@';"><?= htmlspecialchars(!empty($d['serial_number']) ? $d['serial_number'] : '-') ?></td>
              <td><?= htmlspecialchars(!empty($d['tindakan']) ? $d['tindakan'] : '-') ?></td>
              <td><?= htmlspecialchars(!empty($d['teknisi']) ? $d['teknisi'] : '-') ?></td>
              <td><?= htmlspecialchars(!empty($d['status']) ? $d['status'] : '-') ?></td>
            </tr>
          <?php else : ?>
            <tr>
              <td style="text-align: center;"><?= $no++ ?></td>
              <td><?= htmlspecialchars(!empty($d['nama_bagian']) ? $d['nama_bagian'] : '-') ?></td>
              <td style="mso-number-format:'\@';"><?= htmlspecialchars(!empty($d['serial_number']) ? $d['serial_number'] : '-') ?></td>
              <td><?= htmlspecialchars(!empty($d['nama_mesin']) ? $d['nama_mesin'] : '-') ?></td>
              <td><?= htmlspecialchars(!empty($d['nama_sub_mesin']) ? $d['nama_sub_mesin'] : '-') ?></td>
              <td><?= htmlspecialchars(!empty($d['kondisi']) ? $d['kondisi'] : 'Baik') ?></td>
            </tr>
          <?php endif; ?>
        <?php endwhile;
      else : ?>
        <tr>
          <td colspan="<?= ($jenis == 'maintenance') ? 8 : 6 ?>" style="text-align: center;">Tidak ada data.</td>
        </tr>
      <?php endif; ?>
    </tbody>
  </table>

</body>
</html>
<?php 
if (isset($stmt)) {
    mysqli_stmt_close($stmt);
}
?>
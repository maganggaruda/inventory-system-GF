<?php
include "../koneksi.php";

$tgl_mulai   = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : date('Y-m-01');
$tgl_selesai = isset($_GET['tgl_selesai']) ? $_GET['tgl_selesai'] : date('Y-m-d');
$jenis       = isset($_GET['jenis']) ? $_GET['jenis'] : 'maintenance';

// Set Header Http untuk Download File Excel (.xls)
$filename = "Laporan_" . strtoupper($jenis) . "_" . date('Ymd_His') . ".xls";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

// Query Data Berdasarkan Jenis Laporan (k.part_number diganti k.kode_mesin)
if ($jenis == 'maintenance') {
    $sql = mysqli_query($conn, "
        SELECT rm.*, k.nama_bagian, k.kode_mesin, m.nama_mesin 
        FROM riwayat_maintenance rm
        LEFT JOIN komponen k ON rm.id_komponen = k.id
        LEFT JOIN mesin m ON k.id_mesin = m.id
        WHERE rm.tanggal BETWEEN '$tgl_mulai' AND '$tgl_selesai'
        ORDER BY rm.tanggal DESC
    ");
} else {
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

  <!-- Header Laporan dengan Logo Garudafood Format PNG (Compatible dengan Excel) -->
  <table style="border: none; margin-bottom: 15px;">
    <tr style="border: none;">
      <td width="120" style="border: none; vertical-align: middle; text-align: center;">
        <img src="https://i.ibb.co/680324k/garudafood.png" width="100" height="40" alt="Garudafood Logo">
      </td>
      <td style="border: none; vertical-align: middle;">
        <div class="title">PT GARUDAFOOD PUTRA PUTRI JAYA Tbk</div>
        <div class="subtitle">LAPORAN <?= strtoupper($jenis) ?> INVENTARIS MESIN</div>
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
          <th>Kode Mesin</th>
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
              <td><?= $no++ ?></td>
              <td><?= date('d/m/Y', strtotime($d['tanggal'])) ?></td>
              <td><?= htmlspecialchars($d['nama_mesin'] ?: '-') ?></td>
              <td><?= htmlspecialchars($d['nama_bagian'] ?: '-') ?></td>
              <td style="mso-number-format:'\@';"><?= htmlspecialchars($d['kode_mesin'] ?: '-') ?></td>
              <td><?= htmlspecialchars($d['tindakan']) ?></td>
              <td><?= htmlspecialchars($d['teknisi'] ?? '-') ?></td>
              <td><?= htmlspecialchars($d['status']) ?></td>
            </tr>
          <?php else : ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= htmlspecialchars($d['nama_bagian']) ?></td>
              <td style="mso-number-format:'\@';"><?= htmlspecialchars($d['kode_mesin'] ?: '-') ?></td>
              <td><?= htmlspecialchars($d['nama_mesin'] ?: '-') ?></td>
              <td><?= htmlspecialchars($d['nama_sub_mesin'] ?: '-') ?></td>
              <td><?= htmlspecialchars($d['kondisi']) ?></td>
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
<?php
require_once "../vendor/autoload.php";
include "../koneksi.php";

use Dompdf\Dompdf;
use Dompdf\Options;

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("ID komponen tidak valid.");
}

/* =========================================================
   HELPER
========================================================= */

function e($text)
{
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

function imageToDataUri($path)
{
    if (!$path || !file_exists($path)) {
        return '';
    }

    $type = mime_content_type($path);

    if (!$type) {
        return '';
    }

    $data = file_get_contents($path);

    if ($data === false) {
        return '';
    }

    return 'data:' . $type . ';base64,' . base64_encode($data);
}

/* =========================================================
   DATA KOMPONEN
========================================================= */

$stmt = mysqli_prepare($conn, "
    SELECT 
        k.*,
        sm.nama_sub_mesin,
        m.nama_mesin,
        m.serial_number AS sn_mesin,
        jm.nama_jenis_mesin,
        ab.nama_area,
        ab.lokasi
    FROM komponen k
    LEFT JOIN sub_mesin sm
        ON k.id_sub_mesin = sm.id
    LEFT JOIN mesin m
        ON sm.id_mesin = m.id
    LEFT JOIN jenis_mesin jm
        ON m.id_jenis_mesin = jm.id
    LEFT JOIN area_bagian ab
        ON jm.id_area = ab.id
    WHERE k.id = ?
");

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$komponen = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$komponen) {
    die("Data komponen tidak ditemukan.");
}

/* =========================================================
   FOTO
========================================================= */

$foto = '';

if (!empty($komponen['gambar'])) {

    $path = dirname(__DIR__) .
        "/uploads/komponen/" .
        $komponen['gambar'];

    $foto = imageToDataUri($path);
}

/* =========================================================
   MAINTENANCE
========================================================= */

$stmt = mysqli_prepare($conn, "
    SELECT
        rm.*,
        k.nama_bagian,
        k.serial_number
    FROM riwayat_maintenance rm
    LEFT JOIN komponen k
        ON rm.id_komponen = k.id
    WHERE rm.id_komponen = ?
    ORDER BY rm.tanggal DESC
");

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$dataMaintenance = [];

while ($row = mysqli_fetch_assoc($result)) {
    $dataMaintenance[] = $row;
}

mysqli_stmt_close($stmt);

/* =========================================================
   KONDISI
========================================================= */

$kondisi = $komponen['kondisi'] ?? 'Baik';

$kondisiClass = 'good';

if ($kondisi == 'Perlu Pemeriksaan') {
    $kondisiClass = 'warning';
} elseif (
    $kondisi == 'Dalam Perbaikan' ||
    $kondisi == 'Rusak'
) {
    $kondisiClass = 'danger';
}

/* =========================================================
   HTML
========================================================= */

$html = '
<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<style>

@page {
    margin: 35px 35px 45px;
}

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 10px;
    color: #263238;
}

.header {
    border-bottom: 3px solid #005baa;
    padding-bottom: 12px;
    margin-bottom: 18px;
}

.title {
    font-size: 20px;
    font-weight: bold;
    color: #005baa;
}

.subtitle {
    font-size: 9px;
    color: #64748b;
}

.date {
    float: right;
    color: #64748b;
    font-size: 8px;
}

.component-box {
    border: 1px solid #dce3ea;
    padding: 15px;
    margin-bottom: 18px;
}

.photo {
    width: 160px;
    height: 135px;
    object-fit: contain;
}

.no-photo {
    width: 160px;
    height: 135px;
    background: #f1f5f9;
    text-align: center;
    vertical-align: middle;
    color: #94a3b8;
}

.component-name {
    font-size: 19px;
    font-weight: bold;
    color: #111827;
    margin-bottom: 10px;
}

.info {
    width: 100%;
    border-collapse: collapse;
}

.info td {
    padding: 5px 7px;
    border-bottom: 1px solid #edf0f2;
}

.label {
    width: 125px;
    color: #64748b;
}

.condition {
    display: inline-block;
    padding: 4px 9px;
    border-radius: 12px;
    font-weight: bold;
}

.section {
    background: #005baa;
    color: white;
    padding: 8px 10px;
    font-weight: bold;
    margin-top: 15px;
    margin-bottom: 8px;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th {
    background: #eef5fb;
    color: #174a73;
    padding: 7px 6px;
    border: 1px solid #d7e1ea;
    font-size: 9px;
}

.table td {
    padding: 7px 6px;
    border: 1px solid #d7e1ea;
    vertical-align: top;
}

.center {
    text-align: center;
}

.good {
    background: #dcfce7;
    color: #166534;
}

.warning {
    background: #fef3c7;
    color: #92400e;
}

.danger {
    background: #fee2e2;
    color: #991b1b;
}

.badge {
    display: inline-block;
    padding: 3px 7px;
    border-radius: 10px;
}

.small {
    font-size: 8px;
    color: #64748b;
}

.footer {
    position: fixed;
    bottom: -25px;
    left: 0;
    right: 0;
    text-align: center;
    color: #94a3b8;
    font-size: 8px;
}

</style>

</head>

<body>


<div class="header">

<div class="date">
    Dicetak: ' . date('d/m/Y H:i') . ' WIB
</div>

<div class="title">
    DETAIL KOMPONEN
</div>

<div class="subtitle">
    Inventory & Maintenance System — PT Garudafood Putra Putri Jaya Tbk
</div>

</div>


<div class="component-box">

<table width="100%">

<tr>

<td width="180" valign="top">
';

if ($foto) {

    $html .= '
        <img src="' . $foto . '" class="photo">
    ';

} else {

    $html .= '
        <div class="no-photo">
            Tidak ada foto
        </div>
    ';
}

$html .= '

</td>

<td valign="top">

<div class="component-name">
    ' . e($komponen['nama_bagian'] ?? '-') . '
</div>

<table class="info">

<tr>
<td class="label">Serial Number</td>
<td><b>' . e($komponen['serial_number'] ?? '-') . '</b></td>
</tr>

<tr>
<td class="label">Mesin Induk</td>
<td>' . e($komponen['nama_mesin'] ?? '-') . '</td>
</tr>

<tr>
<td class="label">SN Mesin</td>
<td>' . e($komponen['sn_mesin'] ?? '-') . '</td>
</tr>

<tr>
<td class="label">Sub Mesin</td>
<td>' . e($komponen['nama_sub_mesin'] ?? '-') . '</td>
</tr>

<tr>
<td class="label">Area</td>
<td>' . e($komponen['nama_area'] ?? '-') . '</td>
</tr>

<tr>
<td class="label">Lokasi</td>
<td>' . e($komponen['lokasi'] ?? '-') . '</td>
</tr>

<tr>
<td class="label">Kategori</td>
<td>' . e($komponen['kategori'] ?? '-') . '</td>
</tr>

<tr>
<td class="label">Kondisi</td>
<td>
    <span class="condition ' . $kondisiClass . '">
        ' . e($kondisi) . '
    </span>
</td>
</tr>

</table>

</td>

</tr>

</table>

</div>


<div class="section">
    SPESIFIKASI TEKNIS
</div>

<table class="table">

<tr>
<td width="25%">Brand / Merk</td>
<td>' . e($komponen['brand'] ?? '-') . '</td>
<td width="25%">Tipe</td>
<td>' . e($komponen['tipe'] ?? '-') . '</td>
</tr>

<tr>
<td>Part Number</td>
<td>' . e($komponen['part_number'] ?? '-') . '</td>
<td>Daya</td>
<td>' . e($komponen['daya'] ?? '-') . '</td>
</tr>

<tr>
<td>IO Address</td>
<td>' . e($komponen['io_address'] ?? '-') . '</td>
<td>Input Voltage</td>
<td>' . e($komponen['input_voltage'] ?? '-') . '</td>
</tr>

<tr>
<td>Frekuensi Input</td>
<td>' . e($komponen['frekuensi_input'] ?? '-') . '</td>
<td>Arus Input</td>
<td>' . e($komponen['arus_input'] ?? '-') . '</td>
</tr>

<tr>
<td>Output</td>
<td>' . e($komponen['output'] ?? '-') . '</td>
<td>Frekuensi Output</td>
<td>' . e($komponen['frekuensi_output'] ?? '-') . '</td>
</tr>

<tr>
<td>IP Rating</td>
<td colspan="3">' . e($komponen['ip_rating'] ?? '-') . '</td>
</tr>

</table>


<div class="section">
    KETERANGAN
</div>

<table class="table">

<tr>
<td>
' . nl2br(e($komponen['keterangan'] ?? 'Tidak ada keterangan tambahan.')) . '
</td>
</tr>

</table>


<div class="section">
    RIWAYAT MAINTENANCE
</div>

<table class="table">

<thead>

<tr>
<th width="30">No</th>
<th width="80">Tanggal</th>
<th>Jenis</th>
<th>Tindakan</th>
<th>Teknisi</th>
<th width="75">Status</th>
</tr>

</thead>

<tbody>
';

if (!empty($dataMaintenance)) {

    $no = 1;

    foreach ($dataMaintenance as $m) {

        $status = $m['status'] ?? 'Pending';

        $class = 'danger';

        if ($status == 'Selesai') {
            $class = 'good';
        } elseif ($status == 'Proses') {
            $class = 'warning';
        }

        $tanggal = '-';

        if (!empty($m['tanggal'])) {

            $tanggal = date(
                'd/m/Y H:i',
                strtotime($m['tanggal'])
            );
        }

        $html .= '
        <tr>

        <td class="center">
            ' . $no++ . '
        </td>

        <td>
            ' . $tanggal . '
        </td>

        <td>
            ' . e($m['jenis'] ?? '-') . '
        </td>

        <td>
            ' . nl2br(e($m['tindakan'] ?? '-')) . '
        </td>

        <td>
            ' . e($m['teknisi'] ?? '-') . '
        </td>

        <td class="center">
            <span class="badge ' . $class . '">
                ' . e($status) . '
            </span>
        </td>

        </tr>
        ';
    }

} else {

    $html .= '
    <tr>

        <td colspan="6" class="center">
            Belum ada riwayat maintenance.
        </td>

    </tr>
    ';
}

$html .= '

</tbody>

</table>


<div class="footer">
    Inventory & Maintenance System — PT Garudafood Putra Putri Jaya Tbk
</div>


</body>

</html>
';

/* =========================================================
   GENERATE PDF
========================================================= */

$options = new Options();

$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('defaultFont', 'DejaVu Sans');

$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);

$dompdf->setPaper('A4', 'portrait');

$dompdf->render();

$namaFile = 'Detail_Komponen_' .
    preg_replace(
        '/[^A-Za-z0-9_-]/',
        '_',
        $komponen['nama_bagian'] ?? 'komponen'
    ) .
    '.pdf';

$dompdf->stream($namaFile, [
    'Attachment' => true
]);

exit;
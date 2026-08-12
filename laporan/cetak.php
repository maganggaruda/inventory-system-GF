<?php
include "../koneksi.php";

/* =========================================================
   PARAMETER
========================================================= */

$tgl_mulai    = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : date('Y-m-01');
$tgl_selesai  = isset($_GET['tgl_selesai']) ? $_GET['tgl_selesai'] : date('Y-m-d');

$jenis        = isset($_GET['jenis']) ? $_GET['jenis'] : 'maintenance';
$id           = isset($_GET['id']) ? intval($_GET['id']) : 0;

$id_mesin     = isset($_GET['id_mesin']) ? $_GET['id_mesin'] : '';
$id_sub_mesin = isset($_GET['id_sub_mesin']) ? $_GET['id_sub_mesin'] : '';

$cari_komp    = isset($_GET['cari_komponen'])
    ? trim($_GET['cari_komponen'])
    : '';

/* =========================================================
   HELPER
========================================================= */

function e($value)
{
    return htmlspecialchars((string)($value ?? '-'), ENT_QUOTES, 'UTF-8');
}

function valueOrDash($value)
{
    return ($value !== null && $value !== '') ? e($value) : '-';
}

function formatTanggal($tanggal)
{
    if (empty($tanggal)) {
        return '-';
    }

    $time = strtotime($tanggal);

    if (!$time) {
        return '-';
    }

    return date('d/m/Y', $time);
}

function formatTanggalPanjang($tanggal)
{
    if (empty($tanggal)) {
        return '-';
    }

    $time = strtotime($tanggal);

    if (!$time) {
        return '-';
    }

    $bulan = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];

    return date('d', $time) . ' ' .
           $bulan[(int)date('n', $time)] . ' ' .
           date('Y', $time);
}

/* =========================================================
   DATA
========================================================= */

$sql = false;
$stmt = null;

/* =========================================================
   SINGLE MAINTENANCE
========================================================= */

if ($jenis === 'single_maintenance') {

    $query = "
        SELECT
            rm.*,

            /* DATA SNAPSHOT MAINTENANCE */
            rm.serial_number AS rm_serial_number,
            rm.nama_bagian AS rm_nama_bagian,
            rm.kategori AS rm_kategori,
            rm.nama_mesin AS rm_nama_mesin,
            rm.nama_sub_mesin AS rm_nama_sub_mesin,
            rm.lokasi_penempatan AS rm_lokasi_penempatan,
            rm.brand AS rm_brand,
            rm.tipe AS rm_tipe,
            rm.part_number AS rm_part_number,
            rm.daya AS rm_daya,
            rm.io_address AS rm_io_address,
            rm.input_voltage AS rm_input_voltage,
            rm.frekuensi_input AS rm_frekuensi_input,
            rm.arus_input AS rm_arus_input,
            rm.output AS rm_output,
            rm.frekuensi_output AS rm_frekuensi_output,
            rm.ip_rating AS rm_ip_rating,
            rm.gambar AS rm_gambar,

            /* DATA MASTER KOMPONEN */
            k.serial_number AS k_serial_number,
            k.nama_bagian AS k_nama_bagian,
            k.kategori AS k_kategori,
            k.brand AS k_brand,
            k.tipe AS k_tipe,
            k.part_number AS k_part_number,
            k.daya AS k_daya,
            k.io_address AS k_io_address,
            k.input_voltage AS k_input_voltage,
            k.frekuensi_input AS k_frekuensi_input,
            k.arus_input AS k_arus_input,
            k.output AS k_output,
            k.frekuensi_output AS k_frekuensi_output,
            k.ip_rating AS k_ip_rating,
            k.gambar AS k_gambar,

            sm.nama_sub_mesin AS master_sub_mesin,
            m.nama_mesin AS master_mesin,
            m.serial_number AS sn_mesin

        FROM riwayat_maintenance rm

        LEFT JOIN komponen k
            ON rm.id_komponen = k.id

        LEFT JOIN sub_mesin sm
            ON k.id_sub_mesin = sm.id

        LEFT JOIN mesin m
            ON sm.id_mesin = m.id

        WHERE rm.id = ?
        LIMIT 1
    ";

    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {

        mysqli_stmt_bind_param($stmt, "i", $id);

        mysqli_stmt_execute($stmt);

        $sql = mysqli_stmt_get_result($stmt);
    }
}

/* =========================================================
   SINGLE KOMPONEN
========================================================= */

elseif ($jenis === 'single_komponen') {

    $query = "
        SELECT
            k.*,
            k.serial_number AS sn_komponen,
            m.nama_mesin,
            m.serial_number AS sn_mesin,
            sm.nama_sub_mesin
        FROM komponen k

        LEFT JOIN sub_mesin sm
            ON k.id_sub_mesin = sm.id

        LEFT JOIN mesin m
            ON sm.id_mesin = m.id

        WHERE k.id = ?
        LIMIT 1
    ";

    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {

        mysqli_stmt_bind_param($stmt, "i", $id);

        mysqli_stmt_execute($stmt);

        $sql = mysqli_stmt_get_result($stmt);
    }
}

/* =========================================================
   MAINTENANCE
========================================================= */

elseif ($jenis === 'maintenance') {

    $query = "
        SELECT
            rm.*,
            k.*,

            k.serial_number AS sn_komponen,
            k.brand AS k_brand,
            k.tipe AS k_tipe,
            k.part_number AS k_part_number,
            k.daya AS k_daya,
            k.io_address AS k_io_address,
            k.input_voltage AS k_input_voltage,
            k.frekuensi_input AS k_frekuensi_input,
            k.arus_input AS k_arus_input,
            k.output AS k_output,
            k.frekuensi_output AS k_frekuensi_output,
            k.ip_rating AS k_ip_rating,

            sm.nama_sub_mesin,
            m.serial_number AS sn_mesin,
            m.nama_mesin

        FROM riwayat_maintenance rm

        LEFT JOIN komponen k
            ON rm.id_komponen = k.id

        LEFT JOIN sub_mesin sm
            ON k.id_sub_mesin = sm.id

        LEFT JOIN mesin m
            ON sm.id_mesin = m.id

        WHERE DATE(rm.tanggal) BETWEEN ? AND ?
    ";

    $params = [
        $tgl_mulai,
        $tgl_selesai
    ];

    $types = "ss";

    if (!empty($id_mesin)) {

        $query .= " AND sm.id_mesin = ?";

        $params[] = $id_mesin;

        $types .= "i";
    }

    if (!empty($id_sub_mesin)) {

        $query .= " AND k.id_sub_mesin = ?";

        $params[] = $id_sub_mesin;

        $types .= "i";
    }

    if (!empty($cari_komp)) {

        $query .= "
            AND (
                k.nama_bagian LIKE ?
                OR m.nama_mesin LIKE ?
                OR sm.nama_sub_mesin LIKE ?
                OR k.serial_number LIKE ?
                OR k.brand LIKE ?
                OR k.tipe LIKE ?
            )
        ";

        $keyword = "%{$cari_komp}%";

        for ($i = 0; $i < 6; $i++) {
            $params[] = $keyword;
        }

        $types .= "ssssss";
    }

    $query .= " ORDER BY rm.tanggal DESC";

    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            $types,
            ...$params
        );

        mysqli_stmt_execute($stmt);

        $sql = mysqli_stmt_get_result($stmt);
    }
}

/* =========================================================
   INVENTARIS KOMPONEN
========================================================= */

else {

    $query = "
        SELECT
            k.*,

            k.serial_number AS sn_komponen,

            m.nama_mesin,
            m.serial_number AS sn_mesin,

            sm.nama_sub_mesin

        FROM komponen k

        LEFT JOIN sub_mesin sm
            ON k.id_sub_mesin = sm.id

        LEFT JOIN mesin m
            ON sm.id_mesin = m.id

        WHERE 1=1
    ";

    $params = [];
    $types  = "";

    if (!empty($id_mesin)) {

        $query .= " AND sm.id_mesin = ?";

        $params[] = $id_mesin;

        $types .= "i";
    }

    if (!empty($id_sub_mesin)) {

        $query .= " AND k.id_sub_mesin = ?";

        $params[] = $id_sub_mesin;

        $types .= "i";
    }

    if (!empty($cari_komp)) {

        $query .= "
            AND (
                k.nama_bagian LIKE ?
                OR m.nama_mesin LIKE ?
                OR sm.nama_sub_mesin LIKE ?
                OR k.serial_number LIKE ?
                OR k.brand LIKE ?
                OR k.tipe LIKE ?
            )
        ";

        $keyword = "%{$cari_komp}%";

        for ($i = 0; $i < 6; $i++) {
            $params[] = $keyword;
        }

        $types .= "ssssss";
    }

    $query .= " ORDER BY m.nama_mesin ASC, sm.nama_sub_mesin ASC, k.nama_bagian ASC";

    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {

        if (!empty($params)) {

            mysqli_stmt_bind_param(
                $stmt,
                $types,
                ...$params
            );
        }

        mysqli_stmt_execute($stmt);

        $sql = mysqli_stmt_get_result($stmt);
    }
}

/* =========================================================
   DATA SINGLE MAINTENANCE
========================================================= */

$maintenance = null;

if ($jenis === 'single_maintenance' && $sql) {

    $maintenance = mysqli_fetch_assoc($sql);

    if (!$maintenance) {
        $maintenance = null;
    }
}

/* =========================================================
   STATUS
========================================================= */

$status = '';

if ($maintenance) {
    $status = strtolower(trim($maintenance['status'] ?? ''));
}

$statusClass = 'status-default';

if ($status === 'selesai') {
    $statusClass = 'status-selesai';
} elseif ($status === 'proses') {
    $statusClass = 'status-proses';
} elseif ($status === 'pending') {
    $statusClass = 'status-pending';
}

/* =========================================================
   JUDUL
========================================================= */

if ($jenis === 'single_maintenance') {

    $judul = 'MAINTENANCE ORDER';

} elseif ($jenis === 'maintenance') {

    $judul = 'LAPORAN RIWAYAT MAINTENANCE';

} elseif ($jenis === 'single_komponen') {

    $judul = 'DATA KOMPONEN';

} else {

    $judul = 'LAPORAN INVENTARIS KOMPONEN';
}

/* =========================================================
   NOMOR MO
========================================================= */

$nomorMO = '';

if ($maintenance) {

    $tanggalMO = !empty($maintenance['tanggal'])
        ? date('Ymd', strtotime($maintenance['tanggal']))
        : date('Ymd');

    $nomorMO = 'MO-' . $tanggalMO . '-' . str_pad(
        (int)($maintenance['id'] ?? 0),
        4,
        '0',
        STR_PAD_LEFT
    );
}

?>
<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>
    <?= e($judul) ?> - Garudafood
</title>

<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
>

<style>

/* =========================================================
   GLOBAL
========================================================= */

* {
    box-sizing: border-box;
}

html,
body {
    margin: 0;
    padding: 0;
}

body {

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background: #eef2f6;

    color: #18212b;

    font-size: 10px;
}

.page {

    width: 1120px;

    max-width: calc(100% - 30px);

    margin: 25px auto;

    background: #ffffff;

    padding: 25px 28px;

    box-shadow:
        0 4px 18px rgba(0,0,0,.08);

    border: 1px solid #dce2e8;
}

/* =========================================================
   TOOLBAR
========================================================= */

.toolbar {

    display: flex;

    justify-content: flex-end;

    gap: 8px;

    margin-bottom: 15px;
}

.btn-print {

    border: none;

    background: #075aaa;

    color: white;

    padding: 8px 15px;

    border-radius: 5px;

    font-size: 10px;

    font-weight: 700;

    cursor: pointer;
}

.btn-print:hover {
    background: #064b8f;
}

.btn-close {

    border: 1px solid #9ca6b0;

    background: #ffffff;

    color: #34404b;

    padding: 8px 15px;

    border-radius: 5px;

    font-size: 10px;

    font-weight: 700;

    cursor: pointer;
}

/* =========================================================
   HEADER
========================================================= */

.company-header {

    display: grid;

    grid-template-columns: 150px 1fr 150px;

    align-items: center;

    min-height: 85px;

    border-bottom: 3px solid #075aaa;

    padding-bottom: 12px;

    margin-bottom: 15px;
}

.logo {

    width: 120px;

    max-height: 70px;

    object-fit: contain;
}

.company-title {

    text-align: center;
}

.company-name {

    font-size: 18px;

    font-weight: 800;

    color: #075aaa;

    letter-spacing: .2px;

    margin: 0;
}

.document-title {

    margin-top: 4px;

    font-size: 14px;

    font-weight: 800;

    color: #202a34;

    letter-spacing: .5px;
}

.document-subtitle {

    margin-top: 4px;

    color: #687480;

    font-size: 9px;
}

/* =========================================================
   DOCUMENT META
========================================================= */

.meta-grid {

    display: grid;

    grid-template-columns: 1fr 1fr 1fr;

    border: 1px solid #cfd7df;

    margin-bottom: 12px;
}

.meta-item {

    min-height: 54px;

    padding: 8px 10px;

    border-right: 1px solid #cfd7df;

    border-bottom: 1px solid #cfd7df;
}

.meta-item:nth-child(3n) {
    border-right: none;
}

.meta-label {

    display: block;

    font-size: 8px;

    font-weight: 800;

    color: #6b7782;

    text-transform: uppercase;

    margin-bottom: 6px;
}

.meta-value {

    font-size: 11px;

    font-weight: 700;

    color: #151d25;
}

/* =========================================================
   SECTION
========================================================= */

.section {

    border: 1px solid #cfd7df;

    margin-bottom: 12px;

    page-break-inside: avoid;

    break-inside: avoid;
}

.section-title {

    background: #075aaa;

    color: #ffffff;

    padding: 8px 10px;

    font-size: 10px;

    font-weight: 800;

    text-transform: uppercase;

    letter-spacing: .3px;
}

.section-body {

    padding: 10px;
}

/* =========================================================
   EQUIPMENT
========================================================= */

.equipment-grid {

    display: grid;

    grid-template-columns: 1fr 1fr;

    border-top: 1px solid #d8dee4;

    border-left: 1px solid #d8dee4;
}

.equipment-item {

    min-height: 45px;

    padding: 7px 9px;

    border-right: 1px solid #d8dee4;

    border-bottom: 1px solid #d8dee4;
}

.label {

    display: block;

    font-size: 7.5px;

    color: #68737d;

    font-weight: 800;

    text-transform: uppercase;

    margin-bottom: 4px;
}

.value {

    font-size: 10px;

    font-weight: 700;

    color: #1b232b;
}

/* =========================================================
   SPECIFICATION
========================================================= */

.spec-grid {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 10px;
}

.spec-box {

    border: 1px solid #ccd4dc;

    background: #fafbfc;
}

.spec-heading {

    background: #eaf2fa;

    border-bottom: 1px solid #ccd4dc;

    padding: 7px 9px;

    font-weight: 800;

    color: #075aaa;

    font-size: 9px;

    text-transform: uppercase;
}

.spec-heading.after {

    background: #e9f7ef;

    color: #14733b;
}

.spec-table {

    width: 100%;

    border-collapse: collapse;
}

.spec-table td {

    border-bottom: 1px solid #e1e5e9;

    padding: 5px 7px;

    font-size: 8.5px;

    vertical-align: top;
}

.spec-table tr:last-child td {
    border-bottom: none;
}

.spec-table td:first-child {

    width: 40%;

    color: #66717b;

    font-weight: 700;
}

.spec-table td:last-child {

    color: #17212a;

    font-weight: 700;
}

/* =========================================================
   ACTION
========================================================= */

.action-grid {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 10px;
}

.action-box {

    min-height: 80px;

    border: 1px solid #d1d8df;

    background: #ffffff;

    padding: 9px;
}

.action-content {

    white-space: pre-line;

    font-size: 10px;

    line-height: 1.5;
}

/* =========================================================
   WORK INFO
========================================================= */

.work-grid {

    display: grid;

    grid-template-columns: 1fr 1fr 1fr;

    border-top: 1px solid #d7dee6;

    border-left: 1px solid #d7dee6;
}

.work-box {

    min-height: 78px;

    padding: 10px 12px;

    border-right: 1px solid #d7dee6;

    border-bottom: 1px solid #d7dee6;

    display: flex;

    flex-direction: column;

    justify-content: center;
}

.work-label {

    display: block;

    font-size: 8px;

    text-transform: uppercase;

    font-weight: 800;

    color: #68737d;

    margin-bottom: 8px;

    line-height: 1.2;
}

.work-value {

    font-size: 10.5px;

    line-height: 1.45;

    min-height: 20px;

    display: flex;

    align-items: center;
}

/* =========================================================
   STATUS
========================================================= */

.status {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    min-height: 28px;

    padding: 5px 14px;

    border-radius: 16px;

    font-size: 8px;

    font-weight: 800;

    line-height: 1;

    white-space: nowrap;
}

.status-selesai {

    background: #dff5e7;

    color: #14733b;
}

.status-proses {

    background: #fff0cf;

    color: #946200;
}

.status-pending {

    background: #edf0f3;

    color: #5b6570;
}

.status-default {

    background: #e9edf1;

    color: #4d5863;
}

/* =========================================================
   PHOTO
========================================================= */

.photo-box {

    min-height: 120px;

    border: 1px dashed #aeb8c2;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 10px;

    background: #fafbfc;
}

.photo-box img {

    max-width: 300px;

    max-height: 170px;

    object-fit: contain;
}

.no-photo {

    color: #7b858e;

    font-style: italic;

    font-size: 9px;
}

/* =========================================================
   RESULT
========================================================= */

.result-box {

    border: 1px solid #cfd7df;

    background: #f8fafc;

    padding: 10px;

    min-height: 60px;
}

.result-title {

    font-size: 8px;

    text-transform: uppercase;

    font-weight: 800;

    color: #68737d;

    margin-bottom: 5px;
}

.result-text {

    font-size: 10px;

    line-height: 1.5;
}

/* =========================================================
   SIGNATURE
========================================================= */

.signature-grid {

    display: grid;

    grid-template-columns: 1fr 1fr 1fr;

    gap: 15px;

    margin-top: 25px;

    page-break-inside: avoid;
}

.signature-box {

    text-align: center;

    min-height: 115px;

    border: 1px solid #d1d8df;
}

.signature-header {

    background: #f1f4f7;

    padding: 7px;

    border-bottom: 1px solid #d1d8df;

    font-weight: 800;

    font-size: 8px;

    text-transform: uppercase;
}

.signature-space {

    height: 65px;
}

.signature-name {

    font-size: 9px;

    font-weight: 800;

    text-decoration: underline;
}

/* =========================================================
   TABLE INVENTORY
========================================================= */

.inventory-table {

    width: 100%;

    border-collapse: collapse;

    font-size: 8.5px;
}

.inventory-table th {

    background: #075aaa;

    color: #ffffff;

    padding: 7px 6px;

    border: 1px solid #075aaa;

    text-align: center;

    font-size: 8px;

    text-transform: uppercase;
}

.inventory-table td {

    border: 1px solid #cfd7df;

    padding: 6px;

    vertical-align: middle;
}

.inventory-table tbody tr:nth-child(even) {
    background: #f7f9fb;
}

.img-komponen {

    width: 55px;

    height: 45px;

    object-fit: contain;

    border: 1px solid #d5dbe1;

    background: #ffffff;
}

/* =========================================================
   CONDITION
========================================================= */

.condition {

    display: inline-flex;

    padding: 4px 8px;

    border-radius: 12px;

    font-size: 7.5px;

    font-weight: 800;
}

.condition-baik {

    background: #dff5e7;

    color: #14733b;
}

.condition-periksa {

    background: #fff0cf;

    color: #946200;
}

.condition-rusak {

    background: #fde3e3;

    color: #a52727;
}

/* =========================================================
   FOOTER
========================================================= */

.document-footer {

    margin-top: 12px;

    padding-top: 8px;

    border-top: 1px solid #d4dbe2;

    display: flex;

    justify-content: space-between;

    color: #77818b;

    font-size: 7.5px;
}

/* =========================================================
   PRINT
========================================================= */

@media print {

    * {

        -webkit-print-color-adjust: exact !important;

        print-color-adjust: exact !important;
    }

    html,
    body {

        width: 100%;

        margin: 0;

        padding: 0;

        background: #ffffff !important;
    }

    body {

        font-size: 9px;
    }

    .no-print {

        display: none !important;
    }

    .toolbar {

        display: none !important;
    }

    .page {

        width: 100%;

        max-width: none;

        margin: 0;

        padding: 0;

        background: #ffffff;

        box-shadow: none;

        border: none;
    }

    .section {

        page-break-inside: avoid;

        break-inside: avoid;
    }

    .meta-grid {

        page-break-inside: avoid;

        break-inside: avoid;
    }

    .equipment-grid {

        page-break-inside: avoid;

        break-inside: avoid;
    }

    .spec-grid {

        page-break-inside: avoid;

        break-inside: avoid;
    }

    .action-grid {

        page-break-inside: avoid;

        break-inside: avoid;
    }

    .work-grid {

        page-break-inside: avoid;

        break-inside: avoid;
    }

    .photo-box {

        page-break-inside: avoid;

        break-inside: avoid;
    }

    .signature-grid {

        page-break-inside: avoid;

        break-inside: avoid;
    }

    @page {

        size: A4 landscape;

        margin: 8mm;
    }
}

/* =========================================================
   SCREEN
========================================================= */

@media screen and (max-width: 900px) {

    .page {

        width: 100%;

        max-width: 100%;

        margin: 0;

        padding: 15px;
    }

    .company-header {

        grid-template-columns: 100px 1fr;
    }

    .company-header > div:last-child {

        display: none;
    }

    .spec-grid,
    .action-grid,
    .equipment-grid,
    .work-grid,
    .meta-grid,
    .signature-grid {

        grid-template-columns: 1fr;
    }
}

</style>

</head>

<body>

<div class="page">

    <!-- =====================================================
         TOOLBAR
    ====================================================== -->

    <div class="toolbar no-print">

        <button
            type="button"
            class="btn-print"
            onclick="printDocument()"
        >
            🖨 Cetak / Simpan PDF
        </button>

        <button
            type="button"
            class="btn-close"
            onclick="window.close()"
        >
            ✕ Tutup
        </button>

    </div>


    <!-- =====================================================
         SINGLE MAINTENANCE = MO PROFESIONAL
    ====================================================== -->

    <?php if ($jenis === 'single_maintenance') : ?>

        <?php if (!$maintenance) : ?>

            <div style="
                text-align:center;
                padding:80px 20px;
                font-family:Arial,sans-serif;
            ">

                <h2>Data Tidak Ditemukan</h2>

                <p>
                    Data Maintenance Order yang diminta tidak ditemukan.
                </p>

                <button
                    class="no-print"
                    onclick="history.back()"
                >
                    Kembali
                </button>

            </div>

        <?php else : ?>

            <!-- HEADER -->

            <div class="company-header">

                <div>

                    <img
                        src="../assets/img/logo-garudafood.png"
                        class="logo"
                        alt="Garudafood"
                    >

                </div>

                <div class="company-title">

                    <h1 class="company-name">
                        PT GARUDAFOOD PUTRA PUTRI JAYA TBK
                    </h1>

                    <div class="document-title">
                        MAINTENANCE ORDER
                    </div>

                    <div class="document-subtitle">
                        Dokumen Perintah dan Pelaksanaan Maintenance
                    </div>

                </div>

                <div></div>

            </div>


            <!-- META -->

            <div class="meta-grid">

                <div class="meta-item">

                    <span class="meta-label">
                        Nomor Maintenance Order
                    </span>

                    <div class="meta-value">
                        <?= e($nomorMO) ?>
                    </div>

                </div>


                <div class="meta-item">

                    <span class="meta-label">
                        Tanggal Pelaksanaan
                    </span>

                    <div class="meta-value">
                        <?= formatTanggalPanjang($maintenance['tanggal'] ?? '') ?>
                    </div>

                </div>


                <div class="meta-item">

                    <span class="meta-label">
                        Jenis Maintenance
                    </span>

                    <div class="meta-value">
                        <?= valueOrDash($maintenance['jenis'] ?? '') ?>
                    </div>

                </div>

            </div>


            <!-- IDENTITAS PERALATAN -->

            <div class="section">

                <div class="section-title">
                    01 &nbsp; IDENTITAS PERALATAN
                </div>

                <div class="section-body">

                    <div class="equipment-grid">

                        <div class="equipment-item">

                            <span class="label">
                                Mesin Induk
                            </span>

                            <div class="value">
                                <?= valueOrDash(
                                    $maintenance['master_mesin']
                                    ?? $maintenance['rm_nama_mesin']
                                    ?? ''
                                ) ?>
                            </div>

                        </div>


                        <div class="equipment-item">

                            <span class="label">
                                Serial Number Mesin
                            </span>

                            <div class="value">
                                <?= valueOrDash(
                                    $maintenance['sn_mesin']
                                    ?? ''
                                ) ?>
                            </div>

                        </div>


                        <div class="equipment-item">

                            <span class="label">
                                Sub Mesin
                            </span>

                            <div class="value">
                                <?= valueOrDash(
                                    $maintenance['master_sub_mesin']
                                    ?? $maintenance['rm_nama_sub_mesin']
                                    ?? ''
                                ) ?>
                            </div>

                        </div>


                        <div class="equipment-item">

                            <span class="label">
                                Komponen
                            </span>

                            <div class="value">
                                <?= valueOrDash(
                                    $maintenance['rm_nama_bagian']
                                    ?? ''
                                ) ?>
                            </div>

                        </div>


                        <div class="equipment-item">

                            <span class="label">
                                Serial Number Komponen
                            </span>

                            <div class="value">
                                <?= valueOrDash(
                                    $maintenance['rm_serial_number']
                                    ?? ''
                                ) ?>
                            </div>

                        </div>


                        <div class="equipment-item">

                            <span class="label">
                                Lokasi Penempatan
                            </span>

                            <div class="value">
                                <?= valueOrDash(
                                    $maintenance['rm_lokasi_penempatan']
                                    ?? ''
                                ) ?>
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- SPESIFIKASI -->

            <div class="section">

                <div class="section-title">
                    02 &nbsp; PERBANDINGAN SPESIFIKASI TEKNIS
                </div>

                <div class="section-body">

                    <div class="spec-grid">

                        <!-- NORMAL -->

                        <div class="spec-box">

                            <div class="spec-heading">
                                SPESIFIKASI NORMAL / SEBELUM MAINTENANCE
                            </div>

                            <table class="spec-table">

                                <tr>
                                    <td>Brand / Merk</td>
                                    <td>
                                        <?= valueOrDash(
                                            $maintenance['k_brand']
                                            ?? ''
                                        ) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Tipe</td>
                                    <td>
                                        <?= valueOrDash(
                                            $maintenance['k_tipe']
                                            ?? ''
                                        ) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Part Number</td>
                                    <td>
                                        <?= valueOrDash(
                                            $maintenance['k_part_number']
                                            ?? ''
                                        ) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Daya</td>
                                    <td>
                                        <?= valueOrDash(
                                            $maintenance['daya']
                                            ?? ''
                                        ) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td>IO Address</td>
                                    <td>
                                        <?= valueOrDash(
                                            $maintenance['k_io_address']
                                            ?? ''
                                        ) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Input Voltage</td>
                                    <td>
                                        <?= valueOrDash(
                                            $maintenance['k_input_voltage']
                                            ?? ''
                                        ) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Frekuensi Input</td>
                                    <td>
                                        <?= valueOrDash(
                                            $maintenance['k_frekuensi_input']
                                            ?? ''
                                        ) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Arus Input</td>
                                    <td>
                                        <?= valueOrDash(
                                            $maintenance['k_arus_input']
                                            ?? ''
                                        ) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Output</td>
                                    <td>
                                        <?= valueOrDash(
                                            $maintenance['k_output']
                                            ?? ''
                                        ) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Frekuensi Output</td>
                                    <td>
                                        <?= valueOrDash(
                                            $maintenance['k_frekuensi_output']
                                            ?? ''
                                        ) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td>IP Rating</td>
                                    <td>
                                        <?= valueOrDash(
                                            $maintenance['k_ip_rating']
                                            ?? ''
                                        ) ?>
                                    </td>
                                </tr>

                            </table>

                        </div>


                        <!-- TERBARU -->

                        <div class="spec-box">

                            <div class="spec-heading after">
                                SPESIFIKASI TERBARU / SESUDAH MAINTENANCE
                            </div>

                            <table class="spec-table">

                                <tr>
                                    <td>Brand / Merk</td>
                                    <td>
                                        <?= valueOrDash(
                                            $maintenance['rm_brand']
                                            ?? ''
                                        ) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Tipe</td>
                                    <td>
                                        <?= valueOrDash(
                                            $maintenance['rm_tipe']
                                            ?? ''
                                        ) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Part Number</td>
                                    <td>
                                        <?= valueOrDash(
                                            $maintenance['rm_part_number']
                                            ?? ''
                                        ) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Daya</td>
                                    <td>
                                        <?= valueOrDash(
                                            $maintenance['rm_daya']
                                            ?? ''
                                        ) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td>IO Address</td>
                                    <td>
                                        <?= valueOrDash(
                                            $maintenance['rm_io_address']
                                            ?? ''
                                        ) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Input Voltage</td>
                                    <td>
                                        <?= valueOrDash(
                                            $maintenance['rm_input_voltage']
                                            ?? ''
                                        ) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Frekuensi Input</td>
                                    <td>
                                        <?= valueOrDash(
                                            $maintenance['rm_frekuensi_input']
                                            ?? ''
                                        ) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Arus Input</td>
                                    <td>
                                        <?= valueOrDash(
                                            $maintenance['rm_arus_input']
                                            ?? ''
                                        ) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Output</td>
                                    <td>
                                        <?= valueOrDash(
                                            $maintenance['rm_output']
                                            ?? ''
                                        ) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Frekuensi Output</td>
                                    <td>
                                        <?= valueOrDash(
                                            $maintenance['rm_frekuensi_output']
                                            ?? ''
                                        ) ?>
                                    </td>
                                </tr>

                                <tr>
                                    <td>IP Rating</td>
                                    <td>
                                        <?= valueOrDash(
                                            $maintenance['rm_ip_rating']
                                            ?? ''
                                        ) ?>
                                    </td>
                                </tr>

                            </table>

                        </div>

                    </div>

                </div>

            </div>


            <!-- PEKERJAAN -->

            <div class="section">

                <div class="section-title">
                    03 &nbsp; DETAIL PEKERJAAN MAINTENANCE
                </div>

                <div class="section-body">

                    <div class="action-grid">

                        <div class="action-box">

                            <span class="label">
                                Tindakan Maintenance
                            </span>

                            <div class="action-content">
                                <?= nl2br(
                                    e($maintenance['tindakan'] ?? '-')
                                ) ?>
                            </div>

                        </div>


                        <div class="action-box">

                            <span class="label">
                                Sparepart Diganti
                            </span>

                            <div class="action-content">
                                <?= nl2br(
                                    e($maintenance['sparepart'] ?? '-')
                                ) ?>
                            </div>

                        </div>

                    </div>


                    <div style="margin-top:10px;">

                        <div class="result-box">

                            <div class="result-title">
                                Catatan Maintenance
                            </div>

                            <div class="result-text">
                                <?= nl2br(
                                    e($maintenance['catatan'] ?? '-')
                                ) ?>
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- STATUS -->

            <div class="section">

                <div class="section-title">
                    04 &nbsp; INFORMASI PELAKSANAAN
                </div>

                <div class="section-body">

                    <div class="work-grid">

                        <div class="work-box">

                            <span class="work-label">
                                Teknisi Pelaksana
                            </span>

                            <div class="work-value">
                                <?= valueOrDash(
                                    $maintenance['teknisi'] ?? ''
                                ) ?>
                            </div>

                        </div>


                        <div class="work-box">

                            <span class="work-label">
                                Jenis Maintenance
                            </span>

                            <div class="work-value">
                                <?= valueOrDash(
                                    $maintenance['jenis'] ?? ''
                                ) ?>
                            </div>

                        </div>


                        <div class="work-box">

                            <span class="work-label">
                                Status Pekerjaan
                            </span>

                            <div class="work-value">

                                <span class="status <?= $statusClass ?>">
                                    <?= strtoupper(
                                        e($maintenance['status'] ?? '-')
                                    ) ?>
                                </span>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- FOTO -->

            <div class="section">

                <div class="section-title">
                    05 &nbsp; DOKUMENTASI MAINTENANCE
                </div>

                <div class="section-body">

                    <?php

                    $fotoMaintenance = '';

                    if (
                        !empty($maintenance['rm_gambar']) &&
                        file_exists(
                            "../assets/img/komponen/" .
                            $maintenance['rm_gambar']
                        )
                    ) {

                        $fotoMaintenance =
                            "../assets/img/komponen/" .
                            $maintenance['rm_gambar'];
                    }

                    ?>

                    <div class="photo-box">

                        <?php if (!empty($fotoMaintenance)) : ?>

                            <img
                                src="<?= e($fotoMaintenance) ?>"
                                alt="Dokumentasi Maintenance"
                            >

                        <?php else : ?>

                            <div class="no-photo">
                                Tidak ada foto dokumentasi yang diunggah.
                            </div>

                        <?php endif; ?>

                    </div>

                </div>

            </div>


            <!-- KESIMPULAN -->

            <div class="section">

                <div class="section-title">
                    06 &nbsp; HASIL / KESIMPULAN
                </div>

                <div class="section-body">

                    <div class="result-box">

                        <div class="result-title">
                            Hasil Maintenance
                        </div>

                        <div class="result-text">

                            <?php if ($status === 'selesai') : ?>

                                Pekerjaan maintenance telah selesai
                                dilaksanakan. Peralatan telah diperiksa
                                dan dinyatakan selesai sesuai hasil
                                pelaksanaan maintenance.

                            <?php elseif ($status === 'proses') : ?>

                                Pekerjaan maintenance masih dalam
                                proses pelaksanaan.

                            <?php elseif ($status === 'pending') : ?>

                                Pekerjaan maintenance masih berstatus
                                pending dan memerlukan tindak lanjut.

                            <?php else : ?>

                                Status pekerjaan belum ditentukan.

                            <?php endif; ?>

                        </div>

                    </div>

                </div>

            </div>


            <!-- APPROVAL -->

            <div class="signature-grid">

                <div class="signature-box">

                    <div class="signature-header">
                        Teknisi Pelaksana
                    </div>

                    <div class="signature-space"></div>

                    <div class="signature-name">
                        <?= valueOrDash(
                            $maintenance['teknisi'] ?? ''
                        ) ?>
                    </div>

                </div>


                <div class="signature-box">

                    <div class="signature-header">
                        Supervisor Maintenance
                    </div>

                    <div class="signature-space"></div>

                    <div class="signature-name">
                        ( ................................ )
                    </div>

                </div>


                <div class="signature-box">

                    <div class="signature-header">
                        Manager Maintenance
                    </div>

                    <div class="signature-space"></div>

                    <div class="signature-name">
                        ( ................................ )
                    </div>

                </div>

            </div>


            <div class="document-footer">

                <span>
                    Maintenance Management System
                </span>

                <span>
                    PT Garudafood Putra Putri Jaya Tbk
                </span>

                <span>
                    Dicetak:
                    <?= date('d/m/Y H:i') ?>
                </span>

            </div>

        <?php endif; ?>


    <!-- =====================================================
         SINGLE KOMPONEN
    ====================================================== -->

    <?php elseif ($jenis === 'single_komponen') : ?>

        <?php

        $komponen = $sql
            ? mysqli_fetch_assoc($sql)
            : null;

        ?>

        <?php if (!$komponen) : ?>

            <div style="
                text-align:center;
                padding:80px 20px;
            ">

                <h2>Data Tidak Ditemukan</h2>

                <p>
                    Data komponen yang diminta tidak ditemukan.
                </p>

                <button
                    class="no-print"
                    onclick="history.back()"
                >
                    Kembali
                </button>

            </div>

        <?php else : ?>

            <div class="company-header">

                <div>

                    <img
                        src="../assets/img/logo-garudafood.png"
                        class="logo"
                        alt="Garudafood"
                    >

                </div>

                <div class="company-title">

                    <h1 class="company-name">
                        PT GARUDAFOOD PUTRA PUTRI JAYA TBK
                    </h1>

                    <div class="document-title">
                        LAPORAN DATA KOMPONEN
                    </div>

                    <div class="document-subtitle">
                        Inventaris dan Spesifikasi Teknis Komponen
                    </div>

                </div>

                <div></div>

            </div>


            <div class="section">

                <div class="section-title">
                    IDENTITAS KOMPONEN
                </div>

                <div class="section-body">

                    <div class="equipment-grid">

                        <div class="equipment-item">

                            <span class="label">
                                Mesin
                            </span>

                            <div class="value">
                                <?= valueOrDash(
                                    $komponen['nama_mesin'] ?? ''
                                ) ?>
                            </div>

                        </div>


                        <div class="equipment-item">

                            <span class="label">
                                Serial Number Mesin
                            </span>

                            <div class="value">
                                <?= valueOrDash(
                                    $komponen['sn_mesin'] ?? ''
                                ) ?>
                            </div>

                        </div>


                        <div class="equipment-item">

                            <span class="label">
                                Sub Mesin
                            </span>

                            <div class="value">
                                <?= valueOrDash(
                                    $komponen['nama_sub_mesin'] ?? ''
                                ) ?>
                            </div>

                        </div>


                        <div class="equipment-item">

                            <span class="label">
                                Nama Komponen
                            </span>

                            <div class="value">
                                <?= valueOrDash(
                                    $komponen['nama_bagian'] ?? ''
                                ) ?>
                            </div>

                        </div>


                        <div class="equipment-item">

                            <span class="label">
                                Serial Number
                            </span>

                            <div class="value">
                                <?= valueOrDash(
                                    $komponen['serial_number'] ?? ''
                                ) ?>
                            </div>

                        </div>


                        <div class="equipment-item">

                            <span class="label">
                                Kondisi
                            </span>

                            <div class="value">
                                <?= valueOrDash(
                                    $komponen['kondisi'] ?? ''
                                ) ?>
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <div class="section">

                <div class="section-title">
                    SPESIFIKASI TEKNIS
                </div>

                <div class="section-body">

                    <table class="spec-table">

                        <tr>
                            <td>Brand / Merk</td>
                            <td><?= valueOrDash($komponen['brand'] ?? '') ?></td>
                        </tr>

                        <tr>
                            <td>Tipe</td>
                            <td><?= valueOrDash($komponen['tipe'] ?? '') ?></td>
                        </tr>

                        <tr>
                            <td>Part Number</td>
                            <td><?= valueOrDash($komponen['part_number'] ?? '') ?></td>
                        </tr>

                        <tr>
                            <td>Daya</td>
                            <td><?= valueOrDash($komponen['daya'] ?? '') ?></td>
                        </tr>

                        <tr>
                            <td>IO Address</td>
                            <td><?= valueOrDash($komponen['io_address'] ?? '') ?></td>
                        </tr>

                        <tr>
                            <td>Input Voltage</td>
                            <td><?= valueOrDash($komponen['input_voltage'] ?? '') ?></td>
                        </tr>

                        <tr>
                            <td>Frekuensi Input</td>
                            <td><?= valueOrDash($komponen['frekuensi_input'] ?? '') ?></td>
                        </tr>

                        <tr>
                            <td>Arus Input</td>
                            <td><?= valueOrDash($komponen['arus_input'] ?? '') ?></td>
                        </tr>

                        <tr>
                            <td>Output</td>
                            <td><?= valueOrDash($komponen['output'] ?? '') ?></td>
                        </tr>

                        <tr>
                            <td>Frekuensi Output</td>
                            <td><?= valueOrDash($komponen['frekuensi_output'] ?? '') ?></td>
                        </tr>

                        <tr>
                            <td>IP Rating</td>
                            <td><?= valueOrDash($komponen['ip_rating'] ?? '') ?></td>
                        </tr>

                        <tr>
                            <td>Lokasi</td>
                            <td><?= valueOrDash($komponen['lokasi'] ?? '') ?></td>
                        </tr>

                    </table>

                </div>

            </div>


            <div class="signature-grid">

                <div></div>

                <div></div>

                <div class="signature-box">

                    <div class="signature-header">
                        Supervisor / Manager Maintenance
                    </div>

                    <div class="signature-space"></div>

                    <div class="signature-name">
                        ( ................................ )
                    </div>

                </div>

            </div>

        <?php endif; ?>


    <!-- =====================================================
         LAPORAN MAINTENANCE / INVENTARIS KOMPONEN
    ====================================================== -->

    <?php else : ?>

        <div class="company-header">

            <div>

                <img
                    src="../assets/img/logo-garudafood.png"
                    class="logo"
                    alt="Garudafood"
                >

            </div>

            <div class="company-title">

                <h1 class="company-name">
                    PT GARUDAFOOD PUTRA PUTRI JAYA TBK
                </h1>

                <div class="document-title">
                    <?= e($judul) ?>
                </div>

                <div class="document-subtitle">

                    <?php if ($jenis === 'maintenance') : ?>

                        Periode
                        <?= formatTanggal($tgl_mulai) ?>
                        s/d
                        <?= formatTanggal($tgl_selesai) ?>

                    <?php else : ?>

                        Rekapitulasi data inventaris mesin dan komponen

                    <?php endif; ?>

                </div>

            </div>

            <div></div>

        </div>


        <table class="inventory-table">

            <thead>

                <?php if ($jenis === 'maintenance') : ?>

                    <tr>

                        <th width="30">
                            No
                        </th>

                        <th width="75">
                            Tanggal
                        </th>

                        <th>
                            Mesin
                        </th>

                        <th>
                            Sub Mesin
                        </th>

                        <th>
                            Komponen
                        </th>

                        <th>
                            Tindakan
                        </th>

                        <th>
                            Teknisi
                        </th>

                        <th>
                            Jenis
                        </th>

                        <th>
                            Status
                        </th>

                    </tr>

                <?php else : ?>

                    <tr>

                        <th width="30">
                            No
                        </th>

                        <th>
                            Mesin
                        </th>

                        <th>
                            Sub Mesin
                        </th>

                        <th>
                            Komponen
                        </th>

                        <th>
                            Serial Number
                        </th>

                        <th>
                            Brand
                        </th>

                        <th>
                            Tipe
                        </th>

                        <th>
                            Part Number
                        </th>

                        <th>
                            Kondisi
                        </th>

                    </tr>

                <?php endif; ?>

            </thead>

            <tbody>

                <?php

                $no = 1;

                ?>

                <?php if ($sql && mysqli_num_rows($sql) > 0) : ?>

                    <?php while ($d = mysqli_fetch_assoc($sql)) : ?>

                        <?php if ($jenis === 'maintenance') : ?>

                            <tr>

                                <td class="text-center">
                                    <?= $no++ ?>
                                </td>

                                <td>
                                    <?= formatTanggal(
                                        $d['tanggal'] ?? ''
                                    ) ?>
                                </td>

                                <td>
                                    <strong>
                                        <?= valueOrDash(
                                            $d['nama_mesin'] ?? ''
                                        ) ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= valueOrDash(
                                        $d['nama_sub_mesin'] ?? ''
                                    ) ?>
                                </td>

                                <td>

                                    <strong>
                                        <?= valueOrDash(
                                            $d['nama_bagian'] ?? ''
                                        ) ?>
                                    </strong>

                                    <br>

                                    SN:
                                    <?= valueOrDash(
                                        $d['sn_komponen'] ?? ''
                                    ) ?>

                                </td>

                                <td>
                                    <?= nl2br(
                                        e($d['tindakan'] ?? '-')
                                    ) ?>
                                </td>

                                <td>
                                    <?= valueOrDash(
                                        $d['teknisi'] ?? ''
                                    ) ?>
                                </td>

                                <td>
                                    <?= valueOrDash(
                                        $d['jenis'] ?? ''
                                    ) ?>
                                </td>

                                <td class="text-center">

                                    <span class="status
                                        <?php
                                        $st = strtolower(
                                            trim(
                                                $d['status'] ?? ''
                                            )
                                        );

                                        if ($st === 'selesai') {
                                            echo 'status-selesai';
                                        } elseif ($st === 'proses') {
                                            echo 'status-proses';
                                        } elseif ($st === 'pending') {
                                            echo 'status-pending';
                                        } else {
                                            echo 'status-default';
                                        }
                                        ?>
                                    ">

                                        <?= strtoupper(
                                            e($d['status'] ?? '-')
                                        ) ?>

                                    </span>

                                </td>

                            </tr>

                        <?php else : ?>

                            <tr>

                                <td class="text-center">
                                    <?= $no++ ?>
                                </td>

                                <td>

                                    <strong>
                                        <?= valueOrDash(
                                            $d['nama_mesin'] ?? ''
                                        ) ?>
                                    </strong>

                                    <br>

                                    <small>
                                        SN Mesin:
                                        <?= valueOrDash(
                                            $d['sn_mesin'] ?? ''
                                        ) ?>
                                    </small>

                                </td>

                                <td>
                                    <?= valueOrDash(
                                        $d['nama_sub_mesin'] ?? ''
                                    ) ?>
                                </td>

                                <td>

                                    <strong>
                                        <?= valueOrDash(
                                            $d['nama_bagian'] ?? ''
                                        ) ?>
                                    </strong>

                                </td>

                                <td>
                                    <?= valueOrDash(
                                        $d['sn_komponen'] ?? ''
                                    ) ?>
                                </td>

                                <td>
                                    <?= valueOrDash(
                                        $d['brand'] ?? ''
                                    ) ?>
                                </td>

                                <td>
                                    <?= valueOrDash(
                                        $d['tipe'] ?? ''
                                    ) ?>
                                </td>

                                <td>
                                    <?= valueOrDash(
                                        $d['part_number'] ?? ''
                                    ) ?>
                                </td>

                                <td class="text-center">

                                    <?php

                                    $kondisi =
                                        strtolower(
                                            trim(
                                                $d['kondisi'] ?? ''
                                            )
                                        );

                                    $conditionClass =
                                        'condition';

                                    if ($kondisi === 'baik') {

                                        $conditionClass .=
                                            ' condition-baik';

                                    } elseif (
                                        $kondisi === 'perlu pemeriksaan'
                                    ) {

                                        $conditionClass .=
                                            ' condition-periksa';

                                    } elseif (
                                        $kondisi === 'dalam perbaikan'
                                    ) {

                                        $conditionClass .=
                                            ' condition-rusak';
                                    }

                                    ?>

                                    <span class="<?= $conditionClass ?>">

                                        <?= strtoupper(
                                            e(
                                                $d['kondisi']
                                                ?? '-'
                                            )
                                        ) ?>

                                    </span>

                                </td>

                            </tr>

                        <?php endif; ?>

                    <?php endwhile; ?>

                <?php else : ?>

                    <tr>

                        <td
                            colspan="<?= $jenis === 'maintenance' ? '9' : '9' ?>"
                            style="
                                text-align:center;
                                padding:25px;
                                color:#737e88;
                            "
                        >

                            Tidak ada data untuk ditampilkan.

                        </td>

                    </tr>

                <?php endif; ?>

            </tbody>

        </table>


        <div class="document-footer">

            <span>
                Maintenance Management System
            </span>

            <span>
                PT Garudafood Putra Putri Jaya Tbk
            </span>

            <span>
                Dicetak:
                <?= date('d/m/Y H:i') ?>
            </span>

        </div>

    <?php endif; ?>

</div>


<script>

function printDocument()
{
    window.print();
}

</script>

</body>

</html>

<?php

if (isset($stmt) && $stmt) {
    mysqli_stmt_close($stmt);
}

?>
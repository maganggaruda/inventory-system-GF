<?php

/* =========================================================
   MAINTENANCE DOWNLOAD PDF
   PT GARUDAFOOD PUTRA PUTRI JAYA Tbk
========================================================= */

require_once "../vendor/autoload.php";
include "../koneksi.php";

use Dompdf\Dompdf;
use Dompdf\Options;

date_default_timezone_set('Asia/Jakarta');


/* =========================================================
   PARAMETER
========================================================= */

$id = isset($_GET['id'])
    ? intval($_GET['id'])
    : 0;

if ($id <= 0) {
    die("ID maintenance tidak valid.");
}


/* =========================================================
   HELPER
========================================================= */

function e($value)
{
    return htmlspecialchars(
        (string)($value ?? '-'),
        ENT_QUOTES,
        'UTF-8'
    );
}


function nilai($value)
{
    $value = trim((string)($value ?? ''));

    return $value !== ''
        ? $value
        : '-';
}


function nilaiTerbaru($terbaru, $normal)
{
    $terbaru = trim((string)($terbaru ?? ''));

    if ($terbaru !== '') {
        return $terbaru;
    }

    return trim((string)($normal ?? ''));
}


function compareValue($normal, $terbaru)
{
    $a = strtolower(
        trim((string)($normal ?? ''))
    );

    $b = strtolower(
        trim((string)($terbaru ?? ''))
    );

    if ($a === '' && $b === '') {
        return 'empty';
    }

    if ($a === $b) {
        return 'same';
    }

    return 'changed';
}


/* =========================================================
   IMAGE TO DATA URI
========================================================= */

function imageToDataUri($path)
{
    if (
        !$path ||
        !file_exists($path) ||
        !is_file($path)
    ) {
        return '';
    }

    $type = mime_content_type($path);

    if (!$type) {
        $extension = strtolower(
            pathinfo($path, PATHINFO_EXTENSION)
        );

        $types = [
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp'
        ];

        $type = $types[$extension] ?? '';
    }

    if (!$type) {
        return '';
    }

    $data = file_get_contents($path);

    if ($data === false) {
        return '';
    }

    return 'data:' .
        $type .
        ';base64,' .
        base64_encode($data);
}


/* =========================================================
   TANGGAL INDONESIA
========================================================= */

function tanggalIndonesia($tanggal)
{
    if (empty($tanggal)) {
        return '-';
    }

    $bulan = [
        1  => 'Januari',
        2  => 'Februari',
        3  => 'Maret',
        4  => 'April',
        5  => 'Mei',
        6  => 'Juni',
        7  => 'Juli',
        8  => 'Agustus',
        9  => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];

    $time = strtotime($tanggal);

    if (!$time) {
        return '-';
    }

    return date('d', $time) . ' ' .
        $bulan[(int)date('n', $time)] . ' ' .
        date('Y', $time);
}


function tanggalJamIndonesia($tanggal)
{
    if (empty($tanggal)) {
        return '-';
    }

    $bulan = [
        1  => 'Januari',
        2  => 'Februari',
        3  => 'Maret',
        4  => 'April',
        5  => 'Mei',
        6  => 'Juni',
        7  => 'Juli',
        8  => 'Agustus',
        9  => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];

    $time = strtotime($tanggal);

    if (!$time) {
        return '-';
    }

    return date('d', $time) . ' ' .
        $bulan[(int)date('n', $time)] . ' ' .
        date('Y', $time) . ' ' .
        date('H:i', $time);
}


/* =========================================================
   1. AMBIL MAINTENANCE YANG DIPILIH
========================================================= */

$stmt = mysqli_prepare(
    $conn,
    "
    SELECT
        rm.id,
        rm.id_komponen,
        rm.tanggal,
        rm.jenis,
        rm.tindakan,
        rm.sparepart,
        rm.status,
        rm.teknisi,
        rm.catatan,
        rm.gambar

    FROM riwayat_maintenance rm

    WHERE rm.id = ?

    LIMIT 1
    "
);

if (!$stmt) {
    die(
        "Query maintenance gagal dibuat: " .
        e(mysqli_error($conn))
    );
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$current = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$current) {
    die("Data maintenance tidak ditemukan.");
}


$id_komponen = intval(
    $current['id_komponen']
);

if ($id_komponen <= 0) {
    die("Komponen dari maintenance tidak valid.");
}


/* =========================================================
   2. AMBIL DATA KOMPONEN
=========================================================

   PENTING:
   JENIS KOMPONEN DIAMBIL DARI:

       komponen.jenis_komponen

   BUKAN dari:
       riwayat_maintenance
========================================================= */

$stmt = mysqli_prepare(
    $conn,
    "
    SELECT

        k.id,
        k.serial_number,
        k.nama_bagian,

        /* JENIS KOMPONEN */
        k.jenis_komponen,

        k.brand,
        k.tipe,
        k.part_number,
        k.daya,
        k.io_address,
        k.input_voltage,
        k.frekuensi_input,
        k.arus_input,
        k.output,
        k.frekuensi_output,
        k.ip_rating,

        k.kondisi,
        k.gambar,
        k.lokasi,

        sm.nama_sub_mesin,

        m.nama_mesin,
        m.serial_number AS serial_number_mesin,
        m.lokasi AS lokasi_mesin,

        jm.nama_jenis_mesin,

        ab.nama_area,
        ab.lokasi AS lokasi_area

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

    LIMIT 1
    "
);

if (!$stmt) {
    die(
        "Query komponen gagal dibuat: " .
        e(mysqli_error($conn))
    );
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_komponen
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$normal = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$normal) {
    die("Data komponen tidak ditemukan.");
}


/* =========================================================
   3. AMBIL MAINTENANCE TERBARU
=========================================================

   TIDAK mengambil jenis_komponen dari sini.
   Jenis komponen tetap dari tabel komponen.
========================================================= */

$stmt = mysqli_prepare(
    $conn,
    "
    SELECT

        rm.id AS latest_id,
        rm.tanggal AS latest_tanggal,

        rm.serial_number AS latest_serial_number,
        rm.nama_bagian AS latest_nama_bagian,

        rm.brand AS latest_brand,
        rm.tipe AS latest_tipe,
        rm.part_number AS latest_part_number,
        rm.daya AS latest_daya,
        rm.io_address AS latest_io_address,
        rm.input_voltage AS latest_input_voltage,
        rm.frekuensi_input AS latest_frekuensi_input,
        rm.arus_input AS latest_arus_input,
        rm.output AS latest_output,
        rm.frekuensi_output AS latest_frekuensi_output,
        rm.ip_rating AS latest_ip_rating,

        rm.status AS latest_status,
        rm.teknisi AS latest_teknisi,
        rm.jenis AS latest_jenis,
        rm.tindakan AS latest_tindakan,
        rm.sparepart AS latest_sparepart,
        rm.catatan AS latest_catatan,
        rm.gambar AS latest_gambar

    FROM riwayat_maintenance rm

    WHERE rm.id_komponen = ?

    ORDER BY
        rm.tanggal DESC,
        rm.id DESC

    LIMIT 1
    "
);

if (!$stmt) {
    die(
        "Query maintenance terbaru gagal dibuat: " .
        e(mysqli_error($conn))
    );
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id_komponen
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$latest = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


if (!$latest) {
    $latest = [];
}


/* =========================================================
   DATA UTAMA
========================================================= */

$namaMesin = nilai(
    $normal['nama_mesin'] ?? ''
);

$namaSubMesin = nilai(
    $normal['nama_sub_mesin'] ?? ''
);

$namaKomponen = nilai(
    $normal['nama_bagian'] ?? ''
);

$serialKomponen = nilai(
    $normal['serial_number'] ?? ''
);


/*
 * JENIS KOMPONEN
 *
 * SUMBER:
 * tabel komponen
 * kolom jenis_komponen
 */

$jenisKomponen = nilai(
    $normal['jenis_komponen'] ?? ''
);


$lokasi = nilai(
    !empty($normal['lokasi'])
        ? $normal['lokasi']
        : (
            !empty($normal['lokasi_area'])
                ? $normal['lokasi_area']
                : (
                    $normal['lokasi_mesin'] ?? ''
                )
        )
);


$jenisMaintenance = nilai(
    $current['jenis'] ?? ''
);

$teknisi = nilai(
    $current['teknisi'] ?? ''
);

$status = nilai(
    $current['status'] ?? ''
);

$tanggalMaintenance = tanggalJamIndonesia(
    $current['tanggal'] ?? ''
);


/* =========================================================
   STATUS CLASS
========================================================= */

$statusLower = strtolower(
    trim($status)
);

if ($statusLower === 'selesai') {

    $statusClass = 'status-selesai';

} elseif (
    $statusLower === 'proses' ||
    $statusLower === 'dalam proses'
) {

    $statusClass = 'status-proses';

} else {

    $statusClass = 'status-pending';
}


/* =========================================================
   NOMOR MAINTENANCE ORDER
========================================================= */

$timestampMaintenance = strtotime(
    $current['tanggal'] ?? ''
);

if (!$timestampMaintenance) {
    $timestampMaintenance = time();
}

$nomorMO =
    'MO-' .
    date('Ymd', $timestampMaintenance) .
    '-' .
    str_pad(
        $current['id'],
        4,
        '0',
        STR_PAD_LEFT
    );


/* =========================================================
   DATA NORMAL
========================================================= */

$normal_serial_number = trim(
    (string)($normal['serial_number'] ?? '')
);

$normal_nama_bagian = trim(
    (string)($normal['nama_bagian'] ?? '')
);


/*
 * JENIS KOMPONEN
 *
 * LANGSUNG DARI:
 *
 * k.jenis_komponen
 */

$normal_jenis_komponen = trim(
    (string)($normal['jenis_komponen'] ?? '')
);


$normal_brand = trim(
    (string)($normal['brand'] ?? '')
);

$normal_tipe = trim(
    (string)($normal['tipe'] ?? '')
);

$normal_part_number = trim(
    (string)($normal['part_number'] ?? '')
);

$normal_daya = trim(
    (string)($normal['daya'] ?? '')
);

$normal_io_address = trim(
    (string)($normal['io_address'] ?? '')
);

$normal_input_voltage = trim(
    (string)($normal['input_voltage'] ?? '')
);

$normal_frekuensi_input = trim(
    (string)($normal['frekuensi_input'] ?? '')
);

$normal_arus_input = trim(
    (string)($normal['arus_input'] ?? '')
);

$normal_output = trim(
    (string)($normal['output'] ?? '')
);

$normal_frekuensi_output = trim(
    (string)($normal['frekuensi_output'] ?? '')
);

$normal_ip_rating = trim(
    (string)($normal['ip_rating'] ?? '')
);


/* =========================================================
   DATA TERBARU
========================================================= */

$terbaru_serial_number = nilaiTerbaru(
    $latest['latest_serial_number'] ?? '',
    $normal_serial_number
);

$terbaru_nama_bagian = nilaiTerbaru(
    $latest['latest_nama_bagian'] ?? '',
    $normal_nama_bagian
);


/*
 * JENIS KOMPONEN
 *
 * Tidak mengambil dari maintenance.
 * Tetap menggunakan data komponen.
 */

$terbaru_jenis_komponen =
    $normal_jenis_komponen;


$terbaru_brand = nilaiTerbaru(
    $latest['latest_brand'] ?? '',
    $normal_brand
);

$terbaru_tipe = nilaiTerbaru(
    $latest['latest_tipe'] ?? '',
    $normal_tipe
);

$terbaru_part_number = nilaiTerbaru(
    $latest['latest_part_number'] ?? '',
    $normal_part_number
);

$terbaru_daya = nilaiTerbaru(
    $latest['latest_daya'] ?? '',
    $normal_daya
);

$terbaru_io_address = nilaiTerbaru(
    $latest['latest_io_address'] ?? '',
    $normal_io_address
);

$terbaru_input_voltage = nilaiTerbaru(
    $latest['latest_input_voltage'] ?? '',
    $normal_input_voltage
);

$terbaru_frekuensi_input = nilaiTerbaru(
    $latest['latest_frekuensi_input'] ?? '',
    $normal_frekuensi_input
);

$terbaru_arus_input = nilaiTerbaru(
    $latest['latest_arus_input'] ?? '',
    $normal_arus_input
);

$terbaru_output = nilaiTerbaru(
    $latest['latest_output'] ?? '',
    $normal_output
);

$terbaru_frekuensi_output = nilaiTerbaru(
    $latest['latest_frekuensi_output'] ?? '',
    $normal_frekuensi_output
);

$terbaru_ip_rating = nilaiTerbaru(
    $latest['latest_ip_rating'] ?? '',
    $normal_ip_rating
);


/* =========================================================
   DATA SPESIFIKASI
========================================================= */

$spesifikasi = [

    [
        'nama' => 'Serial Number',
        'normal' => $normal_serial_number,
        'terbaru' => $terbaru_serial_number
    ],

    [
        'nama' => 'Nama Komponen',
        'normal' => $normal_nama_bagian,
        'terbaru' => $terbaru_nama_bagian
    ],

    [
        /*
         * JENIS KOMPONEN
         */
        'nama' => 'Jenis Komponen',
        'normal' => $normal_jenis_komponen,
        'terbaru' => $terbaru_jenis_komponen
    ],

    [
        'nama' => 'Brand / Merk',
        'normal' => $normal_brand,
        'terbaru' => $terbaru_brand
    ],

    [
        'nama' => 'Tipe',
        'normal' => $normal_tipe,
        'terbaru' => $terbaru_tipe
    ],

    [
        'nama' => 'Part Number',
        'normal' => $normal_part_number,
        'terbaru' => $terbaru_part_number
    ],

    [
        'nama' => 'Daya',
        'normal' => $normal_daya,
        'terbaru' => $terbaru_daya
    ],

    [
        'nama' => 'IO Address',
        'normal' => $normal_io_address,
        'terbaru' => $terbaru_io_address
    ],

    [
        'nama' => 'Input Voltage',
        'normal' => $normal_input_voltage,
        'terbaru' => $terbaru_input_voltage
    ],

    [
        'nama' => 'Frekuensi Input',
        'normal' => $normal_frekuensi_input,
        'terbaru' => $terbaru_frekuensi_input
    ],

    [
        'nama' => 'Arus Input',
        'normal' => $normal_arus_input,
        'terbaru' => $terbaru_arus_input
    ],

    [
        'nama' => 'Output',
        'normal' => $normal_output,
        'terbaru' => $terbaru_output
    ],

    [
        'nama' => 'Frekuensi Output',
        'normal' => $normal_frekuensi_output,
        'terbaru' => $terbaru_frekuensi_output
    ],

    [
        'nama' => 'IP Rating',
        'normal' => $normal_ip_rating,
        'terbaru' => $terbaru_ip_rating
    ]

];


/* =========================================================
   HITUNG PERUBAHAN
========================================================= */

$totalBerubah = 0;
$totalSama = 0;
$totalKosong = 0;

foreach ($spesifikasi as $spec) {

    $hasil = compareValue(
        $spec['normal'],
        $spec['terbaru']
    );

    if ($hasil === 'changed') {

        $totalBerubah++;

    } elseif ($hasil === 'same') {

        $totalSama++;

    } else {

        $totalKosong++;
    }
}


/* =========================================================
   KESIMPULAN
========================================================= */

if ($totalBerubah > 0) {

    $kesimpulan =
        "TERDAPAT PERUBAHAN SPESIFIKASI";

    $kesimpulanClass =
        "kesimpulan-berubah";

} else {

    $kesimpulan =
        "TIDAK TERDAPAT PERUBAHAN SPESIFIKASI";

    $kesimpulanClass =
        "kesimpulan-sama";
}


/* =========================================================
   FOTO
========================================================= */

$foto = '';

$baseDir = dirname(__DIR__);


/*
 * 1. FOTO MAINTENANCE YANG SEDANG DIBUKA
 */

if (
    !empty($current['gambar']) &&
    file_exists(
        $baseDir .
        "/uploads/maintenance/" .
        $current['gambar']
    )
) {

    $foto = imageToDataUri(
        $baseDir .
        "/uploads/maintenance/" .
        $current['gambar']
    );
}


/*
 * 2. FOTO MAINTENANCE TERBARU
 */

elseif (
    !empty($latest['latest_gambar']) &&
    file_exists(
        $baseDir .
        "/uploads/maintenance/" .
        $latest['latest_gambar']
    )
) {

    $foto = imageToDataUri(
        $baseDir .
        "/uploads/maintenance/" .
        $latest['latest_gambar']
    );
}


/*
 * 3. FOLDER LAMA MAINTENANCE
 */

elseif (
    !empty($current['gambar']) &&
    file_exists(
        $baseDir .
        "/assets/img/maintenance/" .
        $current['gambar']
    )
) {

    $foto = imageToDataUri(
        $baseDir .
        "/assets/img/maintenance/" .
        $current['gambar']
    );
}


/*
 * 4. FOLDER LAMA MAINTENANCE TERBARU
 */

elseif (
    !empty($latest['latest_gambar']) &&
    file_exists(
        $baseDir .
        "/assets/img/maintenance/" .
        $latest['latest_gambar']
    )
) {

    $foto = imageToDataUri(
        $baseDir .
        "/assets/img/maintenance/" .
        $latest['latest_gambar']
    );
}


/*
 * 5. FOTO KOMPONEN
 */

elseif (
    !empty($normal['gambar']) &&
    file_exists(
        $baseDir .
        "/uploads/komponen/" .
        $normal['gambar']
    )
) {

    $foto = imageToDataUri(
        $baseDir .
        "/uploads/komponen/" .
        $normal['gambar']
    );
}


/*
 * 6. FOLDER LAMA KOMPONEN
 */

elseif (
    !empty($normal['gambar']) &&
    file_exists(
        $baseDir .
        "/assets/img/komponen/" .
        $normal['gambar']
    )
) {

    $foto = imageToDataUri(
        $baseDir .
        "/assets/img/komponen/" .
        $normal['gambar']
    );
}


/* =========================================================
   LOGO
========================================================= */

$logoPath =
    $baseDir .
    "/assets/img/logo-garudafood.png";

$logo = '';

if (file_exists($logoPath)) {
    $logo = imageToDataUri($logoPath);
}


/* =========================================================
   TANGGAL CETAK
========================================================= */

$tanggalCetak =
    tanggalJamIndonesia(
        date('Y-m-d H:i:s')
    );


/* =========================================================
   HTML PDF
========================================================= */

$html = '
<!DOCTYPE html>
<html lang="id">

<head>

<meta charset="UTF-8">

<style>

@page {
    margin: 25px 30px 35px;
}

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 0;
    background: white;
    font-family: DejaVu Sans, Arial, sans-serif;
    color: #1f2933;
    font-size: 9px;
    line-height: 1.45;
}


/* =========================================================
   HEADER
========================================================= */

.header {
    width: 100%;
    border-bottom: 3px solid #07549b;
    padding-bottom: 10px;
    margin-bottom: 14px;
}

.header-table {
    width: 100%;
    border-collapse: collapse;
}

.logo-cell {
    width: 18%;
    vertical-align: middle;
}

.logo-cell img {
    max-width: 125px;
    max-height: 55px;
}

.company-cell {
    width: 62%;
    text-align: center;
    vertical-align: middle;
}

.company-title {
    font-size: 16px;
    font-weight: bold;
    color: #07549b;
}

.company-subtitle {
    font-size: 11px;
    font-weight: bold;
    margin-top: 3px;
}

.company-desc {
    font-size: 8px;
    color: #6b7280;
    margin-top: 3px;
}

.document-cell {
    width: 20%;
    text-align: right;
    vertical-align: middle;
}

.document-title {
    font-size: 11px;
    font-weight: bold;
    color: #07549b;
}

.document-number {
    font-size: 9px;
    font-weight: bold;
    margin-top: 3px;
}

.document-date {
    font-size: 7px;
    color: #687381;
    margin-top: 2px;
}


/* =========================================================
   SECTION
========================================================= */

.section {
    margin-top: 12px;
    page-break-inside: avoid;
}

.section-title {
    background: #07549b;
    color: white;
    font-size: 10px;
    font-weight: bold;
    padding: 7px 9px;
    text-transform: uppercase;
}


/* =========================================================
   INFO
========================================================= */

.info-grid {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #d7dee6;
    border-top: 0;
}

.info-item {
    width: 33.333%;
    padding: 7px 9px;
    border-right: 1px solid #d7dee6;
    border-bottom: 1px solid #d7dee6;
    vertical-align: top;
}

.info-item-last {
    border-right: 0;
}

.info-label {
    display: block;
    font-size: 7px;
    text-transform: uppercase;
    color: #6b7785;
    font-weight: bold;
    margin-bottom: 3px;
}

.info-value {
    font-size: 9px;
    font-weight: bold;
    color: #17212b;
}


/* =========================================================
   STATUS
========================================================= */

.status {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 7px;
    font-weight: bold;
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


/* =========================================================
   SUMMARY
========================================================= */

.summary-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 8px;
    margin-left: -8px;
}

.summary-box {
    border: 1px solid #d7dee6;
    padding: 9px;
    text-align: center;
    background: #f8fafc;
}

.summary-number {
    font-size: 18px;
    font-weight: bold;
    color: #07549b;
}

.summary-label {
    margin-top: 2px;
    font-size: 7px;
    color: #6b7280;
    font-weight: bold;
    text-transform: uppercase;
}


/* =========================================================
   KESIMPULAN
========================================================= */

.kesimpulan {
    margin-top: 8px;
    padding: 9px 11px;
    border-radius: 4px;
    font-size: 9px;
    font-weight: bold;
    text-align: center;
}

.kesimpulan-berubah {
    background: #fff1c7;
    border: 1px solid #e7c866;
    color: #795700;
}

.kesimpulan-sama {
    background: #e5f5eb;
    border: 1px solid #a9d9b8;
    color: #236b39;
}


/* =========================================================
   SPEC TABLE
========================================================= */

.spec-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.spec-table th {
    background: #eaf0f6;
    color: #1f2933;
    font-size: 7px;
    font-weight: bold;
    text-transform: uppercase;
    border: 1px solid #cbd5df;
    padding: 7px 6px;
    text-align: center;
}

.spec-table td {
    border: 1px solid #d5dde5;
    padding: 6px;
    vertical-align: middle;
}

.spec-name {
    width: 22%;
    font-weight: bold;
    background: #f7f9fb;
}

.spec-normal {
    width: 28%;
}

.spec-terbaru {
    width: 28%;
}

.spec-hasil {
    width: 22%;
    text-align: center;
}

.value-changed {
    background: #fff4d6;
    color: #7a5600;
    font-weight: bold;
}

.value-same {
    color: #26323d;
    background: white;
}

.value-empty {
    color: #8a94a0;
    background: #f8fafc;
}

.change-badge {
    display: inline-block;
    padding: 4px 7px;
    border-radius: 10px;
    font-size: 6px;
    font-weight: bold;
}

.badge-changed {
    background: #ffe6a3;
    color: #815900;
}

.badge-same {
    background: #dff3e6;
    color: #24713d;
}

.badge-empty {
    background: #edf0f3;
    color: #65717c;
}


/* =========================================================
   WORK
========================================================= */

.work-table {
    width: 100%;
    border-collapse: collapse;
}

.work-table td {
    width: 50%;
    border: 1px solid #d7dee6;
    padding: 9px 10px;
    vertical-align: top;
    min-height: 70px;
}

.work-label {
    display: block;
    font-size: 7px;
    text-transform: uppercase;
    font-weight: bold;
    color: #6c7782;
    margin-bottom: 6px;
}

.work-value {
    font-size: 8.5px;
    line-height: 1.55;
}


/* =========================================================
   PHOTO
========================================================= */

.photo-container {
    border: 1px solid #d7dee6;
    padding: 10px;
    text-align: center;
    background: #f8fafc;
}

.photo-container img {
    max-width: 100%;
    max-height: 250px;
}

.no-photo {
    color: #89939e;
    padding: 35px;
    font-size: 8px;
}


/* =========================================================
   SIGNATURE
========================================================= */

.signature-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 30px;
}

.signature-table td {
    width: 33.333%;
    text-align: center;
    vertical-align: top;
}

.signature-title {
    font-size: 7px;
    font-weight: bold;
    text-transform: uppercase;
    color: #5e6873;
}

.signature-space {
    height: 50px;
}

.signature-line {
    border-bottom: 1px solid #333;
    padding-bottom: 3px;
    margin: 0 20px;
    min-height: 16px;
    font-weight: bold;
    font-size: 8px;
}

.signature-role {
    font-size: 7px;
    color: #6b7280;
    margin-top: 3px;
}


/* =========================================================
   FOOTER
========================================================= */

.footer {
    margin-top: 20px;
    padding-top: 7px;
    border-top: 1px solid #d7dee6;
    width: 100%;
    font-size: 7px;
    color: #8a94a0;
}

.footer-table {
    width: 100%;
}

.footer-center {
    text-align: center;
}

.footer-right {
    text-align: right;
}

</style>

</head>

<body>


<!-- =====================================================
     HEADER
====================================================== -->

<div class="header">

<table class="header-table">

<tr>

<td class="logo-cell">
';

if ($logo) {

    $html .= '
        <img
            src="' . $logo . '"
            alt="Garudafood"
        >
    ';

} else {

    $html .= '
        <strong
            style="
                color:#07549b;
                font-size:14px;
            "
        >
            GARUDAFOOD
        </strong>
    ';
}

$html .= '

</td>


<td class="company-cell">

<div class="company-title">
    PT GARUDAFOOD PUTRA PUTRI JAYA Tbk
</div>

<div class="company-subtitle">
    MAINTENANCE MANAGEMENT SYSTEM
</div>

<div class="company-desc">
    Maintenance Order & Technical Maintenance Report
</div>

</td>


<td class="document-cell">

<div class="document-title">
    MAINTENANCE ORDER
</div>

<div class="document-number">
    ' . e($nomorMO) . '
</div>

<div class="document-date">
    ' . e($tanggalMaintenance) . '
</div>

</td>

</tr>

</table>

</div>


<!-- =====================================================
     01 INFORMASI MAINTENANCE
====================================================== -->

<div class="section">

<div class="section-title">
    01 &nbsp; INFORMASI MAINTENANCE
</div>


<table class="info-grid">

<tr>

<td class="info-item">

<span class="info-label">
    Nomor Maintenance Order
</span>

<span class="info-value">
    ' . e($nomorMO) . '
</span>

</td>


<td class="info-item">

<span class="info-label">
    Tanggal Maintenance
</span>

<span class="info-value">
    ' . e($tanggalMaintenance) . '
</span>

</td>


<td class="info-item info-item-last">

<span class="info-label">
    Status
</span>

<span class="info-value">

<span class="status ' . $statusClass . '">
    ' . e(strtoupper($status)) . '
</span>

</span>

</td>

</tr>


<tr>

<td class="info-item">

<span class="info-label">
    Jenis Maintenance
</span>

<span class="info-value">
    ' . e($jenisMaintenance) . '
</span>

</td>


<td class="info-item">

<span class="info-label">
    Teknisi
</span>

<span class="info-value">
    ' . e($teknisi) . '
</span>

</td>


<td class="info-item info-item-last">

<span class="info-label">
    Lokasi
</span>

<span class="info-value">
    ' . e($lokasi) . '
</span>

</td>

</tr>

</table>

</div>


<!-- =====================================================
     02 IDENTITAS MESIN / PERALATAN
====================================================== -->

<div class="section">

<div class="section-title">
    02 &nbsp; IDENTITAS MESIN / PERALATAN
</div>


<table class="info-grid">

<tr>

<td class="info-item">

<span class="info-label">
    Jenis Mesin
</span>

<span class="info-value">
    ' . e(
        $normal['nama_jenis_mesin'] ?? '-'
    ) . '
</span>

</td>


<td class="info-item">

<span class="info-label">
    Mesin Induk
</span>

<span class="info-value">
    ' . e($namaMesin) . '
</span>

</td>


<td class="info-item info-item-last">

<span class="info-label">
    Serial Number Mesin
</span>

<span class="info-value">
    ' . e(
        $normal['serial_number_mesin'] ?? '-'
    ) . '
</span>

</td>

</tr>


<tr>

<td class="info-item">

<span class="info-label">
    Sub Mesin
</span>

<span class="info-value">
    ' . e($namaSubMesin) . '
</span>

</td>


<td class="info-item">

<span class="info-label">
    Komponen
</span>

<span class="info-value">
    ' . e($namaKomponen) . '
</span>

</td>


<td class="info-item info-item-last">

<span class="info-label">
    Jenis Komponen
</span>

<span class="info-value">
    ' . e(
        $jenisKomponen
    ) . '
</span>

</td>

</tr>


<tr>

<td class="info-item">

<span class="info-label">
    Serial Number Komponen
</span>

<span class="info-value">
    ' . e($serialKomponen) . '
</span>

</td>


<td class="info-item">

<span class="info-label">
    Area
</span>

<span class="info-value">
    ' . e(
        $normal['nama_area'] ?? '-'
    ) . '
</span>

</td>


<td class="info-item info-item-last">

<span class="info-label">
    Lokasi
</span>

<span class="info-value">
    ' . e($lokasi) . '
</span>

</td>

</tr>

</table>

</div>


<!-- =====================================================
     03 RINGKASAN
====================================================== -->

<div class="section">

<div class="section-title">
    03 &nbsp; RINGKASAN HASIL PEMERIKSAAN SPESIFIKASI
</div>


<table class="summary-table">

<tr>

<td class="summary-box">

<div class="summary-number">
    ' . $totalBerubah . '
</div>

<div class="summary-label">
    Parameter Berubah
</div>

</td>


<td class="summary-box">

<div class="summary-number">
    ' . $totalSama . '
</div>

<div class="summary-label">
    Parameter Tidak Berubah
</div>

</td>


<td class="summary-box">

<div class="summary-number">
    ' . count($spesifikasi) . '
</div>

<div class="summary-label">
    Total Parameter
</div>

</td>

</tr>

</table>


<div class="kesimpulan ' . $kesimpulanClass . '">

' . e($kesimpulan) . '

';

if ($totalBerubah > 0) {

    $html .= '
        &nbsp; —
        ' . $totalBerubah . '
        parameter mengalami perubahan
        setelah maintenance.
    ';

} else {

    $html .= '
        &nbsp; —
        seluruh parameter yang tercatat
        tetap sama setelah maintenance.
    ';
}

$html .= '

</div>

</div>


<!-- =====================================================
     04 PERBANDINGAN SPESIFIKASI
====================================================== -->

<div class="section">

<div class="section-title">
    04 &nbsp; PERBANDINGAN SPESIFIKASI NORMAL DAN TERBARU
</div>


<table class="spec-table">

<thead>

<tr>

<th class="spec-name">
    Parameter
</th>

<th class="spec-normal">
    NORMAL / SEBELUM MAINTENANCE
</th>

<th class="spec-terbaru">
    TERBARU / SESUDAH MAINTENANCE
</th>

<th class="spec-hasil">
    HASIL PEMERIKSAAN
</th>

</tr>

</thead>


<tbody>
';


foreach ($spesifikasi as $spec) {

    $hasil = compareValue(
        $spec['normal'],
        $spec['terbaru']
    );

    $normalValue = nilai(
        $spec['normal']
    );

    $terbaruValue = nilai(
        $spec['terbaru']
    );


    if ($hasil === 'changed') {

        $valueClass = 'value-changed';

    } elseif ($hasil === 'empty') {

        $valueClass = 'value-empty';

    } else {

        $valueClass = 'value-same';
    }


    $html .= '

    <tr>

    <td class="spec-name">
        ' . e($spec['nama']) . '
    </td>

    <td class="' . $valueClass . '">
        ' . e($normalValue) . '
    </td>

    <td class="' . $valueClass . '">
        ' . e($terbaruValue) . '
    </td>

    <td class="spec-hasil">
    ';


    if ($hasil === 'changed') {

        $html .= '
            <span class="change-badge badge-changed">
                BERUBAH
            </span>
        ';

    } elseif ($hasil === 'same') {

        $html .= '
            <span class="change-badge badge-same">
                TIDAK BERUBAH
            </span>
        ';

    } else {

        $html .= '
            <span class="change-badge badge-empty">
                TIDAK ADA DATA
            </span>
        ';
    }


    $html .= '

    </td>

    </tr>

    ';
}


$html .= '

</tbody>

</table>

</div>


<!-- =====================================================
     05 HASIL MAINTENANCE
====================================================== -->

<div class="section">

<div class="section-title">
    05 &nbsp; HASIL / PEKERJAAN MAINTENANCE
</div>


<table class="work-table">

<tr>

<td>

<span class="work-label">
    Tindakan Maintenance
</span>

<div class="work-value">
';


if (!empty($current['tindakan'])) {

    $html .= nl2br(
        e($current['tindakan'])
    );

} else {

    $html .= '-';
}


$html .= '

</div>

</td>


<td>

<span class="work-label">
    Sparepart / Komponen Diganti
</span>

<div class="work-value">
';


if (!empty($current['sparepart'])) {

    $html .= nl2br(
        e($current['sparepart'])
    );

} else {

    $html .= '-';
}


$html .= '

</div>

</td>

</tr>


<tr>

<td colspan="2">

<span class="work-label">
    Catatan / Hasil Pemeriksaan
</span>

<div class="work-value">
';


if (!empty($current['catatan'])) {

    $html .= nl2br(
        e($current['catatan'])
    );

} else {

    $html .= 'Tidak ada catatan tambahan.';
}


$html .= '

</div>

</td>

</tr>

</table>

</div>


<!-- =====================================================
     06 DOKUMENTASI
====================================================== -->

<div class="section">

<div class="section-title">
    06 &nbsp; DOKUMENTASI MAINTENANCE
</div>


<div class="photo-container">
';


if ($foto) {

    $html .= '

        <img
            src="' . $foto . '"
            alt="Dokumentasi Maintenance"
        >

        <div
            style="
                margin-top:5px;
                color:#7b8794;
                font-size:7px;
            "
        >
            Dokumentasi pekerjaan maintenance
        </div>

    ';

} else {

    $html .= '

        <div class="no-photo">

            Tidak ada foto dokumentasi
            yang diunggah.

        </div>

    ';
}


$html .= '

</div>

</div>


<!-- =====================================================
     07 KESIMPULAN
====================================================== -->

<div class="section">

<div class="section-title">
    07 &nbsp; KESIMPULAN MAINTENANCE
</div>


<div
    class="kesimpulan ' . $kesimpulanClass . '"
    style="
        text-align:left;
        line-height:1.6;
    "
>

<strong>
    Hasil Maintenance:
</strong>

<br>
';


if ($totalBerubah > 0) {

    $html .= '

        Berdasarkan perbandingan data
        spesifikasi normal dengan data
        terbaru setelah maintenance,
        terdapat

        <strong>
            ' . $totalBerubah . '
            parameter
        </strong>

        yang mengalami perubahan.

        Perubahan tersebut dapat digunakan
        sebagai informasi kondisi aktual
        komponen setelah dilakukan pekerjaan
        maintenance.

    ';

} else {

    $html .= '

        Berdasarkan perbandingan data
        spesifikasi normal dengan data
        terbaru setelah maintenance,

        <strong>
            tidak ditemukan perubahan
            spesifikasi
        </strong>

        pada parameter yang tercatat.

    ';
}


$html .= '

</div>

</div>


<!-- =====================================================
     08 APPROVAL
====================================================== -->

<table class="signature-table">

<tr>

<td>

<div class="signature-title">
    DILAKSANAKAN OLEH
</div>

<div class="signature-space"></div>

<div class="signature-line">
    ' . e($teknisi) . '
</div>

<div class="signature-role">
    Teknisi Maintenance
</div>

</td>


<td>

<div class="signature-title">
    DIVERIFIKASI OLEH
</div>

<div class="signature-space"></div>

<div class="signature-line">
    &nbsp;
</div>

<div class="signature-role">
    Supervisor Maintenance
</div>

</td>


<td>

<div class="signature-title">
    DISETUJUI OLEH
</div>

<div class="signature-space"></div>

<div class="signature-line">
    &nbsp;
</div>

<div class="signature-role">
    Manager Maintenance
</div>

</td>

</tr>

</table>


<!-- =====================================================
     FOOTER
====================================================== -->

<div class="footer">

<table class="footer-table">

<tr>

<td>
    Maintenance Management System
</td>

<td class="footer-center">
    Dokumen: ' . e($nomorMO) . '
</td>

<td class="footer-right">
    Dicetak: ' . e($tanggalCetak) . '
</td>

</tr>

</table>

</div>


</body>

</html>
';


/* =========================================================
   DOMPDF
========================================================= */

$options = new Options();

$options->set(
    'isHtml5ParserEnabled',
    true
);

$options->set(
    'isRemoteEnabled',
    true
);

$options->set(
    'defaultFont',
    'DejaVu Sans'
);


$dompdf = new Dompdf($options);

$dompdf->loadHtml($html);

$dompdf->setPaper(
    'A4',
    'landscape'
);

$dompdf->render();


/* =========================================================
   NAMA FILE
========================================================= */

$namaFile =
    'Maintenance_Order_' .
    preg_replace(
        '/[^A-Za-z0-9_-]/',
        '_',
        $nomorMO
    ) .
    '.pdf';


/* =========================================================
   DOWNLOAD LANGSUNG
========================================================= */

$dompdf->stream(
    $namaFile,
    [
        'Attachment' => true
    ]
);

exit;
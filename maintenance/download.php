<?php
include "../koneksi.php";

/*
|--------------------------------------------------------------------------
| LAPORAN MAINTENANCE / MAINTENANCE ORDER
|--------------------------------------------------------------------------
| KONSEP DATA:
|
| NORMAL  = DATA AWAL DARI TABEL komponen
| TERBARU = DATA HASIL MAINTENANCE DARI tabel riwayat_maintenance
|
| User TIDAK perlu mengisi data normal lagi.
| Data normal otomatis mengikuti data komponen.
|--------------------------------------------------------------------------
*/


/* =========================================================
   PARAMETER
========================================================= */

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

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

    return $value !== '' ? $value : '-';
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
   BANDINGKAN NILAI
========================================================= */

function compareValue($normal, $terbaru)
{
    $a = strtolower(
        trim(
            (string)($normal ?? '')
        )
    );

    $b = strtolower(
        trim(
            (string)($terbaru ?? '')
        )
    );


    /*
     * Dua-duanya kosong
     */
    if ($a === '' && $b === '') {
        return 'empty';
    }


    /*
     * Sama
     */
    if ($a === $b) {
        return 'same';
    }


    /*
     * Berbeda
     */
    return 'changed';
}


/* =========================================================
   AMBIL DATA MAINTENANCE
========================================================= */

/*
|--------------------------------------------------------------------------
| NORMAL
|--------------------------------------------------------------------------
| SEMUA DATA NORMAL DIAMBIL DARI:
|
|       tabel komponen
|
|--------------------------------------------------------------------------
| TERBARU
|--------------------------------------------------------------------------
| SEMUA DATA TERBARU DIAMBIL DARI:
|
|       tabel riwayat_maintenance
|
|--------------------------------------------------------------------------
| JADI:
|
| k.brand  = normal
| rm.brand = terbaru
|
| k.tipe   = normal
| rm.tipe  = terbaru
|
| dst...
|--------------------------------------------------------------------------
*/

$sql = "

    SELECT

        /* =====================================================
           DATA MAINTENANCE
        ===================================================== */

        rm.id,
        rm.id_komponen,
        rm.tanggal,
        rm.jenis,
        rm.tindakan,
        rm.sparepart,
        rm.status,
        rm.teknisi,
        rm.catatan,

        /* FOTO MAINTENANCE */
        rm.gambar AS gambar_maintenance,


        /* =====================================================
           DATA TERBARU / HASIL MAINTENANCE
        ===================================================== */

        rm.serial_number
            AS terbaru_serial_number,

        rm.nama_bagian
            AS terbaru_nama_bagian,

        rm.kategori
            AS terbaru_kategori,

        rm.brand
            AS terbaru_brand,

        rm.tipe
            AS terbaru_tipe,

        rm.part_number
            AS terbaru_part_number,

        rm.daya
            AS terbaru_daya,

        rm.io_address
            AS terbaru_io_address,

        rm.input_voltage
            AS terbaru_input_voltage,

        rm.frekuensi_input
            AS terbaru_frekuensi_input,

        rm.arus_input
            AS terbaru_arus_input,

        rm.output
            AS terbaru_output,

        rm.frekuensi_output
            AS terbaru_frekuensi_output,

        rm.ip_rating
            AS terbaru_ip_rating,


        /* =====================================================
           DATA NORMAL / DATA AWAL KOMPONEN
        ===================================================== */

        k.serial_number
            AS normal_serial_number,

        k.nama_bagian
            AS normal_nama_bagian,

        k.kategori
            AS normal_kategori,

        k.brand
            AS normal_brand,

        k.tipe
            AS normal_tipe,

        k.part_number
            AS normal_part_number,

        k.daya
            AS normal_daya,

        k.io_address
            AS normal_io_address,

        k.input_voltage
            AS normal_input_voltage,

        k.frekuensi_input
            AS normal_frekuensi_input,

        k.arus_input
            AS normal_arus_input,

        k.output
            AS normal_output,

        k.frekuensi_output
            AS normal_frekuensi_output,

        k.ip_rating
            AS normal_ip_rating,

        k.kondisi
            AS normal_kondisi,

        k.gambar
            AS gambar_komponen,

        k.lokasi
            AS lokasi_komponen,


        /* =====================================================
           SUB MESIN
        ===================================================== */

        sm.nama_sub_mesin,


        /* =====================================================
           MESIN
        ===================================================== */

        m.nama_mesin,

        m.serial_number
            AS serial_number_mesin,

        m.lokasi
            AS lokasi_mesin,


        /* =====================================================
           JENIS MESIN
        ===================================================== */

        jm.nama_jenis_mesin


    FROM riwayat_maintenance rm


    LEFT JOIN komponen k
        ON rm.id_komponen = k.id


    LEFT JOIN sub_mesin sm
        ON k.id_sub_mesin = sm.id


    LEFT JOIN mesin m
        ON sm.id_mesin = m.id


    LEFT JOIN jenis_mesin jm
        ON m.id_jenis_mesin = jm.id


    WHERE rm.id = ?

    LIMIT 1
";


$stmt = mysqli_prepare(
    $conn,
    $sql
);


if (!$stmt) {

    die(
        "Query gagal dibuat: " .
        e(mysqli_error($conn))
    );
}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);


mysqli_stmt_execute($stmt);


$result =
    mysqli_stmt_get_result($stmt);


$d =
    mysqli_fetch_assoc($result);


mysqli_stmt_close($stmt);


if (!$d) {

    die(
        "Data maintenance tidak ditemukan."
    );
}


/* =========================================================
   NOMOR MAINTENANCE ORDER
========================================================= */

$nomorMO =
    'MO-' .
    date(
        'Ymd',
        strtotime($d['tanggal'])
    ) .
    '-' .
    str_pad(
        $d['id'],
        4,
        '0',
        STR_PAD_LEFT
    );


/* =========================================================
   DATA UTAMA
========================================================= */

$namaMesin =
    nilai(
        $d['nama_mesin']
    );


$namaSubMesin =
    nilai(
        $d['nama_sub_mesin']
    );


/*
 * Nama komponen NORMAL
 * diambil dari tabel komponen
 */
$namaKomponen =
    nilai(
        $d['normal_nama_bagian']
    );


/*
 * Serial number NORMAL
 * diambil dari tabel komponen
 */
$serialKomponen =
    nilai(
        $d['normal_serial_number']
    );


$lokasi =
    nilai(
        !empty(
            $d['lokasi_komponen']
        )
            ? $d['lokasi_komponen']
            : $d['lokasi_mesin']
    );


$jenisMaintenance =
    nilai(
        $d['jenis']
    );


$teknisi =
    nilai(
        $d['teknisi']
    );


$status =
    nilai(
        $d['status']
    );


$tanggalMaintenance =
    tanggalJamIndonesia(
        $d['tanggal']
    );


/* =========================================================
   STATUS
========================================================= */

$statusLower =
    strtolower(
        trim($status)
    );


if ($statusLower === 'selesai') {

    $statusClass =
        'status-selesai';

} elseif ($statusLower === 'proses') {

    $statusClass =
        'status-proses';

} else {

    $statusClass =
        'status-pending';
}


/* =========================================================
   FOTO
========================================================= */

$foto = '';


/*
|--------------------------------------------------------------------------
| PRIORITAS:
|
| 1. Foto maintenance
| 2. Foto komponen
|--------------------------------------------------------------------------
*/


if (
    !empty(
        $d['gambar_maintenance']
    )
    &&
    file_exists(
        "../uploads/maintenance/" .
        $d['gambar_maintenance']
    )
) {

    $foto =
        "../uploads/maintenance/" .
        $d['gambar_maintenance'];
}


/*
 * Folder maintenance lama
 */

elseif (
    !empty(
        $d['gambar_maintenance']
    )
    &&
    file_exists(
        "../assets/img/maintenance/" .
        $d['gambar_maintenance']
    )
) {

    $foto =
        "../assets/img/maintenance/" .
        $d['gambar_maintenance'];
}


/*
 * Foto komponen
 */

elseif (
    !empty(
        $d['gambar_komponen']
    )
    &&
    file_exists(
        "../uploads/komponen/" .
        $d['gambar_komponen']
    )
) {

    $foto =
        "../uploads/komponen/" .
        $d['gambar_komponen'];
}


/*
 * Folder komponen lama
 */

elseif (
    !empty(
        $d['gambar_komponen']
    )
    &&
    file_exists(
        "../assets/img/komponen/" .
        $d['gambar_komponen']
    )
) {

    $foto =
        "../assets/img/komponen/" .
        $d['gambar_komponen'];
}


/* =========================================================
   DATA SPESIFIKASI
========================================================= */

/*
|--------------------------------------------------------------------------
| NORMAL = tabel komponen
|
| TERBARU = tabel riwayat_maintenance
|--------------------------------------------------------------------------
*/

$spesifikasi = [

    [
        'nama' =>
            'Serial Number',

        'normal' =>
            $d['normal_serial_number'],

        'terbaru' =>
            $d['terbaru_serial_number']
    ],

    [
        'nama' =>
            'Nama Komponen',

        'normal' =>
            $d['normal_nama_bagian'],

        'terbaru' =>
            $d['terbaru_nama_bagian']
    ],

    [
        'nama' =>
            'Kategori',

        'normal' =>
            $d['normal_kategori'],

        'terbaru' =>
            $d['terbaru_kategori']
    ],

    [
        'nama' =>
            'Brand / Merk',

        'normal' =>
            $d['normal_brand'],

        'terbaru' =>
            $d['terbaru_brand']
    ],

    [
        'nama' =>
            'Tipe',

        'normal' =>
            $d['normal_tipe'],

        'terbaru' =>
            $d['terbaru_tipe']
    ],

    [
        'nama' =>
            'Part Number',

        'normal' =>
            $d['normal_part_number'],

        'terbaru' =>
            $d['terbaru_part_number']
    ],

    [
        'nama' =>
            'Daya',

        'normal' =>
            $d['normal_daya'],

        'terbaru' =>
            $d['terbaru_daya']
    ],

    [
        'nama' =>
            'IO Address',

        'normal' =>
            $d['normal_io_address'],

        'terbaru' =>
            $d['terbaru_io_address']
    ],

    [
        'nama' =>
            'Input Voltage',

        'normal' =>
            $d['normal_input_voltage'],

        'terbaru' =>
            $d['terbaru_input_voltage']
    ],

    [
        'nama' =>
            'Frekuensi Input',

        'normal' =>
            $d['normal_frekuensi_input'],

        'terbaru' =>
            $d['terbaru_frekuensi_input']
    ],

    [
        'nama' =>
            'Arus Input',

        'normal' =>
            $d['normal_arus_input'],

        'terbaru' =>
            $d['terbaru_arus_input']
    ],

    [
        'nama' =>
            'Output',

        'normal' =>
            $d['normal_output'],

        'terbaru' =>
            $d['terbaru_output']
    ],

    [
        'nama' =>
            'Frekuensi Output',

        'normal' =>
            $d['normal_frekuensi_output'],

        'terbaru' =>
            $d['terbaru_frekuensi_output']
    ],

    [
        'nama' =>
            'IP Rating',

        'normal' =>
            $d['normal_ip_rating'],

        'terbaru' =>
            $d['terbaru_ip_rating']
    ],

    [
        'nama' =>
            'Kondisi',

        'normal' =>
            $d['normal_kondisi'],

        /*
         * Kondisi terbaru TIDAK diambil
         * dari k.kondisi karena itu adalah
         * data normal.
         *
         * Kalau riwayat_maintenance belum
         * mempunyai kolom kondisi, kita
         * gunakan hasil status maintenance.
         */
        'terbaru' =>
            (
                !empty($d['status'])
                    ? $d['status']
                    : $d['normal_kondisi']
            )
    ]

];


/* =========================================================
   HITUNG PERUBAHAN
========================================================= */

$totalBerubah = 0;

$totalSama = 0;

$totalKosong = 0;


foreach (
    $spesifikasi
    as $spec
) {

    $hasil =
        compareValue(
            $spec['normal'],
            $spec['terbaru']
        );


    if (
        $hasil === 'changed'
    ) {

        $totalBerubah++;

    } elseif (
        $hasil === 'same'
    ) {

        $totalSama++;

    } else {

        $totalKosong++;
    }
}


/* =========================================================
   KESIMPULAN
========================================================= */

if (
    $totalBerubah > 0
) {

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
   TANGGAL CETAK
========================================================= */

$tanggalCetak =
    tanggalJamIndonesia(
        date(
            'Y-m-d H:i:s'
        )
    );

?>
<!DOCTYPE html>

<html lang="id">

<head>

<meta charset="UTF-8">

<title>
    Maintenance Order <?= e($nomorMO) ?>
</title>

<style>

/* =========================================================
   GLOBAL
========================================================= */

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    padding: 20px;

    background: #edf1f5;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    color: #1f2933;

    font-size: 10px;

    line-height: 1.45;
}


/* =========================================================
   PAGE
========================================================= */

.page {

    width: 1120px;

    min-height: 1120px;

    margin: auto;

    background: white;

    padding:
        24px
        28px
        35px;

    box-shadow:
        0 5px 25px
        rgba(0,0,0,.10);
}


/* =========================================================
   HEADER
========================================================= */

.header {

    display: flex;

    align-items: center;

    border-bottom:
        3px solid #07549b;

    padding-bottom: 12px;

    margin-bottom: 15px;
}


.logo {

    width: 160px;

    min-width: 160px;
}


.logo img {

    max-width: 125px;

    max-height: 65px;

    object-fit: contain;
}


.company {

    flex: 1;

    text-align: center;
}


.company h1 {

    margin: 0;

    font-size: 18px;

    color: #07549b;

    font-weight: 800;
}


.company h2 {

    margin:
        3px 0 0;

    font-size: 13px;

    color: #1f2933;

    font-weight: 800;
}


.company p {

    margin:
        3px 0 0;

    color: #6b7280;

    font-size: 9px;
}


.header-right {

    width: 190px;

    min-width: 190px;

    text-align: right;
}


.document-title {

    font-size: 14px;

    font-weight: 800;

    color: #07549b;
}


.document-number {

    font-size: 11px;

    font-weight: 800;

    margin-top: 4px;
}


.document-date {

    font-size: 9px;

    color: #687381;

    margin-top: 2px;
}


/* =========================================================
   SECTION
========================================================= */

.section {

    margin-top: 14px;

    page-break-inside: avoid;
}


.section-title {

    background: #07549b;

    color: white;

    font-size: 11px;

    font-weight: 800;

    padding:
        7px
        10px;

    text-transform: uppercase;

    letter-spacing: .3px;

    border-radius:
        3px
        3px
        0
        0;
}


/* =========================================================
   IDENTITAS
========================================================= */

.info-grid {

    display: grid;

    grid-template-columns:
        1fr
        1fr
        1fr;

    border:
        1px solid #d7dee6;

    border-top: 0;
}


.info-item {

    min-height: 48px;

    padding:
        7px
        10px;

    border-right:
        1px solid #d7dee6;

    border-bottom:
        1px solid #d7dee6;
}


.info-item:nth-child(3n) {

    border-right: 0;
}


.info-label {

    display: block;

    font-size: 8px;

    text-transform: uppercase;

    color: #6b7785;

    font-weight: 700;

    margin-bottom: 3px;
}


.info-value {

    font-size: 10.5px;

    font-weight: 700;

    color: #17212b;
}


/* =========================================================
   STATUS
========================================================= */

.status {

    display: inline-block;

    min-height: 24px;

    padding:
        5px
        12px;

    border-radius: 15px;

    font-size: 8px;

    font-weight: 800;
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

.summary-grid {

    display: grid;

    grid-template-columns:
        1fr
        1fr
        1fr;

    gap: 10px;

    margin-top: 10px;
}


.summary-box {

    border:
        1px solid #d7dee6;

    padding: 10px;

    text-align: center;

    background: #f8fafc;

    border-radius: 4px;
}


.summary-number {

    font-size: 20px;

    font-weight: 800;

    color: #07549b;
}


.summary-label {

    margin-top: 2px;

    font-size: 8px;

    color: #6b7280;

    font-weight: 700;

    text-transform: uppercase;
}


/* =========================================================
   SPESIFIKASI TABLE
========================================================= */

.spec-table {

    width: 100%;

    border-collapse: collapse;

    table-layout: fixed;
}


.spec-table th {

    background: #eaf0f6;

    color: #1f2933;

    font-size: 9px;

    font-weight: 800;

    text-transform: uppercase;

    border:
        1px solid #cbd5df;

    padding: 8px;

    text-align: center;
}


.spec-table td {

    border:
        1px solid #d5dde5;

    padding:
        7px
        8px;

    vertical-align: middle;
}


.spec-name {

    width: 22%;

    font-weight: 800;

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


/* =========================================================
   PERUBAHAN
========================================================= */

.value-changed {

    background: #fff4d6 !important;

    color: #7a5600;

    font-weight: 800;
}


.value-same {

    color: #26323d;

    background: #ffffff;
}


.value-empty {

    color: #8a94a0;

    background: #f8fafc;
}


.change-badge {

    display: inline-block;

    padding:
        4px
        8px;

    border-radius: 12px;

    font-size: 7px;

    font-weight: 800;

    text-transform: uppercase;
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
   KESIMPULAN
========================================================= */

.kesimpulan {

    margin-top: 10px;

    padding:
        10px
        12px;

    border-radius: 4px;

    font-size: 10px;

    font-weight: 800;

    text-align: center;
}


.kesimpulan-berubah {

    background: #fff1c7;

    border:
        1px solid #e7c866;

    color: #795700;
}


.kesimpulan-sama {

    background: #e5f5eb;

    border:
        1px solid #a9d9b8;

    color: #236b39;
}


/* =========================================================
   HASIL PEKERJAAN
========================================================= */

.work-grid {

    display: grid;

    grid-template-columns:
        1fr
        1fr;

    border:
        1px solid #d7dee6;

    border-top: 0;
}


.work-box {

    min-height: 90px;

    padding:
        10px
        12px;

    border-right:
        1px solid #d7dee6;

    border-bottom:
        1px solid #d7dee6;
}


.work-box:nth-child(2n) {

    border-right: 0;
}


.work-box.full {

    grid-column:
        1 / 3;

    border-right: 0;
}


.work-label {

    display: block;

    font-size: 8px;

    text-transform: uppercase;

    font-weight: 800;

    color: #6c7782;

    margin-bottom: 7px;
}


.work-value {

    font-size: 10px;

    line-height: 1.55;

    min-height: 25px;
}


/* =========================================================
   FOTO
========================================================= */

.photo-container {

    border:
        1px solid #d7dee6;

    padding: 10px;

    text-align: center;

    background: #f8fafc;

    min-height: 120px;
}


.photo-container img {

    max-width: 100%;

    max-height: 300px;

    object-fit: contain;
}


.no-photo {

    color: #89939e;

    padding: 40px;
}


/* =========================================================
   SIGNATURE
========================================================= */

.signature {

    display: grid;

    grid-template-columns:
        1fr
        1fr
        1fr;

    gap: 25px;

    margin-top: 35px;

    text-align: center;

    page-break-inside: avoid;
}


.signature-title {

    font-size: 8px;

    font-weight: 800;

    text-transform: uppercase;

    color: #5e6873;
}


.signature-space {

    height: 60px;
}


.signature-line {

    border-bottom:
        1px solid #333;

    padding-bottom: 3px;

    min-height: 18px;

    font-weight: 700;

    font-size: 9px;
}


.signature-role {

    font-size: 8px;

    color: #6b7280;

    margin-top: 3px;
}


/* =========================================================
   FOOTER
========================================================= */

.footer {

    margin-top: 25px;

    padding-top: 8px;

    border-top:
        1px solid #d7dee6;

    display: flex;

    justify-content: space-between;

    color: #8a94a0;

    font-size: 8px;
}


/* =========================================================
   DOMPDF
========================================================= */
<?php
$autoload = __DIR__ . '/../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (file_exists($autoload)) {
    require_once $autoload;
} else {
    die("Autoload file tidak ditemukan. Pastikan Composer sudah diinstal.");
}
@page {

    size: A4 landscape;

    margin: 8mm;
}


@media print {

    body {

        padding: 0;

        background: white;
    }


    .page {

        width: 100%;

        min-height: auto;

        margin: 0;

        padding:
            10px
            15px
            20px;

        box-shadow: none;
    }


    .section {

        page-break-inside: avoid;
    }


    .spec-table tr {

        page-break-inside: avoid;

        page-break-after: auto;
    }


    .work-grid,
    .photo-container,
    .signature {

        page-break-inside: avoid;
    }
}

</style>

</head>


<body>


<div class="page">


    <!-- =================================================
         HEADER
    ================================================== -->

    <div class="header">


        <div class="logo">

            <?php

            $logo =
                "../assets/img/logo-garudafood.png";

            ?>

            <?php if (
                file_exists($logo)
            ): ?>

                <img
                    src="<?= e($logo) ?>"
                    alt="Logo Garudafood"
                >

            <?php else: ?>

                <strong
                    style="
                        color:#07549b;
                        font-size:14px;
                    "
                >
                    GARUDAFOOD
                </strong>

            <?php endif; ?>

        </div>


        <div class="company">

            <h1>
                PT GARUDAFOOD PUTRA PUTRI JAYA Tbk
            </h1>

            <h2>
                MAINTENANCE MANAGEMENT SYSTEM
            </h2>

            <p>
                Maintenance Order & Technical Maintenance Report
            </p>

        </div>


        <div class="header-right">

            <div class="document-title">
                MAINTENANCE ORDER
            </div>

            <div class="document-number">
                <?= e($nomorMO) ?>
            </div>

            <div class="document-date">
                <?= e($tanggalMaintenance) ?>
            </div>

        </div>

    </div>


    <!-- =================================================
         01 INFORMASI MAINTENANCE
    ================================================== -->

    <div class="section">

        <div class="section-title">
            01 &nbsp; INFORMASI MAINTENANCE
        </div>


        <div class="info-grid">


            <div class="info-item">

                <span class="info-label">
                    Nomor Maintenance Order
                </span>

                <span class="info-value">
                    <?= e($nomorMO) ?>
                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Tanggal Maintenance
                </span>

                <span class="info-value">
                    <?= e($tanggalMaintenance) ?>
                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Status
                </span>

                <span class="info-value">

                    <span
                        class="status <?= $statusClass ?>"
                    >
                        <?= e(
                            strtoupper($status)
                        ) ?>
                    </span>

                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Jenis Maintenance
                </span>

                <span class="info-value">
                    <?= e($jenisMaintenance) ?>
                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Teknisi
                </span>

                <span class="info-value">
                    <?= e($teknisi) ?>
                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Lokasi
                </span>

                <span class="info-value">
                    <?= e($lokasi) ?>
                </span>

            </div>


        </div>

    </div>


    <!-- =================================================
         02 IDENTITAS
    ================================================== -->

    <div class="section">

        <div class="section-title">
            02 &nbsp; IDENTITAS MESIN / PERALATAN
        </div>


        <div class="info-grid">


            <div class="info-item">

                <span class="info-label">
                    Jenis Mesin
                </span>

                <span class="info-value">
                    <?= e(
                        $d['nama_jenis_mesin']
                    ) ?>
                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Mesin Induk
                </span>

                <span class="info-value">
                    <?= e($namaMesin) ?>
                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Serial Number Mesin
                </span>

                <span class="info-value">
                    <?= e(
                        $d['serial_number_mesin']
                    ) ?>
                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Sub Mesin
                </span>

                <span class="info-value">
                    <?= e($namaSubMesin) ?>
                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Komponen
                </span>

                <span class="info-value">
                    <?= e($namaKomponen) ?>
                </span>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Serial Number Komponen
                </span>

                <span class="info-value">
                    <?= e($serialKomponen) ?>
                </span>

            </div>


        </div>

    </div>


    <!-- =================================================
         03 RINGKASAN
    ================================================== -->

    <div class="section">

        <div class="section-title">
            03 &nbsp; RINGKASAN HASIL PEMERIKSAAN SPESIFIKASI
        </div>


        <div class="summary-grid">


            <div class="summary-box">

                <div class="summary-number">
                    <?= $totalBerubah ?>
                </div>

                <div class="summary-label">
                    Parameter Berubah
                </div>

            </div>


            <div class="summary-box">

                <div class="summary-number">
                    <?= $totalSama ?>
                </div>

                <div class="summary-label">
                    Parameter Tidak Berubah
                </div>

            </div>


            <div class="summary-box">

                <div class="summary-number">
                    <?= count($spesifikasi) ?>
                </div>

                <div class="summary-label">
                    Total Parameter
                </div>

            </div>


        </div>


        <div
            class="kesimpulan <?= $kesimpulanClass ?>"
        >

            <?= e($kesimpulan) ?>

            <?php if (
                $totalBerubah > 0
            ): ?>

                &nbsp; —

                <?= $totalBerubah ?>

                parameter mengalami perubahan
                setelah maintenance.

            <?php else: ?>

                &nbsp; —

                seluruh parameter yang
                tercatat tetap sama setelah
                maintenance.

            <?php endif; ?>

        </div>

    </div>


    <!-- =================================================
         04 PERBANDINGAN SPESIFIKASI
    ================================================== -->

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


            <?php foreach (
                $spesifikasi
                as $spec
            ): ?>


                <?php

                $hasil =
                    compareValue(
                        $spec['normal'],
                        $spec['terbaru']
                    );


                $normalValue =
                    nilai(
                        $spec['normal']
                    );


                $terbaruValue =
                    nilai(
                        $spec['terbaru']
                    );

                ?>


                <tr>


                    <td class="spec-name">

                        <?= e(
                            $spec['nama']
                        ) ?>

                    </td>


                    <!-- =================================================
                         NORMAL
                    ================================================== -->

                    <td
                        class="<?=
                            $hasil === 'changed'
                                ? 'value-changed'
                                : (
                                    $hasil === 'empty'
                                        ? 'value-empty'
                                        : 'value-same'
                                )
                        ?>"
                    >

                        <?= e(
                            $normalValue
                        ) ?>

                    </td>


                    <!-- =================================================
                         TERBARU
                    ================================================== -->

                    <td
                        class="<?=
                            $hasil === 'changed'
                                ? 'value-changed'
                                : (
                                    $hasil === 'empty'
                                        ? 'value-empty'
                                        : 'value-same'
                                )
                        ?>"
                    >

                        <?= e(
                            $terbaruValue
                        ) ?>

                    </td>


                    <!-- =================================================
                         HASIL
                    ================================================== -->

                    <td class="spec-hasil">


                        <?php if (
                            $hasil === 'changed'
                        ): ?>

                            <span
                                class="
                                    change-badge
                                    badge-changed
                                "
                            >
                                BERUBAH
                            </span>


                        <?php elseif (
                            $hasil === 'same'
                        ): ?>

                            <span
                                class="
                                    change-badge
                                    badge-same
                                "
                            >
                                TIDAK BERUBAH
                            </span>


                        <?php else: ?>

                            <span
                                class="
                                    change-badge
                                    badge-empty
                                "
                            >
                                TIDAK ADA DATA
                            </span>

                        <?php endif; ?>


                    </td>


                </tr>


            <?php endforeach; ?>


            </tbody>

        </table>

    </div>


    <!-- =================================================
         05 HASIL MAINTENANCE
    ================================================== -->

    <div class="section">

        <div class="section-title">
            05 &nbsp; HASIL / PEKERJAAN MAINTENANCE
        </div>


        <div class="work-grid">


            <div class="work-box">

                <span class="work-label">
                    Tindakan Maintenance
                </span>

                <div class="work-value">

                    <?= !empty(
                        $d['tindakan']
                    )
                        ? nl2br(
                            e($d['tindakan'])
                        )
                        : '-'
                    ?>

                </div>

            </div>


            <div class="work-box">

                <span class="work-label">
                    Sparepart / Komponen Diganti
                </span>

                <div class="work-value">

                    <?= !empty(
                        $d['sparepart']
                    )
                        ? nl2br(
                            e($d['sparepart'])
                        )
                        : '-'
                    ?>

                </div>

            </div>


            <div class="work-box full">

                <span class="work-label">
                    Catatan / Hasil Pemeriksaan
                </span>

                <div class="work-value">

                    <?= !empty(
                        $d['catatan']
                    )
                        ? nl2br(
                            e($d['catatan'])
                        )
                        : 'Tidak ada catatan tambahan.'
                    ?>

                </div>

            </div>


        </div>

    </div>


    <!-- =================================================
         06 DOKUMENTASI
    ================================================== -->

    <div class="section">

        <div class="section-title">
            06 &nbsp; DOKUMENTASI MAINTENANCE
        </div>


        <div class="photo-container">


            <?php if ($foto): ?>

                <img
                    src="<?= e($foto) ?>"
                    alt="Dokumentasi Maintenance"
                >

                <div
                    style="
                        margin-top:5px;
                        color:#7b8794;
                        font-size:8px;
                    "
                >
                    Dokumentasi pekerjaan maintenance
                </div>


            <?php else: ?>

                <div class="no-photo">

                    Tidak ada foto dokumentasi
                    yang diunggah.

                </div>

            <?php endif; ?>


        </div>

    </div>


    <!-- =================================================
         07 KESIMPULAN
    ================================================== -->

    <div class="section">

        <div class="section-title">
            07 &nbsp; KESIMPULAN MAINTENANCE
        </div>


        <div
            class="kesimpulan <?= $kesimpulanClass ?>"
            style="
                text-align:left;
                line-height:1.6;
            "
        >

            <strong>
                Hasil Maintenance:
            </strong>

            <br>


            <?php if (
                $totalBerubah > 0
            ): ?>

                Berdasarkan perbandingan
                data spesifikasi normal
                dengan data setelah
                maintenance, terdapat

                <strong>
                    <?= $totalBerubah ?>
                    parameter
                </strong>

                yang mengalami perubahan.

                Perubahan tersebut dapat
                digunakan sebagai informasi
                kondisi aktual komponen
                setelah dilakukan pekerjaan
                maintenance.


            <?php else: ?>

                Berdasarkan perbandingan
                data spesifikasi normal
                dengan data setelah
                maintenance,

                <strong>
                    tidak ditemukan perubahan
                    spesifikasi
                </strong>

                pada parameter yang tercatat.

                Hal ini menunjukkan bahwa
                data spesifikasi komponen
                setelah maintenance masih
                sesuai dengan data normal
                yang tersimpan.

            <?php endif; ?>


        </div>

    </div>


    <!-- =================================================
         08 APPROVAL
    ================================================== -->

    <div class="signature">


        <div>

            <div class="signature-title">
                DILAKSANAKAN OLEH
            </div>

            <div class="signature-space"></div>

            <div class="signature-line">
                <?= e($teknisi) ?>
            </div>

            <div class="signature-role">
                Teknisi Maintenance
            </div>

        </div>


        <div>

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

        </div>


        <div>

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

        </div>


    </div>


    <!-- =================================================
         FOOTER
    ================================================== -->

    <div class="footer">


        <div>
            Maintenance Management System
        </div>


        <div>
            Dokumen:
            <?= e($nomorMO) ?>
        </div>


        <div>
            Dicetak:
            <?= e($tanggalCetak) ?>
        </div>


    </div>


</div>

</body>

</html>
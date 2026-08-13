<?php
include "../koneksi.php";

/* =========================================================
   AMBIL ID SUB MESIN
========================================================= */

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}


/* =========================================================
   DETAIL SUB MESIN
========================================================= */

$stmt = mysqli_prepare($conn, "
    SELECT 
        sm.*,
        m.nama_mesin,
        m.serial_number AS sn_mesin,
        m.gambar AS gambar_mesin,
        m.keterangan AS keterangan_mesin,
        jm.nama_jenis_mesin,
        ab.nama_area,
        ab.lokasi
    FROM sub_mesin sm

    LEFT JOIN mesin m 
        ON sm.id_mesin = m.id

    LEFT JOIN jenis_mesin jm 
        ON m.id_jenis_mesin = jm.id

    LEFT JOIN area_bagian ab 
        ON m.id_area = ab.id

    WHERE sm.id = ?
");

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$d = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$d) {
    header("Location: index.php");
    exit;
}


/* =========================================================
   DATA KOMPONEN
========================================================= */

$stmt_komponen = mysqli_prepare($conn, "
    SELECT 
        k.id,
        k.nama_bagian,
        k.serial_number,
        k.kategori,
        k.brand,
        k.tipe,
        k.kondisi,
        k.gambar
    FROM komponen k
    WHERE k.id_sub_mesin = ?
    ORDER BY k.id DESC
");

mysqli_stmt_bind_param($stmt_komponen, "i", $id);
mysqli_stmt_execute($stmt_komponen);

$result_komponen = mysqli_stmt_get_result($stmt_komponen);

$data_komponen = [];

while ($row = mysqli_fetch_assoc($result_komponen)) {
    $data_komponen[] = $row;
}

mysqli_stmt_close($stmt_komponen);


/* =========================================================
   DATA MAINTENANCE
========================================================= */

$stmt_maintenance = mysqli_prepare($conn, "
    SELECT
        rm.id,
        rm.id_komponen,
        rm.tanggal,
        rm.jenis,
        rm.tindakan,
        rm.teknisi,
        rm.status,
        k.nama_bagian
    FROM riwayat_maintenance rm

    LEFT JOIN komponen k
        ON rm.id_komponen = k.id

    WHERE k.id_sub_mesin = ?

    ORDER BY rm.tanggal DESC, rm.id DESC
");

mysqli_stmt_bind_param($stmt_maintenance, "i", $id);
mysqli_stmt_execute($stmt_maintenance);

$result_maintenance = mysqli_stmt_get_result($stmt_maintenance);

$data_maintenance = [];

while ($row = mysqli_fetch_assoc($result_maintenance)) {
    $data_maintenance[] = $row;
}

mysqli_stmt_close($stmt_maintenance);


/* =========================================================
   FOTO SUB MESIN
========================================================= */

$gambar_sub = "../uploads/sub_mesin/" . ($d['gambar'] ?? '');

$ada_gambar_sub = false;

if (!empty($d['gambar']) && file_exists($gambar_sub)) {
    $ada_gambar_sub = true;
}


/* =========================================================
   TOTAL DATA
========================================================= */

$total_komponen   = count($data_komponen);
$total_maintenance = count($data_maintenance);


/* =========================================================
   BADGE KONDISI
========================================================= */

function badgeKondisi($kondisi)
{
    switch ($kondisi) {

        case 'Baik':
            return 'bg-success-subtle text-success';

        case 'Perlu Pemeriksaan':
            return 'bg-warning-subtle text-warning-emphasis';

        case 'Dalam Perbaikan':
            return 'bg-danger-subtle text-danger';

        case 'Rusak':
            return 'bg-danger text-white';

        default:
            return 'bg-secondary-subtle text-secondary';
    }
}


/* =========================================================
   BADGE STATUS MAINTENANCE
========================================================= */

function badgeStatusMaintenance($status)
{
    switch ($status) {

        case 'Selesai':
            return 'bg-success-subtle text-success';

        case 'Proses':
            return 'bg-warning-subtle text-warning-emphasis';

        case 'Pending':
            return 'bg-danger-subtle text-danger';

        default:
            return 'bg-secondary-subtle text-secondary';
    }
}


include "../template/header.php";
?>


<style>

/* =========================================================
   PAGE
========================================================= */

.sub-detail-page {
    width: 100%;
}


/* =========================================================
   TOP PAGE HEADER
========================================================= */

.sub-page-header {

    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 16px;

    padding: 18px 20px;

    margin-bottom: 20px;

    box-shadow:
        0 3px 12px rgba(15,23,42,.04);
}

.sub-page-title {

    font-size: 24px;

    font-weight: 800;

    color: #172033;

    margin: 0;
}

.sub-page-subtitle {

    color: #64748b;

    font-size: 13px;

    margin-top: 3px;
}


/* =========================================================
   BACK BUTTON
========================================================= */

.sub-back-btn {

    width: 40px;

    height: 40px;

    border-radius: 10px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    border: 1px solid #dbe3ea;

    background: #ffffff;

    color: #475569;

    text-decoration: none;

    transition: .2s;
}

.sub-back-btn:hover {

    background: #005baa;

    border-color: #005baa;

    color: #ffffff;
}


/* =========================================================
   ACTION BUTTON
========================================================= */

.sub-action-btn {

    border-radius: 9px;

    padding: 9px 14px;

    font-size: 13px;

    font-weight: 600;

}


/* =========================================================
   HERO
========================================================= */

.sub-detail-hero {

    background: linear-gradient(
        135deg,
        #005baa,
        #0076c8
    );

    border-radius: 18px;

    overflow: hidden;

    color: #ffffff;

    position: relative;

    box-shadow:
        0 8px 25px rgba(0,91,170,.16);
}

.sub-detail-hero::after {

    content: "";

    position: absolute;

    width: 260px;

    height: 260px;

    right: -100px;

    top: -120px;

    border-radius: 50%;

    background: rgba(255,255,255,.08);
}

.sub-detail-hero::before {

    content: "";

    position: absolute;

    width: 180px;

    height: 180px;

    right: 180px;

    bottom: -120px;

    border-radius: 50%;

    background: rgba(255,255,255,.05);
}


/* =========================================================
   PHOTO
========================================================= */

.sub-photo-box {

    width: 170px;

    height: 170px;

    border-radius: 16px;

    background: rgba(255,255,255,.14);

    border: 1px solid rgba(255,255,255,.25);

    display: flex;

    align-items: center;

    justify-content: center;

    overflow: hidden;

    position: relative;

    z-index: 2;
}

.sub-photo-box img {

    width: 100%;

    height: 100%;

    object-fit: contain;

    padding: 12px;
}

.sub-photo-empty {

    text-align: center;

    opacity: .65;
}


/* =========================================================
   HERO INFO
========================================================= */

.sub-hero-label {

    font-size: 11px;

    text-transform: uppercase;

    letter-spacing: .7px;

    font-weight: 700;

    opacity: .7;

    margin-bottom: 3px;
}

.sub-hero-title {

    font-size: 28px;

    font-weight: 800;

    margin-bottom: 20px;
}

.sub-info-label {

    font-size: 10px;

    text-transform: uppercase;

    letter-spacing: .5px;

    opacity: .7;

    font-weight: 700;

    margin-bottom: 3px;
}

.sub-info-value {

    font-size: 14px;

    font-weight: 600;
}


/* =========================================================
   HERO BUTTON
========================================================= */

.sub-hero-btn {

    position: relative;

    z-index: 5;

    white-space: nowrap;
}


/* =========================================================
   STAT CARD
========================================================= */

.sub-stat {

    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 14px;

    padding: 17px;

    transition: .2s;

    height: 100%;
}

.sub-stat:hover {

    transform: translateY(-2px);

    box-shadow:
        0 7px 20px rgba(15,23,42,.07);
}

.sub-stat-icon {

    width: 44px;

    height: 44px;

    border-radius: 11px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #eef5ff;

    color: #005baa;

    font-size: 20px;
}

.sub-stat-number {

    font-size: 24px;

    font-weight: 800;

    color: #172033;

}


/* =========================================================
   CONTENT CARD
========================================================= */

.sub-content-card {

    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 16px;

    overflow: hidden;

    box-shadow:
        0 3px 12px rgba(15,23,42,.03);

    margin-bottom: 20px;
}

.sub-card-header {

    padding: 17px 20px;

    border-bottom: 1px solid #e5e7eb;

    background: #ffffff;
}

.sub-card-title {

    font-size: 16px;

    font-weight: 700;

    color: #172033;

    margin: 0;
}

.sub-card-subtitle {

    color: #94a3b8;

    font-size: 12px;

    margin-top: 2px;
}


/* =========================================================
   INFO DESCRIPTION
========================================================= */

.sub-description {

    background: #f8fafc;

    border: 1px solid #e5e7eb;

    border-radius: 11px;

    padding: 14px;

    color: #475569;

    font-size: 13px;

    line-height: 1.6;
}


/* =========================================================
   TABLE
========================================================= */

.sub-table {

    width: 100%;

    margin: 0;

    border-collapse: separate;

    border-spacing: 0;
}

.sub-table thead th {

    background: #f8fafc;

    color: #475569;

    font-size: 11px;

    font-weight: 700;

    text-transform: uppercase;

    letter-spacing: .3px;

    padding: 13px 14px;

    border-bottom: 1px solid #e5e7eb;

    white-space: nowrap;
}

.sub-table tbody td {

    padding: 14px;

    border-bottom: 1px solid #f1f5f9;

    font-size: 13px;

    color: #334155;

    vertical-align: middle;
}

.sub-table tbody tr {

    transition: .15s;
}

.sub-table tbody tr:hover {

    background: #f8fbff;
}

.sub-table tbody tr:last-child td {

    border-bottom: none;
}


/* =========================================================
   COMPONENT NAME
========================================================= */

.component-name {

    font-size: 13px;

    font-weight: 700;

    color: #172033;
}

.component-meta {

    font-size: 11px;

    color: #94a3b8;

    margin-top: 2px;
}


/* =========================================================
   EMPTY
========================================================= */

.sub-empty {

    padding: 55px 20px;

    text-align: center;

    color: #94a3b8;
}

.sub-empty-icon {

    width: 65px;

    height: 65px;

    margin: 0 auto 14px;

    border-radius: 17px;

    background: #f1f5f9;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 28px;

    color: #94a3b8;
}

.sub-empty-title {

    font-size: 14px;

    font-weight: 700;

    color: #475569;
}

.sub-empty-text {

    font-size: 12px;

    margin-top: 3px;
}


/* =========================================================
   TIMELINE
========================================================= */

.sub-timeline {

    position: relative;

    padding-left: 32px;
}

.sub-timeline::before {

    content: "";

    position: absolute;

    left: 9px;

    top: 8px;

    bottom: 8px;

    width: 2px;

    background: #e2e8f0;
}

.sub-timeline-item {

    position: relative;

    padding-bottom: 18px;
}

.sub-timeline-item:last-child {

    padding-bottom: 0;
}

.sub-timeline-dot {

    position: absolute;

    left: -30px;

    top: 6px;

    width: 19px;

    height: 19px;

    border-radius: 50%;

    background: #005baa;

    border: 4px solid #dbeafe;

}

.sub-timeline-card {

    border: 1px solid #e5e7eb;

    border-radius: 12px;

    padding: 15px;

    background: #ffffff;

    transition: .2s;
}

.sub-timeline-card:hover {

    box-shadow:
        0 5px 16px rgba(15,23,42,.06);
}


/* =========================================================
   TIMELINE LABEL
========================================================= */

.timeline-label {

    font-size: 10px;

    text-transform: uppercase;

    letter-spacing: .4px;

    color: #94a3b8;

    font-weight: 700;

    margin-bottom: 3px;
}

.timeline-value {

    font-size: 12px;

    font-weight: 600;

    color: #334155;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 992px) {

    .sub-photo-box {

        width: 140px;

        height: 140px;
    }

    .sub-hero-title {

        font-size: 23px;
    }

}


@media (max-width: 768px) {

    .sub-page-header {

        padding: 15px;
    }

    .sub-page-title {

        font-size: 21px;
    }

    .sub-detail-hero {

        border-radius: 15px;
    }

    .sub-photo-box {

        width: 110px;

        height: 110px;
    }

    .sub-hero-title {

        font-size: 21px;
    }

    .sub-detail-hero .row {

        padding: 5px;
    }

}

</style>


<div class="container-fluid p-0 sub-detail-page">


    <!-- =====================================================
         PAGE HEADER / BAR PUTIH
    ====================================================== -->

    <div class="sub-page-header">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <div class="d-flex align-items-center gap-3">

                <a
                    href="index.php"
                    class="sub-back-btn"
                    title="Kembali"
                >

                    <i class="bi bi-arrow-left"></i>

                </a>


                <div>

                    <h2 class="sub-page-title">

                        Detail Sub Mesin

                    </h2>

                    <div class="sub-page-subtitle">

                        Informasi lengkap sub mesin, komponen,
                        dan riwayat maintenance.

                    </div>

                </div>

            </div>


            <div class="d-flex gap-2 flex-wrap">

                <!-- DOWNLOAD PDF -->

                <a
                    href="download_pdf.php?id=<?= $id ?>"
                    class="btn btn-danger sub-action-btn"
                    target="_blank"
                >

                    <i class="bi bi-file-earmark-pdf me-1"></i>

                    Download PDF

                </a>


                <!-- EDIT -->

                <a
                    href="edit.php?id=<?= $id ?>"
                    class="btn btn-warning sub-action-btn"
                >

                    <i class="bi bi-pencil-square me-1"></i>

                    Edit Sub Mesin

                </a>

            </div>

        </div>

    </div>



    <!-- =====================================================
         HERO DETAIL SUB MESIN
    ====================================================== -->

    <div class="sub-detail-hero mb-4">

        <div class="row align-items-center g-4 p-4">


            <!-- FOTO -->

            <div class="col-auto">

                <div class="sub-photo-box">

                    <?php if ($ada_gambar_sub): ?>

                        <img
                            src="<?= htmlspecialchars($gambar_sub) ?>"
                            alt="<?= htmlspecialchars(
                                $d['nama_sub_mesin'] ?? 'Sub Mesin'
                            ) ?>"
                        >

                    <?php else: ?>

                        <div class="sub-photo-empty">

                            <i
                                class="bi bi-diagram-3"
                                style="font-size:60px;"
                            ></i>

                            <div class="small mt-1">

                                Tidak ada foto

                            </div>

                        </div>

                    <?php endif; ?>

                </div>

            </div>


            <!-- INFORMASI -->

            <div class="col">

                <div class="sub-hero-label">

                    SUB MESIN

                </div>


                <div class="sub-hero-title">

                    <?= htmlspecialchars(
                        $d['nama_sub_mesin'] ?? '-'
                    ) ?>

                </div>


                <div class="row g-3">


                    <!-- MESIN INDUK -->

                    <div class="col-md-3">

                        <div class="sub-info-label">

                            Mesin Induk

                        </div>

                        <div class="sub-info-value">

                            <i class="bi bi-gear-wide-connected me-1"></i>

                            <?= htmlspecialchars(
                                $d['nama_mesin'] ?? '-'
                            ) ?>

                        </div>

                    </div>


                    <!-- SERIAL MESIN -->

                    <div class="col-md-3">

                        <div class="sub-info-label">

                            Serial Number Mesin

                        </div>

                        <div class="sub-info-value">

                            <i class="bi bi-upc-scan me-1"></i>

                            <?= htmlspecialchars(
                                $d['sn_mesin'] ?? '-'
                            ) ?>

                        </div>

                    </div>


                    <!-- JENIS -->

                    <div class="col-md-3">

                        <div class="sub-info-label">

                            Jenis Mesin

                        </div>

                        <div class="sub-info-value">

                            <i class="bi bi-grid me-1"></i>

                            <?= htmlspecialchars(
                                $d['nama_jenis_mesin'] ?? '-'
                            ) ?>

                        </div>

                    </div>


                    <!-- LOKASI -->

                    <div class="col-md-3">

                        <div class="sub-info-label">

                            Lokasi / Area

                        </div>

                        <div class="sub-info-value">

                            <i class="bi bi-geo-alt me-1"></i>

                            <?= htmlspecialchars(
                                $d['lokasi'] ?? '-'
                            ) ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- =====================================================
         STATISTIK
    ====================================================== -->

    <div class="row g-3 mb-4">


        <!-- KOMPONEN -->

        <div class="col-md-4">

            <div class="sub-stat">

                <div class="d-flex align-items-center gap-3">

                    <div class="sub-stat-icon">

                        <i class="bi bi-cpu"></i>

                    </div>

                    <div>

                        <div class="sub-stat-number">

                            <?= $total_komponen ?>

                        </div>

                        <div class="text-muted small">

                            Komponen

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- MAINTENANCE -->

        <div class="col-md-4">

            <div class="sub-stat">

                <div class="d-flex align-items-center gap-3">

                    <div class="sub-stat-icon">

                        <i class="bi bi-tools"></i>

                    </div>

                    <div>

                        <div class="sub-stat-number">

                            <?= $total_maintenance ?>

                        </div>

                        <div class="text-muted small">

                            Riwayat Maintenance

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- KETERANGAN -->

        <div class="col-md-4">

            <div class="sub-stat">

                <div class="d-flex align-items-center gap-3">

                    <div class="sub-stat-icon">

                        <i class="bi bi-info-circle"></i>

                    </div>

                    <div>

                        <div class="sub-stat-number">

                            <?= !empty($d['keterangan'])
                                ? 'Ada'
                                : '-'
                            ?>

                        </div>

                        <div class="text-muted small">

                            Keterangan

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- =====================================================
         KETERANGAN
    ====================================================== -->

    <?php if (!empty($d['keterangan'])): ?>

    <div class="sub-content-card">

        <div class="sub-card-header">

            <h5 class="sub-card-title">

                <i class="bi bi-journal-text text-primary me-2"></i>

                Keterangan Sub Mesin

            </h5>

            <div class="sub-card-subtitle">

                Informasi tambahan mengenai sub mesin.

            </div>

        </div>


        <div class="p-3">

            <div class="sub-description">

                <?= nl2br(
                    htmlspecialchars(
                        $d['keterangan']
                    )
                ) ?>

            </div>

        </div>

    </div>

    <?php endif; ?>



    <!-- =====================================================
         KOMPONEN
    ====================================================== -->

    <div class="sub-content-card">

        <div class="sub-card-header">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                <div>

                    <h5 class="sub-card-title">

                        <i class="bi bi-cpu text-primary me-2"></i>

                        Komponen

                    </h5>

                    <div class="sub-card-subtitle">

                        Komponen yang terhubung dengan sub mesin ini.

                    </div>

                </div>


                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">

                    <?= $total_komponen ?>

                    Komponen

                </span>

            </div>

        </div>


        <?php if (!empty($data_komponen)): ?>

            <div class="table-responsive">

                <table class="sub-table">

                    <thead>

                        <tr>

                            <th
                                width="55"
                                class="text-center"
                            >
                                No
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
                                Kondisi
                            </th>

                            <th
                                width="80"
                                class="text-center"
                            >
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php

                    $no = 1;

                    foreach ($data_komponen as $k):

                    ?>

                        <tr>


                            <!-- NO -->

                            <td class="text-center">

                                <span class="text-muted fw-semibold">

                                    <?= $no++ ?>

                                </span>

                            </td>


                            <!-- KOMPONEN -->

                            <td>

                                <div class="d-flex align-items-center gap-2">

                                    <div
                                        class="rounded-3 d-flex align-items-center justify-content-center"
                                        style="
                                            width:38px;
                                            height:38px;
                                            background:#eef5ff;
                                            color:#005baa;
                                            flex-shrink:0;
                                        "
                                    >

                                        <i class="bi bi-cpu"></i>

                                    </div>


                                    <div>

                                        <div class="component-name">

                                            <?= htmlspecialchars(
                                                $k['nama_bagian'] ?? '-'
                                            ) ?>

                                        </div>

                                        <div class="component-meta">

                                            <?= htmlspecialchars(
                                                $k['kategori'] ?? '-'
                                            ) ?>

                                        </div>

                                    </div>

                                </div>

                            </td>


                            <!-- SERIAL -->

                            <td>

                                <span
                                    class="badge bg-light text-dark border font-monospace"
                                >

                                    <?= htmlspecialchars(
                                        $k['serial_number'] ?? '-'
                                    ) ?>

                                </span>

                            </td>


                            <!-- BRAND -->

                            <td>

                                <?= htmlspecialchars(
                                    $k['brand'] ?? '-'
                                ) ?>

                            </td>


                            <!-- TIPE -->

                            <td>

                                <?= htmlspecialchars(
                                    $k['tipe'] ?? '-'
                                ) ?>

                            </td>


                            <!-- KONDISI -->

                            <td>

                                <span
                                    class="badge <?= badgeKondisi(
                                        $k['kondisi'] ?? ''
                                    ) ?> rounded-pill"
                                >

                                    <?= htmlspecialchars(
                                        $k['kondisi'] ?? '-'
                                    ) ?>

                                </span>

                            </td>


                            <!-- AKSI -->

                            <td class="text-center">

                                <a
                                    href="../komponen/detail.php?id=<?= intval($k['id']) ?>"
                                    class="btn btn-sm btn-outline-primary"
                                    title="Lihat Detail Komponen"
                                >

                                    <i class="bi bi-eye"></i>

                                </a>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>


        <?php else: ?>


            <div class="sub-empty">

                <div class="sub-empty-icon">

                    <i class="bi bi-cpu"></i>

                </div>

                <div class="sub-empty-title">

                    Belum ada komponen

                </div>

                <div class="sub-empty-text">

                    Belum ada komponen yang terhubung dengan sub mesin ini.

                </div>

            </div>


        <?php endif; ?>

    </div>



    <!-- =====================================================
         MAINTENANCE
    ====================================================== -->

    <div class="sub-content-card">

        <div class="sub-card-header">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                <div>

                    <h5 class="sub-card-title">

                        <i class="bi bi-tools text-warning me-2"></i>

                        Riwayat Maintenance

                    </h5>

                    <div class="sub-card-subtitle">

                        Riwayat maintenance seluruh komponen
                        pada sub mesin ini.

                    </div>

                </div>


                <span class="badge bg-warning-subtle text-warning-emphasis">

                    <?= $total_maintenance ?>

                    Riwayat

                </span>

            </div>

        </div>


        <div class="p-4">

            <?php if (!empty($data_maintenance)): ?>

                <div class="sub-timeline">


                    <?php foreach ($data_maintenance as $rm): ?>


                        <div class="sub-timeline-item">

                            <div class="sub-timeline-dot"></div>


                            <div class="sub-timeline-card">


                                <!-- TOP -->

                                <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">

                                    <div>

                                        <div class="fw-bold text-dark">

                                            <?= htmlspecialchars(
                                                $rm['nama_bagian']
                                                    ?? 'Komponen'
                                            ) ?>

                                        </div>


                                        <div class="small text-muted mt-1">

                                            <i class="bi bi-calendar3 me-1"></i>

                                            <?= !empty($rm['tanggal'])
                                                ? date(
                                                    'd M Y H:i',
                                                    strtotime(
                                                        $rm['tanggal']
                                                    )
                                                )
                                                : '-'
                                            ?>

                                        </div>

                                    </div>


                                    <span
                                        class="badge <?= badgeStatusMaintenance(
                                            $rm['status'] ?? ''
                                        ) ?> rounded-pill"
                                    >

                                        <?= htmlspecialchars(
                                            $rm['status'] ?? '-'
                                        ) ?>

                                    </span>

                                </div>



                                <!-- DETAIL -->

                                <div class="row g-3 mt-2">


                                    <!-- JENIS -->

                                    <div class="col-md-4">

                                        <div class="timeline-label">

                                            Jenis Maintenance

                                        </div>

                                        <div class="timeline-value">

                                            <?= htmlspecialchars(
                                                $rm['jenis'] ?? '-'
                                            ) ?>

                                        </div>

                                    </div>


                                    <!-- TEKNISI -->

                                    <div class="col-md-4">

                                        <div class="timeline-label">

                                            Teknisi

                                        </div>

                                        <div class="timeline-value">

                                            <?= htmlspecialchars(
                                                $rm['teknisi'] ?? '-'
                                            ) ?>

                                        </div>

                                    </div>


                                    <!-- TINDAKAN -->

                                    <div class="col-md-4">

                                        <div class="timeline-label">

                                            Tindakan

                                        </div>

                                        <div class="timeline-value">

                                            <?= htmlspecialchars(
                                                $rm['tindakan'] ?? '-'
                                            ) ?>

                                        </div>

                                    </div>

                                </div>



                                <!-- DETAIL BUTTON -->

                                <div class="mt-3">

                                    <a
                                        href="../maintenance/detail.php?id=<?= intval($rm['id']) ?>"
                                        class="btn btn-sm btn-outline-primary"
                                    >

                                        <i class="bi bi-eye me-1"></i>

                                        Lihat Detail Maintenance

                                    </a>

                                </div>

                            </div>

                        </div>


                    <?php endforeach; ?>


                </div>


            <?php else: ?>


                <div class="sub-empty">

                    <div class="sub-empty-icon">

                        <i class="bi bi-tools"></i>

                    </div>

                    <div class="sub-empty-title">

                        Belum ada riwayat maintenance

                    </div>

                    <div class="sub-empty-text">

                        Belum ada aktivitas maintenance
                        untuk sub mesin ini.

                    </div>

                </div>


            <?php endif; ?>

        </div>

    </div>


</div>


<?php include "../template/footer.php"; ?>
<?php
include "../koneksi.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

/* =========================================================
   DATA MESIN
========================================================= */

$stmt = mysqli_prepare($conn, "
    SELECT 
        m.*,
        jm.nama_jenis_mesin,
        ab.nama_area,
        ab.lokasi
    FROM mesin m
    LEFT JOIN jenis_mesin jm 
        ON m.id_jenis_mesin = jm.id
    LEFT JOIN area_bagian ab 
        ON m.id_area = ab.id
    WHERE m.id = ?
");

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$mesin = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$mesin) {
    header("Location: index.php");
    exit;
}


/* =========================================================
   DATA SUB MESIN
========================================================= */

$stmt_sub = mysqli_prepare($conn, "
    SELECT 
        sm.*,

        (
            SELECT COUNT(*)
            FROM komponen k
            WHERE k.id_sub_mesin = sm.id
        ) AS total_komponen,

        (
            SELECT COUNT(*)
            FROM riwayat_maintenance rm
            INNER JOIN komponen k2
                ON rm.id_komponen = k2.id
            WHERE k2.id_sub_mesin = sm.id
        ) AS total_maintenance

    FROM sub_mesin sm

    WHERE sm.id_mesin = ?

    ORDER BY sm.id ASC
");

mysqli_stmt_bind_param($stmt_sub, "i", $id);
mysqli_stmt_execute($stmt_sub);

$result_sub = mysqli_stmt_get_result($stmt_sub);

$data_sub = [];

while ($row = mysqli_fetch_assoc($result_sub)) {
    $data_sub[] = $row;
}

mysqli_stmt_close($stmt_sub);


/* =========================================================
   DATA KOMPONEN
========================================================= */

$stmt_komp = mysqli_prepare($conn, "
    SELECT 
        k.*,
        sm.nama_sub_mesin

    FROM komponen k

    LEFT JOIN sub_mesin sm
        ON k.id_sub_mesin = sm.id

    WHERE sm.id_mesin = ?

    ORDER BY k.id DESC
");

mysqli_stmt_bind_param($stmt_komp, "i", $id);
mysqli_stmt_execute($stmt_komp);

$result_komp = mysqli_stmt_get_result($stmt_komp);

$data_komponen = [];

while ($row = mysqli_fetch_assoc($result_komp)) {
    $data_komponen[] = $row;
}

mysqli_stmt_close($stmt_komp);


/* =========================================================
   DATA MAINTENANCE
========================================================= */

$stmt_maint = mysqli_prepare($conn, "
    SELECT
        rm.*,
        k.nama_bagian,
        k.serial_number,
        sm.nama_sub_mesin

    FROM riwayat_maintenance rm

    LEFT JOIN komponen k
        ON rm.id_komponen = k.id

    LEFT JOIN sub_mesin sm
        ON k.id_sub_mesin = sm.id

    WHERE sm.id_mesin = ?

    ORDER BY rm.tanggal DESC
");

mysqli_stmt_bind_param($stmt_maint, "i", $id);
mysqli_stmt_execute($stmt_maint);

$result_maint = mysqli_stmt_get_result($stmt_maint);

$data_maintenance = [];

while ($row = mysqli_fetch_assoc($result_maint)) {
    $data_maintenance[] = $row;
}

mysqli_stmt_close($stmt_maint);


/* =========================================================
   TOTAL
========================================================= */

$total_sub = count($data_sub);
$total_komponen = count($data_komponen);
$total_maintenance = count($data_maintenance);


/* =========================================================
   HEADER TEMPLATE
========================================================= */

include "../template/header.php";
?>


<style>

/* =========================================================
   PAGE
========================================================= */

.machine-detail-page {
    width: 100%;
}


/* =========================================================
   WHITE PAGE HEADER
========================================================= */

.detail-page-header {

    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 14px;

    padding: 16px 20px;

    margin-bottom: 20px;

    box-shadow:
        0 2px 8px rgba(15,23,42,.04);
}

.detail-page-header-inner {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    flex-wrap: wrap;
}

.detail-page-left {

    display: flex;

    align-items: center;

    gap: 12px;
}

.detail-back {

    width: 40px;

    height: 40px;

    border-radius: 10px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    background: #f8fafc;

    border: 1px solid #e2e8f0;

    color: #475569;

    text-decoration: none;

    transition: .2s;
}

.detail-back:hover {

    background: #005baa;

    border-color: #005baa;

    color: #fff;
}

.detail-page-title {

    font-size: 21px;

    font-weight: 800;

    color: #172033;

    margin: 0;
}

.detail-page-subtitle {

    font-size: 12px;

    color: #94a3b8;

    margin-top: 3px;
}


/* =========================================================
   HEADER ACTION
========================================================= */

.detail-page-actions {

    display: flex;

    align-items: center;

    gap: 8px;
}

.detail-page-actions .btn {

    border-radius: 8px;

    font-weight: 600;

    white-space: nowrap;
}


/* =========================================================
   HEADER MESIN
========================================================= */

.machine-detail-header {

    background:
        linear-gradient(
            135deg,
            #005baa,
            #0076c8
        );

    border-radius: 18px;

    padding: 25px;

    color: #fff;

    position: relative;

    overflow: hidden;

    box-shadow:
        0 8px 25px rgba(0,91,170,.12);
}

.machine-detail-header::before {

    content: "";

    position: absolute;

    width: 300px;

    height: 300px;

    right: -100px;

    top: -150px;

    border-radius: 50%;

    background: rgba(255,255,255,.07);
}

.machine-detail-header::after {

    content: "";

    position: absolute;

    width: 180px;

    height: 180px;

    right: 120px;

    bottom: -130px;

    border-radius: 50%;

    background: rgba(255,255,255,.05);
}


/* =========================================================
   MACHINE IMAGE
========================================================= */

.machine-image-box {

    width: 150px;

    height: 150px;

    background:
        rgba(255,255,255,.14);

    border:
        1px solid rgba(255,255,255,.25);

    border-radius: 16px;

    display: flex;

    align-items: center;

    justify-content: center;

    overflow: hidden;

    position: relative;

    z-index: 2;
}

.machine-image-box img {

    width: 100%;

    height: 100%;

    object-fit: contain;

    background: #fff;
}


/* =========================================================
   MACHINE INFO
========================================================= */

.machine-info-label {

    font-size: 10px;

    text-transform: uppercase;

    opacity: .72;

    font-weight: 700;

    letter-spacing: .5px;

    margin-bottom: 3px;
}

.machine-info-value {

    font-size: 14px;

    font-weight: 600;

    word-break: break-word;
}


/* =========================================================
   STATISTIC
========================================================= */

.detail-stat {

    border:
        1px solid #e5e7eb;

    background:
        #fff;

    border-radius: 14px;

    padding: 18px;

    transition: .2s;

    height: 100%;
}

.detail-stat:hover {

    transform: translateY(-2px);

    box-shadow:
        0 7px 20px rgba(15,23,42,.07);
}

.detail-stat-icon {

    width: 45px;

    height: 45px;

    border-radius: 12px;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 21px;

    background: #eef5ff;

    color: #0066b3;
}

.detail-stat-number {

    font-size: 25px;

    font-weight: 800;

    color: #172033;
}


/* =========================================================
   SECTION
========================================================= */

.detail-section {

    background:
        #fff;

    border:
        1px solid #e5e7eb;

    border-radius: 16px;

    overflow: hidden;

    box-shadow:
        0 2px 8px rgba(15,23,42,.025);
}

.detail-section-header {

    padding: 17px 20px;

    border-bottom:
        1px solid #e5e7eb;

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 15px;
}

.detail-section-title {

    font-size: 16px;

    font-weight: 750;

    color: #172033;
}

.detail-section-subtitle {

    font-size: 12px;

    color: #94a3b8;

    margin-top: 3px;
}


/* =========================================================
   CLICKABLE ROW
========================================================= */

.clickable-row {

    text-decoration: none;

    color: inherit;

    display: block;

    transition: .18s;
}

.clickable-row:hover {

    background:
        #f7fbff;

    color: inherit;
}

.clickable-row:last-child > div {

    border-bottom: none !important;
}


/* =========================================================
   ITEM ICON
========================================================= */

.item-icon {

    width: 42px;

    height: 42px;

    border-radius: 11px;

    background:
        #edf5ff;

    color:
        #0066b3;

    display: flex;

    align-items: center;

    justify-content: center;

    flex-shrink: 0;
}

.item-icon.orange {

    background:
        #fff4e8;

    color:
        #f08a00;
}


/* =========================================================
   EMPTY
========================================================= */

.empty-box {

    padding: 45px 20px;

    text-align: center;

    color: #9ca3af;
}

.empty-box i {

    opacity: .5;
}

.empty-box div {

    font-size: 13px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width: 992px) {

    .machine-detail-header {

        padding: 20px;

    }

    .machine-image-box {

        width: 120px;

        height: 120px;

    }

}

@media(max-width: 768px) {

    .detail-page-header {

        padding: 14px;

    }

    .detail-page-title {

        font-size: 18px;

    }

    .detail-page-subtitle {

        font-size: 11px;

    }

    .detail-page-actions {

        width: 100%;

    }

    .detail-page-actions .btn {

        flex: 1;

    }

    .machine-detail-header {

        padding: 18px;

    }

    .machine-image-box {

        width: 100px;

        height: 100px;

    }

    .detail-section-header {

        align-items: flex-start;

    }

}

</style>


<div class="container-fluid p-0 machine-detail-page">


    <!-- =====================================================
         WHITE PAGE HEADER
    ====================================================== -->

    <div class="detail-page-header">

        <div class="detail-page-header-inner">


            <!-- KIRI -->

            <div class="detail-page-left">

                <a
                    href="index.php"
                    class="detail-back"
                    title="Kembali ke Data Mesin"
                >

                    <i class="bi bi-arrow-left"></i>

                </a>

                <div>

                    <h2 class="detail-page-title">
                        Detail Mesin
                    </h2>

                    <div class="detail-page-subtitle">
                        Informasi mesin, struktur sub mesin,
                        komponen, dan riwayat maintenance
                    </div>

                </div>

            </div>


            <!-- KANAN -->

            <div class="detail-page-actions">


                <!-- DOWNLOAD PDF -->

                <a
                    href="download_pdf.php?id=<?= $id ?>"
                    class="btn btn-danger btn-sm px-3"
                    target="_blank"
                >

                    <i class="bi bi-file-earmark-pdf me-1"></i>

                    Download PDF

                </a>


                <!-- EDIT -->

                <a
                    href="edit.php?id=<?= $id ?>"
                    class="btn btn-warning btn-sm px-3"
                >

                    <i class="bi bi-pencil-square me-1"></i>

                    Edit Mesin

                </a>

            </div>

        </div>

    </div>



    <!-- =====================================================
         HEADER MESIN
    ====================================================== -->

    <div class="machine-detail-header mb-4">

        <div class="row align-items-center g-4">


            <!-- FOTO -->

            <div class="col-auto">

                <div class="machine-image-box">

                    <?php

                    $gambar = $mesin['gambar'] ?? '';

                    $gambar_path = "../uploads/mesin/" . $gambar;

                    if (
                        !empty($gambar) &&
                        file_exists($gambar_path)
                    ):

                    ?>

                        <img
                            src="<?= htmlspecialchars($gambar_path) ?>"
                            alt="Foto Mesin"
                        >

                    <?php else: ?>

                        <i
                            class="bi bi-gear-wide-connected"
                            style="
                                font-size:65px;
                                opacity:.5;
                            "
                        ></i>

                    <?php endif; ?>

                </div>

            </div>



            <!-- INFORMASI MESIN -->

            <div class="col">

                <div
                    class="small opacity-75 mb-1"
                    style="
                        font-weight:700;
                        letter-spacing:.7px;
                    "
                >
                    MESIN INDUK
                </div>


                <h2 class="fw-bold mb-3">

                    <?= htmlspecialchars(
                        $mesin['nama_mesin'] ?? '-'
                    ) ?>

                </h2>


                <div class="row g-3">


                    <!-- SERIAL -->

                    <div class="col-lg-3 col-md-6">

                        <div class="machine-info-label">
                            Serial Number
                        </div>

                        <div class="machine-info-value">

                            <?= htmlspecialchars(
                                $mesin['serial_number'] ?? '-'
                            ) ?>

                        </div>

                    </div>


                    <!-- AREA -->

                    <div class="col-lg-3 col-md-6">

                        <div class="machine-info-label">
                            Area
                        </div>

                        <div class="machine-info-value">

                            <?= htmlspecialchars(
                                $mesin['nama_area'] ?? '-'
                            ) ?>

                        </div>

                    </div>


                    <!-- LOKASI -->

                    <div class="col-lg-3 col-md-6">

                        <div class="machine-info-label">
                            Lokasi
                        </div>

                        <div class="machine-info-value">

                            <?= htmlspecialchars(
                                $mesin['lokasi'] ?? '-'
                            ) ?>

                        </div>

                    </div>


                    <!-- JENIS -->

                    <div class="col-lg-3 col-md-6">

                        <div class="machine-info-label">
                            Jenis Mesin
                        </div>

                        <div class="machine-info-value">

                            <?= htmlspecialchars(
                                $mesin['nama_jenis_mesin'] ?? '-'
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


        <!-- SUB MESIN -->

        <div class="col-md-4">

            <div class="detail-stat">

                <div class="d-flex align-items-center gap-3">

                    <div class="detail-stat-icon">

                        <i class="bi bi-diagram-3"></i>

                    </div>

                    <div>

                        <div class="text-muted small">
                            SUB MESIN
                        </div>

                        <div class="detail-stat-number">

                            <?= $total_sub ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- KOMPONEN -->

        <div class="col-md-4">

            <div class="detail-stat">

                <div class="d-flex align-items-center gap-3">

                    <div class="detail-stat-icon">

                        <i class="bi bi-cpu"></i>

                    </div>

                    <div>

                        <div class="text-muted small">
                            KOMPONEN
                        </div>

                        <div class="detail-stat-number">

                            <?= $total_komponen ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- MAINTENANCE -->

        <div class="col-md-4">

            <div class="detail-stat">

                <div class="d-flex align-items-center gap-3">

                    <div class="detail-stat-icon">

                        <i class="bi bi-tools"></i>

                    </div>

                    <div>

                        <div class="text-muted small">
                            MAINTENANCE
                        </div>

                        <div class="detail-stat-number">

                            <?= $total_maintenance ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>



    <!-- =====================================================
         SUB MESIN
    ====================================================== -->

    <div class="detail-section mb-4">

        <div class="detail-section-header">

            <div>

                <div class="detail-section-title">

                    <i class="bi bi-diagram-3 text-primary me-2"></i>

                    Sub Mesin

                </div>

                <div class="detail-section-subtitle">

                    Sub mesin yang terhubung dengan mesin ini

                </div>

            </div>


            <span class="badge bg-primary-subtle text-primary">

                <?= $total_sub ?> Sub Mesin

            </span>

        </div>


        <?php if (!empty($data_sub)): ?>

            <?php foreach ($data_sub as $sub): ?>

                <a
                    href="../sub_mesin/detail.php?id=<?= intval($sub['id']) ?>"
                    class="clickable-row"
                >

                    <div
                        class="d-flex align-items-center gap-3 p-3 border-bottom"
                    >

                        <div class="item-icon">

                            <i class="bi bi-diagram-3"></i>

                        </div>


                        <div class="flex-grow-1">

                            <div class="fw-bold">

                                <?= htmlspecialchars(
                                    $sub['nama_sub_mesin'] ?? '-'
                                ) ?>

                            </div>

                            <small class="text-muted">

                                <?= intval(
                                    $sub['total_komponen']
                                ) ?>

                                Komponen

                                <span class="mx-1">•</span>

                                <?= intval(
                                    $sub['total_maintenance']
                                ) ?>

                                Maintenance

                            </small>

                        </div>


                        <div class="text-end me-2">

                            <span
                                class="badge bg-light text-primary border"
                            >

                                Lihat Detail

                            </span>

                        </div>


                        <i class="bi bi-chevron-right text-muted"></i>

                    </div>

                </a>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="empty-box">

                <i class="bi bi-diagram-3 fs-1"></i>

                <div class="mt-2">
                    Belum ada sub mesin.
                </div>

            </div>

        <?php endif; ?>

    </div>



    <!-- =====================================================
         KOMPONEN
    ====================================================== -->

    <div class="detail-section mb-4">

        <div class="detail-section-header">

            <div>

                <div class="detail-section-title">

                    <i class="bi bi-cpu text-primary me-2"></i>

                    Komponen

                </div>

                <div class="detail-section-subtitle">

                    Komponen yang terpasang pada mesin

                </div>

            </div>


            <span class="badge bg-primary-subtle text-primary">

                <?= $total_komponen ?> Komponen

            </span>

        </div>


        <?php if (!empty($data_komponen)): ?>

            <?php foreach ($data_komponen as $komp): ?>

                <a
                    href="../komponen/detail.php?id=<?= intval($komp['id']) ?>"
                    class="clickable-row"
                >

                    <div
                        class="d-flex align-items-center gap-3 p-3 border-bottom"
                    >

                        <div class="item-icon">

                            <i class="bi bi-cpu"></i>

                        </div>


                        <div class="flex-grow-1">

                            <div class="fw-bold">

                                <?= htmlspecialchars(
                                    $komp['nama_bagian'] ?? '-'
                                ) ?>

                            </div>

                            <div class="small text-muted">

                                Sub Mesin:

                                <?= htmlspecialchars(
                                    $komp['nama_sub_mesin'] ?? '-'
                                ) ?>

                            </div>

                        </div>


                        <div class="text-end me-2">

                            <?php

                            $kondisi =
                                $komp['kondisi']
                                ?? 'Baik';

                            if ($kondisi == 'Baik') {

                                $badge = 'bg-success';

                            } elseif (
                                $kondisi ==
                                'Perlu Pemeriksaan'
                            ) {

                                $badge =
                                    'bg-warning text-dark';

                            } else {

                                $badge =
                                    'bg-danger';

                            }

                            ?>

                            <span
                                class="badge <?= $badge ?> rounded-pill"
                            >

                                <?= htmlspecialchars(
                                    $kondisi
                                ) ?>

                            </span>

                        </div>


                        <i class="bi bi-chevron-right text-muted"></i>

                    </div>

                </a>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="empty-box">

                <i class="bi bi-cpu fs-1"></i>

                <div class="mt-2">
                    Belum ada komponen.
                </div>

            </div>

        <?php endif; ?>

    </div>



    <!-- =====================================================
         RIWAYAT MAINTENANCE
    ====================================================== -->

    <div class="detail-section mb-4">

        <div class="detail-section-header">

            <div>

                <div class="detail-section-title">

                    <i class="bi bi-tools text-warning me-2"></i>

                    Riwayat Maintenance

                </div>

                <div class="detail-section-subtitle">

                    Riwayat maintenance yang berkaitan dengan mesin

                </div>

            </div>


            <span
                class="badge bg-warning-subtle text-warning-emphasis"
            >

                <?= $total_maintenance ?>
                Maintenance

            </span>

        </div>


        <?php if (!empty($data_maintenance)): ?>

            <?php foreach ($data_maintenance as $maint): ?>

                <a
                    href="../maintenance/detail.php?id=<?= intval($maint['id']) ?>"
                    class="clickable-row"
                >

                    <div
                        class="d-flex align-items-center gap-3 p-3 border-bottom"
                    >

                        <div class="item-icon orange">

                            <i class="bi bi-tools"></i>

                        </div>


                        <div class="flex-grow-1">

                            <div class="fw-bold">

                                <?= htmlspecialchars(
                                    $maint['nama_bagian'] ?? '-'
                                ) ?>

                            </div>


                            <div class="small text-muted">

                                <?= htmlspecialchars(
                                    $maint['tindakan'] ?? '-'
                                ) ?>

                                <span class="mx-1">•</span>

                                <?= !empty($maint['tanggal'])
                                    ? date(
                                        'd M Y H:i',
                                        strtotime(
                                            $maint['tanggal']
                                        )
                                      )
                                    : '-'
                                ?>

                            </div>

                        </div>


                        <div class="text-end me-2">

                            <?php

                            $status =
                                $maint['status']
                                ?? 'Pending';

                            if (
                                $status ==
                                'Selesai'
                            ) {

                                $badge_status =
                                    'bg-success';

                            } elseif (
                                $status ==
                                'Proses'
                            ) {

                                $badge_status =
                                    'bg-warning text-dark';

                            } else {

                                $badge_status =
                                    'bg-danger';

                            }

                            ?>

                            <span
                                class="badge <?= $badge_status ?> rounded-pill"
                            >

                                <?= htmlspecialchars(
                                    $status
                                ) ?>

                            </span>

                        </div>


                        <i class="bi bi-chevron-right text-muted"></i>

                    </div>

                </a>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="empty-box">

                <i class="bi bi-tools fs-1"></i>

                <div class="mt-2">
                    Belum ada riwayat maintenance.
                </div>

            </div>

        <?php endif; ?>

    </div>


</div>


<?php include "../template/footer.php"; ?>
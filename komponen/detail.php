<?php
include "../koneksi.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

/* =========================================================
   AMBIL DATA KOMPONEN
========================================================= */

$stmt = mysqli_prepare($conn, "
    SELECT 
        k.*,

        m.nama_mesin AS nama_m,
        m.serial_number AS sn_mesin,

        sm.nama_sub_mesin AS nama_s,

        jm.nama_jenis_mesin AS nama_jenis,

        ab.nama_area AS nama_area,
        ab.lokasi AS lokasi_aktual

    FROM komponen k

    LEFT JOIN sub_mesin sm 
        ON k.id_sub_mesin = sm.id

    LEFT JOIN mesin m 
        ON sm.id_mesin = m.id

    LEFT JOIN jenis_mesin jm 
        ON m.id_jenis_mesin = jm.id

    LEFT JOIN area_bagian ab 
        ON m.id_area = ab.id

    WHERE k.id = ?
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
   KONDISI KOMPONEN
========================================================= */

$kondisi = $d['kondisi'] ?? 'Baik';

$badgeKondisi =
    'bg-success-subtle text-success border border-success-subtle';

$iconKondisi = 'bi-check-circle-fill';

if ($kondisi == 'Dalam Perbaikan') {

    $badgeKondisi =
        'bg-danger-subtle text-danger border border-danger-subtle';

    $iconKondisi = 'bi-tools';

} elseif ($kondisi == 'Perlu Pemeriksaan') {

    $badgeKondisi =
        'bg-warning-subtle text-warning-emphasis border border-warning-subtle';

    $iconKondisi = 'bi-exclamation-circle-fill';

} elseif ($kondisi == 'Rusak') {

    $badgeKondisi =
        'bg-danger-subtle text-danger border border-danger-subtle';

    $iconKondisi = 'bi-x-circle-fill';
}


/* =========================================================
   DATA TAMPILAN
========================================================= */

$namaKomponen =
    !empty($d['nama_bagian'])
        ? $d['nama_bagian']
        : '-';

$snKomponen =
    !empty($d['serial_number'])
        ? $d['serial_number']
        : '-';

$snMesin =
    !empty($d['sn_mesin'])
        ? $d['sn_mesin']
        : '-';

$namaMesin =
    !empty($d['nama_m'])
        ? $d['nama_m']
        : (
            !empty($d['mesin'])
                ? $d['mesin']
                : '-'
        );

$namaSubMesin =
    !empty($d['nama_s'])
        ? $d['nama_s']
        : (
            !empty($d['sub_mesin'])
                ? $d['sub_mesin']
                : '-'
        );

$namaJenis =
    !empty($d['nama_jenis'])
        ? $d['nama_jenis']
        : '-';

$namaArea =
    !empty($d['nama_area'])
        ? $d['nama_area']
        : '-';

$lokasi =
    !empty($d['lokasi_aktual'])
        ? $d['lokasi_aktual']
        : (
            !empty($d['lokasi'])
                ? $d['lokasi']
                : '-'
        );


/* =========================================================
   FOTO KOMPONEN
========================================================= */

$gambar_path =
    "../uploads/komponen/" .
    ($d['gambar'] ?? '');

$ada_gambar =
    !empty($d['gambar']) &&
    file_exists($gambar_path);


include "../template/header.php";
?>


<style>

/* =========================================================
   DETAIL KOMPONEN
========================================================= */

.detail-component-page {
    width: 100%;
}


/* =========================================================
   HEADER ATAS
========================================================= */

.detail-hero {

    background:
        linear-gradient(
            135deg,
            #ffffff 0%,
            #f7fbff 100%
        );

    border:
        1px solid #e4eaf2;

    border-radius:
        16px;

    padding:
        16px 20px;

    box-shadow:
        0 4px 15px rgba(15, 23, 42, .04);
}


.detail-hero-inner {

    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        15px;

    flex-wrap:
        wrap;
}


.detail-hero-left {

    display:
        flex;

    align-items:
        center;

    gap:
        12px;
}


.detail-back {

    width:
        40px;

    height:
        40px;

    border-radius:
        10px;

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        #f8fafc;

    border:
        1px solid #e2e8f0;

    color:
        #475569;

    text-decoration:
        none;

    transition:
        .2s;
}


.detail-back:hover {

    background:
        #005baa;

    border-color:
        #005baa;

    color:
        #fff;
}


.detail-page-title {

    font-size:
        21px;

    font-weight:
        800;

    color:
        #172033;

    margin:
        0;
}


.detail-page-subtitle {

    font-size:
        12px;

    color:
        #94a3b8;

    margin-top:
        3px;
}


/* =========================================================
   ACTION HEADER
========================================================= */

.detail-header-actions {

    display:
        flex;

    gap:
        8px;

    flex-wrap:
        wrap;
}


.detail-header-actions .btn {

    border-radius:
        8px;

    font-weight:
        600;
}


/* =========================================================
   MAIN CARD
========================================================= */

.detail-main-card {

    background:
        #ffffff;

    border:
        1px solid #e4eaf2;

    border-radius:
        16px;

    overflow:
        hidden;

    box-shadow:
        0 4px 18px rgba(15, 23, 42, .05);
}


/* =========================================================
   SECTION
========================================================= */

.detail-section {

    background:
        #ffffff;

    border:
        1px solid #e8edf3;

    border-radius:
        14px;

    overflow:
        hidden;
}


.detail-section-header {

    background:
        #f8fafc;

    border-bottom:
        1px solid #e8edf3;

    padding:
        13px 16px;
}


.detail-section-body {

    padding:
        16px;
}


/* =========================================================
   FOTO
========================================================= */

.detail-photo {

    background:
        linear-gradient(
            145deg,
            #f8fafc,
            #eef4fa
        );

    border:
        1px solid #e3eaf2;

    border-radius:
        14px;

    min-height:
        280px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    overflow:
        hidden;
}


.detail-photo img {

    max-width:
        100%;

    max-height:
        300px;

    object-fit:
        contain;

    transition:
        transform .25s ease;
}


.detail-photo img:hover {

    transform:
        scale(1.03);
}


/* =========================================================
   INFO
========================================================= */

.info-row {

    display:
        flex;

    align-items:
        flex-start;

    gap:
        15px;

    padding:
        11px 0;

    border-bottom:
        1px dashed #e5e7eb;
}


.info-row:last-child {

    border-bottom:
        0;
}


.info-label {

    width:
        145px;

    min-width:
        145px;

    color:
        #64748b;

    font-size:
        12px;

    font-weight:
        500;
}


.info-value {

    flex:
        1;

    color:
        #172033;

    font-size:
        13px;

    font-weight:
        600;

    word-break:
        break-word;
}


.code-badge {

    display:
        inline-flex;

    align-items:
        center;

    gap:
        7px;

    background:
        #f8fafc;

    border:
        1px solid #dbe3ec;

    border-radius:
        7px;

    padding:
        5px 9px;

    color:
        #334155;

    font-family:
        monospace;

    font-size:
        12px;
}


.status-badge {

    display:
        inline-flex;

    align-items:
        center;

    gap:
        7px;

    padding:
        7px 11px;

    border-radius:
        8px;

    font-size:
        12px;

    font-weight:
        700;
}


/* =========================================================
   SPESIFIKASI
========================================================= */

.spec-grid {

    display:
        grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap:
        10px;
}


.spec-item {

    background:
        #f8fafc;

    border:
        1px solid #e8edf3;

    border-radius:
        10px;

    padding:
        12px;

    min-height:
        72px;
}


.spec-label {

    color:
        #64748b;

    font-size:
        11px;

    margin-bottom:
        5px;
}


.spec-value {

    color:
        #172033;

    font-size:
        13px;

    font-weight:
        600;

    word-break:
        break-word;
}


/* =========================================================
   HIERARKI
========================================================= */

.hierarchy-box {

    background:
        linear-gradient(
            135deg,
            #f8fbff,
            #f1f7fd
        );

    border:
        1px solid #dce9f5;

    border-radius:
        12px;

    padding:
        14px;
}


.hierarchy-item {

    position:
        relative;

    padding-left:
        28px;

    padding-bottom:
        13px;
}


.hierarchy-item:last-child {

    padding-bottom:
        0;
}


.hierarchy-item:not(:last-child)::before {

    content:
        "";

    position:
        absolute;

    left:
        9px;

    top:
        22px;

    width:
        1px;

    height:
        calc(100% - 8px);

    background:
        #cbd5e1;
}


.hierarchy-icon {

    position:
        absolute;

    left:
        0;

    top:
        1px;

    width:
        19px;

    height:
        19px;

    border-radius:
        50%;

    background:
        #ffffff;

    border:
        2px solid #0d6efd;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    font-size:
        8px;

    color:
        #0d6efd;
}


.hierarchy-label {

    display:
        block;

    color:
        #64748b;

    font-size:
        10px;

    margin-bottom:
        2px;
}


.hierarchy-value {

    color:
        #172033;

    font-size:
        12px;

    font-weight:
        700;
}


/* =========================================================
   CATATAN
========================================================= */

.note-box {

    background:
        #f8fafc;

    border:
        1px solid #e5eaf0;

    border-radius:
        12px;

    padding:
        15px;

    color:
        #475569;

    font-size:
        13px;

    line-height:
        1.7;

    min-height:
        80px;
}


/* =========================================================
   FOOTER
========================================================= */

.detail-footer {

    background:
        #f8fafc;

    border-top:
        1px solid #e5e7eb;

    padding:
        13px 16px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 767px) {

    .detail-hero {

        padding:
            14px;

    }


    .detail-page-title {

        font-size:
            18px;

    }


    .detail-page-subtitle {

        font-size:
            11px;

    }


    .detail-header-actions {

        width:
            100%;

    }


    .detail-header-actions .btn {

        flex:
            1;

    }


    .spec-grid {

        grid-template-columns:
            1fr;

    }


    .info-row {

        display:
            block;

    }


    .info-label {

        width:
            auto;

        min-width:
            auto;

        margin-bottom:
            4px;

    }


    .detail-photo {

        min-height:
            220px;

    }

}

</style>


<div class="container-fluid p-0 detail-component-page">


    <!-- =====================================================
         HEADER ATAS
    ====================================================== -->

    <div class="detail-hero mb-3">

        <div class="detail-hero-inner">


            <!-- KIRI -->

            <div class="detail-hero-left">

                <a
                    href="index.php"
                    class="detail-back"
                    title="Kembali"
                >

                    <i class="bi bi-arrow-left"></i>

                </a>


                <div>

                    <h2 class="detail-page-title">

                        Detail Komponen

                    </h2>


                    <div class="detail-page-subtitle">

                        Informasi teknis, spesifikasi dan status komponen

                    </div>

                </div>

            </div>



            <!-- KANAN -->

            <div class="detail-header-actions">


                <!-- DOWNLOAD PDF -->

                <a
                    href="download_pdf.php?id=<?= intval($d['id']) ?>"
                    class="btn btn-danger btn-sm px-3"
                    target="_blank"
                >

                    <i class="bi bi-file-earmark-pdf me-1"></i>

                    Download PDF

                </a>



                <!-- EDIT -->

                <a
                    href="edit.php?id=<?= intval($d['id']) ?>"
                    class="btn btn-warning btn-sm px-3 text-dark"
                >

                    <i class="bi bi-pencil-square me-1"></i>

                    Edit Data

                </a>

            </div>

        </div>

    </div>



    <!-- =====================================================
         MAIN CARD
    ====================================================== -->

    <div class="detail-main-card">

        <div class="p-3 p-md-4">

            <div class="row g-4">


                <!-- =================================================
                     KOLOM KIRI
                ================================================== -->

                <div class="col-xl-5 col-lg-5">


                    <!-- FOTO -->

                    <div class="detail-section mb-3">

                        <div class="detail-section-header">

                            <div class="d-flex align-items-center gap-2">

                                <i class="bi bi-image text-primary"></i>

                                <span class="fw-bold small">

                                    Foto Komponen

                                </span>

                            </div>

                        </div>


                        <div class="detail-section-body">

                            <div class="detail-photo">

                                <?php if ($ada_gambar): ?>

                                    <img
                                        src="<?= htmlspecialchars($gambar_path) ?>"
                                        alt="Foto <?= htmlspecialchars($namaKomponen) ?>"
                                        class="img-fluid rounded"
                                    >

                                <?php else: ?>

                                    <div class="text-center text-muted">

                                        <i
                                            class="bi bi-image fs-1 opacity-25 d-block mb-2"
                                        ></i>

                                        <div class="small">

                                            Tidak ada foto komponen

                                        </div>

                                    </div>

                                <?php endif; ?>

                            </div>

                        </div>

                    </div>



                    <!-- INFORMASI UTAMA -->

                    <div class="detail-section mb-3">

                        <div class="detail-section-header">

                            <div class="d-flex align-items-center gap-2">

                                <i class="bi bi-info-circle text-primary"></i>

                                <span class="fw-bold small">

                                    Informasi Komponen

                                </span>

                            </div>

                        </div>


                        <div class="detail-section-body">


                            <!-- NAMA -->

                            <div class="info-row">

                                <div class="info-label">

                                    Nama Komponen

                                </div>


                                <div class="info-value fs-6">

                                    <?= htmlspecialchars($namaKomponen) ?>

                                </div>

                            </div>



                            <!-- SERIAL NUMBER -->

                            <div class="info-row">

                                <div class="info-label">

                                    Serial Number

                                </div>


                                <div class="info-value">

                                    <span class="code-badge">

                                        <i class="bi bi-upc-scan"></i>

                                        <?= htmlspecialchars($snKomponen) ?>

                                    </span>

                                </div>

                            </div>



                            <!-- KONDISI -->

                            <div class="info-row">

                                <div class="info-label">

                                    Kondisi

                                </div>


                                <div class="info-value">

                                    <span class="status-badge <?= $badgeKondisi ?>">

                                        <i class="bi <?= $iconKondisi ?>"></i>

                                        <?= htmlspecialchars($kondisi) ?>

                                    </span>

                                </div>

                            </div>


                        </div>

                    </div>



                    <!-- HIERARKI MESIN -->

                    <div class="detail-section">

                        <div class="detail-section-header">

                            <div class="d-flex align-items-center gap-2">

                                <i class="bi bi-diagram-3 text-primary"></i>

                                <span class="fw-bold small">

                                    Struktur Penempatan

                                </span>

                            </div>

                        </div>


                        <div class="detail-section-body">

                            <div class="hierarchy-box">


                                <!-- LOKASI -->

                                <div class="hierarchy-item">

                                    <span class="hierarchy-icon">

                                        <i class="bi bi-geo-alt"></i>

                                    </span>


                                    <span class="hierarchy-label">

                                        Lokasi

                                    </span>


                                    <span class="hierarchy-value">

                                        <?= htmlspecialchars($lokasi) ?>

                                    </span>

                                </div>



                                <!-- AREA -->

                                <div class="hierarchy-item">

                                    <span class="hierarchy-icon">

                                        <i class="bi bi-building"></i>

                                    </span>


                                    <span class="hierarchy-label">

                                        Area / Bagian

                                    </span>


                                    <span class="hierarchy-value">

                                        <?= htmlspecialchars($namaArea) ?>

                                    </span>

                                </div>



                                <!-- JENIS MESIN -->

                                <div class="hierarchy-item">

                                    <span class="hierarchy-icon">

                                        <i class="bi bi-grid"></i>

                                    </span>


                                    <span class="hierarchy-label">

                                        Jenis Mesin

                                    </span>


                                    <span class="hierarchy-value">

                                        <?= htmlspecialchars($namaJenis) ?>

                                    </span>

                                </div>



                                <!-- MESIN -->

                                <div class="hierarchy-item">

                                    <span class="hierarchy-icon">

                                        <i class="bi bi-cpu"></i>

                                    </span>


                                    <span class="hierarchy-label">

                                        Mesin Induk

                                    </span>


                                    <span class="hierarchy-value">

                                        <?= htmlspecialchars($namaMesin) ?>

                                    </span>

                                </div>



                                <!-- SUB MESIN -->

                                <div class="hierarchy-item">

                                    <span class="hierarchy-icon">

                                        <i class="bi bi-diagram-2"></i>

                                    </span>


                                    <span class="hierarchy-label">

                                        Sub Mesin

                                    </span>


                                    <span class="hierarchy-value">

                                        <?= htmlspecialchars($namaSubMesin) ?>

                                    </span>

                                </div>


                            </div>

                        </div>

                    </div>

                </div>



                <!-- =================================================
                     KOLOM KANAN
                ================================================== -->

                <div class="col-xl-7 col-lg-7">


                    <!-- SPESIFIKASI -->

                    <div class="detail-section mb-3">

                        <div class="detail-section-header">

                            <div class="d-flex align-items-center gap-2">

                                <i class="bi bi-cpu text-primary"></i>

                                <span class="fw-bold small">

                                    Spesifikasi Teknis

                                </span>

                            </div>

                        </div>


                        <div class="detail-section-body">

                            <div class="spec-grid">


                                <!-- BRAND -->

                                <div class="spec-item">

                                    <div class="spec-label">

                                        Brand / Merk

                                    </div>

                                    <div class="spec-value">

                                        <?= htmlspecialchars(
                                            !empty($d['brand'])
                                                ? $d['brand']
                                                : '-'
                                        ) ?>

                                    </div>

                                </div>



                                <!-- TIPE -->

                                <div class="spec-item">

                                    <div class="spec-label">

                                        Tipe

                                    </div>

                                    <div class="spec-value">

                                        <?= htmlspecialchars(
                                            !empty($d['tipe'])
                                                ? $d['tipe']
                                                : '-'
                                        ) ?>

                                    </div>

                                </div>



                                <!-- PART NUMBER -->

                                <div class="spec-item">

                                    <div class="spec-label">

                                        Part Number

                                    </div>

                                    <div class="spec-value">

                                        <?= htmlspecialchars(
                                            !empty($d['part_number'])
                                                ? $d['part_number']
                                                : '-'
                                        ) ?>

                                    </div>

                                </div>



                                <!-- DAYA -->

                                <div class="spec-item">

                                    <div class="spec-label">

                                        Daya

                                    </div>

                                    <div class="spec-value">

                                        <?= htmlspecialchars(
                                            !empty($d['daya'])
                                                ? $d['daya']
                                                : '-'
                                        ) ?>

                                    </div>

                                </div>



                                <!-- IO ADDRESS -->

                                <div class="spec-item">

                                    <div class="spec-label">

                                        IO Address

                                    </div>

                                    <div class="spec-value">

                                        <?= htmlspecialchars(
                                            !empty($d['io_address'])
                                                ? $d['io_address']
                                                : '-'
                                        ) ?>

                                    </div>

                                </div>



                                <!-- INPUT VOLTAGE -->

                                <div class="spec-item">

                                    <div class="spec-label">

                                        Input Voltage

                                    </div>

                                    <div class="spec-value">

                                        <?= htmlspecialchars(
                                            !empty($d['input_voltage'])
                                                ? $d['input_voltage']
                                                : '-'
                                        ) ?>

                                    </div>

                                </div>



                                <!-- FREKUENSI INPUT -->

                                <div class="spec-item">

                                    <div class="spec-label">

                                        Frekuensi Input

                                    </div>

                                    <div class="spec-value">

                                        <?= htmlspecialchars(
                                            !empty($d['frekuensi_input'])
                                                ? $d['frekuensi_input']
                                                : '-'
                                        ) ?>

                                    </div>

                                </div>



                                <!-- ARUS INPUT -->

                                <div class="spec-item">

                                    <div class="spec-label">

                                        Arus Input

                                    </div>

                                    <div class="spec-value">

                                        <?= htmlspecialchars(
                                            !empty($d['arus_input'])
                                                ? $d['arus_input']
                                                : '-'
                                        ) ?>

                                    </div>

                                </div>



                                <!-- OUTPUT -->

                                <div class="spec-item">

                                    <div class="spec-label">

                                        Output

                                    </div>

                                    <div class="spec-value">

                                        <?= htmlspecialchars(
                                            !empty($d['output'])
                                                ? $d['output']
                                                : '-'
                                        ) ?>

                                    </div>

                                </div>



                                <!-- FREKUENSI OUTPUT -->

                                <div class="spec-item">

                                    <div class="spec-label">

                                        Frekuensi Output

                                    </div>

                                    <div class="spec-value">

                                        <?= htmlspecialchars(
                                            !empty($d['frekuensi_output'])
                                                ? $d['frekuensi_output']
                                                : '-'
                                        ) ?>

                                    </div>

                                </div>



                                <!-- IP RATING -->

                                <div class="spec-item">

                                    <div class="spec-label">

                                        IP Rating

                                    </div>

                                    <div class="spec-value">

                                        <?= htmlspecialchars(
                                            !empty($d['ip_rating'])
                                                ? $d['ip_rating']
                                                : '-'
                                        ) ?>

                                    </div>

                                </div>



                                <!-- SERIAL NUMBER MESIN -->

                                <div class="spec-item">

                                    <div class="spec-label">

                                        Serial Number Mesin

                                    </div>

                                    <div class="spec-value">

                                        <span class="code-badge">

                                            <i class="bi bi-qr-code"></i>

                                            <?= htmlspecialchars($snMesin) ?>

                                        </span>

                                    </div>

                                </div>


                            </div>

                        </div>

                    </div>



                    <!-- KETERANGAN -->

                    <div class="detail-section">

                        <div class="detail-section-header">

                            <div class="d-flex align-items-center gap-2">

                                <i class="bi bi-journal-text text-primary"></i>

                                <span class="fw-bold small">

                                    Keterangan / Catatan

                                </span>

                            </div>

                        </div>


                        <div class="detail-section-body">

                            <div class="note-box">

                                <?php if (!empty($d['keterangan'])): ?>

                                    <?= nl2br(
                                        htmlspecialchars(
                                            $d['keterangan']
                                        )
                                    ) ?>

                                <?php else: ?>

                                    <span class="text-muted">

                                        Tidak ada keterangan tambahan.

                                    </span>

                                <?php endif; ?>

                            </div>

                        </div>

                    </div>


                </div>

            </div>

        </div>


        <!-- =====================================================
             FOOTER CARD
        ====================================================== -->

        <div class="detail-footer">

            <a
                href="index.php"
                class="btn btn-light border btn-sm px-3 fw-semibold"
            >

                <i class="bi bi-arrow-left me-1"></i>

                Kembali

            </a>

        </div>

    </div>

</div>


<?php include "../template/footer.php"; ?>
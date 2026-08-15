<?php
include "../koneksi.php";

/* =========================================================
   PARAMETER FILTER
========================================================= */

$tgl_mulai = isset($_GET['tgl_mulai'])
    ? trim($_GET['tgl_mulai'])
    : '';

$tgl_selesai = isset($_GET['tgl_selesai'])
    ? trim($_GET['tgl_selesai'])
    : '';

$jenis = isset($_GET['jenis'])
    ? trim($_GET['jenis'])
    : 'maintenance';

$id_mesin = isset($_GET['id_mesin'])
    ? intval($_GET['id_mesin'])
    : 0;

$id_sub_mesin = isset($_GET['id_sub_mesin'])
    ? intval($_GET['id_sub_mesin'])
    : 0;

$cari_komp = isset($_GET['cari_komponen'])
    ? trim($_GET['cari_komponen'])
    : '';

/* =========================================================
   VALIDASI JENIS LAPORAN
========================================================= */

if ($jenis !== 'maintenance' && $jenis !== 'komponen') {
    $jenis = 'maintenance';
}


/* =========================================================
   DATA MESIN
========================================================= */

$q_mesin = mysqli_query(
    $conn,
    "
    SELECT
        id,
        nama_mesin,
        serial_number
    FROM mesin
    ORDER BY nama_mesin ASC
    "
);


/* =========================================================
   DATA SUB MESIN
   HANYA BERDASARKAN MESIN YANG DIPILIH
========================================================= */

$q_sub_mesin = false;

if ($id_mesin > 0) {

    $stmt_sub_filter = mysqli_prepare(
        $conn,
        "
        SELECT
            id,
            nama_sub_mesin
        FROM sub_mesin
        WHERE id_mesin = ?
        ORDER BY nama_sub_mesin ASC
        "
    );

    mysqli_stmt_bind_param(
        $stmt_sub_filter,
        "i",
        $id_mesin
    );

    mysqli_stmt_execute(
        $stmt_sub_filter
    );

    $q_sub_mesin =
        mysqli_stmt_get_result(
            $stmt_sub_filter
        );
}


/* =========================================================
   QUERY LAPORAN
========================================================= */

$sql = false;
$stmt = null;

if ($jenis === 'maintenance') {

    /* =====================================================
       LAPORAN RIWAYAT MAINTENANCE
    ====================================================== */

    $query = "
        SELECT
            rm.*,

            k.nama_bagian,
            k.serial_number AS serial_number_komponen,
            k.brand,
            k.tipe,

            sm.id AS id_sub_mesin,
            sm.nama_sub_mesin,

            m.id AS id_mesin,
            m.nama_mesin,
            m.serial_number AS serial_number_mesin

        FROM riwayat_maintenance rm

        LEFT JOIN komponen k
            ON rm.id_komponen = k.id

        LEFT JOIN sub_mesin sm
            ON k.id_sub_mesin = sm.id

        LEFT JOIN mesin m
            ON sm.id_mesin = m.id

        WHERE 1=1
    ";

    $params = [];
    $types  = "";


    /* =====================================================
       FILTER TANGGAL
    ====================================================== */

    if (!empty($tgl_mulai) && !empty($tgl_selesai)) {

        /*
         * Jika tanggal bertipe DATE:
         * BETWEEN tanggal awal dan tanggal akhir tetap aman.
         *
         * Jika DATETIME:
         * menggunakan >= tanggal 00:00:00
         * dan < tanggal berikutnya 00:00:00
         */

        $query .= "
            AND rm.tanggal >= ?
            AND rm.tanggal < DATE_ADD(?, INTERVAL 1 DAY)
        ";

        $params[] = $tgl_mulai;
        $params[] = $tgl_selesai;

        $types .= "ss";

    } elseif (!empty($tgl_mulai)) {

        $query .= "
            AND rm.tanggal >= ?
        ";

        $params[] = $tgl_mulai;

        $types .= "s";

    } elseif (!empty($tgl_selesai)) {

        $query .= "
            AND rm.tanggal < DATE_ADD(?, INTERVAL 1 DAY)
        ";

        $params[] = $tgl_selesai;

        $types .= "s";
    }


    /* =====================================================
       FILTER MESIN
    ====================================================== */

    if ($id_mesin > 0) {

        $query .= "
            AND sm.id_mesin = ?
        ";

        $params[] = $id_mesin;

        $types .= "i";
    }


    /* =====================================================
       FILTER SUB MESIN
    ====================================================== */

    if ($id_sub_mesin > 0) {

        $query .= "
            AND k.id_sub_mesin = ?
        ";

        $params[] = $id_sub_mesin;

        $types .= "i";
    }


    /* =====================================================
       SEARCH KOMPONEN
    ====================================================== */

    if ($cari_komp !== '') {

        $search = "%" . $cari_komp . "%";

        $query .= "
            AND (
                k.nama_bagian LIKE ?
                OR k.serial_number LIKE ?
                OR m.serial_number LIKE ?
                OR m.nama_mesin LIKE ?
                OR sm.nama_sub_mesin LIKE ?
            )
        ";

        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;

        $types .= "sssss";
    }


    /* =====================================================
       ORDER
    ====================================================== */

    $query .= "
        ORDER BY
            rm.tanggal DESC,
            rm.id DESC
    ";


} else {

    /* =====================================================
       LAPORAN INVENTARIS KOMPONEN
    ====================================================== */

    $query = "
        SELECT

            k.*,

            sm.id AS id_sub_mesin,
            sm.nama_sub_mesin,

            m.id AS id_mesin,
            m.nama_mesin,
            m.serial_number AS serial_number_mesin

        FROM komponen k

        LEFT JOIN sub_mesin sm
            ON k.id_sub_mesin = sm.id

        LEFT JOIN mesin m
            ON sm.id_mesin = m.id

        WHERE 1=1
    ";

    $params = [];
    $types  = "";


    /* =====================================================
       FILTER MESIN
    ====================================================== */

    if ($id_mesin > 0) {

        $query .= "
            AND sm.id_mesin = ?
        ";

        $params[] = $id_mesin;

        $types .= "i";
    }


    /* =====================================================
       FILTER SUB MESIN
    ====================================================== */

    if ($id_sub_mesin > 0) {

        $query .= "
            AND k.id_sub_mesin = ?
        ";

        $params[] = $id_sub_mesin;

        $types .= "i";
    }


    /* =====================================================
       SEARCH KOMPONEN
    ====================================================== */

    if ($cari_komp !== '') {

        $search = "%" . $cari_komp . "%";

        $query .= "
            AND (
                k.nama_bagian LIKE ?
                OR k.serial_number LIKE ?
                OR m.serial_number LIKE ?
                OR m.nama_mesin LIKE ?
                OR sm.nama_sub_mesin LIKE ?
            )
        ";

        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;
        $params[] = $search;

        $types .= "sssss";
    }


    /* =====================================================
       ORDER
    ====================================================== */

    $query .= "
        ORDER BY
            k.id DESC
    ";
}


/* =========================================================
   EXECUTE QUERY
========================================================= */

$stmt = mysqli_prepare(
    $conn,
    $query
);

if (!$stmt) {

    die(
        "Query laporan gagal dipersiapkan: "
        . htmlspecialchars(mysqli_error($conn))
    );
}


/* =========================================================
   BIND PARAMETER
========================================================= */

if (!empty($params)) {

    mysqli_stmt_bind_param(
        $stmt,
        $types,
        ...$params
    );
}


/* =========================================================
   EXECUTE
========================================================= */

if (!mysqli_stmt_execute($stmt)) {

    die(
        "Query laporan gagal dijalankan: "
        . htmlspecialchars(mysqli_stmt_error($stmt))
    );
}


$sql =
    mysqli_stmt_get_result(
        $stmt
    );


/* =========================================================
   TOTAL DATA
========================================================= */

$total_data = $sql
    ? mysqli_num_rows($sql)
    : 0;


/* =========================================================
   INCLUDE HEADER
========================================================= */

include "../template/header.php";
?>


<style>

/* =========================================================
   LAPORAN PAGE
========================================================= */

.laporan-card {

    border: 0;

    border-radius: 18px;

    background: #ffffff;

    box-shadow:
        0 4px 18px rgba(15, 23, 42, .06);

}


/* =========================================================
   HEADER
========================================================= */

.laporan-header {

    padding: 24px;
}


.laporan-header h2 {

    font-size: 22px;

    font-weight: 700;

    color: #1e293b;
}


.laporan-header p {

    font-size: 13px;

    color: #64748b;

    margin-bottom: 0;
}


/* =========================================================
   FILTER
========================================================= */

.filter-card {

    padding: 22px;
}


.filter-card .form-label {

    font-size: 12px;

    font-weight: 600;

    color: #334155;

    margin-bottom: 6px;
}


.filter-card .form-control,
.filter-card .form-select {

    font-size: 13px;

    min-height: 38px;

    border-color: #e2e8f0;
}


.filter-card .form-control:focus,
.filter-card .form-select:focus {

    border-color: #0056a6;

    box-shadow:
        0 0 0 .2rem rgba(0, 86, 166, .10);
}


/* =========================================================
   TABLE
========================================================= */

.laporan-table {

    font-size: 13px;
}


.laporan-table thead th {

    background: #f8fafc;

    color: #64748b;

    font-size: 11px;

    font-weight: 700;

    text-transform: uppercase;

    letter-spacing: .3px;

    white-space: nowrap;

    border-bottom:
        1px solid #e2e8f0;
}


.laporan-table tbody td {

    vertical-align: middle;

    border-bottom:
        1px solid #f1f5f9;
}


.laporan-table tbody tr:hover {

    background: #f8fafc;
}


/* =========================================================
   BADGE
========================================================= */

.badge-status {

    font-size: 11px;

    padding:
        6px 10px;

    border-radius: 50px;

    white-space: nowrap;
}


/* =========================================================
   EMPTY
========================================================= */

.empty-report {

    padding: 60px 20px;

    text-align: center;

    color: #94a3b8;
}


.empty-report i {

    font-size: 45px;

    opacity: .4;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 768px) {

    .laporan-header,
    .filter-card {

        padding: 16px;
    }

    .laporan-header h2 {

        font-size: 19px;
    }

}

</style>


<div class="container-fluid p-0">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="card laporan-card laporan-header mb-4">

        <div
            class="d-flex
                   justify-content-between
                   align-items-center
                   flex-wrap
                   gap-3"
        >

            <div>

                <h2 class="m-0">

                    <i
                        class="bi bi-file-earmark-bar-graph me-2"
                        style="color:#0056a6;"
                    ></i>

                    Laporan & Rekapitulasi

                </h2>

                <p class="mt-1">

                    Cetak laporan riwayat pemeliharaan
                    dan inventaris komponen mesin.

                </p>

            </div>


            <div>

                <span
                    class="badge rounded-pill px-3 py-2"
                    style="
                        background:#eef6ff;
                        color:#0056a6;
                        border:1px solid #d7eaff;
                    "
                >

                    <i class="bi bi-database me-1"></i>

                    <?= number_format($total_data) ?>
                    Data

                </span>

            </div>

        </div>

    </div>



    <!-- =====================================================
         FILTER CARD
    ====================================================== -->

    <div class="card laporan-card filter-card mb-4">

        <form
            method="GET"
            id="formFilter"
        >

            <div class="row g-3">


                <!-- =================================================
                     JENIS LAPORAN
                ================================================== -->

                <div class="col-md-3">

                    <label class="form-label">

                        Jenis Laporan

                    </label>

                    <select
                        name="jenis"
                        id="jenis"
                        class="form-select rounded-3"
                        onchange="changeJenisLaporan()"
                    >

                        <option
                            value="maintenance"
                            <?= $jenis === 'maintenance'
                                ? 'selected'
                                : '' ?>
                        >

                            Riwayat Maintenance

                        </option>


                        <option
                            value="komponen"
                            <?= $jenis === 'komponen'
                                ? 'selected'
                                : '' ?>
                        >

                            Inventaris Komponen

                        </option>

                    </select>

                </div>



                <!-- =================================================
                     MESIN
                ================================================== -->

                <div class="col-md-3">

                    <label class="form-label">

                        Filter Mesin

                    </label>

                    <select
                        name="id_mesin"
                        id="id_mesin"
                        class="form-select rounded-3"
                        onchange="loadSubMesin()"
                    >

                        <option value="">

                            -- Semua Mesin --

                        </option>


                        <?php
                        if ($q_mesin) :

                            while (
                                $m = mysqli_fetch_assoc(
                                    $q_mesin
                                )
                            ) :

                                $selected =
                                    (
                                        $id_mesin ==
                                        $m['id']
                                    )
                                    ? 'selected'
                                    : '';
                        ?>

                            <option
                                value="<?= (int)$m['id'] ?>"
                                <?= $selected ?>
                            >

                                <?= htmlspecialchars(
                                    $m['nama_mesin']
                                ) ?>

                                <?php if (
                                    !empty(
                                        $m['serial_number']
                                    )
                                ) : ?>

                                    -
                                    <?= htmlspecialchars(
                                        $m['serial_number']
                                    ) ?>

                                <?php endif; ?>

                            </option>

                        <?php
                            endwhile;
                        endif;
                        ?>

                    </select>

                </div>



                <!-- =================================================
                     SUB MESIN
                ================================================== -->

                <div class="col-md-3">

                    <label class="form-label">

                        Filter Sub Mesin

                    </label>

                    <select
                        name="id_sub_mesin"
                        id="id_sub_mesin"
                        class="form-select rounded-3"
                        <?= $id_mesin <= 0
                            ? 'disabled'
                            : '' ?>
                    >

                        <option value="">

                            <?=
                                $id_mesin > 0
                                ? '-- Semua Sub Mesin --'
                                : '-- Pilih Mesin Dahulu --'
                            ?>

                        </option>


                        <?php

                        if (
                            $q_sub_mesin &&
                            $id_mesin > 0
                        ) :

                            while (
                                $s =
                                mysqli_fetch_assoc(
                                    $q_sub_mesin
                                )
                            ) :

                                $selected =
                                    (
                                        $id_sub_mesin ==
                                        $s['id']
                                    )
                                    ? 'selected'
                                    : '';

                        ?>

                            <option
                                value="<?= (int)$s['id'] ?>"
                                <?= $selected ?>
                            >

                                <?= htmlspecialchars(
                                    $s['nama_sub_mesin']
                                ) ?>

                            </option>

                        <?php
                            endwhile;
                        endif;
                        ?>

                    </select>

                </div>



                <!-- =================================================
                     SEARCH
                ================================================== -->

                <div class="col-md-3">

                    <label class="form-label">

                        Cari Komponen

                    </label>

                    <input
                        type="text"
                        name="cari_komponen"
                        class="form-control rounded-3"
                        value="<?= htmlspecialchars(
                            $cari_komp
                        ) ?>"
                        placeholder="Nama komponen / SN..."
                    >

                </div>



                <!-- =================================================
                     TANGGAL
                ================================================== -->

                <?php if (
                    $jenis === 'maintenance'
                ) : ?>

                    <div class="col-md-3">

                        <label class="form-label">

                            Dari Tanggal

                        </label>

                        <input
                            type="date"
                            name="tgl_mulai"
                            class="form-control rounded-3"
                            value="<?= htmlspecialchars(
                                $tgl_mulai
                            ) ?>"
                        >

                    </div>


                    <div class="col-md-3">

                        <label class="form-label">

                            Sampai Tanggal

                        </label>

                        <input
                            type="date"
                            name="tgl_selesai"
                            class="form-control rounded-3"
                            value="<?= htmlspecialchars(
                                $tgl_selesai
                            ) ?>"
                        >

                    </div>

                <?php endif; ?>


            </div>



            <!-- =====================================================
                 BUTTON
            ====================================================== -->

            <div
                class="
                    d-flex
                    flex-wrap
                    align-items-center
                    gap-2
                    mt-4
                    pt-3
                    border-top
                "
            >


                <!-- FILTER -->

                <button
                    type="submit"
                    class="btn btn-primary rounded-3 fw-semibold px-4"
                    style="
                        background:#0056a6;
                        border-color:#0056a6;
                    "
                >

                    <i class="bi bi-funnel me-1"></i>

                    Filter

                </button>



                <!-- RESET -->

                <a
                    href="index.php"
                    class="btn btn-light border rounded-3 fw-semibold px-4"
                >

                    <i class="bi bi-arrow-counterclockwise me-1"></i>

                    Reset

                </a>



                <!-- SPACER -->

                <div class="ms-auto d-flex gap-2 flex-wrap">


                    <!-- PDF -->

                    <a
                        href="cetak.php?jenis=<?= urlencode($jenis) ?>&tgl_mulai=<?= urlencode($tgl_mulai) ?>&tgl_selesai=<?= urlencode($tgl_selesai) ?>&id_mesin=<?= urlencode($id_mesin) ?>&id_sub_mesin=<?= urlencode($id_sub_mesin) ?>&cari_komponen=<?= urlencode($cari_komp) ?>"
                        target="_blank"
                        class="btn btn-outline-danger rounded-3 fw-semibold px-3"
                    >

                        <i class="bi bi-printer me-1"></i>

                        Cetak PDF

                    </a>



                    <!-- EXCEL -->

                    <a
                        href="export_excel.php?jenis=<?= urlencode($jenis) ?>&tgl_mulai=<?= urlencode($tgl_mulai) ?>&tgl_selesai=<?= urlencode($tgl_selesai) ?>&id_mesin=<?= urlencode($id_mesin) ?>&id_sub_mesin=<?= urlencode($id_sub_mesin) ?>&cari_komponen=<?= urlencode($cari_komp) ?>"
                        class="btn btn-success rounded-3 fw-semibold px-3"
                        style="
                            background:#198754;
                            border-color:#198754;
                        "
                    >

                        <i class="bi bi-file-earmark-excel me-1"></i>

                        Excel (.xls)

                    </a>

                </div>

            </div>

        </form>

    </div>



    <!-- =====================================================
         PREVIEW LAPORAN
    ====================================================== -->

    <div class="card laporan-card overflow-hidden">


        <!-- =================================================
             TABLE HEADER
        ================================================== -->

        <div
            class="
                p-4
                border-bottom
                d-flex
                justify-content-between
                align-items-center
                flex-wrap
                gap-2
            "
        >

            <div>

                <h5
                    class="fw-bold m-0"
                    style="color:#0056a6;"
                >

                    <i class="bi bi-file-earmark-text me-2"></i>

                    Preview Data Laporan

                </h5>

                <small class="text-muted">

                    <?=
                        $jenis === 'maintenance'
                        ? 'Riwayat Maintenance'
                        : 'Inventaris Komponen'
                    ?>

                </small>

            </div>


            <span
                class="badge rounded-pill px-3 py-2"
                style="
                    background:#f1f5f9;
                    color:#0056a6;
                    border:1px solid #e2e8f0;
                "
            >

                Total:
                <?= number_format($total_data) ?>
                Data

            </span>

        </div>



        <!-- =================================================
             TABLE
        ================================================== -->

        <div class="table-responsive">

            <?php if (
                $jenis === 'maintenance'
            ) : ?>


                <!-- =========================================
                     TABLE MAINTENANCE
                ========================================== -->

                <table
                    class="
                        table
                        table-hover
                        align-middle
                        mb-0
                        laporan-table
                    "
                >

                    <thead>

                        <tr>

                            <th
                                width="50"
                                class="text-center"
                            >
                                NO
                            </th>

                            <th width="110">

                                TANGGAL

                            </th>

                            <th>

                                MESIN

                            </th>

                            <th>

                                SUB MESIN

                            </th>

                            <th>

                                KOMPONEN

                            </th>

                            <th>

                                DETAIL PERBAIKAN / TINDAKAN

                            </th>

                            <th>

                                TEKNISI

                            </th>

                            <th
                                width="150"
                                class="text-center"
                            >

                                STATUS & AKSI

                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php

                        $no = 1;

                        if (
                            $sql &&
                            mysqli_num_rows($sql) > 0
                        ) :

                            while (
                                $d =
                                mysqli_fetch_assoc($sql)
                            ) :

                                $status =
                                    $d['status'] ?? '-';


                                if (
                                    strtolower(
                                        $status
                                    ) === 'proses'
                                ) {

                                    $badgeStatus =
                                        'bg-warning text-dark';

                                } elseif (
                                    strtolower(
                                        $status
                                    ) === 'pending'
                                ) {

                                    $badgeStatus =
                                        'bg-danger';

                                } elseif (
                                    strtolower(
                                        $status
                                    ) === 'selesai'
                                ) {

                                    $badgeStatus =
                                        'bg-success';

                                } else {

                                    $badgeStatus =
                                        'bg-secondary';
                                }

                        ?>

                            <tr>


                                <!-- NO -->

                                <td
                                    class="text-center text-secondary"
                                >

                                    <?= $no++ ?>

                                </td>



                                <!-- TANGGAL -->

                                <td>

                                    <span
                                        class="fw-semibold text-dark"
                                    >

                                        <?php
                                        if (
                                            !empty(
                                                $d['tanggal']
                                            )
                                        ) {

                                            echo date(
                                                'd/m/Y',
                                                strtotime(
                                                    $d['tanggal']
                                                )
                                            );

                                        } else {

                                            echo '-';
                                        }
                                        ?>

                                    </span>

                                </td>



                                <!-- MESIN -->

                                <td>

                                    <strong
                                        style="
                                            color:#0056a6;
                                        "
                                    >

                                        <?= htmlspecialchars(
                                            $d['nama_mesin']
                                            ?? '-'
                                        ) ?>

                                    </strong>


                                    <small
                                        class="
                                            d-block
                                            text-muted
                                        "
                                    >

                                        SN Mesin:
                                        <?= htmlspecialchars(
                                            $d[
                                                'serial_number_mesin'
                                            ]
                                            ?? '-'
                                        ) ?>

                                    </small>

                                </td>



                                <!-- SUB MESIN -->

                                <td>

                                    <?= htmlspecialchars(
                                        $d[
                                            'nama_sub_mesin'
                                        ]
                                        ?? '-'
                                    ) ?>

                                </td>



                                <!-- KOMPONEN -->

                                <td>

                                    <strong
                                        class="
                                            text-dark
                                            d-block
                                        "
                                    >

                                        <?= htmlspecialchars(
                                            $d[
                                                'nama_bagian'
                                            ]
                                            ?? '-'
                                        ) ?>

                                    </strong>


                                    <small
                                        class="
                                            text-muted
                                        "
                                    >

                                        SN:
                                        <?= htmlspecialchars(
                                            $d[
                                                'serial_number_komponen'
                                            ]
                                            ?? '-'
                                        ) ?>

                                    </small>

                                </td>



                                <!-- TINDAKAN -->

                                <td>

                                    <small
                                        class="text-dark"
                                    >

                                        <?= htmlspecialchars(
                                            $d[
                                                'tindakan'
                                            ]
                                            ?? '-'
                                        ) ?>

                                    </small>

                                </td>



                                <!-- TEKNISI -->

                                <td>

                                    <small
                                        class="text-muted"
                                    >

                                        <i
                                            class="
                                                bi
                                                bi-person
                                                me-1
                                            "
                                        ></i>

                                        <?= htmlspecialchars(
                                            $d[
                                                'teknisi'
                                            ]
                                            ?? '-'
                                        ) ?>

                                    </small>

                                </td>



                                <!-- STATUS -->

                                <td
                                    class="text-center"
                                >

                                    <div
                                        class="
                                            d-flex
                                            justify-content-center
                                            align-items-center
                                            gap-1
                                        "
                                    >

                                        <span
                                            class="
                                                badge
                                                <?= $badgeStatus ?>
                                                badge-status
                                            "
                                        >

                                            <?= htmlspecialchars(
                                                $status
                                            ) ?>

                                        </span>


                                        <?php
                                        if (
                                            !empty(
                                                $d['id']
                                            )
                                        ) :
                                        ?>

                                            <a
                                                href="cetak.php?jenis=single_maintenance&id=<?= (int)$d['id'] ?>"
                                                target="_blank"
                                                class="
                                                    btn
                                                    btn-sm
                                                    btn-outline-secondary
                                                    rounded-pill
                                                    px-2
                                                "
                                                title="Cetak data"
                                            >

                                                <i
                                                    class="
                                                        bi
                                                        bi-printer
                                                    "
                                                ></i>

                                            </a>

                                        <?php endif; ?>

                                    </div>

                                </td>


                            </tr>

                        <?php

                            endwhile;

                        else :

                        ?>

                            <tr>

                                <td
                                    colspan="8"
                                    class="empty-report"
                                >

                                    <i
                                        class="
                                            bi
                                            bi-inbox
                                            d-block
                                            mb-3
                                        "
                                    ></i>

                                    <strong
                                        class="
                                            d-block
                                            text-secondary
                                            mb-1
                                        "
                                    >

                                        Data tidak ditemukan

                                    </strong>

                                    <small>

                                        Tidak ada riwayat
                                        maintenance yang
                                        sesuai dengan filter.

                                    </small>

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>



            <?php else : ?>


                <!-- =========================================
                     TABLE KOMPONEN
                ========================================== -->

                <table
                    class="
                        table
                        table-hover
                        align-middle
                        mb-0
                        laporan-table
                    "
                >

                    <thead>

                        <tr>

                            <th
                                width="50"
                                class="text-center"
                            >

                                NO

                            </th>

                            <th>

                                NAMA KOMPONEN

                            </th>

                            <th>

                                SN KOMPONEN

                            </th>

                            <th>

                                MESIN INDUK

                            </th>

                            <th>

                                SN MESIN

                            </th>

                            <th>

                                SUB MESIN

                            </th>

                            <th
                                width="160"
                                class="text-center"
                            >

                                KONDISI & AKSI

                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php

                        $no = 1;

                        if (
                            $sql &&
                            mysqli_num_rows($sql) > 0
                        ) :

                            while (
                                $d =
                                mysqli_fetch_assoc($sql)
                            ) :

                                $kondisi =
                                    $d['kondisi']
                                    ?? 'Baik';


                                if (
                                    $kondisi === 'Baik'
                                ) {

                                    $badgeKondisi =
                                        'bg-success';

                                } elseif (
                                    $kondisi ===
                                    'Perlu Pemeriksaan'
                                ) {

                                    $badgeKondisi =
                                        'bg-warning text-dark';

                                } elseif (
                                    $kondisi ===
                                    'Dalam Perbaikan'
                                ) {

                                    $badgeKondisi =
                                        'bg-warning text-dark';

                                } elseif (
                                    $kondisi === 'Rusak'
                                ) {

                                    $badgeKondisi =
                                        'bg-danger';

                                } else {

                                    $badgeKondisi =
                                        'bg-secondary';
                                }

                        ?>

                            <tr>


                                <!-- NO -->

                                <td
                                    class="text-center text-secondary"
                                >

                                    <?= $no++ ?>

                                </td>



                                <!-- KOMPONEN -->

                                <td>

                                    <strong
                                        class="text-dark"
                                    >

                                        <?= htmlspecialchars(
                                            $d[
                                                'nama_bagian'
                                            ]
                                            ?? '-'
                                        ) ?>

                                    </strong>


                                    <?php
                                    if (
                                        !empty(
                                            $d['brand']
                                        ) ||
                                        !empty(
                                            $d['tipe']
                                        )
                                    ) :
                                    ?>

                                        <small
                                            class="
                                                d-block
                                                text-muted
                                            "
                                        >

                                            <?= htmlspecialchars(
                                                $d[
                                                    'brand'
                                                ]
                                                ?? ''
                                            ) ?>

                                            <?php
                                            if (
                                                !empty(
                                                    $d[
                                                        'brand'
                                                    ]
                                                ) &&
                                                !empty(
                                                    $d[
                                                        'tipe'
                                                    ]
                                                )
                                            ) :
                                            ?>

                                                -
                                            <?php endif; ?>

                                            <?= htmlspecialchars(
                                                $d[
                                                    'tipe'
                                                ]
                                                ?? ''
                                            ) ?>

                                        </small>

                                    <?php endif; ?>

                                </td>



                                <!-- SN KOMPONEN -->

                                <td>

                                    <span
                                        class="
                                            badge
                                            bg-light
                                            text-dark
                                            border
                                            fw-semibold
                                        "
                                    >

                                        <?= htmlspecialchars(
                                            $d[
                                                'serial_number'
                                            ]
                                            ?? '-'
                                        ) ?>

                                    </span>

                                </td>



                                <!-- MESIN -->

                                <td>

                                    <span
                                        class="fw-semibold"
                                    >

                                        <?= htmlspecialchars(
                                            $d[
                                                'nama_mesin'
                                            ]
                                            ?? '-'
                                        ) ?>

                                    </span>

                                </td>



                                <!-- SN MESIN -->

                                <td>

                                    <small
                                        class="text-muted"
                                    >

                                        <?= htmlspecialchars(
                                            $d[
                                                'serial_number_mesin'
                                            ]
                                            ?? '-'
                                        ) ?>

                                    </small>

                                </td>



                                <!-- SUB MESIN -->

                                <td>

                                    <?= htmlspecialchars(
                                        $d[
                                            'nama_sub_mesin'
                                        ]
                                        ?? '-'
                                    ) ?>

                                </td>



                                <!-- KONDISI -->

                                <td
                                    class="text-center"
                                >

                                    <div
                                        class="
                                            d-flex
                                            justify-content-center
                                            align-items-center
                                            gap-1
                                        "
                                    >

                                        <span
                                            class="
                                                badge
                                                <?= $badgeKondisi ?>
                                                badge-status
                                            "
                                        >

                                            <?= htmlspecialchars(
                                                $kondisi
                                            ) ?>

                                        </span>


                                        <?php
                                        if (
                                            !empty(
                                                $d['id']
                                            )
                                        ) :
                                        ?>

                                            <a
                                                href="cetak.php?jenis=single_komponen&id=<?= (int)$d['id'] ?>"
                                                target="_blank"
                                                class="
                                                    btn
                                                    btn-sm
                                                    btn-outline-secondary
                                                    rounded-pill
                                                    px-2
                                                "
                                                title="Cetak data"
                                            >

                                                <i
                                                    class="
                                                        bi
                                                        bi-printer
                                                    "
                                                ></i>

                                            </a>

                                        <?php endif; ?>

                                    </div>

                                </td>


                            </tr>

                        <?php

                            endwhile;

                        else :

                        ?>

                            <tr>

                                <td
                                    colspan="7"
                                    class="empty-report"
                                >

                                    <i
                                        class="
                                            bi
                                            bi-inbox
                                            d-block
                                            mb-3
                                        "
                                    ></i>

                                    <strong
                                        class="
                                            d-block
                                            text-secondary
                                            mb-1
                                        "
                                    >

                                        Data komponen tidak ditemukan

                                    </strong>

                                    <small>

                                        Tidak ada komponen
                                        yang sesuai dengan
                                        filter.

                                    </small>

                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

            <?php endif; ?>

        </div>

    </div>

</div>



<script>

/* =========================================================
   LOAD SUB MESIN BERDASARKAN MESIN
========================================================= */

function loadSubMesin(
    selectedSubMesin = ''
) {

    const mesinSelect =
        document.getElementById(
            'id_mesin'
        );

    const subSelect =
        document.getElementById(
            'id_sub_mesin'
        );


    if (!mesinSelect || !subSelect) {
        return;
    }


    const idMesin =
        mesinSelect.value;


    /* =====================================================
       RESET SUB MESIN
    ====================================================== */

    subSelect.innerHTML =
        '<option value="">-- Memuat Sub Mesin --</option>';

    subSelect.disabled = true;


    /* =====================================================
       JIKA SEMUA MESIN
    ====================================================== */

    if (!idMesin) {

        subSelect.innerHTML =
            '<option value="">-- Pilih Mesin Dahulu --</option>';

        subSelect.disabled = true;

        return;
    }


    /* =====================================================
       FETCH
    ====================================================== */

    fetch(
        'get_sub_mesin.php?id_mesin=' +
        encodeURIComponent(
            idMesin
        )
    )

    .then(function(response) {

        if (!response.ok) {

            throw new Error(
                'HTTP error ' +
                response.status
            );
        }

        return response.text();
    })

    .then(function(data) {

        /*
         * Endpoint get_sub_mesin.php
         * diasumsikan mengembalikan:
         *
         * <option value="">-- Pilih Sub Mesin --</option>
         * <option value="1">Sub Mesin A</option>
         *
         * sehingga langsung dimasukkan ke select.
         */

        subSelect.innerHTML =
            data;


        subSelect.disabled =
            false;


        /* =================================================
           KEMBALIKAN PILIHAN SEBELUMNYA
        ================================================== */

        if (
            selectedSubMesin !== ''
        ) {

            subSelect.value =
                selectedSubMesin;

        }

    })

    .catch(function(error) {

        console.error(
            'Gagal memuat Sub Mesin:',
            error
        );


        subSelect.innerHTML =
            '<option value="">-- Gagal memuat Sub Mesin --</option>';

        subSelect.disabled =
            false;
    });

}


/* =========================================================
   GANTI JENIS LAPORAN
========================================================= */

function changeJenisLaporan() {

    const jenis =
        document.getElementById(
            'jenis'
        ).value;


    /*
     * Submit otomatis agar
     * field tanggal maintenance
     * muncul / hilang.
     */

    const form =
        document.getElementById(
            'formFilter'
        );


    if (form) {

        form.submit();

    }

}


/* =========================================================
   INISIALISASI
========================================================= */

document.addEventListener(
    'DOMContentLoaded',
    function() {

        const selectedSubMesin =
            <?= json_encode(
                $id_sub_mesin > 0
                    ? (string)$id_sub_mesin
                    : ''
            ) ?>;


        const idMesin =
            document.getElementById(
                'id_mesin'
            ).value;


        /*
         * Jika mesin sudah dipilih
         * dari GET parameter,
         * load ulang sub mesin.
         */

        if (idMesin !== '') {

            loadSubMesin(
                selectedSubMesin
            );

        }

    }
);

</script>


<?php

/* =========================================================
   CLOSE STATEMENT
========================================================= */

if ($stmt) {

    mysqli_stmt_close(
        $stmt
    );
}


/* =========================================================
   FOOTER
========================================================= */

include "../template/footer.php";

?>
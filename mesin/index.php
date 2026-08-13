<?php
include "../koneksi.php";
include "../template/header.php";

/* =========================================================
   SEARCH
========================================================= */

$keyword = isset($_GET['keyword'])
    ? trim($_GET['keyword'])
    : '';


/* =========================================================
   QUERY DATA MESIN
========================================================= */

$query_str = "
    SELECT
        m.*,
        jm.nama_jenis_mesin,
        a.nama_area,
        a.lokasi,

        /* TOTAL SUB MESIN */
        (
            SELECT COUNT(*)
            FROM sub_mesin sm
            WHERE sm.id_mesin = m.id
        ) AS total_sub_mesin,

        /* TOTAL KOMPONEN */
        (
            SELECT COUNT(*)
            FROM komponen k
            INNER JOIN sub_mesin sm2
                ON k.id_sub_mesin = sm2.id
            WHERE sm2.id_mesin = m.id
        ) AS total_komponen,

        /* TOTAL MAINTENANCE */
        (
            SELECT COUNT(*)
            FROM riwayat_maintenance rm
            INNER JOIN komponen k2
                ON rm.id_komponen = k2.id
            INNER JOIN sub_mesin sm3
                ON k2.id_sub_mesin = sm3.id
            WHERE sm3.id_mesin = m.id
        ) AS total_maintenance

    FROM mesin m

    LEFT JOIN jenis_mesin jm
        ON m.id_jenis_mesin = jm.id

    LEFT JOIN area_bagian a
        ON m.id_area = a.id
";


/* =========================================================
   SEARCH CONDITION
========================================================= */

if (!empty($keyword)) {

    $query_str .= "
        WHERE
            m.nama_mesin LIKE ?
            OR m.serial_number LIKE ?
            OR a.nama_area LIKE ?
            OR a.lokasi LIKE ?
            OR jm.nama_jenis_mesin LIKE ?
    ";

    $query_str .= " ORDER BY m.id DESC";

    $stmt = mysqli_prepare($conn, $query_str);

    if (!$stmt) {
        die("Query gagal diproses.");
    }

    $kw = "%" . $keyword . "%";

    mysqli_stmt_bind_param(
        $stmt,
        "sssss",
        $kw,
        $kw,
        $kw,
        $kw,
        $kw
    );

    mysqli_stmt_execute($stmt);

    $sql = mysqli_stmt_get_result($stmt);

} else {

    $query_str .= " ORDER BY m.id DESC";

    $sql = mysqli_query($conn, $query_str);
}


/* =========================================================
   TOTAL DATA
========================================================= */

$total_mesin = $sql
    ? mysqli_num_rows($sql)
    : 0;

?>

<style>

/* =========================================================
   PAGE
========================================================= */

.machine-page {
    width: 100%;
}


/* =========================================================
   HEADER AREA
========================================================= */

.machine-page-header {
    position: relative;

    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 14px;

    padding: 20px 22px;

    margin-bottom: 20px;

    box-shadow:
        0 3px 12px rgba(15, 23, 42, .04);
}


/* garis biru kecil di kiri */

.machine-page-header::before {

    content: "";

    position: absolute;

    left: 0;
    top: 18px;
    bottom: 18px;

    width: 4px;

    background: linear-gradient(
        180deg,
        #005baa,
        #0076c8
    );

    border-radius: 0 5px 5px 0;
}


/* TITLE */

.machine-page-title {

    font-size: 25px;

    font-weight: 800;

    color: #172033;

    margin: 0;

    line-height: 1.25;
}


/* SUBTITLE */

.machine-page-subtitle {

    color: #64748b;

    font-size: 13px;

    margin-top: 5px;
}


/* =========================================================
   ADD BUTTON
========================================================= */

.btn-add-machine {

    background: linear-gradient(
        135deg,
        #005baa,
        #0076c8
    );

    border: none;

    color: #fff;

    border-radius: 9px;

    padding: 10px 16px;

    font-size: 13px;

    font-weight: 600;

    text-decoration: none;

    transition: .2s;
}

.btn-add-machine:hover {

    color: #fff;

    transform: translateY(-1px);

    box-shadow:
        0 7px 18px rgba(0,91,170,.20);
}


/* =========================================================
   MAIN CARD
========================================================= */

.machine-card {

    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 14px;

    overflow: hidden;

    box-shadow:
        0 3px 12px rgba(15,23,42,.04);
}


/* =========================================================
   CARD HEADER
========================================================= */

.machine-card-header {

    padding: 17px 20px;

    border-bottom: 1px solid #e5e7eb;

    background: #ffffff;
}


.machine-card-title {

    font-size: 15px;

    font-weight: 700;

    color: #172033;
}


.machine-card-subtitle {

    font-size: 11px;

    color: #94a3b8;

    margin-top: 3px;
}


/* =========================================================
   SEARCH
========================================================= */

.machine-search {

    width: 300px;

    position: relative;
}


.machine-search input {

    width: 100%;

    height: 38px;

    border: 1px solid #dbe3ea;

    border-radius: 8px;

    padding: 8px 12px 8px 36px;

    font-size: 12px;

    color: #334155;

    outline: none;

    background: #fff;

    transition: .2s;
}


.machine-search input:focus {

    border-color: #0076c8;

    box-shadow:
        0 0 0 3px rgba(0,118,200,.08);
}


.machine-search i {

    position: absolute;

    left: 13px;

    top: 50%;

    transform: translateY(-50%);

    color: #94a3b8;

    font-size: 14px;
}


/* =========================================================
   TABLE
========================================================= */

.machine-table {

    width: 100%;

    margin: 0;

    border-collapse: separate;

    border-spacing: 0;
}


.machine-table thead th {

    background: #f8fafc;

    color: #475569;

    font-size: 10px;

    font-weight: 700;

    text-transform: uppercase;

    letter-spacing: .35px;

    padding: 12px 13px;

    border-bottom: 1px solid #e5e7eb;

    white-space: nowrap;
}


.machine-table tbody td {

    padding: 14px 13px;

    border-bottom: 1px solid #f1f5f9;

    vertical-align: middle;

    font-size: 12px;

    color: #334155;
}


.machine-table tbody tr {

    transition: .15s;
}


.machine-table tbody tr:hover {

    background: #f8fbff;
}


.machine-table tbody tr:last-child td {

    border-bottom: none;
}


/* =========================================================
   MACHINE ICON
========================================================= */

.machine-icon {

    width: 42px;

    height: 42px;

    border-radius: 10px;

    display: flex;

    align-items: center;

    justify-content: center;

    flex-shrink: 0;

    background: #eef5ff;

    color: #005baa;

    border: 1px solid #dcecff;

    font-size: 18px;
}


/* =========================================================
   MACHINE NAME
========================================================= */

.machine-name {

    font-weight: 700;

    color: #172033;

    font-size: 13px;

    line-height: 1.3;
}


.machine-id {

    font-size: 10px;

    color: #94a3b8;

    margin-top: 3px;
}


/* =========================================================
   LOCATION
========================================================= */

.location-name {

    font-weight: 600;

    color: #334155;

    font-size: 12px;
}


.location-place {

    font-size: 10px;

    color: #94a3b8;

    margin-top: 3px;
}


/* =========================================================
   TYPE
========================================================= */

.type-badge {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    background: #eef5ff;

    color: #005baa;

    border: 1px solid #d8eaff;

    padding: 5px 8px;

    border-radius: 7px;

    font-size: 10px;

    font-weight: 600;

    white-space: nowrap;
}


/* =========================================================
   SERIAL
========================================================= */

.machine-sn {

    display: inline-block;

    background: #f8fafc;

    border: 1px solid #e2e8f0;

    color: #334155;

    border-radius: 6px;

    padding: 5px 7px;

    font-size: 10px;

    font-weight: 600;

    white-space: nowrap;
}


/* =========================================================
   STRUCTURE
========================================================= */

.machine-stats {

    display: flex;

    align-items: center;

    gap: 4px;

    flex-wrap: wrap;
}


.machine-stat {

    display: inline-flex;

    align-items: center;

    background: #f8fafc;

    border: 1px solid #e5e7eb;

    border-radius: 6px;

    padding: 4px 6px;

    font-size: 9px;

    color: #64748b;

    white-space: nowrap;
}


.machine-stat strong {

    color: #334155;

    font-weight: 700;

    margin-left: 2px;
}


/* =========================================================
   ACTION
========================================================= */

.machine-actions {

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 5px;
}


.machine-action {

    width: 32px;

    height: 32px;

    border-radius: 7px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    text-decoration: none;

    border: 1px solid transparent;

    transition: .15s;

    font-size: 13px;
}


/* DETAIL */

.machine-action-detail {

    color: #005baa;

    background: #eef5ff;

    border-color: #d8eaff;
}


.machine-action-detail:hover {

    background: #005baa;

    color: #fff;
}


/* EDIT */

.machine-action-edit {

    color: #b76a00;

    background: #fff7e8;

    border-color: #ffe5b5;
}


.machine-action-edit:hover {

    background: #f59e0b;

    color: #fff;
}


/* DELETE */

.machine-action-delete {

    color: #dc2626;

    background: #fff1f2;

    border-color: #ffe0e3;
}


.machine-action-delete:hover {

    background: #dc2626;

    color: #fff;
}


/* =========================================================
   FOOTER
========================================================= */

.machine-card-footer {

    padding: 11px 18px;

    background: #f8fafc;

    border-top: 1px solid #e5e7eb;

    color: #64748b;

    font-size: 10px;
}


/* =========================================================
   EMPTY
========================================================= */

.machine-empty {

    padding: 65px 20px;

    text-align: center;

    color: #94a3b8;
}


.machine-empty-icon {

    width: 68px;

    height: 68px;

    margin: 0 auto 15px;

    border-radius: 17px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #f1f5f9;

    color: #94a3b8;

    font-size: 28px;
}


.machine-empty-title {

    font-size: 15px;

    font-weight: 700;

    color: #475569;
}


.machine-empty-text {

    font-size: 12px;

    margin-top: 4px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1100px) {

    .machine-table {

        min-width: 1050px;
    }

}


@media (max-width: 768px) {

    .machine-page-header {

        padding: 18px;
    }


    .machine-page-title {

        font-size: 21px;
    }


    .machine-page-header .btn-add-machine {

        width: 100%;

        text-align: center;

        justify-content: center;
    }


    .machine-card-header {

        align-items: flex-start !important;

        flex-direction: column;

        gap: 12px;
    }


    .machine-search {

        width: 100%;
    }

}

</style>


<div class="container-fluid p-0 machine-page">


    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div class="machine-page-header">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <div class="ps-1">

                <h2 class="machine-page-title">
                    Data Mesin
                </h2>

                <div class="machine-page-subtitle">

                    Kelola mesin induk, struktur sub mesin,
                    komponen dan riwayat maintenance.

                </div>

            </div>


            <a
                href="tambah.php"
                class="btn btn-add-machine d-inline-flex align-items-center"
            >

                <i class="bi bi-plus-lg me-2"></i>

                Tambah Mesin

            </a>

        </div>

    </div>



    <!-- =====================================================
         MAIN CARD
    ====================================================== -->

    <div class="machine-card">


        <!-- =================================================
             CARD HEADER
        ================================================== -->

        <div class="machine-card-header">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">


                <div>

                    <div class="machine-card-title">

                        <i class="bi bi-gear-wide-connected text-primary me-2"></i>

                        Daftar Mesin Induk

                    </div>

                    <div class="machine-card-subtitle">

                        Seluruh mesin yang terdaftar pada sistem inventory.

                    </div>

                </div>



                <!-- SEARCH -->

                <form
                    method="GET"
                    class="machine-search"
                    autocomplete="off"
                >

                    <i class="bi bi-search"></i>

                    <input
                        type="text"
                        name="keyword"
                        value="<?= htmlspecialchars($keyword) ?>"
                        placeholder="Cari nama, serial, area..."
                    >

                </form>


            </div>

        </div>



        <!-- =================================================
             TABLE
        ================================================== -->

        <?php if ($total_mesin > 0): ?>

            <div class="table-responsive">

                <table class="machine-table">

                    <thead>

                        <tr>

                            <th
                                width="50"
                                class="text-center"
                            >
                                No
                            </th>


                            <th width="220">
                                Mesin
                            </th>


                            <th width="165">
                                Area / Lokasi
                            </th>


                            <th width="165">
                                Jenis Mesin
                            </th>


                            <th width="145">
                                Serial Number
                            </th>


                            <th>
                                Struktur
                            </th>


                            <th
                                width="120"
                                class="text-center"
                            >
                                Aksi
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    $no = 1;

                    while ($d = mysqli_fetch_assoc($sql)):

                    ?>


                        <tr>


                            <!-- =================================
                                 NOMOR
                            ================================== -->

                            <td class="text-center">

                                <span class="text-muted fw-semibold">

                                    <?= $no++ ?>

                                </span>

                            </td>



                            <!-- =================================
                                 MESIN
                            ================================== -->

                            <td>

                                <div class="d-flex align-items-center gap-3">


                                    <div class="machine-icon">

                                        <i class="bi bi-gear-wide-connected"></i>

                                    </div>


                                    <div>

                                        <div class="machine-name">

                                            <?= htmlspecialchars(
                                                $d['nama_mesin'] ?? '-'
                                            ) ?>

                                        </div>


                                        <div class="machine-id">

                                            ID Mesin #

                                            <?= intval($d['id']) ?>

                                        </div>

                                    </div>


                                </div>

                            </td>



                            <!-- =================================
                                 AREA
                            ================================== -->

                            <td>

                                <div class="location-name">

                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>

                                    <?= htmlspecialchars(
                                        $d['nama_area'] ?? '-'
                                    ) ?>

                                </div>


                                <div class="location-place">

                                    <i class="bi bi-pin-map me-1"></i>

                                    <?= htmlspecialchars(
                                        $d['lokasi'] ?? '-'
                                    ) ?>

                                </div>

                            </td>



                            <!-- =================================
                                 JENIS MESIN
                            ================================== -->

                            <td>

                                <span class="type-badge">

                                    <i class="bi bi-tags"></i>

                                    <?= htmlspecialchars(
                                        $d['nama_jenis_mesin'] ?? '-'
                                    ) ?>

                                </span>

                            </td>



                            <!-- =================================
                                 SERIAL NUMBER
                            ================================== -->

                            <td>

                                <?php if (!empty($d['serial_number'])): ?>

                                    <span class="machine-sn">

                                        <?= htmlspecialchars(
                                            $d['serial_number']
                                        ) ?>

                                    </span>

                                <?php else: ?>

                                    <span class="text-muted small">

                                        -

                                    </span>

                                <?php endif; ?>

                            </td>



                            <!-- =================================
                                 STRUKTUR
                            ================================== -->

                            <td>

                                <div class="machine-stats">


                                    <!-- SUB MESIN -->

                                    <span class="machine-stat">

                                        <i class="bi bi-diagram-3 me-1"></i>

                                        Sub

                                        <strong>

                                            <?= intval(
                                                $d['total_sub_mesin'] ?? 0
                                            ) ?>

                                        </strong>

                                    </span>



                                    <!-- KOMPONEN -->

                                    <span class="machine-stat">

                                        <i class="bi bi-cpu me-1"></i>

                                        Komp.

                                        <strong>

                                            <?= intval(
                                                $d['total_komponen'] ?? 0
                                            ) ?>

                                        </strong>

                                    </span>



                                    <!-- MAINTENANCE -->

                                    <span class="machine-stat">

                                        <i class="bi bi-tools me-1"></i>

                                        Maint.

                                        <strong>

                                            <?= intval(
                                                $d['total_maintenance'] ?? 0
                                            ) ?>

                                        </strong>

                                    </span>


                                </div>

                            </td>



                            <!-- =================================
                                 AKSI
                            ================================== -->

                            <td>

                                <div class="machine-actions">


                                    <!-- DETAIL -->

                                    <a
                                        href="detail.php?id=<?= intval($d['id']) ?>"
                                        class="machine-action machine-action-detail"
                                        title="Lihat Detail Mesin"
                                    >

                                        <i class="bi bi-eye"></i>

                                    </a>



                                    <!-- EDIT -->

                                    <a
                                        href="edit.php?id=<?= intval($d['id']) ?>"
                                        class="machine-action machine-action-edit"
                                        title="Edit Mesin"
                                    >

                                        <i class="bi bi-pencil-square"></i>

                                    </a>



                                    <!-- HAPUS -->

                                    <a
                                        href="hapus.php?id=<?= intval($d['id']) ?>"
                                        class="machine-action machine-action-delete"
                                        title="Hapus Mesin"
                                        onclick="return confirm(
                                            'Apakah Anda yakin ingin menghapus mesin <?= htmlspecialchars(
                                                $d['nama_mesin'] ?? '',
                                                ENT_QUOTES
                                            ) ?>?'
                                        );"
                                    >

                                        <i class="bi bi-trash"></i>

                                    </a>


                                </div>

                            </td>


                        </tr>


                    <?php endwhile; ?>


                    </tbody>

                </table>

            </div>



            <!-- =================================================
                 FOOTER CARD
            ================================================== -->

            <div class="machine-card-footer">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">


                    <div>

                        <i class="bi bi-database me-1"></i>

                        Menampilkan

                        <strong>
                            <?= $total_mesin ?>
                        </strong>

                        mesin

                        <?php if (!empty($keyword)): ?>

                            untuk pencarian

                            <strong>
                                "<?= htmlspecialchars($keyword) ?>"
                            </strong>

                        <?php endif; ?>

                    </div>



                    <?php if (!empty($keyword)): ?>

                        <a
                            href="index.php"
                            class="text-decoration-none text-primary fw-semibold"
                        >

                            <i class="bi bi-x-circle me-1"></i>

                            Reset pencarian

                        </a>

                    <?php endif; ?>


                </div>

            </div>


        <?php else: ?>


            <!-- =================================================
                 EMPTY DATA
            ================================================== -->

            <div class="machine-empty">


                <div class="machine-empty-icon">

                    <i class="bi bi-gear-wide-connected"></i>

                </div>


                <div class="machine-empty-title">

                    <?php if (!empty($keyword)): ?>

                        Mesin tidak ditemukan

                    <?php else: ?>

                        Belum ada data mesin

                    <?php endif; ?>

                </div>


                <div class="machine-empty-text">

                    <?php if (!empty($keyword)): ?>

                        Tidak ada mesin yang sesuai dengan pencarian

                        <strong>
                            "<?= htmlspecialchars($keyword) ?>"
                        </strong>.

                    <?php else: ?>

                        Silakan tambahkan mesin terlebih dahulu.

                    <?php endif; ?>

                </div>


                <div class="mt-3">


                    <?php if (!empty($keyword)): ?>

                        <a
                            href="index.php"
                            class="btn btn-outline-primary btn-sm"
                        >

                            <i class="bi bi-arrow-counterclockwise me-1"></i>

                            Reset Pencarian

                        </a>

                    <?php else: ?>

                        <a
                            href="tambah.php"
                            class="btn btn-primary btn-sm"
                        >

                            <i class="bi bi-plus-lg me-1"></i>

                            Tambah Mesin

                        </a>

                    <?php endif; ?>


                </div>


            </div>


        <?php endif; ?>


    </div>


</div>


<?php include "../template/footer.php"; ?>
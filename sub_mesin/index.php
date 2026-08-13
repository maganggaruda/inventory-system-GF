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
   QUERY DATA SUB MESIN
========================================================= */

if (!empty($keyword)) {

    $stmt = mysqli_prepare($conn, "
        SELECT 
            sm.*,
            m.nama_mesin,
            m.serial_number AS sn_mesin_induk,

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

        LEFT JOIN mesin m
            ON sm.id_mesin = m.id

        WHERE
            sm.nama_sub_mesin LIKE ?
            OR sm.serial_number LIKE ?
            OR m.nama_mesin LIKE ?
            OR m.serial_number LIKE ?

        ORDER BY sm.id DESC
    ");

    $kw = "%" . $keyword . "%";

    mysqli_stmt_bind_param(
        $stmt,
        "ssss",
        $kw,
        $kw,
        $kw,
        $kw
    );

    mysqli_stmt_execute($stmt);

    $sql = mysqli_stmt_get_result($stmt);

} else {

    $sql = mysqli_query($conn, "
        SELECT 
            sm.*,
            m.nama_mesin,
            m.serial_number AS sn_mesin_induk,

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

        LEFT JOIN mesin m
            ON sm.id_mesin = m.id

        ORDER BY sm.id DESC
    ");
}


/* =========================================================
   TOTAL DATA
========================================================= */

$total_sub_mesin = $sql
    ? mysqli_num_rows($sql)
    : 0;

?>

<style>

/* =========================================================
   WHITE TOP BAR
========================================================= */

.submachine-topbar {

    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 14px;

    padding: 18px 22px;

    box-shadow:
        0 3px 12px rgba(15,23,42,.04);

    margin-bottom: 22px;
}

/* =========================================================
   PAGE TITLE
========================================================= */

.submachine-page-title {

    font-size: 25px;

    font-weight: 800;

    color: #172033;

    margin: 0;

    line-height: 1.3;
}

.submachine-page-subtitle {

    color: #64748b;

    font-size: 14px;

    margin: 0;
}


/* =========================================================
   ADD BUTTON
========================================================= */

.btn-add-submachine {

    background: linear-gradient(
        135deg,
        #005baa,
        #0076c8
    );

    border: none;

    color: #fff;

    border-radius: 10px;

    padding: 10px 16px;

    font-weight: 600;

    transition: .2s;
}

.btn-add-submachine:hover {

    color: #fff;

    transform: translateY(-1px);

    box-shadow:
        0 7px 18px rgba(0,91,170,.20);
}


/* =========================================================
   MAIN CARD
========================================================= */

.submachine-card {

    background: #fff;

    border: 1px solid #e5e7eb;

    border-radius: 16px;

    overflow: hidden;

    box-shadow:
        0 3px 12px rgba(15,23,42,.04);
}
/* garis biru kecil di kiri */

.submachine-page-header::before {

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


/* =========================================================
   CARD HEADER
========================================================= */

.submachine-card-header {

    padding: 18px 20px;

    border-bottom: 1px solid #e5e7eb;

    background: #fff;
}

.submachine-card-title {

    font-size: 16px;

    font-weight: 700;

    color: #172033;
}

.submachine-card-subtitle {

    font-size: 12px;

    color: #94a3b8;

    margin-top: 2px;
}


/* =========================================================
   SEARCH
========================================================= */

.submachine-search {

    width: 320px;

    position: relative;
}

.submachine-search input {

    width: 100%;

    border: 1px solid #dbe3ea;

    border-radius: 9px;

    padding: 9px 12px 9px 37px;

    font-size: 13px;

    outline: none;

    transition: .2s;

    background: #fff;
}

.submachine-search input:focus {

    border-color: #0076c8;

    box-shadow:
        0 0 0 3px rgba(0,118,200,.08);
}

.submachine-search i {

    position: absolute;

    left: 13px;

    top: 50%;

    transform: translateY(-50%);

    color: #94a3b8;

}


/* =========================================================
   TABLE
========================================================= */

.submachine-table {

    width: 100%;

    margin: 0;

    border-collapse: separate;

    border-spacing: 0;
}

.submachine-table thead th {

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

.submachine-table tbody td {

    padding: 15px 14px;

    border-bottom: 1px solid #f1f5f9;

    vertical-align: middle;

    font-size: 13px;

    color: #334155;
}

.submachine-table tbody tr {

    transition: .15s;
}

.submachine-table tbody tr:hover {

    background: #f8fbff;
}

.submachine-table tbody tr:last-child td {

    border-bottom: none;
}


/* =========================================================
   PHOTO
========================================================= */

.submachine-photo {

    width: 46px;

    height: 46px;

    object-fit: cover;

    border-radius: 10px;

    border: 1px solid #e2e8f0;

    background: #f8fafc;
}

.submachine-photo-placeholder {

    width: 46px;

    height: 46px;

    border-radius: 10px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #eef5ff;

    border: 1px solid #d8eaff;

    color: #005baa;

    font-size: 19px;
}


/* =========================================================
   SUB MESIN NAME
========================================================= */

.submachine-name {

    font-weight: 700;

    color: #172033;

    font-size: 14px;
}

.submachine-name:hover {

    color: #005baa;
}

.submachine-id {

    font-size: 11px;

    color: #94a3b8;

    margin-top: 2px;
}


/* =========================================================
   SERIAL NUMBER
========================================================= */

.submachine-sn {

    display: inline-block;

    background: #f1f5f9;

    border: 1px solid #e2e8f0;

    color: #334155;

    border-radius: 6px;

    padding: 4px 7px;

    font-size: 11px;

    font-weight: 600;

    font-family: monospace;
}


/* =========================================================
   PARENT MACHINE
========================================================= */

.parent-machine {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    color: #334155;

    text-decoration: none;

    font-weight: 600;

    transition: .15s;
}

.parent-machine:hover {

    color: #005baa;
}

.parent-machine i {

    color: #005baa;
}


/* =========================================================
   STRUCTURE
========================================================= */

.submachine-stats {

    display: flex;

    align-items: center;

    gap: 5px;

    flex-wrap: wrap;
}

.submachine-stat {

    background: #f8fafc;

    border: 1px solid #e5e7eb;

    border-radius: 6px;

    padding: 4px 7px;

    font-size: 10px;

    color: #64748b;

    white-space: nowrap;
}

.submachine-stat strong {

    color: #334155;

    font-weight: 700;
}


/* =========================================================
   DESCRIPTION
========================================================= */

.submachine-description {

    max-width: 220px;

    font-size: 12px;

    color: #64748b;

    line-height: 1.5;

    display: -webkit-box;

    -webkit-line-clamp: 2;

    -webkit-box-orient: vertical;

    overflow: hidden;
}


/* =========================================================
   ACTION BUTTONS
========================================================= */

.submachine-actions {

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 5px;
}

.submachine-action {

    width: 34px;

    height: 34px;

    border-radius: 8px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    text-decoration: none;

    border: 1px solid transparent;

    transition: .15s;

    font-size: 14px;
}


/* DETAIL */

.submachine-action-detail {

    color: #005baa;

    background: #eef5ff;

    border-color: #d8eaff;
}

.submachine-action-detail:hover {

    background: #005baa;

    color: #fff;
}


/* EDIT */

.submachine-action-edit {

    color: #b76a00;

    background: #fff7e8;

    border-color: #ffe5b5;
}

.submachine-action-edit:hover {

    background: #f59e0b;

    color: #fff;
}


/* DELETE */

.submachine-action-delete {

    color: #dc2626;

    background: #fff1f2;

    border-color: #ffe0e3;
}

.submachine-action-delete:hover {

    background: #dc2626;

    color: #fff;
}


/* =========================================================
   FOOTER
========================================================= */

.submachine-card-footer {

    padding: 12px 20px;

    background: #f8fafc;

    border-top: 1px solid #e5e7eb;

    color: #64748b;

    font-size: 11px;
}


/* =========================================================
   EMPTY
========================================================= */

.submachine-empty {

    padding: 65px 20px;

    text-align: center;

    color: #94a3b8;
}

.submachine-empty-icon {

    width: 70px;

    height: 70px;

    margin: 0 auto 15px;

    border-radius: 18px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #f1f5f9;

    color: #94a3b8;

    font-size: 30px;
}

.submachine-empty-title {

    font-size: 15px;

    font-weight: 700;

    color: #475569;
}

.submachine-empty-text {

    font-size: 12px;

    margin-top: 4px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1150px) {

    .submachine-table {

        min-width: 1150px;
    }

}

@media (max-width: 768px) {

    .submachine-topbar {

        padding: 16px;
    }

    .submachine-page-title {

        font-size: 21px;
    }

    .submachine-card-header {

        align-items: flex-start !important;

        flex-direction: column;

        gap: 12px;
    }

    .submachine-search {

        width: 100%;
    }

}

</style>


<div class="container-fluid p-0">


    <!-- =====================================================
         WHITE HEADER BAR
    ====================================================== -->

    <div class="submachine-topbar">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <div>

                <h2 class="submachine-page-title">

                    Data Sub Mesin

                </h2>

                <div class="submachine-page-subtitle">

                    Kelola bagian atau sub-sistem dari setiap mesin produksi.

                </div>

            </div>


            <a
                href="tambah.php"
                class="btn btn-add-submachine"
            >

                <i class="bi bi-plus-lg me-1"></i>

                Tambah Sub Mesin

            </a>

        </div>

    </div>



    <!-- =====================================================
         MAIN CARD
    ====================================================== -->

    <div class="submachine-card">


        <!-- =================================================
             CARD HEADER
        ================================================== -->

        <div class="submachine-card-header">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                <div>

                    <div class="submachine-card-title">

                        <i class="bi bi-diagram-3 text-primary me-2"></i>

                        Daftar Sub Mesin

                    </div>

                    <div class="submachine-card-subtitle">

                        Daftar seluruh sub mesin yang terhubung dengan mesin induk.

                    </div>

                </div>


                <!-- SEARCH -->

                <form
                    method="GET"
                    class="submachine-search"
                >

                    <i class="bi bi-search"></i>

                    <input
                        type="text"
                        name="keyword"
                        value="<?= htmlspecialchars($keyword) ?>"
                        placeholder="Cari sub mesin, serial number, mesin..."
                    >

                </form>

            </div>

        </div>



        <!-- =================================================
             DATA TABLE
        ================================================== -->

        <?php if ($total_sub_mesin > 0): ?>

            <div class="table-responsive">

                <table class="submachine-table">

                    <thead>

                        <tr>

                            <th
                                width="55"
                                class="text-center"
                            >
                                No
                            </th>

                            <th
                                width="75"
                                class="text-center"
                            >
                                Foto
                            </th>

                            <th width="190">
                                Sub Mesin
                            </th>

                            <th width="170">
                                Serial Number
                            </th>

                            <th width="200">
                                Mesin Induk
                            </th>

                            <th width="180">
                                Struktur
                            </th>

                            <th width="220">
                                Keterangan
                            </th>

                            <th
                                width="125"
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

                        $foto =
                            $d['gambar'] ?? '';

                        $foto_path =
                            "../uploads/sub_mesin/" . $foto;

                        $has_foto =
                            !empty($foto) &&
                            file_exists($foto_path);

                    ?>

                        <tr>


                            <!-- =================================================
                                 NO
                            ================================================== -->

                            <td class="text-center">

                                <span class="text-muted fw-semibold">

                                    <?= $no++ ?>

                                </span>

                            </td>



                            <!-- =================================================
                                 FOTO
                            ================================================== -->

                            <td class="text-center">

                                <a
                                    href="detail.php?id=<?= intval($d['id']) ?>"
                                    class="text-decoration-none"
                                >

                                    <?php if ($has_foto): ?>

                                        <img
                                            src="<?= htmlspecialchars($foto_path) ?>"
                                            class="submachine-photo"
                                            alt="Foto Sub Mesin"
                                        >

                                    <?php else: ?>

                                        <div class="submachine-photo-placeholder mx-auto">

                                            <i class="bi bi-diagram-3"></i>

                                        </div>

                                    <?php endif; ?>

                                </a>

                            </td>



                            <!-- =================================================
                                 NAMA SUB MESIN
                            ================================================== -->

                            <td>

                                <a
                                    href="detail.php?id=<?= intval($d['id']) ?>"
                                    class="text-decoration-none"
                                >

                                    <div class="submachine-name">

                                        <?= htmlspecialchars(
                                            $d['nama_sub_mesin'] ?? '-'
                                        ) ?>

                                    </div>

                                    <div class="submachine-id">

                                        ID Sub Mesin #<?= intval($d['id']) ?>

                                    </div>

                                </a>

                            </td>



                            <!-- =================================================
                                 SERIAL NUMBER
                            ================================================== -->

                            <td>

                                <span class="submachine-sn">

                                    <?= htmlspecialchars(
                                        !empty($d['serial_number'])
                                            ? $d['serial_number']
                                            : '-'
                                    ) ?>

                                </span>

                                <?php if (!empty($d['sn_mesin_induk'])): ?>

                                    <div class="mt-1">

                                        <small class="text-muted">

                                            SN Mesin:
                                            <?= htmlspecialchars(
                                                $d['sn_mesin_induk']
                                            ) ?>

                                        </small>

                                    </div>

                                <?php endif; ?>

                            </td>



                            <!-- =================================================
                                 MESIN INDUK
                            ================================================== -->

                            <td>

                                <?php if (!empty($d['nama_mesin'])): ?>

                                    <a
                                        href="../mesin/detail.php?id=<?= intval($d['id_mesin']) ?>"
                                        class="parent-machine"
                                    >

                                        <i class="bi bi-gear-wide-connected"></i>

                                        <span>

                                            <?= htmlspecialchars(
                                                $d['nama_mesin']
                                            ) ?>

                                        </span>

                                    </a>

                                <?php else: ?>

                                    <span class="text-muted">

                                        Tidak terkait

                                    </span>

                                <?php endif; ?>

                            </td>



                            <!-- =================================================
                                 STRUKTUR
                            ================================================== -->

                            <td>

                                <div class="submachine-stats">


                                    <!-- KOMPONEN -->

                                    <span class="submachine-stat">

                                        <i class="bi bi-cpu me-1"></i>

                                        Komp.

                                        <strong>

                                            <?= intval(
                                                $d['total_komponen'] ?? 0
                                            ) ?>

                                        </strong>

                                    </span>


                                    <!-- MAINTENANCE -->

                                    <span class="submachine-stat">

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



                            <!-- =================================================
                                 KETERANGAN
                            ================================================== -->

                            <td>

                                <div class="submachine-description">

                                    <?php

                                    $keterangan =
                                        trim(
                                            $d['keterangan'] ?? ''
                                        );

                                    if ($keterangan !== ''):

                                    ?>

                                        <?= htmlspecialchars($keterangan) ?>

                                    <?php else: ?>

                                        <span class="text-muted">

                                            Tidak ada keterangan

                                        </span>

                                    <?php endif; ?>

                                </div>

                            </td>



                            <!-- =================================================
                                 AKSI
                            ================================================== -->

                            <td>

                                <div class="submachine-actions">


                                    <!-- DETAIL -->

                                    <a
                                        href="detail.php?id=<?= intval($d['id']) ?>"
                                        class="submachine-action submachine-action-detail"
                                        title="Lihat Detail"
                                    >

                                        <i class="bi bi-eye"></i>

                                    </a>



                                    <!-- EDIT -->

                                    <a
                                        href="edit.php?id=<?= intval($d['id']) ?>"
                                        class="submachine-action submachine-action-edit"
                                        title="Edit Sub Mesin"
                                    >

                                        <i class="bi bi-pencil-square"></i>

                                    </a>



                                    <!-- HAPUS -->

                                    <a
                                        href="hapus.php?id=<?= intval($d['id']) ?>"
                                        class="submachine-action submachine-action-delete"
                                        title="Hapus Sub Mesin"
                                        onclick="return confirm(
                                            'Apakah Anda yakin ingin menghapus sub mesin <?= htmlspecialchars(
                                                $d['nama_sub_mesin'] ?? '',
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

            <div class="submachine-card-footer">

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                    <div>

                        <i class="bi bi-database me-1"></i>

                        Menampilkan

                        <strong>
                            <?= $total_sub_mesin ?>
                        </strong>

                        sub mesin

                        <?php if (!empty($keyword)): ?>

                            untuk pencarian:

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
                 EMPTY STATE
            ================================================== -->

            <div class="submachine-empty">

                <div class="submachine-empty-icon">

                    <i class="bi bi-diagram-3"></i>

                </div>


                <div class="submachine-empty-title">

                    <?php if (!empty($keyword)): ?>

                        Sub mesin tidak ditemukan

                    <?php else: ?>

                        Belum ada data sub mesin

                    <?php endif; ?>

                </div>


                <div class="submachine-empty-text">

                    <?php if (!empty($keyword)): ?>

                        Tidak ada sub mesin yang sesuai dengan pencarian

                        "<?= htmlspecialchars($keyword) ?>".

                    <?php else: ?>

                        Silakan tambahkan sub mesin terlebih dahulu.

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

                            Tambah Sub Mesin

                        </a>

                    <?php endif; ?>

                </div>

            </div>

        <?php endif; ?>


    </div>

</div>


<?php include "../template/footer.php"; ?>
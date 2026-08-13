<?php
include "../koneksi.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

/* =========================================================
   AMBIL DATA KOMPONEN LENGKAP
========================================================= */

$stmt_get = mysqli_prepare($conn, "
    SELECT 
        k.*,
        sm.id_mesin,
        m.id_jenis_mesin,
        jm.id_area,
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
");

mysqli_stmt_bind_param($stmt_get, "i", $id);
mysqli_stmt_execute($stmt_get);

$result = mysqli_stmt_get_result($stmt_get);
$d = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt_get);

if (!$d) {
    header("Location: index.php");
    exit;
}

$error = "";


/* =========================================================
   PROSES UPDATE
========================================================= */

if (isset($_POST['update'])) {

    $serial_number = trim($_POST['serial_number'] ?? '');
    $nama_bagian   = trim($_POST['nama_bagian'] ?? '');

    $id_area = !empty($_POST['id_area'])
        ? intval($_POST['id_area'])
        : null;

    $id_sub_mesin = !empty($_POST['id_sub_mesin'])
        ? intval($_POST['id_sub_mesin'])
        : null;


    /* =====================================================
       AMBIL LOKASI BERDASARKAN AREA
    ===================================================== */

    $lokasi_str = "";

    if ($id_area) {

        $stmt_l = mysqli_prepare(
            $conn,
            "SELECT lokasi 
             FROM area_bagian 
             WHERE id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt_l,
            "i",
            $id_area
        );

        mysqli_stmt_execute($stmt_l);

        $res_l = mysqli_stmt_get_result($stmt_l);

        if ($row_l = mysqli_fetch_assoc($res_l)) {
            $lokasi_str = $row_l['lokasi'];
        }

        mysqli_stmt_close($stmt_l);
    }


    /* =====================================================
       AMBIL MESIN & SUB MESIN
    ===================================================== */

    $mesin_str = "";
    $sub_mesin_str = "";

    if ($id_sub_mesin) {

        $stmt_s = mysqli_prepare(
            $conn,
            "SELECT 
                sm.nama_sub_mesin,
                m.nama_mesin
             FROM sub_mesin sm
             LEFT JOIN mesin m 
                ON sm.id_mesin = m.id
             WHERE sm.id = ?"
        );

        mysqli_stmt_bind_param(
            $stmt_s,
            "i",
            $id_sub_mesin
        );

        mysqli_stmt_execute($stmt_s);

        $res_s = mysqli_stmt_get_result($stmt_s);

        if ($row_s = mysqli_fetch_assoc($res_s)) {

            $sub_mesin_str = $row_s['nama_sub_mesin'];
            $mesin_str     = $row_s['nama_mesin'];
        }

        mysqli_stmt_close($stmt_s);
    }


    /* =====================================================
       DATA SPESIFIKASI
    ===================================================== */

    $brand            = trim($_POST['brand'] ?? '');
    $tipe             = trim($_POST['tipe'] ?? '');
    $part_number      = trim($_POST['part_number'] ?? '');
    $daya             = trim($_POST['daya'] ?? '');
    $io_address       = trim($_POST['io_address'] ?? '');
    $input_voltage    = trim($_POST['input_voltage'] ?? '');
    $frekuensi_input  = trim($_POST['frekuensi_input'] ?? '');
    $arus_input       = trim($_POST['arus_input'] ?? '');
    $output           = trim($_POST['output'] ?? '');
    $frekuensi_output = trim($_POST['frekuensi_output'] ?? '');
    $ip_rating        = trim($_POST['ip_rating'] ?? '');
    $kondisi          = trim($_POST['kondisi'] ?? '');
    $keterangan       = trim($_POST['keterangan'] ?? '');


    /* =====================================================
       KATEGORI DIHAPUS
       DATABASE LAMA TETAP AMAN
    ===================================================== */

    $kategori = "";


    /* =====================================================
       VALIDASI
    ===================================================== */

    if (empty($nama_bagian)) {
        $error = "Nama Komponen wajib diisi!";
    }


    /* =====================================================
       UPDATE DATA
    ===================================================== */

    if (empty($error)) {

        $sql_update = "
            UPDATE komponen SET

                serial_number = ?,
                id_sub_mesin = ?,
                mesin = ?,
                sub_mesin = ?,
                nama_bagian = ?,
                kategori = ?,
                brand = ?,
                tipe = ?,
                part_number = ?,
                daya = ?,
                io_address = ?,
                input_voltage = ?,
                frekuensi_input = ?,
                arus_input = ?,
                output = ?,
                frekuensi_output = ?,
                ip_rating = ?,
                lokasi = ?,
                kondisi = ?,
                keterangan = ?

            WHERE id = ?
        ";

        $stmt_up = mysqli_prepare(
            $conn,
            $sql_update
        );

        mysqli_stmt_bind_param(
            $stmt_up,
            "sissssssssssssssssssi",

            $serial_number,
            $id_sub_mesin,
            $mesin_str,
            $sub_mesin_str,
            $nama_bagian,

            $kategori,

            $brand,
            $tipe,
            $part_number,
            $daya,
            $io_address,
            $input_voltage,
            $frekuensi_input,
            $arus_input,
            $output,
            $frekuensi_output,
            $ip_rating,
            $lokasi_str,
            $kondisi,
            $keterangan,

            $id
        );


        if (mysqli_stmt_execute($stmt_up)) {

            mysqli_stmt_close($stmt_up);

            header("Location: index.php");
            exit;

        } else {

            $error = "Gagal memperbarui data: " . mysqli_error($conn);

            mysqli_stmt_close($stmt_up);
        }
    }
}


/* =========================================================
   AMBIL DATA LOKASI
========================================================= */

$q_lokasi = mysqli_query(
    $conn,
    "SELECT DISTINCT lokasi
     FROM area_bagian
     WHERE lokasi IS NOT NULL
     AND lokasi != ''
     ORDER BY lokasi ASC"
);


include "../template/header.php";
?>


<div class="container-fluid p-0">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="dashboard-header mb-3 py-3 px-4">

        <div class="d-flex align-items-center gap-3">

            <a
                href="index.php"
                class="btn btn-outline-secondary btn-sm rounded-circle d-inline-flex align-items-center justify-content-center"
                style="width:38px;height:38px;flex-shrink:0;"
            >

                <i class="bi bi-arrow-left fs-5"></i>

            </a>


            <div>

                <h3 class="dashboard-title m-0 fs-4 fw-bold">
                    Edit Komponen
                </h3>

                <p class="dashboard-subtitle m-0 small text-muted">
                    Perbarui rincian spesifikasi dan data komponen
                </p>

            </div>

        </div>

    </div>



    <!-- =====================================================
         FORM CARD
    ====================================================== -->

    <div class="content-card mb-3">


        <div class="card-header-custom py-2 px-3">

            <h6 class="card-title-custom m-0 fw-bold">

                <i class="bi bi-pencil-square me-2"></i>
                Form Edit Komponen

            </h6>

        </div>



        <div class="card-body-custom p-3">


            <!-- ERROR -->

            <?php if (!empty($error)) : ?>

                <div
                    class="alert alert-danger border-0 d-flex align-items-center py-2 px-3 mb-3"
                    role="alert"
                >

                    <i class="bi bi-exclamation-triangle-fill fs-6 me-2"></i>

                    <div class="small">

                        <?= htmlspecialchars($error); ?>

                    </div>

                </div>

            <?php endif; ?>



            <form method="POST">


                <!-- =================================================
                     SECTION 1
                ================================================== -->

                <h6 class="fw-bold text-primary mb-3 small">

                    <i class="bi bi-info-circle me-1"></i>

                    INFORMASI UMUM

                </h6>


                <div class="row g-3 mb-3">


                    <!-- SERIAL NUMBER -->

                    <div class="col-md-3">

                        <label class="form-label fw-semibold text-dark small mb-1">

                            Serial Number (SN)

                        </label>

                        <input
                            type="text"
                            name="serial_number"
                            class="form-control form-control-sm"
                            value="<?= htmlspecialchars($d['serial_number'] ?? '') ?>"
                        >

                    </div>



                    <!-- NAMA KOMPONEN -->

                    <div class="col-md-5">

                        <label class="form-label fw-semibold text-dark small mb-1">

                            Nama Komponen
                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="text"
                            name="nama_bagian"
                            class="form-control form-control-sm"
                            value="<?= htmlspecialchars($d['nama_bagian'] ?? '') ?>"
                            required
                        >

                    </div>



                    <!-- LOKASI -->

                    <div class="col-md-4">

                        <label class="form-label fw-semibold text-dark small mb-1">

                            Lokasi

                        </label>

                        <select
                            id="form_lokasi"
                            class="form-select form-select-sm"
                            onchange="loadAreaByLokasi(this.value)"
                        >

                            <option value="">
                                -- Pilih Lokasi --
                            </option>

                            <?php while ($l = mysqli_fetch_assoc($q_lokasi)) : ?>

                                <option
                                    value="<?= htmlspecialchars($l['lokasi']) ?>"
                                    <?= ($l['lokasi'] == ($d['lokasi_area'] ?? '')) ? 'selected' : '' ?>
                                >

                                    <?= htmlspecialchars($l['lokasi']) ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>



                    <!-- AREA -->

                    <div class="col-md-3">

                        <label class="form-label fw-semibold text-dark small mb-1">

                            Area / Bagian

                        </label>

                        <select
                            name="id_area"
                            id="form_area"
                            class="form-select form-select-sm"
                            onchange="loadJenisMesin(this.value)"
                        >

                            <option value="">
                                -- Pilih Area --
                            </option>

                        </select>

                    </div>



                    <!-- JENIS MESIN -->

                    <div class="col-md-3">

                        <label class="form-label fw-semibold text-dark small mb-1">

                            Jenis Mesin

                        </label>

                        <select
                            name="id_jenis_mesin"
                            id="form_jenis_mesin"
                            class="form-select form-select-sm"
                            onchange="loadMesin(this.value)"
                        >

                            <option value="">
                                -- Pilih Jenis Mesin --
                            </option>

                        </select>

                    </div>



                    <!-- MESIN -->

                    <div class="col-md-3">

                        <label class="form-label fw-semibold text-dark small mb-1">

                            Mesin Induk

                        </label>

                        <select
                            name="id_mesin"
                            id="form_mesin"
                            class="form-select form-select-sm"
                            onchange="loadSubMesinForm(this.value)"
                        >

                            <option value="">
                                -- Pilih Mesin --
                            </option>

                        </select>

                    </div>



                    <!-- SUB MESIN -->

                    <div class="col-md-3">

                        <label class="form-label fw-semibold text-dark small mb-1">

                            Sub Mesin

                        </label>

                        <select
                            name="id_sub_mesin"
                            id="form_sub_mesin"
                            class="form-select form-select-sm"
                        >

                            <option value="">
                                -- Pilih Sub Mesin --
                            </option>

                        </select>

                    </div>


                </div>



                <hr class="my-3 text-muted opacity-25">



                <!-- =================================================
                     SECTION 2
                ================================================== -->

                <h6 class="fw-bold text-primary mb-3 small">

                    <i class="bi bi-tools me-1"></i>

                    SPESIFIKASI & BRAND

                </h6>


                <div class="row g-3 mb-3">


                    <!-- BRAND -->

                    <div class="col-md-4">

                        <label class="form-label fw-semibold text-dark small mb-1">

                            Brand / Merk

                        </label>

                        <input
                            type="text"
                            name="brand"
                            class="form-control form-control-sm"
                            value="<?= htmlspecialchars($d['brand'] ?? '') ?>"
                        >

                    </div>



                    <!-- TIPE -->

                    <div class="col-md-4">

                        <label class="form-label fw-semibold text-dark small mb-1">

                            Tipe

                        </label>

                        <input
                            type="text"
                            name="tipe"
                            class="form-control form-control-sm"
                            value="<?= htmlspecialchars($d['tipe'] ?? '') ?>"
                        >

                    </div>



                    <!-- PART NUMBER -->

                    <div class="col-md-4">

                        <label class="form-label fw-semibold text-dark small mb-1">

                            Part Number

                        </label>

                        <input
                            type="text"
                            name="part_number"
                            class="form-control form-control-sm"
                            value="<?= htmlspecialchars($d['part_number'] ?? '') ?>"
                        >

                    </div>



                    <!-- DAYA -->

                    <div class="col-md-3">

                        <label class="form-label fw-semibold text-dark small mb-1">

                            Daya

                        </label>

                        <input
                            type="text"
                            name="daya"
                            class="form-control form-control-sm"
                            value="<?= htmlspecialchars($d['daya'] ?? '') ?>"
                        >

                    </div>



                    <!-- IO ADDRESS -->

                    <div class="col-md-3">

                        <label class="form-label fw-semibold text-dark small mb-1">

                            IO Address

                        </label>

                        <input
                            type="text"
                            name="io_address"
                            class="form-control form-control-sm"
                            value="<?= htmlspecialchars($d['io_address'] ?? '') ?>"
                        >

                    </div>



                    <!-- INPUT VOLTAGE -->

                    <div class="col-md-3">

                        <label class="form-label fw-semibold text-dark small mb-1">

                            Input Voltage

                        </label>

                        <input
                            type="text"
                            name="input_voltage"
                            class="form-control form-control-sm"
                            value="<?= htmlspecialchars($d['input_voltage'] ?? '') ?>"
                        >

                    </div>



                    <!-- FREKUENSI INPUT -->

                    <div class="col-md-3">

                        <label class="form-label fw-semibold text-dark small mb-1">

                            Frekuensi Input

                        </label>

                        <input
                            type="text"
                            name="frekuensi_input"
                            class="form-control form-control-sm"
                            value="<?= htmlspecialchars($d['frekuensi_input'] ?? '') ?>"
                        >

                    </div>



                    <!-- ARUS INPUT -->

                    <div class="col-md-3">

                        <label class="form-label fw-semibold text-dark small mb-1">

                            Arus Input

                        </label>

                        <input
                            type="text"
                            name="arus_input"
                            class="form-control form-control-sm"
                            value="<?= htmlspecialchars($d['arus_input'] ?? '') ?>"
                        >

                    </div>



                    <!-- OUTPUT -->

                    <div class="col-md-3">

                        <label class="form-label fw-semibold text-dark small mb-1">

                            Output

                        </label>

                        <input
                            type="text"
                            name="output"
                            class="form-control form-control-sm"
                            value="<?= htmlspecialchars($d['output'] ?? '') ?>"
                        >

                    </div>



                    <!-- FREKUENSI OUTPUT -->

                    <div class="col-md-3">

                        <label class="form-label fw-semibold text-dark small mb-1">

                            Frekuensi Output

                        </label>

                        <input
                            type="text"
                            name="frekuensi_output"
                            class="form-control form-control-sm"
                            value="<?= htmlspecialchars($d['frekuensi_output'] ?? '') ?>"
                        >

                    </div>



                    <!-- IP RATING -->

                    <div class="col-md-3">

                        <label class="form-label fw-semibold text-dark small mb-1">

                            IP Rating

                        </label>

                        <input
                            type="text"
                            name="ip_rating"
                            class="form-control form-control-sm"
                            value="<?= htmlspecialchars($d['ip_rating'] ?? '') ?>"
                        >

                    </div>


                </div>



                <hr class="my-3 text-muted opacity-25">



                <!-- =================================================
                     SECTION 3
                ================================================== -->

                <h6 class="fw-bold text-primary mb-3 small">

                    <i class="bi bi-card-checklist me-1"></i>

                    STATUS KOMPONEN

                </h6>


                <div class="row g-3">


                    <!-- KONDISI -->

                    <div class="col-md-4">

                        <label class="form-label fw-semibold text-dark small mb-1">

                            Kondisi
                            <span class="text-danger">*</span>

                        </label>


                        <select
                            name="kondisi"
                            class="form-select form-select-sm"
                            required
                        >

                            <option
                                value="Baik"
                                <?= (($d['kondisi'] ?? '') == 'Baik') ? 'selected' : '' ?>
                            >
                                Baik
                            </option>

                            <option
                                value="Perlu Pemeriksaan"
                                <?= (($d['kondisi'] ?? '') == 'Perlu Pemeriksaan') ? 'selected' : '' ?>
                            >
                                Perlu Pemeriksaan
                            </option>

                            <option
                                value="Dalam Perbaikan"
                                <?= (($d['kondisi'] ?? '') == 'Dalam Perbaikan') ? 'selected' : '' ?>
                            >
                                Dalam Perbaikan
                            </option>

                        </select>

                    </div>



                    <!-- KETERANGAN -->

                    <div class="col-md-8">

                        <label class="form-label fw-semibold text-dark small mb-1">

                            Keterangan Tambahan

                        </label>

                        <textarea
                            name="keterangan"
                            class="form-control form-control-sm"
                            rows="2"
                        ><?= htmlspecialchars($d['keterangan'] ?? '') ?></textarea>

                    </div>


                </div>



                <!-- BUTTON -->

                <div class="border-top mt-3 pt-3 d-flex align-items-center gap-2">


                    <button
                        type="submit"
                        name="update"
                        class="btn btn-warning btn-sm fw-semibold text-dark px-4"
                    >

                        <i class="bi bi-check-lg me-1"></i>

                        Update Data

                    </button>


                    <a
                        href="index.php"
                        class="btn btn-light border px-4 btn-sm fw-semibold text-secondary"
                    >

                        Batal

                    </a>


                </div>


            </form>


        </div>

    </div>

</div>



<script>

/* =========================================================
   LOAD AREA BERDASARKAN LOKASI
========================================================= */

function loadAreaByLokasi(
    lokasi,
    selectedArea = ''
) {

    const areaSelect =
        document.getElementById('form_area');

    const jenisSelect =
        document.getElementById('form_jenis_mesin');

    const mesinSelect =
        document.getElementById('form_mesin');

    const subSelect =
        document.getElementById('form_sub_mesin');


    areaSelect.innerHTML =
        '<option value="">-- Pilih Area --</option>';

    jenisSelect.innerHTML =
        '<option value="">-- Pilih Jenis Mesin --</option>';

    mesinSelect.innerHTML =
        '<option value="">-- Pilih Mesin --</option>';

    subSelect.innerHTML =
        '<option value="">-- Pilih Sub Mesin --</option>';


    if (!lokasi) {
        return;
    }


    fetch(
        'get_area.php?lokasi=' +
        encodeURIComponent(lokasi)
    )

    .then(response => response.text())

    .then(data => {

        areaSelect.innerHTML = data;

        if (selectedArea !== '') {

            areaSelect.value = selectedArea;

        }

    })

    .catch(err => {

        console.error(
            'Gagal memuat Area:',
            err
        );

    });

}


/* =========================================================
   LOAD JENIS MESIN
========================================================= */

function loadJenisMesin(
    id_area,
    selectedJenis = ''
) {

    const jenisSelect =
        document.getElementById('form_jenis_mesin');

    const mesinSelect =
        document.getElementById('form_mesin');

    const subSelect =
        document.getElementById('form_sub_mesin');


    jenisSelect.innerHTML =
        '<option value="">-- Pilih Jenis Mesin --</option>';

    mesinSelect.innerHTML =
        '<option value="">-- Pilih Mesin --</option>';

    subSelect.innerHTML =
        '<option value="">-- Pilih Sub Mesin --</option>';


    if (!id_area) {
        return;
    }


    fetch(
        'get_jenis_mesin.php?id_area=' +
        id_area
    )

    .then(response => response.text())

    .then(data => {

        jenisSelect.innerHTML = data;

        if (selectedJenis !== '') {

            jenisSelect.value = selectedJenis;

        }

    })

    .catch(err => {

        console.error(
            'Gagal memuat Jenis Mesin:',
            err
        );

    });

}


/* =========================================================
   LOAD MESIN
========================================================= */

function loadMesin(
    id_jenis,
    selectedMesin = ''
) {

    const mesinSelect =
        document.getElementById('form_mesin');

    const subSelect =
        document.getElementById('form_sub_mesin');


    mesinSelect.innerHTML =
        '<option value="">-- Pilih Mesin --</option>';

    subSelect.innerHTML =
        '<option value="">-- Pilih Sub Mesin --</option>';


    if (!id_jenis) {
        return;
    }


    fetch(
        'get_mesin.php?id_jenis=' +
        id_jenis
    )

    .then(response => response.text())

    .then(data => {

        mesinSelect.innerHTML = data;

        if (selectedMesin !== '') {

            mesinSelect.value = selectedMesin;

        }

    })

    .catch(err => {

        console.error(
            'Gagal memuat Mesin:',
            err
        );

    });

}


/* =========================================================
   LOAD SUB MESIN
========================================================= */

function loadSubMesinForm(
    id_mesin,
    selectedSub = ''
) {

    const subSelect =
        document.getElementById('form_sub_mesin');


    subSelect.innerHTML =
        '<option value="">-- Pilih Sub Mesin --</option>';


    if (!id_mesin) {
        return;
    }


    fetch(
        'get_sub_mesin.php?id_mesin=' +
        id_mesin
    )

    .then(response => response.text())

    .then(data => {

        subSelect.innerHTML = data;

        if (selectedSub !== '') {

            subSelect.value = selectedSub;

        }

    })

    .catch(err => {

        console.error(
            'Gagal memuat Sub Mesin:',
            err
        );

    });

}


/* =========================================================
   INISIALISASI DATA EDIT
========================================================= */

document.addEventListener(
    "DOMContentLoaded",
    function() {

        const initLokasi =
            <?= json_encode($d['lokasi_area'] ?? '') ?>;

        const initArea =
            <?= json_encode($d['id_area'] ?? '') ?>;

        const initJenis =
            <?= json_encode($d['id_jenis_mesin'] ?? '') ?>;

        const initMesin =
            <?= json_encode($d['id_mesin'] ?? '') ?>;

        const initSub =
            <?= json_encode($d['id_sub_mesin'] ?? '') ?>;


        /*
         * Urutan:
         *
         * Lokasi
         *    ↓
         * Area
         *    ↓
         * Jenis Mesin
         *    ↓
         * Mesin
         *    ↓
         * Sub Mesin
         */


        if (initLokasi !== "") {

            loadAreaByLokasi(
                initLokasi,
                initArea
            );


            if (initArea !== "") {

                setTimeout(
                    function() {

                        loadJenisMesin(
                            initArea,
                            initJenis
                        );


                        if (initJenis !== "") {

                            setTimeout(
                                function() {

                                    loadMesin(
                                        initJenis,
                                        initMesin
                                    );


                                    if (initMesin !== "") {

                                        setTimeout(
                                            function() {

                                                loadSubMesinForm(
                                                    initMesin,
                                                    initSub
                                                );

                                            },
                                            200
                                        );

                                    }

                                },
                                200
                            );

                        }

                    },
                    200
                );

            }

        }

    }
);

</script>


<?php include "../template/footer.php"; ?>
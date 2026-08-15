<?php
include "../koneksi.php";

date_default_timezone_set('Asia/Jakarta');

$error = "";
$success = "";

/* =========================================================
   FUNGSI ESCAPE
========================================================= */
function e($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}


/* =========================================================
   PROSES SIMPAN MAINTENANCE
========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_maintenance'])) {

    /* =====================================================
       DATA UTAMA
    ===================================================== */

    $id_komponen = isset($_POST['id_komponen'])
        ? intval($_POST['id_komponen'])
        : 0;

    $status = trim($_POST['status'] ?? '');

    $teknisi = trim($_POST['teknisi'] ?? '');

    $tindakan = trim($_POST['tindakan'] ?? '');

    $jenis = trim($_POST['jenis'] ?? '');

    $sparepart = trim($_POST['sparepart'] ?? '');

    $catatan = trim($_POST['catatan'] ?? '');

    $input_tanggal = trim(
        $_POST['tanggal'] ?? date('Y-m-d')
    );


    /* =====================================================
       DATA SPESIFIKASI
    ===================================================== */

    $serial_number = trim(
        $_POST['serial_number'] ?? ''
    );

    $nama_bagian = trim(
        $_POST['nama_bagian'] ?? ''
    );

    $kategori = trim(
        $_POST['kategori'] ?? ''
    );

    $nama_mesin = trim(
        $_POST['nama_mesin'] ?? ''
    );

    $nama_sub_mesin = trim(
        $_POST['nama_sub_mesin'] ?? ''
    );

    $lokasi_penempatan = trim(
        $_POST['lokasi_penempatan'] ?? ''
    );

    $brand = trim(
        $_POST['brand'] ?? ''
    );

    $tipe = trim(
        $_POST['tipe'] ?? ''
    );

    $part_number = trim(
        $_POST['part_number'] ?? ''
    );

    $daya = trim(
        $_POST['daya'] ?? ''
    );

    $io_address = trim(
        $_POST['io_address'] ?? ''
    );

    $input_voltage = trim(
        $_POST['input_voltage'] ?? ''
    );

    $frekuensi_input = trim(
        $_POST['frekuensi_input'] ?? ''
    );

    $arus_input = trim(
        $_POST['arus_input'] ?? ''
    );

    $output = trim(
        $_POST['output'] ?? ''
    );

    $frekuensi_output = trim(
        $_POST['frekuensi_output'] ?? ''
    );

    $ip_rating = trim(
        $_POST['ip_rating'] ?? ''
    );


    /* =====================================================
       VALIDASI DATA
    ===================================================== */

    if ($id_komponen <= 0) {

        $error = "Silakan pilih komponen terlebih dahulu.";

    } elseif (empty($tindakan)) {

        $error = "Tindakan maintenance wajib diisi.";

    } elseif (empty($status)) {

        $error = "Status pekerjaan wajib dipilih.";

    } elseif (empty($input_tanggal)) {

        $error = "Tanggal maintenance wajib diisi.";

    } else {

        $tanggal_obj = DateTime::createFromFormat(
            'Y-m-d',
            $input_tanggal
        );

        if (
            !$tanggal_obj ||
            $tanggal_obj->format('Y-m-d') !== $input_tanggal
        ) {

            $error = "Format tanggal maintenance tidak valid.";
        }
    }


    /* =====================================================
       VALIDASI STATUS
    ===================================================== */

    $status_allowed = [
        'Selesai',
        'Proses',
        'Pending'
    ];

    if (
        empty($error) &&
        !in_array($status, $status_allowed, true)
    ) {

        $error = "Status maintenance tidak valid.";
    }


    /* =====================================================
       VALIDASI JENIS MAINTENANCE
    ===================================================== */

    $jenis_allowed = [
        'Preventive',
        'Corrective',
        'Breakdown',
        'Predictive'
    ];

    if (
        empty($error) &&
        !empty($jenis) &&
        !in_array($jenis, $jenis_allowed, true)
    ) {

        $error = "Jenis maintenance tidak valid.";
    }


    /* =====================================================
       AMBIL DATA KOMPONEN DARI DATABASE
       Supaya data spesifikasi tidak mudah dimanipulasi
    ===================================================== */

    if (empty($error)) {

        $stmt_komponen = mysqli_prepare(
            $conn,
            "
            SELECT
                k.*,
                sm.nama_sub_mesin,
                m.nama_mesin
            FROM komponen k

            LEFT JOIN sub_mesin sm
                ON k.id_sub_mesin = sm.id

            LEFT JOIN mesin m
                ON sm.id_mesin = m.id

            WHERE k.id = ?
            LIMIT 1
            "
        );


        if (!$stmt_komponen) {

            $error =
                "Gagal menyiapkan data komponen: " .
                mysqli_error($conn);

        } else {

            mysqli_stmt_bind_param(
                $stmt_komponen,
                "i",
                $id_komponen
            );

            mysqli_stmt_execute(
                $stmt_komponen
            );

            $result_komponen =
                mysqli_stmt_get_result(
                    $stmt_komponen
                );

            $data_komponen =
                mysqli_fetch_assoc(
                    $result_komponen
                );

            mysqli_stmt_close(
                $stmt_komponen
            );


            if (!$data_komponen) {

                $error =
                    "Komponen yang dipilih tidak ditemukan.";
            }
        }
    }


    /* =====================================================
       JIKA DATA KOMPONEN VALID,
       GUNAKAN DATA DATABASE SEBAGAI SUMBER UTAMA
    ===================================================== */

    if (
        empty($error) &&
        !empty($data_komponen)
    ) {

        $serial_number =
            $data_komponen['serial_number'] ?? '';

        $nama_bagian =
            $data_komponen['nama_bagian'] ?? '';

        $kategori =
            $data_komponen['kategori'] ?? '';

        $nama_mesin =
            $data_komponen['nama_mesin'] ?? '';

        $nama_sub_mesin =
            $data_komponen['nama_sub_mesin'] ?? '';

        $lokasi_penempatan =
            $data_komponen['lokasi'] ?? '';

        $brand =
            $data_komponen['brand'] ?? '';

        $tipe =
            $data_komponen['tipe'] ?? '';

        $part_number =
            $data_komponen['part_number'] ?? '';

        $daya =
            $data_komponen['daya'] ?? '';

        $io_address =
            $data_komponen['io_address'] ?? '';

        $input_voltage =
            $data_komponen['input_voltage'] ?? '';

        $frekuensi_input =
            $data_komponen['frekuensi_input'] ?? '';

        $arus_input =
            $data_komponen['arus_input'] ?? '';

        $output =
            $data_komponen['output'] ?? '';

        $frekuensi_output =
            $data_komponen['frekuensi_output'] ?? '';

        $ip_rating =
            $data_komponen['ip_rating'] ?? '';
    }


    /* =====================================================
       UPLOAD FOTO
    ===================================================== */

    $foto_nama = null;

    $foto_baru_path = "";


    if (
        empty($error) &&
        isset($_FILES['foto']) &&
        $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        $file = $_FILES['foto'];


        /* =================================================
           CEK ERROR UPLOAD
        ================================================= */

        if ($file['error'] !== UPLOAD_ERR_OK) {

            $error =
                "Gagal mengupload foto. Kode error upload: " .
                $file['error'];

        } elseif ($file['size'] > 5 * 1024 * 1024) {

            $error =
                "Ukuran foto maksimal 5 MB.";

        } elseif (!is_uploaded_file($file['tmp_name'])) {

            $error =
                "File foto tidak valid.";

        } else {

            /* =============================================
               VALIDASI MIME
            ============================================= */

            $allowed_mime = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp'
            ];


            $finfo = finfo_open(
                FILEINFO_MIME_TYPE
            );


            $mime_type = finfo_file(
                $finfo,
                $file['tmp_name']
            );


            finfo_close($finfo);


            if (
                !isset(
                    $allowed_mime[$mime_type]
                )
            ) {

                $error =
                    "Format foto harus JPG, PNG, atau WEBP.";

            } else {

                /* =========================================
                   FOLDER UPLOAD
                ========================================= */

                $target_dir =
                    "../uploads/maintenance/";


                if (!is_dir($target_dir)) {

                    if (
                        !mkdir(
                            $target_dir,
                            0777,
                            true
                        )
                    ) {

                        $error =
                            "Folder upload foto tidak dapat dibuat.";
                    }
                }


                /* =========================================
                   GENERATE NAMA FILE
                ========================================= */

                if (empty($error)) {

                    $extension =
                        $allowed_mime[$mime_type];


                    try {

                        $random =
                            bin2hex(
                                random_bytes(5)
                            );

                    } catch (Exception $e) {

                        $random =
                            uniqid();
                    }


                    $foto_nama =
                        'maintenance_' .
                        $id_komponen . '_' .
                        time() . '_' .
                        $random . '.' .
                        $extension;


                    $foto_baru_path =
                        $target_dir .
                        $foto_nama;


                    /* =====================================
                       PINDAHKAN FILE
                    ===================================== */

                    if (
                        !move_uploaded_file(
                            $file['tmp_name'],
                            $foto_baru_path
                        )
                    ) {

                        $error =
                            "Gagal menyimpan foto ke server.";

                        $foto_nama = null;

                        $foto_baru_path = "";
                    }
                }
            }
        }
    }


    /* =====================================================
       SIMPAN KE DATABASE
    ===================================================== */

    if (empty($error)) {

        $tanggal_lengkap =
            $input_tanggal .
            ' ' .
            date('H:i:s');


        $sql_insert = "
            INSERT INTO riwayat_maintenance (

                id_komponen,
                tanggal,
                status,
                teknisi,
                tindakan,
                jenis,

                serial_number,
                nama_bagian,
                kategori,
                nama_mesin,
                nama_sub_mesin,
                lokasi_penempatan,

                brand,
                tipe,
                part_number,
                daya,
                io_address,
                input_voltage,
                frekuensi_input,

                arus_input,
                output,
                frekuensi_output,
                ip_rating,

                sparepart,
                catatan,
                gambar

            ) VALUES (

                ?,
                ?,
                ?,
                ?,
                ?,
                ?,

                ?,
                ?,
                ?,
                ?,
                ?,
                ?,

                ?,
                ?,
                ?,
                ?,
                ?,
                ?,
                ?,

                ?,
                ?,
                ?,
                ?,

                ?,
                ?,
                ?

            )
        ";


        $stmt =
            mysqli_prepare(
                $conn,
                $sql_insert
            );


        if (!$stmt) {

            $error =
                "Gagal menyiapkan query database: " .
                mysqli_error($conn);

        } else {

            mysqli_stmt_bind_param(
                $stmt,
                "isssssssssssssssssssssssss",

                $id_komponen,
                $tanggal_lengkap,
                $status,
                $teknisi,
                $tindakan,
                $jenis,

                $serial_number,
                $nama_bagian,
                $kategori,
                $nama_mesin,
                $nama_sub_mesin,
                $lokasi_penempatan,

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

                $sparepart,
                $catatan,
                $foto_nama
            );


            if (
                mysqli_stmt_execute($stmt)
            ) {

                /* =========================================
                   UPDATE KONDISI KOMPONEN
                ========================================= */

                $kondisi_baru = "";


                if ($status === 'Selesai') {

                    $kondisi_baru = 'Baik';

                } elseif ($status === 'Proses') {

                    $kondisi_baru = 'Dalam Perbaikan';

                } elseif ($status === 'Pending') {

                    $kondisi_baru = 'Perlu Pemeriksaan';
                }


                if (!empty($kondisi_baru)) {

                    $stmt_update =
                        mysqli_prepare(
                            $conn,
                            "
                            UPDATE komponen
                            SET kondisi = ?
                            WHERE id = ?
                            "
                        );


                    if ($stmt_update) {

                        mysqli_stmt_bind_param(
                            $stmt_update,
                            "si",
                            $kondisi_baru,
                            $id_komponen
                        );

                        mysqli_stmt_execute(
                            $stmt_update
                        );

                        mysqli_stmt_close(
                            $stmt_update
                        );
                    }
                }


                mysqli_stmt_close($stmt);


                /* =========================================
                   REDIRECT
                ========================================= */

                header(
                    "Location: index.php?simpan=berhasil"
                );

                exit;


            } else {

                $error =
                    "Gagal menyimpan data maintenance: " .
                    mysqli_stmt_error($stmt);


                mysqli_stmt_close($stmt);


                /* =========================================
                   HAPUS FOTO JIKA DB GAGAL
                ========================================= */

                if (
                    !empty($foto_baru_path) &&
                    file_exists($foto_baru_path)
                ) {

                    @unlink(
                        $foto_baru_path
                    );
                }
            }
        }
    }


    /* =====================================================
       JIKA ERROR DAN FOTO SUDAH TERUPLOAD
       HAPUS FOTO BARU
    ===================================================== */

    if (
        !empty($error) &&
        !empty($foto_baru_path) &&
        file_exists($foto_baru_path)
    ) {

        @unlink(
            $foto_baru_path
        );
    }
}


/* =========================================================
   AMBIL DATA KOMPONEN
========================================================= */

$q_komponen = mysqli_query(
    $conn,
    "
    SELECT
        k.*,
        sm.nama_sub_mesin,
        m.nama_mesin

    FROM komponen k

    LEFT JOIN sub_mesin sm
        ON k.id_sub_mesin = sm.id

    LEFT JOIN mesin m
        ON sm.id_mesin = m.id

    ORDER BY
        k.nama_bagian ASC
    "
);


if (!$q_komponen) {

    $error =
        "Gagal mengambil data komponen: " .
        mysqli_error($conn);
}


include "../template/header.php";
?>


<!-- =========================================================
     SELECT2 CSS
========================================================= -->

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css"
>

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
>


<style>

/* =========================================================
   GENERAL
========================================================= */

.maintenance-page {
    max-width: 100%;
}

.maintenance-card {
    border: 0;
    border-radius: 18px;
    box-shadow: 0 4px 18px rgba(0, 0, 0, .05);
}

.section-title {
    color: #0056a6;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .3px;
    text-transform: uppercase;
}

.form-label {
    font-size: 13px;
    margin-bottom: 6px;
}

.form-control,
.form-select {
    min-height: 40px;
}

textarea.form-control {
    min-height: auto;
}


/* =========================================================
   SELECT2
========================================================= */

.select2-container {
    width: 100% !important;
}

.select2-container--bootstrap-5
.select2-selection {

    min-height: 40px !important;

    border-radius: 10px !important;

    border-color: #dee2e6 !important;

    padding-top: 3px;
}

.select2-container--bootstrap-5
.select2-selection__rendered {

    padding-left: 10px !important;

    line-height: 31px !important;
}


/* =========================================================
   FOTO PREVIEW
========================================================= */

.photo-preview-wrapper {

    width: 100%;

    min-height: 180px;

    border: 1px dashed #cbd5e1;

    border-radius: 14px;

    background: #f8fafc;

    display: flex;

    align-items: center;

    justify-content: center;

    overflow: hidden;

    position: relative;
}

.photo-preview-wrapper img {

    max-width: 100%;

    max-height: 240px;

    width: auto;

    height: auto;

    object-fit: contain;

    border-radius: 10px;
}

.photo-empty {

    text-align: center;

    color: #94a3b8;
}

.photo-empty i {

    font-size: 42px;

    opacity: .45;
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 767.98px) {

    .container-fluid {
        padding-left: 10px !important;
        padding-right: 10px !important;
    }

    .maintenance-card {
        border-radius: 14px;
    }

    .maintenance-card .card-body {
        padding: 15px !important;
    }

    .page-header {
        padding: 15px !important;
    }

    .page-header h2 {
        font-size: 19px !important;
    }

    .page-header p {
        font-size: 12px;
    }

    .back-button {
        width: 38px !important;
        height: 38px !important;
    }

    .submit-wrapper {
        flex-direction: column;
    }

    .submit-wrapper .btn {
        width: 100%;
    }

    .photo-preview-wrapper {
        min-height: 150px;
    }

}


/* =========================================================
   TABLET
========================================================= */

@media (min-width: 768px) and (max-width: 991.98px) {

    .page-header h2 {
        font-size: 21px !important;
    }

}


/* =========================================================
   BUTTON
========================================================= */

.btn-primary-garuda {

    background-color: #0056a6;

    border-color: #0056a6;

    color: #fff;
}

.btn-primary-garuda:hover {

    background-color: #004685;

    border-color: #004685;

    color: #fff;
}

</style>


<div class="container-fluid maintenance-page px-3 py-2">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div
        class="card maintenance-card bg-white p-4 mb-4 page-header"
    >

        <div
            class="d-flex align-items-center gap-3"
        >

            <a
                href="index.php"
                class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 back-button"
                style="width:42px;height:42px;"
                title="Kembali"
            >

                <i class="bi bi-arrow-left fs-5"></i>

            </a>


            <div class="min-w-0">

                <h2
                    class="fw-bold text-dark m-0"
                    style="font-size:23px;"
                >

                    Tambah Catatan Maintenance

                </h2>


                <p
                    class="text-muted small m-0 mt-1"
                >

                    Pilih komponen untuk otomatis mengisi
                    informasi dan spesifikasi teknis.

                </p>

            </div>

        </div>

    </div>



    <!-- =====================================================
         FORM CARD
    ====================================================== -->

    <div
        class="card maintenance-card bg-white mb-4"
    >

        <div class="card-body p-4">


            <!-- =================================================
                 ERROR
            ================================================== -->

            <?php if (!empty($error)) : ?>

                <div
                    class="alert alert-danger border-0 rounded-3 d-flex align-items-start"
                >

                    <i
                        class="bi bi-exclamation-triangle-fill me-2 mt-1"
                    ></i>

                    <div>

                        <strong>Gagal menyimpan data</strong>

                        <div class="small mt-1">

                            <?= e($error); ?>

                        </div>

                    </div>

                </div>

            <?php endif; ?>



            <!-- =================================================
                 FORM
            ================================================== -->

            <form
                method="POST"
                action=""
                enctype="multipart/form-data"
                id="maintenanceForm"
            >

                <input
                    type="hidden"
                    name="simpan_maintenance"
                    value="1"
                >



                <!-- =================================================
                     PILIH KOMPONEN
                ================================================== -->

                <div class="mb-4">

                    <div class="section-title mb-3">

                        <i class="bi bi-search me-2"></i>

                        Pilih Komponen / Part

                    </div>


                    <select
                        name="id_komponen"
                        id="select-komponen"
                        class="form-select"
                        required
                    >

                        <option value=""></option>


                        <?php if ($q_komponen) : ?>

                            <?php
                            while (
                                $k =
                                mysqli_fetch_assoc(
                                    $q_komponen
                                )
                            ) :
                            ?>

                                <?php

                                $label =
                                    ($k['nama_bagian'] ?? 'Tanpa Nama') .
                                    ' — [SN: ' .
                                    (
                                        !empty($k['serial_number'])
                                            ? $k['serial_number']
                                            : '-'
                                    ) .
                                    '] (Mesin: ' .
                                    (
                                        !empty($k['nama_mesin'])
                                            ? $k['nama_mesin']
                                            : 'Tanpa Mesin'
                                    ) .
                                    ')';

                                ?>

                                <option
                                    value="<?= e($k['id']); ?>"

                                    data-sn="<?= e($k['serial_number'] ?? ''); ?>"

                                    data-namabagian="<?= e($k['nama_bagian'] ?? ''); ?>"

                                    data-kategori="<?= e($k['kategori'] ?? ''); ?>"

                                    data-mesin="<?= e($k['nama_mesin'] ?? ''); ?>"

                                    data-submesin="<?= e($k['nama_sub_mesin'] ?? ''); ?>"

                                    data-lokasi="<?= e($k['lokasi'] ?? ''); ?>"

                                    data-brand="<?= e($k['brand'] ?? ''); ?>"

                                    data-tipe="<?= e($k['tipe'] ?? ''); ?>"

                                    data-pn="<?= e($k['part_number'] ?? ''); ?>"

                                    data-daya="<?= e($k['daya'] ?? ''); ?>"

                                    data-io="<?= e($k['io_address'] ?? ''); ?>"

                                    data-vol="<?= e($k['input_voltage'] ?? ''); ?>"

                                    data-freqin="<?= e($k['frekuensi_input'] ?? ''); ?>"

                                    data-arus="<?= e($k['arus_input'] ?? ''); ?>"

                                    data-out="<?= e($k['output'] ?? ''); ?>"

                                    data-freqout="<?= e($k['frekuensi_output'] ?? ''); ?>"

                                    data-ip="<?= e($k['ip_rating'] ?? ''); ?>"
                                >

                                    <?= e($label); ?>

                                </option>

                            <?php endwhile; ?>

                        <?php endif; ?>

                    </select>


                    <div class="form-text">

                        Ketik nama komponen, nama bagian,
                        serial number, atau nama mesin.

                    </div>

                </div>



                <hr class="my-4 border-light-subtle">



                <!-- =================================================
                     INFORMASI UMUM
                ================================================== -->

                <div class="mb-4">

                    <div class="section-title mb-3">

                        <i class="bi bi-info-circle me-2"></i>

                        Informasi Umum

                    </div>


                    <div class="row g-3">


                        <!-- SERIAL NUMBER -->

                        <div class="col-12 col-md-4">

                            <label
                                class="form-label fw-semibold"
                            >

                                Serial Number (SN)

                            </label>

                            <input
                                type="text"
                                name="serial_number"
                                id="f-sn"
                                class="form-control rounded-3"
                                readonly
                            >

                        </div>



                        <!-- NAMA BAGIAN -->

                        <div class="col-12 col-md-4">

                            <label
                                class="form-label fw-semibold"
                            >

                                Nama Bagian

                                <span class="text-danger">*</span>

                            </label>

                            <input
                                type="text"
                                name="nama_bagian"
                                id="f-namabagian"
                                class="form-control rounded-3"
                                readonly
                                required
                            >

                        </div>



                        <!-- KATEGORI -->

                        <div class="col-12 col-md-4">

                            <label
                                class="form-label fw-semibold"
                            >

                                Kategori

                            </label>

                            <input
                                type="text"
                                name="kategori"
                                id="f-kategori"
                                class="form-control rounded-3"
                                readonly
                            >

                        </div>



                        <!-- MESIN -->

                        <div class="col-12 col-md-4">

                            <label
                                class="form-label fw-semibold"
                            >

                                Mesin Induk

                            </label>

                            <input
                                type="text"
                                name="nama_mesin"
                                id="f-mesin"
                                class="form-control rounded-3"
                                readonly
                            >

                        </div>



                        <!-- SUB MESIN -->

                        <div class="col-12 col-md-4">

                            <label
                                class="form-label fw-semibold"
                            >

                                Sub Mesin

                            </label>

                            <input
                                type="text"
                                name="nama_sub_mesin"
                                id="f-submesin"
                                class="form-control rounded-3"
                                readonly
                            >

                        </div>



                        <!-- LOKASI -->

                        <div class="col-12 col-md-4">

                            <label
                                class="form-label fw-semibold"
                            >

                                Lokasi Penempatan

                            </label>

                            <input
                                type="text"
                                name="lokasi_penempatan"
                                id="f-lokasi"
                                class="form-control rounded-3"
                                readonly
                            >

                        </div>

                    </div>

                </div>



                <!-- =================================================
                     FOTO
                ================================================== -->

                <div class="mb-4">

                    <div class="section-title mb-3">

                        <i class="bi bi-camera me-2"></i>

                        Dokumentasi Maintenance

                    </div>


                    <div class="row g-3 align-items-start">


                        <div class="col-12 col-md-7">

                            <label
                                class="form-label fw-semibold"
                            >

                                Foto Dokumentasi / Bukti Maintenance

                            </label>


                            <input
                                type="file"
                                name="foto"
                                id="input-foto"
                                class="form-control rounded-3"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            >


                            <div
                                class="form-text mt-2"
                            >

                                Format JPG, JPEG, PNG,
                                atau WEBP. Maksimal 5 MB.

                            </div>

                        </div>



                        <div class="col-12 col-md-5">

                            <div
                                class="photo-preview-wrapper"
                                id="preview-container"
                            >

                                <div
                                    class="photo-empty"
                                    id="photo-empty"
                                >

                                    <i
                                        class="bi bi-image d-block"
                                    ></i>

                                    <small>
                                        Preview foto
                                    </small>

                                </div>


                                <img
                                    src=""
                                    id="image-preview"
                                    alt="Preview Foto"
                                    class="d-none"
                                >


                                <button
                                    type="button"
                                    id="btn-remove-foto"
                                    class="btn btn-danger btn-sm position-absolute top-0 end-0 m-2 rounded-circle d-none"
                                    style="width:32px;height:32px;"
                                    title="Hapus foto"
                                >

                                    <i class="bi bi-x-lg"></i>

                                </button>

                            </div>

                        </div>

                    </div>

                </div>



                <hr class="my-4 border-light-subtle">



                <!-- =================================================
                     SPESIFIKASI
                ================================================== -->

                <div class="mb-4">

                    <div class="section-title mb-3">

                        <i class="bi bi-cpu me-2"></i>

                        Spesifikasi & Brand

                    </div>


                    <div class="row g-3">


                        <div class="col-12 col-md-4">

                            <label class="form-label fw-semibold">
                                Brand / Merk
                            </label>

                            <input
                                type="text"
                                name="brand"
                                id="f-brand"
                                class="form-control rounded-3"
                                readonly
                            >

                        </div>


                        <div class="col-12 col-md-4">

                            <label class="form-label fw-semibold">
                                Tipe
                            </label>

                            <input
                                type="text"
                                name="tipe"
                                id="f-tipe"
                                class="form-control rounded-3"
                                readonly
                            >

                        </div>


                        <div class="col-12 col-md-4">

                            <label class="form-label fw-semibold">
                                Part Number
                            </label>

                            <input
                                type="text"
                                name="part_number"
                                id="f-pn"
                                class="form-control rounded-3"
                                readonly
                            >

                        </div>


                        <div class="col-12 col-md-3">

                            <label class="form-label fw-semibold">
                                Daya
                            </label>

                            <input
                                type="text"
                                name="daya"
                                id="f-daya"
                                class="form-control rounded-3"
                                readonly
                            >

                        </div>


                        <div class="col-12 col-md-3">

                            <label class="form-label fw-semibold">
                                IO Address
                            </label>

                            <input
                                type="text"
                                name="io_address"
                                id="f-io"
                                class="form-control rounded-3"
                                readonly
                            >

                        </div>


                        <div class="col-12 col-md-3">

                            <label class="form-label fw-semibold">
                                Input Voltage
                            </label>

                            <input
                                type="text"
                                name="input_voltage"
                                id="f-vol"
                                class="form-control rounded-3"
                                readonly
                            >

                        </div>


                        <div class="col-12 col-md-3">

                            <label class="form-label fw-semibold">
                                Frekuensi Input
                            </label>

                            <input
                                type="text"
                                name="frekuensi_input"
                                id="f-freqin"
                                class="form-control rounded-3"
                                readonly
                            >

                        </div>


                        <div class="col-12 col-md-3">

                            <label class="form-label fw-semibold">
                                Arus Input
                            </label>

                            <input
                                type="text"
                                name="arus_input"
                                id="f-arus"
                                class="form-control rounded-3"
                                readonly
                            >

                        </div>


                        <div class="col-12 col-md-3">

                            <label class="form-label fw-semibold">
                                Output
                            </label>

                            <input
                                type="text"
                                name="output"
                                id="f-out"
                                class="form-control rounded-3"
                                readonly
                            >

                        </div>


                        <div class="col-12 col-md-3">

                            <label class="form-label fw-semibold">
                                Frekuensi Output
                            </label>

                            <input
                                type="text"
                                name="frekuensi_output"
                                id="f-freqout"
                                class="form-control rounded-3"
                                readonly
                            >

                        </div>


                        <div class="col-12 col-md-3">

                            <label class="form-label fw-semibold">
                                IP Rating
                            </label>

                            <input
                                type="text"
                                name="ip_rating"
                                id="f-ip"
                                class="form-control rounded-3"
                                readonly
                            >

                        </div>

                    </div>

                </div>



                <hr class="my-4 border-light-subtle">



                <!-- =================================================
                     MAINTENANCE
                ================================================== -->

                <div class="mb-4">

                    <div class="section-title mb-3">

                        <i class="bi bi-tools me-2"></i>

                        Status & Tindakan Maintenance

                    </div>


                    <div class="row g-3">


                        <!-- TANGGAL -->

                        <div class="col-12 col-md-4">

                            <label
                                class="form-label fw-semibold"
                            >

                                Tanggal Maintenance

                                <span class="text-danger">*</span>

                            </label>

                            <input
                                type="date"
                                name="tanggal"
                                class="form-control rounded-3"
                                value="<?= e($_POST['tanggal'] ?? date('Y-m-d')); ?>"
                                required
                            >

                        </div>



                        <!-- JENIS -->

                        <div class="col-12 col-md-4">

                            <label
                                class="form-label fw-semibold"
                            >

                                Jenis Maintenance

                            </label>

                            <select
                                name="jenis"
                                class="form-select rounded-3"
                            >

                                <option
                                    value="Preventive"
                                    <?= (($_POST['jenis'] ?? '') === 'Preventive' || empty($_POST['jenis']))
                                        ? 'selected'
                                        : ''; ?>
                                >
                                    Preventive
                                </option>

                                <option
                                    value="Corrective"
                                    <?= (($_POST['jenis'] ?? '') === 'Corrective')
                                        ? 'selected'
                                        : ''; ?>
                                >
                                    Corrective
                                </option>

                                <option
                                    value="Breakdown"
                                    <?= (($_POST['jenis'] ?? '') === 'Breakdown')
                                        ? 'selected'
                                        : ''; ?>
                                >
                                    Breakdown
                                </option>

                                <option
                                    value="Predictive"
                                    <?= (($_POST['jenis'] ?? '') === 'Predictive')
                                        ? 'selected'
                                        : ''; ?>
                                >
                                    Predictive
                                </option>

                            </select>

                        </div>



                        <!-- STATUS -->

                        <div class="col-12 col-md-4">

                            <label
                                class="form-label fw-semibold"
                            >

                                Status Pekerjaan

                                <span class="text-danger">*</span>

                            </label>

                            <select
                                name="status"
                                class="form-select rounded-3"
                                required
                            >

                                <option
                                    value="Selesai"
                                    <?= (($_POST['status'] ?? 'Selesai') === 'Selesai')
                                        ? 'selected'
                                        : ''; ?>
                                >
                                    Selesai
                                </option>

                                <option
                                    value="Proses"
                                    <?= (($_POST['status'] ?? '') === 'Proses')
                                        ? 'selected'
                                        : ''; ?>
                                >
                                    Proses
                                </option>

                                <option
                                    value="Pending"
                                    <?= (($_POST['status'] ?? '') === 'Pending')
                                        ? 'selected'
                                        : ''; ?>
                                >
                                    Pending
                                </option>

                            </select>

                        </div>



                        <!-- TEKNISI -->

                        <div class="col-12">

                            <label
                                class="form-label fw-semibold"
                            >

                                Teknisi / Petugas

                            </label>

                            <input
                                type="text"
                                name="teknisi"
                                class="form-control rounded-3"
                                value="<?= e($_POST['teknisi'] ?? ''); ?>"
                                placeholder="Contoh: Nama Teknisi / Tim Maintenance"
                            >

                        </div>



                        <!-- TINDAKAN -->

                        <div class="col-12">

                            <label
                                class="form-label fw-semibold"
                            >

                                Tindakan Perbaikan / Maintenance

                                <span class="text-danger">*</span>

                            </label>

                            <textarea
                                name="tindakan"
                                class="form-control rounded-3"
                                rows="4"
                                placeholder="Rincian pekerjaan yang dilakukan..."
                                required
                            ><?= e($_POST['tindakan'] ?? ''); ?></textarea>

                        </div>



                        <!-- SPAREPART -->

                        <div class="col-12">

                            <label
                                class="form-label fw-semibold"
                            >

                                Sparepart yang Digunakan / Diganti

                            </label>

                            <input
                                type="text"
                                name="sparepart"
                                class="form-control rounded-3"
                                value="<?= e($_POST['sparepart'] ?? ''); ?>"
                                placeholder="Contoh: Bearing 6204 (1 Pcs)"
                            >

                        </div>



                        <!-- CATATAN -->

                        <div class="col-12">

                            <label
                                class="form-label fw-semibold"
                            >

                                Catatan / Keterangan Tambahan

                            </label>

                            <textarea
                                name="catatan"
                                class="form-control rounded-3"
                                rows="3"
                                placeholder="Catatan lanjutan..."
                            ><?= e($_POST['catatan'] ?? ''); ?></textarea>

                        </div>

                    </div>

                </div>



                <!-- =================================================
                     BUTTON
                ================================================== -->

                <div
                    class="border-top pt-4 mt-4 d-flex gap-2 submit-wrapper"
                >

                    <button
                        type="submit"
                        class="btn btn-primary-garuda fw-semibold px-4 rounded-3"
                        id="btn-submit"
                    >

                        <i class="bi bi-save me-1"></i>

                        Simpan Data Maintenance

                    </button>


                    <a
                        href="index.php"
                        class="btn btn-light border px-4 rounded-3 fw-semibold"
                    >

                        Batal

                    </a>

                </div>

            </form>

        </div>

    </div>

</div>


<!-- =========================================================
     JQUERY
========================================================= -->

<script
    src="https://code.jquery.com/jquery-3.7.1.min.js"
></script>


<!-- =========================================================
     SELECT2
========================================================= -->

<script
    src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"
></script>


<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {


        /* =====================================================
           SELECT2
        ===================================================== */

        if (
            typeof jQuery !== "undefined" &&
            typeof jQuery.fn.select2 !== "undefined"
        ) {

            $('#select-komponen').select2({

                theme: 'bootstrap-5',

                placeholder:
                    '-- Ketik untuk mencari Komponen / Serial Number --',

                allowClear: true,

                width: '100%'

            });


            /* =============================================
               SAAT KOMPONEN DIPILIH
            ============================================= */

            $('#select-komponen').on(
                'change',
                function () {

                    const option =
                        $(this).find(':selected');


                    if (
                        !option ||
                        !option.val()
                    ) {

                        clearComponentFields();

                        return;
                    }


                    setField(
                        '#f-sn',
                        option.attr('data-sn')
                    );

                    setField(
                        '#f-namabagian',
                        option.attr('data-namabagian')
                    );

                    setField(
                        '#f-kategori',
                        option.attr('data-kategori')
                    );

                    setField(
                        '#f-mesin',
                        option.attr('data-mesin')
                    );

                    setField(
                        '#f-submesin',
                        option.attr('data-submesin')
                    );

                    setField(
                        '#f-lokasi',
                        option.attr('data-lokasi')
                    );

                    setField(
                        '#f-brand',
                        option.attr('data-brand')
                    );

                    setField(
                        '#f-tipe',
                        option.attr('data-tipe')
                    );

                    setField(
                        '#f-pn',
                        option.attr('data-pn')
                    );

                    setField(
                        '#f-daya',
                        option.attr('data-daya')
                    );

                    setField(
                        '#f-io',
                        option.attr('data-io')
                    );

                    setField(
                        '#f-vol',
                        option.attr('data-vol')
                    );

                    setField(
                        '#f-freqin',
                        option.attr('data-freqin')
                    );

                    setField(
                        '#f-arus',
                        option.attr('data-arus')
                    );

                    setField(
                        '#f-out',
                        option.attr('data-out')
                    );

                    setField(
                        '#f-freqout',
                        option.attr('data-freqout')
                    );

                    setField(
                        '#f-ip',
                        option.attr('data-ip')
                    );

                }
            );

        }


        /* =====================================================
           PREVIEW FOTO
        ===================================================== */

        const inputFoto =
            document.getElementById(
                'input-foto'
            );

        const imagePreview =
            document.getElementById(
                'image-preview'
            );

        const previewContainer =
            document.getElementById(
                'preview-container'
            );

        const photoEmpty =
            document.getElementById(
                'photo-empty'
            );

        const btnRemoveFoto =
            document.getElementById(
                'btn-remove-foto'
            );


        if (inputFoto) {

            inputFoto.addEventListener(
                'change',
                function () {

                    const file =
                        this.files &&
                        this.files[0];


                    if (!file) {

                        resetPhoto();

                        return;
                    }


                    /* =========================================
                       VALIDASI UKURAN
                    ========================================= */

                    if (
                        file.size >
                        5 * 1024 * 1024
                    ) {

                        alert(
                            'Ukuran foto maksimal 5 MB.'
                        );

                        this.value = '';

                        resetPhoto();

                        return;
                    }


                    /* =========================================
                       VALIDASI TYPE
                    ========================================= */

                    const allowedTypes = [

                        'image/jpeg',

                        'image/png',

                        'image/webp'

                    ];


                    if (
                        !allowedTypes.includes(
                            file.type
                        )
                    ) {

                        alert(
                            'Format foto harus JPG, PNG, atau WEBP.'
                        );

                        this.value = '';

                        resetPhoto();

                        return;
                    }


                    /* =========================================
                       PREVIEW
                    ========================================= */

                    const reader =
                        new FileReader();


                    reader.onload =
                        function (event) {

                            imagePreview.src =
                                event.target.result;


                            imagePreview.classList.remove(
                                'd-none'
                            );


                            photoEmpty.classList.add(
                                'd-none'
                            );


                            btnRemoveFoto.classList.remove(
                                'd-none'
                            );

                        };


                    reader.readAsDataURL(
                        file
                    );

                }
            );

        }


        /* =====================================================
           HAPUS PREVIEW FOTO
        ===================================================== */

        if (btnRemoveFoto) {

            btnRemoveFoto.addEventListener(
                'click',
                function () {

                    if (inputFoto) {

                        inputFoto.value = '';

                    }

                    resetPhoto();

                }
            );

        }


        /* =====================================================
           SUBMIT FORM
        ===================================================== */

        const form =
            document.getElementById(
                'maintenanceForm'
            );

        const submitButton =
            document.getElementById(
                'btn-submit'
            );


        if (form) {

            form.addEventListener(
                'submit',
                function (event) {

                    const selected =
                        document.getElementById(
                            'select-komponen'
                        );


                    if (
                        !selected ||
                        !selected.value
                    ) {

                        event.preventDefault();

                        alert(
                            'Silakan pilih komponen terlebih dahulu.'
                        );

                        return;
                    }


                    const tindakan =
                        form.querySelector(
                            '[name="tindakan"]'
                        );


                    if (
                        !tindakan ||
                        !tindakan.value.trim()
                    ) {

                        event.preventDefault();

                        alert(
                            'Tindakan maintenance wajib diisi.'
                        );

                        if (tindakan) {

                            tindakan.focus();

                        }

                        return;
                    }


                    /* =========================================
                       CEGAH DOUBLE SUBMIT
                    ========================================= */

                    if (submitButton) {

                        submitButton.disabled =
                            true;

                        submitButton.innerHTML =
                            '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan...';

                    }

                }
            );

        }


        /* =====================================================
           HELPER
        ===================================================== */

        function setField(
            selector,
            value
        ) {

            const element =
                document.querySelector(
                    selector
                );


            if (element) {

                element.value =
                    value || '';

            }

        }


        function clearComponentFields()
        {

            const fields = [

                '#f-sn',

                '#f-namabagian',

                '#f-kategori',

                '#f-mesin',

                '#f-submesin',

                '#f-lokasi',

                '#f-brand',

                '#f-tipe',

                '#f-pn',

                '#f-daya',

                '#f-io',

                '#f-vol',

                '#f-freqin',

                '#f-arus',

                '#f-out',

                '#f-freqout',

                '#f-ip'

            ];


            fields.forEach(
                function (selector) {

                    setField(
                        selector,
                        ''
                    );

                }
            );

        }


        function resetPhoto()
        {

            if (imagePreview) {

                imagePreview.src = '';

                imagePreview.classList.add(
                    'd-none'
                );

            }


            if (photoEmpty) {

                photoEmpty.classList.remove(
                    'd-none'
                );

            }


            if (btnRemoveFoto) {

                btnRemoveFoto.classList.add(
                    'd-none'
                );

            }

        }

    }
);

</script>


<?php include "../template/footer.php"; ?>
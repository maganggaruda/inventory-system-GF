<?php
include "../koneksi.php";

$error = "";

/* =========================================================
   HELPER
========================================================= */

function e($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}


/* =========================================================
   DATA DEFAULT FORM
========================================================= */

$serial_number      = trim($_POST['serial_number'] ?? '');
$nama_bagian        = trim($_POST['nama_bagian'] ?? '');
$jenis_komponen     = trim($_POST['jenis_komponen'] ?? '');
$brand              = trim($_POST['brand'] ?? '');
$tipe               = trim($_POST['tipe'] ?? '');
$part_number        = trim($_POST['part_number'] ?? '');
$daya               = trim($_POST['daya'] ?? '');
$io_address         = trim($_POST['io_address'] ?? '');
$ip_address         = trim($_POST['ip_address'] ?? '');
$input_voltage      = trim($_POST['input_voltage'] ?? '');
$frekuensi_input    = trim($_POST['frekuensi_input'] ?? '');
$arus_input         = trim($_POST['arus_input'] ?? '');
$output             = trim($_POST['output'] ?? '');
$frekuensi_output   = trim($_POST['frekuensi_output'] ?? '');
$ip_rating          = trim($_POST['ip_rating'] ?? '');
$kondisi            = trim($_POST['kondisi'] ?? 'Baik');
$keterangan         = trim($_POST['keterangan'] ?? '');

$lokasi_post = trim($_POST['lokasi'] ?? '');


/* =========================================================
   ID HIERARCHY
========================================================= */

$id_area = (
    isset($_POST['id_area']) &&
    $_POST['id_area'] !== ''
)
    ? intval($_POST['id_area'])
    : null;


$id_jenis_mesin = (
    isset($_POST['id_jenis_mesin']) &&
    $_POST['id_jenis_mesin'] !== ''
)
    ? intval($_POST['id_jenis_mesin'])
    : null;


$id_mesin = (
    isset($_POST['id_mesin']) &&
    $_POST['id_mesin'] !== ''
)
    ? intval($_POST['id_mesin'])
    : null;


$id_sub_mesin = (
    isset($_POST['id_sub_mesin']) &&
    $_POST['id_sub_mesin'] !== ''
)
    ? intval($_POST['id_sub_mesin'])
    : null;


/* =========================================================
   VARIABEL TURUNAN
========================================================= */

$lokasi_str    = "";
$mesin_str     = "";
$sub_mesin_str = "";


/* =========================================================
   PROSES SIMPAN
========================================================= */

if (isset($_POST['simpan'])) {

    /* =====================================================
       VALIDASI DASAR
    ===================================================== */

    if ($nama_bagian === '') {

        $error = "Nama Komponen wajib diisi.";

    } elseif (!$id_sub_mesin) {

        $error = "Sub Mesin wajib dipilih.";

    } elseif (
        $kondisi !== 'Baik' &&
        $kondisi !== 'Perlu Pemeriksaan' &&
        $kondisi !== 'Dalam Perbaikan'
    ) {

        $error = "Kondisi komponen tidak valid.";

    }


    /* =====================================================
       VALIDASI HIERARCHY
       SUB MESIN → MESIN → JENIS MESIN → AREA
    ===================================================== */

    if (empty($error) && $id_sub_mesin) {

        $stmt_h = mysqli_prepare(
            $conn,
            "
            SELECT
                sm.id AS id_sub_mesin,
                sm.nama_sub_mesin,

                m.id AS id_mesin,
                m.nama_mesin,
                m.id_jenis_mesin,
                m.id_area,
                m.lokasi,

                jm.id AS id_jenis_mesin_db,
                jm.nama_jenis_mesin,

                a.id AS id_area_db,
                a.nama_area,
                a.lokasi AS lokasi_area

            FROM sub_mesin sm

            INNER JOIN mesin m
                ON m.id = sm.id_mesin

            INNER JOIN jenis_mesin jm
                ON jm.id = m.id_jenis_mesin

            INNER JOIN area_bagian a
                ON a.id = jm.id_area

            WHERE sm.id = ?

            LIMIT 1
            "
        );


        if (!$stmt_h) {

            $error =
                "Gagal memvalidasi hierarchy: " .
                mysqli_error($conn);

        } else {

            mysqli_stmt_bind_param(
                $stmt_h,
                "i",
                $id_sub_mesin
            );


            mysqli_stmt_execute($stmt_h);


            $res_h =
                mysqli_stmt_get_result($stmt_h);


            $hierarchy =
                mysqli_fetch_assoc($res_h);


            mysqli_stmt_close($stmt_h);


            if (!$hierarchy) {

                $error =
                    "Data Sub Mesin tidak valid.";

            } else {

                /*
                 * Ambil data hierarchy langsung
                 * dari database.
                 */

                $db_id_mesin =
                    intval($hierarchy['id_mesin']);

                $db_id_jenis_mesin =
                    intval($hierarchy['id_jenis_mesin']);

                $db_id_area =
                    intval($hierarchy['id_area']);


                $mesin_str =
                    trim(
                        $hierarchy['nama_mesin'] ?? ''
                    );


                $sub_mesin_str =
                    trim(
                        $hierarchy['nama_sub_mesin'] ?? ''
                    );


                /*
                 * Gunakan lokasi dari area.
                 */

                $lokasi_str =
                    trim(
                        $hierarchy['lokasi_area'] ?? ''
                    );


                /*
                 * Jika lokasi area kosong,
                 * gunakan lokasi mesin.
                 */

                if ($lokasi_str === '') {

                    $lokasi_str =
                        trim(
                            $hierarchy['lokasi'] ?? ''
                        );
                }


                /*
                 * Validasi pilihan hierarchy dari form.
                 *
                 * Jika user memilih Area/Jenis Mesin/Mesin
                 * yang tidak sesuai dengan Sub Mesin,
                 * jangan simpan.
                 */

                if (
                    $id_mesin &&
                    $id_mesin !== $db_id_mesin
                ) {

                    $error =
                        "Mesin yang dipilih tidak sesuai dengan Sub Mesin.";

                } elseif (
                    $id_jenis_mesin &&
                    $id_jenis_mesin !== $db_id_jenis_mesin
                ) {

                    $error =
                        "Jenis Mesin yang dipilih tidak sesuai dengan Sub Mesin.";

                } elseif (
                    $id_area &&
                    $id_area !== $db_id_area
                ) {

                    $error =
                        "Area yang dipilih tidak sesuai dengan Sub Mesin.";
                }


                /*
                 * Jika lokasi dipilih dari form,
                 * pastikan sesuai dengan lokasi hierarchy.
                 */

                if (
                    empty($error) &&
                    $lokasi_post !== '' &&
                    $lokasi_str !== '' &&
                    strcasecmp(
                        trim($lokasi_post),
                        trim($lokasi_str)
                    ) !== 0
                ) {

                    $error =
                        "Lokasi yang dipilih tidak sesuai dengan Area.";
                }


                /*
                 * Jika lokasi database kosong,
                 * fallback dari form.
                 */

                if (
                    empty($lokasi_str) &&
                    $lokasi_post !== ''
                ) {

                    $lokasi_str =
                        $lokasi_post;
                }
            }
        }
    }


    /* =====================================================
       UPLOAD GAMBAR
    ===================================================== */

    $nama_gambar = "";


    if (
        empty($error) &&
        isset($_FILES['gambar']) &&
        $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        $upload_error =
            $_FILES['gambar']['error'];


        if (
            $upload_error !==
            UPLOAD_ERR_OK
        ) {

            switch ($upload_error) {

                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:

                    $error =
                        "Ukuran gambar terlalu besar.";

                    break;


                case UPLOAD_ERR_PARTIAL:

                    $error =
                        "Upload gambar tidak selesai.";

                    break;


                default:

                    $error =
                        "Terjadi kesalahan saat mengunggah gambar.";

                    break;
            }

        } else {

            $file_tmp =
                $_FILES['gambar']['tmp_name'];


            $file_name =
                $_FILES['gambar']['name'];


            $file_size =
                (int)$_FILES['gambar']['size'];


            $max_size =
                2 * 1024 * 1024;


            if ($file_size > $max_size) {

                $error =
                    "Ukuran gambar maksimal 2MB.";

            } elseif (
                !is_uploaded_file($file_tmp)
            ) {

                $error =
                    "File upload tidak valid.";

            } else {

                $file_ext =
                    strtolower(
                        pathinfo(
                            $file_name,
                            PATHINFO_EXTENSION
                        )
                    );


                $allowed_exts = [
                    'jpg',
                    'jpeg',
                    'png',
                    'webp'
                ];


                if (
                    !in_array(
                        $file_ext,
                        $allowed_exts,
                        true
                    )
                ) {

                    $error =
                        "Format gambar harus JPG, JPEG, PNG, atau WEBP.";

                } else {

                    /*
                     * Validasi MIME sebenarnya.
                     */

                    $allowed_mimes = [
                        'image/jpeg' => 'jpg',
                        'image/png'  => 'png',
                        'image/webp' => 'webp'
                    ];


                    $mime_type = "";


                    if (
                        function_exists(
                            'finfo_open'
                        )
                    ) {

                        $finfo =
                            finfo_open(
                                FILEINFO_MIME_TYPE
                            );


                        if ($finfo) {

                            $mime_type =
                                finfo_file(
                                    $finfo,
                                    $file_tmp
                                );


                            finfo_close(
                                $finfo
                            );
                        }
                    }


                    if (
                        !empty($mime_type) &&
                        !isset(
                            $allowed_mimes[
                                $mime_type
                            ]
                        )
                    ) {

                        $error =
                            "File yang diupload bukan gambar yang valid.";

                    } else {

                        if (
                            !empty($mime_type) &&
                            isset(
                                $allowed_mimes[
                                    $mime_type
                                ]
                            )
                        ) {

                            $file_ext =
                                $allowed_mimes[
                                    $mime_type
                                ];
                        }


                        $target_dir =
                            "../uploads/komponen/";


                        /*
                         * Buat folder jika belum ada.
                         */

                        if (
                            !is_dir(
                                $target_dir
                            )
                        ) {

                            if (
                                !mkdir(
                                    $target_dir,
                                    0755,
                                    true
                                )
                            ) {

                                $error =
                                    "Folder upload gambar tidak dapat dibuat.";
                            }
                        }


                        /*
                         * Simpan file.
                         */

                        if (empty($error)) {

                            try {

                                $random_name =
                                    bin2hex(
                                        random_bytes(4)
                                    );

                            } catch (
                                Exception $ex
                            ) {

                                $random_name =
                                    uniqid();
                            }


                            $nama_gambar =
                                "KMP_" .
                                date("YmdHis") .
                                "_" .
                                $random_name .
                                "." .
                                $file_ext;


                            $target_file =
                                $target_dir .
                                $nama_gambar;


                            if (
                                !move_uploaded_file(
                                    $file_tmp,
                                    $target_file
                                )
                            ) {

                                $error =
                                    "Gagal mengunggah gambar komponen.";

                                $nama_gambar = "";

                            } else {

                                @chmod(
                                    $target_file,
                                    0644
                                );
                            }
                        }
                    }
                }
            }
        }
    }


    /* =====================================================
       SIMPAN KE DATABASE
    ===================================================== */

    if (empty($error)) {

        /*
         * Kolom kategori dan spesifikasi
         * tetap dikosongkan karena form saat ini
         * menggunakan Jenis Komponen.
         */

        $kategori = "";

        $spesifikasi = "";


        /*
         * Struktur tabel komponen:
         *
         * id
         * serial_number
         * id_sub_mesin
         * mesin
         * sub_mesin
         * nama_bagian
         * jenis_komponen
         * spesifikasi
         * kategori
         * brand
         * tipe
         * part_number
         * daya
         * io_address
         * ip_address
         * input_voltage
         * frekuensi_input
         * arus_input
         * output
         * frekuensi_output
         * ip_rating
         * lokasi
         * kondisi
         * keterangan
         * gambar
         */


        $query = "
            INSERT INTO komponen (
                serial_number,
                id_sub_mesin,
                mesin,
                sub_mesin,
                nama_bagian,
                jenis_komponen,
                spesifikasi,
                kategori,
                brand,
                tipe,
                part_number,
                daya,
                io_address,
                ip_address,
                input_voltage,
                frekuensi_input,
                arus_input,
                output,
                frekuensi_output,
                ip_rating,
                lokasi,
                kondisi,
                keterangan,
                gambar
            )
            VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
        ";


        $stmt =
            mysqli_prepare(
                $conn,
                $query
            );


        if (!$stmt) {

            $error =
                "Gagal menyiapkan query: " .
                mysqli_error($conn);

        } else {

            /*
             * 24 parameter.
             *
             * 1  serial_number
             * 2  id_sub_mesin
             * 3  mesin
             * 4  sub_mesin
             * 5  nama_bagian
             * 6  jenis_komponen
             * 7  spesifikasi
             * 8  kategori
             * 9  brand
             * 10 tipe
             * 11 part_number
             * 12 daya
             * 13 io_address
             * 14 ip_address
             * 15 input_voltage
             * 16 frekuensi_input
             * 17 arus_input
             * 18 output
             * 19 frekuensi_output
             * 20 ip_rating
             * 21 lokasi
             * 22 kondisi
             * 23 keterangan
             * 24 gambar
             */

            mysqli_stmt_bind_param(
                $stmt,
                "sissssssssssssssssssssss",

                $serial_number,
                $id_sub_mesin,
                $mesin_str,
                $sub_mesin_str,
                $nama_bagian,
                $jenis_komponen,
                $spesifikasi,
                $kategori,
                $brand,
                $tipe,
                $part_number,
                $daya,
                $io_address,
                $ip_address,
                $input_voltage,
                $frekuensi_input,
                $arus_input,
                $output,
                $frekuensi_output,
                $ip_rating,
                $lokasi_str,
                $kondisi,
                $keterangan,
                $nama_gambar
            );


            if (
                mysqli_stmt_execute($stmt)
            ) {

                mysqli_stmt_close($stmt);


                header(
                    "Location: index.php?status=success"
                );


                exit;

            } else {

                $error =
                    "Gagal menyimpan data: " .
                    mysqli_stmt_error($stmt);


                mysqli_stmt_close($stmt);


                /*
                 * Hapus gambar jika database gagal.
                 */

                if (
                    !empty($nama_gambar)
                ) {

                    $file_delete =
                        "../uploads/komponen/" .
                        $nama_gambar;


                    if (
                        file_exists(
                            $file_delete
                        )
                    ) {

                        @unlink(
                            $file_delete
                        );
                    }
                }
            }
        }
    }
}


/* =========================================================
   AMBIL DATA LOKASI
========================================================= */

$q_lokasi = mysqli_query(
    $conn,
    "
    SELECT DISTINCT lokasi
    FROM area_bagian
    WHERE lokasi IS NOT NULL
      AND TRIM(lokasi) != ''
    ORDER BY lokasi ASC
    "
);


/* =========================================================
   HEADER
========================================================= */

include "../template/header.php";
?>


<style>

/* =========================================================
   FORM TAMBAH KOMPONEN
========================================================= */

.komponen-form-wrapper {
    width: 100%;
    max-width: 100%;
}


.komponen-page-header {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,.04);
}


.komponen-content-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,.04);
}


.komponen-card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}


.form-section-title {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: .82rem;
    font-weight: 700;
    color: #0d6efd;
    margin-bottom: 14px;
    text-transform: uppercase;
    letter-spacing: .2px;
}


.komponen-form-wrapper .form-label {
    font-size: .78rem;
    margin-bottom: 5px;
}


.komponen-form-wrapper .form-control,
.komponen-form-wrapper .form-select {
    min-height: 36px;
    font-size: .82rem;
    border-radius: 7px;
}


.komponen-form-wrapper textarea.form-control {
    min-height: 70px;
    resize: vertical;
}


.komponen-form-wrapper .form-control:focus,
.komponen-form-wrapper .form-select:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 .18rem rgba(13,110,253,.10);
}


.hierarchy-info {
    background: rgba(13,110,253,.05);
    border: 1px solid rgba(13,110,253,.10);
    border-radius: 8px;
    padding: 9px 12px;
    font-size: .72rem;
    color: #6c757d;
}


.preview-container-komponen {
    width: 100%;
    height: 180px;
    border: 1px dashed #ced4da;
    border-radius: 10px;
    background: #f8f9fa;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}


.preview-container-komponen img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    display: none;
}


.preview-placeholder-komponen {
    text-align: center;
    color: #adb5bd;
    padding: 15px;
}


.preview-placeholder-komponen i {
    font-size: 2.2rem;
    display: block;
    margin-bottom: 5px;
}


.upload-box-komponen {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 15px;
    height: 100%;
}


.upload-icon-komponen {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: rgba(13,110,253,.10);
    color: #0d6efd;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}


.form-action-komponen {
    border-top: 1px solid #e9ecef;
    margin-top: 20px;
    padding-top: 15px;
}


.form-action-komponen .btn {
    min-height: 36px;
    border-radius: 7px;
}


@media (max-width: 767.98px) {

    .komponen-page-header {
        border-radius: 9px;
        padding: 14px !important;
    }


    .komponen-page-header h3 {
        font-size: 1rem !important;
    }


    .komponen-page-header p {
        font-size: .72rem !important;
    }


    .komponen-page-header .back-button {
        width: 34px !important;
        height: 34px !important;
    }


    .komponen-content-card {
        border-radius: 9px;
    }


    .komponen-card-header {
        padding: 11px 13px !important;
    }


    .komponen-card-body {
        padding: 13px !important;
    }


    .form-section-title {
        font-size: .76rem;
        margin-bottom: 12px;
    }


    .preview-container-komponen {
        height: 190px;
    }


    .upload-box-komponen {
        padding: 12px;
    }


    .form-action-komponen {
        flex-direction: column;
        align-items: stretch !important;
    }


    .form-action-komponen .btn {
        width: 100%;
    }
}


@media (max-width: 575.98px) {

    .komponen-form-wrapper {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }


    .komponen-page-header {
        margin-left: 0;
        margin-right: 0;
    }


    .komponen-content-card {
        margin-left: 0;
        margin-right: 0;
    }


    .komponen-form-wrapper .row {
        --bs-gutter-x: .75rem;
        --bs-gutter-y: .75rem;
    }


    .preview-container-komponen {
        height: 160px;
    }


    .upload-box-komponen .d-flex {
        align-items: flex-start !important;
    }
}

</style>


<div class="container-fluid p-0 komponen-form-wrapper">


    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div class="komponen-page-header mb-3 py-3 px-4">

        <div class="d-flex align-items-center gap-3">

            <a
                href="index.php"
                class="btn btn-outline-secondary btn-sm rounded-circle d-inline-flex align-items-center justify-content-center back-button"
                style="
                    width:38px;
                    height:38px;
                    flex-shrink:0;
                "
                title="Kembali"
            >

                <i class="bi bi-arrow-left fs-5"></i>

            </a>


            <div class="min-width-0">

                <h3 class="dashboard-title m-0 fs-4 fw-bold text-dark">
                    Tambah Komponen Baru
                </h3>


                <p class="dashboard-subtitle m-0 small text-muted">
                    Lengkapi rincian spesifikasi dan data komponen
                </p>

            </div>

        </div>

    </div>



    <!-- =====================================================
         FORM CARD
    ====================================================== -->

    <div class="komponen-content-card mb-3">


        <!-- HEADER -->

        <div class="komponen-card-header py-2 px-3">

            <h6 class="m-0 fw-bold text-dark">

                <i class="bi bi-plus-circle me-2 text-primary"></i>

                Form Input Komponen

            </h6>

        </div>



        <!-- BODY -->

        <div class="komponen-card-body p-3 p-md-4">


            <!-- ERROR -->

            <?php if (!empty($error)) : ?>

                <div
                    class="alert alert-danger border-0 d-flex align-items-start py-2 px-3 mb-3"
                    role="alert"
                >

                    <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>

                    <div class="small">
                        <?= e($error); ?>
                    </div>

                </div>

            <?php endif; ?>



            <!-- FORM -->

            <form
                method="POST"
                enctype="multipart/form-data"
                id="formKomponen"
                autocomplete="off"
            >


                <!-- =================================================
                     SECTION 1
                ================================================== -->

                <div class="form-section-title">

                    <i class="bi bi-info-circle"></i>

                    Informasi Umum

                </div>


                <div class="row g-3 mb-3">


                    <!-- SERIAL NUMBER -->

                    <div class="col-12 col-md-3">

                        <label class="form-label fw-semibold text-dark">
                            Serial Number (SN)
                        </label>

                        <input
                            type="text"
                            name="serial_number"
                            class="form-control form-control-sm"
                            placeholder="Contoh: SN-8829102"
                            value="<?= e($serial_number); ?>"
                        >

                    </div>



                    <!-- NAMA KOMPONEN -->

                    <div class="col-12 col-md-5">

                        <label class="form-label fw-semibold text-dark">

                            Nama Komponen

                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="text"
                            name="nama_bagian"
                            class="form-control form-control-sm"
                            placeholder="Contoh: Inverter / Motor Conveyor"
                            value="<?= e($nama_bagian); ?>"
                            maxlength="100"
                            required
                        >

                    </div>



                    <!-- JENIS KOMPONEN -->

                    <div class="col-12 col-md-4">

                        <label class="form-label fw-semibold text-dark">
                            Jenis Komponen
                        </label>

                        <input
                            type="text"
                            name="jenis_komponen"
                            class="form-control form-control-sm"
                            placeholder="Contoh: Drive / Motor / Sensor"
                            value="<?= e($jenis_komponen); ?>"
                            maxlength="100"
                        >

                    </div>



                    <!-- LOKASI -->

                    <div class="col-12 col-md-4">

                        <label class="form-label fw-semibold text-dark">
                            Lokasi
                        </label>

                        <select
                            name="lokasi"
                            id="form_lokasi"
                            class="form-select form-select-sm"
                            onchange="loadAreaByLokasi(this.value)"
                        >

                            <option value="">
                                -- Pilih Lokasi --
                            </option>


                            <?php if ($q_lokasi) : ?>

                                <?php while (
                                    $l =
                                    mysqli_fetch_assoc(
                                        $q_lokasi
                                    )
                                ) : ?>

                                    <?php
                                    $lokasi_option =
                                        trim(
                                            $l['lokasi'] ?? ''
                                        );
                                    ?>

                                    <option
                                        value="<?= e($lokasi_option); ?>"
                                        <?= (
                                            $lokasi_post ===
                                            $lokasi_option
                                        )
                                            ? 'selected'
                                            : ''
                                        ?>
                                    >

                                        <?= e($lokasi_option); ?>

                                    </option>

                                <?php endwhile; ?>

                            <?php endif; ?>

                        </select>

                    </div>



                    <!-- AREA -->

                    <div class="col-12 col-md-3">

                        <label class="form-label fw-semibold text-dark">
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

                    <div class="col-12 col-md-3">

                        <label class="form-label fw-semibold text-dark">
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

                    <div class="col-12 col-md-3">

                        <label class="form-label fw-semibold text-dark">
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

                    <div class="col-12 col-md-3">

                        <label class="form-label fw-semibold text-dark">

                            Sub Mesin

                            <span class="text-danger">*</span>

                        </label>

                        <select
                            name="id_sub_mesin"
                            id="form_sub_mesin"
                            class="form-select form-select-sm"
                            required
                        >

                            <option value="">
                                -- Pilih Sub Mesin --
                            </option>

                        </select>

                    </div>



                    <!-- INFO HIERARCHY -->

                    <div class="col-12">

                        <div class="hierarchy-info">

                            <i class="bi bi-diagram-3 me-1 text-primary"></i>

                            Pilih data secara berurutan:

                            <strong>Lokasi</strong>
                            →
                            <strong>Area</strong>
                            →
                            <strong>Jenis Mesin</strong>
                            →
                            <strong>Mesin</strong>
                            →
                            <strong>Sub Mesin</strong>

                        </div>

                    </div>



                    <!-- =================================================
                         FOTO KOMPONEN
                    ================================================== -->

                    <div class="col-12">

                        <label class="form-label fw-semibold text-dark">
                            Foto Komponen / Part
                        </label>


                        <div class="row g-3 align-items-stretch">


                            <!-- PREVIEW -->

                            <div class="col-12 col-md-4">

                                <div class="preview-container-komponen">

                                    <div
                                        id="preview-placeholder"
                                        class="preview-placeholder-komponen"
                                    >

                                        <i class="bi bi-image"></i>

                                        <span class="small">
                                            Preview Foto
                                        </span>

                                    </div>


                                    <img
                                        id="preview-gambar"
                                        src=""
                                        alt="Preview Gambar Komponen"
                                    >

                                </div>

                            </div>



                            <!-- UPLOAD -->

                            <div class="col-12 col-md-8">

                                <div class="upload-box-komponen">

                                    <div class="d-flex align-items-center gap-3 mb-3">

                                        <div class="upload-icon-komponen">

                                            <i class="bi bi-cloud-arrow-up fs-5"></i>

                                        </div>


                                        <div>

                                            <div class="fw-semibold small text-dark">
                                                Upload Foto Komponen
                                            </div>


                                            <div class="text-muted small">
                                                Pilih foto komponen atau part
                                            </div>

                                        </div>

                                    </div>


                                    <input
                                        type="file"
                                        name="gambar"
                                        id="input-gambar"
                                        class="form-control form-control-sm"
                                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                    >


                                    <div class="form-text text-muted small mt-2">

                                        <i class="bi bi-info-circle me-1"></i>

                                        Format JPG, JPEG, PNG, WEBP

                                        <span class="mx-1">•</span>

                                        Maksimal 2MB

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>



                <hr class="my-4 text-muted opacity-25">



                <!-- =================================================
                     SECTION 2
                ================================================== -->

                <div class="form-section-title">

                    <i class="bi bi-tools"></i>

                    Spesifikasi & Brand

                </div>


                <div class="row g-3 mb-3">


                    <!-- BRAND -->

                    <div class="col-12 col-md-4">

                        <label class="form-label fw-semibold text-dark">
                            Brand / Merk
                        </label>

                        <input
                            type="text"
                            name="brand"
                            class="form-control form-control-sm"
                            placeholder="Contoh: Schneider / Siemens"
                            value="<?= e($brand); ?>"
                            maxlength="50"
                        >

                    </div>



                    <!-- TIPE -->

                    <div class="col-12 col-md-4">

                        <label class="form-label fw-semibold text-dark">
                            Tipe
                        </label>

                        <input
                            type="text"
                            name="tipe"
                            class="form-control form-control-sm"
                            placeholder="Contoh: ATV320 / CPU 314C-2"
                            value="<?= e($tipe); ?>"
                            maxlength="50"
                        >

                    </div>



                    <!-- PART NUMBER -->

                    <div class="col-12 col-md-4">

                        <label class="form-label fw-semibold text-dark">
                            Part Number
                        </label>

                        <input
                            type="text"
                            name="part_number"
                            class="form-control form-control-sm"
                            placeholder="Contoh: PN-99201"
                            value="<?= e($part_number); ?>"
                            maxlength="100"
                        >

                    </div>



                    <!-- DAYA -->

                    <div class="col-12 col-sm-6 col-md-3">

                        <label class="form-label fw-semibold text-dark">
                            Daya
                        </label>

                        <input
                            type="text"
                            name="daya"
                            class="form-control form-control-sm"
                            placeholder="Contoh: 1.5 kW"
                            value="<?= e($daya); ?>"
                            maxlength="20"
                        >

                    </div>



                    <!-- IO ADDRESS -->

                    <div class="col-12 col-sm-6 col-md-3">

                        <label class="form-label fw-semibold text-dark">
                            IO Address
                        </label>

                        <input
                            type="text"
                            name="io_address"
                            class="form-control form-control-sm"
                            placeholder="Contoh: I:0/1"
                            value="<?= e($io_address); ?>"
                            maxlength="30"
                        >

                    </div>



                    <!-- IP ADDRESS -->

                    <div class="col-12 col-sm-6 col-md-3">

                        <label class="form-label fw-semibold text-dark">
                            IP Address
                        </label>

                        <input
                            type="text"
                            name="ip_address"
                            class="form-control form-control-sm"
                            placeholder="Contoh: 192.168.1.10"
                            value="<?= e($ip_address); ?>"
                            maxlength="100"
                        >

                    </div>



                    <!-- IP RATING -->

                    <div class="col-12 col-sm-6 col-md-3">

                        <label class="form-label fw-semibold text-dark">
                            IP Rating
                        </label>

                        <input
                            type="text"
                            name="ip_rating"
                            class="form-control form-control-sm"
                            placeholder="Contoh: IP65"
                            value="<?= e($ip_rating); ?>"
                            maxlength="20"
                        >

                    </div>



                    <!-- INPUT VOLTAGE -->

                    <div class="col-12 col-sm-6 col-md-3">

                        <label class="form-label fw-semibold text-dark">
                            Input Voltage
                        </label>

                        <input
                            type="text"
                            name="input_voltage"
                            class="form-control form-control-sm"
                            placeholder="Contoh: 380V AC"
                            value="<?= e($input_voltage); ?>"
                            maxlength="20"
                        >

                    </div>



                    <!-- FREKUENSI INPUT -->

                    <div class="col-12 col-sm-6 col-md-3">

                        <label class="form-label fw-semibold text-dark">
                            Frekuensi Input
                        </label>

                        <input
                            type="text"
                            name="frekuensi_input"
                            class="form-control form-control-sm"
                            placeholder="Contoh: 50/60 Hz"
                            value="<?= e($frekuensi_input); ?>"
                            maxlength="20"
                        >

                    </div>



                    <!-- ARUS INPUT -->

                    <div class="col-12 col-sm-6 col-md-3">

                        <label class="form-label fw-semibold text-dark">
                            Arus Input
                        </label>

                        <input
                            type="text"
                            name="arus_input"
                            class="form-control form-control-sm"
                            placeholder="Contoh: 5A"
                            value="<?= e($arus_input); ?>"
                            maxlength="20"
                        >

                    </div>



                    <!-- OUTPUT -->

                    <div class="col-12 col-sm-6 col-md-3">

                        <label class="form-label fw-semibold text-dark">
                            Output
                        </label>

                        <input
                            type="text"
                            name="output"
                            class="form-control form-control-sm"
                            placeholder="Contoh: 0-220V"
                            value="<?= e($output); ?>"
                            maxlength="50"
                        >

                    </div>



                    <!-- FREKUENSI OUTPUT -->

                    <div class="col-12 col-sm-6 col-md-3">

                        <label class="form-label fw-semibold text-dark">
                            Frekuensi Output
                        </label>

                        <input
                            type="text"
                            name="frekuensi_output"
                            class="form-control form-control-sm"
                            placeholder="Contoh: 0-400 Hz"
                            value="<?= e($frekuensi_output); ?>"
                            maxlength="20"
                        >

                    </div>

                </div>



                <hr class="my-4 text-muted opacity-25">



                <!-- =================================================
                     SECTION 3
                ================================================== -->

                <div class="form-section-title">

                    <i class="bi bi-card-checklist"></i>

                    Status Komponen

                </div>


                <div class="row g-3">


                    <!-- KONDISI -->

                    <div class="col-12 col-md-4">

                        <label class="form-label fw-semibold text-dark">

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
                                <?= (
                                    $kondisi === 'Baik'
                                )
                                    ? 'selected'
                                    : ''
                                ?>
                            >
                                Baik
                            </option>


                            <option
                                value="Perlu Pemeriksaan"
                                <?= (
                                    $kondisi === 'Perlu Pemeriksaan'
                                )
                                    ? 'selected'
                                    : ''
                                ?>
                            >
                                Perlu Pemeriksaan
                            </option>


                            <option
                                value="Dalam Perbaikan"
                                <?= (
                                    $kondisi === 'Dalam Perbaikan'
                                )
                                    ? 'selected'
                                    : ''
                                ?>
                            >
                                Dalam Perbaikan
                            </option>

                        </select>

                    </div>



                    <!-- KETERANGAN -->

                    <div class="col-12 col-md-8">

                        <label class="form-label fw-semibold text-dark">
                            Keterangan Tambahan
                        </label>

                        <textarea
                            name="keterangan"
                            class="form-control form-control-sm"
                            rows="2"
                            placeholder="Catatan kondisi atau deskripsi tambahan..."
                        ><?= e($keterangan); ?></textarea>

                    </div>

                </div>



                <!-- =================================================
                     BUTTON
                ================================================== -->

                <div class="form-action-komponen d-flex align-items-center gap-2">

                    <button
                        type="submit"
                        name="simpan"
                        id="btnSimpan"
                        class="btn btn-primary px-4 btn-sm fw-semibold"
                    >

                        <i class="bi bi-check-lg me-1"></i>

                        Simpan Data

                    </button>


                    <a
                        href="index.php"
                        class="btn btn-light border px-4 btn-sm fw-semibold text-secondary"
                    >

                        <i class="bi bi-x-lg me-1"></i>

                        Batal

                    </a>

                </div>


            </form>

        </div>

    </div>

</div>



<script>

/* =========================================================
   DATA RESTORE
========================================================= */

const selectedArea =
    <?= json_encode(
        $id_area,
        JSON_UNESCAPED_UNICODE
    ); ?>;


const selectedJenisMesin =
    <?= json_encode(
        $id_jenis_mesin,
        JSON_UNESCAPED_UNICODE
    ); ?>;


const selectedMesin =
    <?= json_encode(
        $id_mesin,
        JSON_UNESCAPED_UNICODE
    ); ?>;


const selectedSubMesin =
    <?= json_encode(
        $id_sub_mesin,
        JSON_UNESCAPED_UNICODE
    ); ?>;


/* =========================================================
   HELPER ELEMENT
========================================================= */

function getElement(id)
{
    return document.getElementById(id);
}


/* =========================================================
   SET SELECT
========================================================= */

function setLoading(select, text)
{
    if (!select) {
        return;
    }


    select.innerHTML =
        '<option value="">' +
        text +
        '</option>';


    select.disabled = true;
}


function setDefault(select, text)
{
    if (!select) {
        return;
    }


    select.innerHTML =
        '<option value="">' +
        text +
        '</option>';


    select.disabled = false;
}


/* =========================================================
   PREVIEW GAMBAR
========================================================= */

function resetPreview()
{
    const input =
        getElement('input-gambar');


    const preview =
        getElement('preview-gambar');


    const placeholder =
        getElement('preview-placeholder');


    if (input) {
        input.value = "";
    }


    if (preview) {

        preview.src = "";

        preview.style.display =
            "none";
    }


    if (placeholder) {

        placeholder.style.display =
            "block";
    }
}


function previewGambar(input)
{
    const preview =
        getElement('preview-gambar');


    const placeholder =
        getElement('preview-placeholder');


    if (
        !input ||
        !input.files ||
        !input.files[0]
    ) {

        resetPreview();

        return;
    }


    const file =
        input.files[0];


    if (
        file.size >
        2 * 1024 * 1024
    ) {

        alert(
            "Ukuran gambar maksimal 2MB."
        );


        resetPreview();

        return;
    }


    const allowedTypes = [
        "image/jpeg",
        "image/png",
        "image/webp"
    ];


    if (
        !allowedTypes.includes(
            file.type
        )
    ) {

        alert(
            "Format gambar harus JPG, JPEG, PNG, atau WEBP."
        );


        resetPreview();

        return;
    }


    const reader =
        new FileReader();


    reader.onload =
        function(event)
        {
            if (preview) {

                preview.src =
                    event.target.result;

                preview.style.display =
                    "block";
            }


            if (placeholder) {

                placeholder.style.display =
                    "none";
            }
        };


    reader.onerror =
        function()
        {
            alert(
                "Gagal membaca file gambar."
            );


            resetPreview();
        };


    reader.readAsDataURL(file);
}


/* =========================================================
   EVENT UPLOAD
========================================================= */

const inputGambar =
    getElement('input-gambar');


if (inputGambar) {

    inputGambar.addEventListener(
        'change',
        function()
        {
            previewGambar(this);
        }
    );
}


/* =========================================================
   LOAD AREA BERDASARKAN LOKASI
========================================================= */

function loadAreaByLokasi(
    lokasi,
    restore = false
)
{
    const areaSelect =
        getElement('form_area');


    const jenisSelect =
        getElement('form_jenis_mesin');


    const mesinSelect =
        getElement('form_mesin');


    const subSelect =
        getElement('form_sub_mesin');


    setDefault(
        areaSelect,
        '-- Pilih Area --'
    );


    setDefault(
        jenisSelect,
        '-- Pilih Jenis Mesin --'
    );


    setDefault(
        mesinSelect,
        '-- Pilih Mesin --'
    );


    setDefault(
        subSelect,
        '-- Pilih Sub Mesin --'
    );


    if (!lokasi) {
        return;
    }


    setLoading(
        areaSelect,
        'Memuat Area...'
    );


    fetch(
        'get_area.php?lokasi=' +
        encodeURIComponent(lokasi),
        {
            method: 'GET',
            cache: 'no-store'
        }
    )

    .then(
        response =>
        {
            if (!response.ok) {

                throw new Error(
                    'HTTP ' +
                    response.status
                );
            }


            return response.text();
        }
    )

    .then(
        data =>
        {
            areaSelect.innerHTML =
                data;


            areaSelect.disabled =
                false;


            if (
                restore &&
                selectedArea
            ) {

                areaSelect.value =
                    String(
                        selectedArea
                    );


                if (
                    areaSelect.value ===
                    String(selectedArea)
                ) {

                    loadJenisMesin(
                        selectedArea,
                        true
                    );
                }
            }
        }
    )

    .catch(
        error =>
        {
            console.error(
                'Gagal memuat Area:',
                error
            );


            setDefault(
                areaSelect,
                '-- Gagal memuat Area --'
            );


            alert(
                'Gagal memuat data Area.'
            );
        }
    );
}


/* =========================================================
   LOAD JENIS MESIN
========================================================= */

function loadJenisMesin(
    id_area,
    restore = false
)
{
    const jenisSelect =
        getElement('form_jenis_mesin');


    const mesinSelect =
        getElement('form_mesin');


    const subSelect =
        getElement('form_sub_mesin');


    setDefault(
        jenisSelect,
        '-- Pilih Jenis Mesin --'
    );


    setDefault(
        mesinSelect,
        '-- Pilih Mesin --'
    );


    setDefault(
        subSelect,
        '-- Pilih Sub Mesin --'
    );


    if (!id_area) {
        return;
    }


    setLoading(
        jenisSelect,
        'Memuat Jenis Mesin...'
    );


    fetch(
        'get_jenis_mesin.php?id_area=' +
        encodeURIComponent(id_area),
        {
            method: 'GET',
            cache: 'no-store'
        }
    )

    .then(
        response =>
        {
            if (!response.ok) {

                throw new Error(
                    'HTTP ' +
                    response.status
                );
            }


            return response.text();
        }
    )

    .then(
        data =>
        {
            jenisSelect.innerHTML =
                data;


            jenisSelect.disabled =
                false;


            if (
                restore &&
                selectedJenisMesin
            ) {

                jenisSelect.value =
                    String(
                        selectedJenisMesin
                    );


                if (
                    jenisSelect.value ===
                    String(selectedJenisMesin)
                ) {

                    loadMesin(
                        selectedJenisMesin,
                        true
                    );
                }
            }
        }
    )

    .catch(
        error =>
        {
            console.error(
                'Gagal memuat Jenis Mesin:',
                error
            );


            setDefault(
                jenisSelect,
                '-- Gagal memuat Jenis Mesin --'
            );


            alert(
                'Gagal memuat data Jenis Mesin.'
            );
        }
    );
}


/* =========================================================
   LOAD MESIN
========================================================= */

function loadMesin(
    id_jenis,
    restore = false
)
{
    const mesinSelect =
        getElement('form_mesin');


    const subSelect =
        getElement('form_sub_mesin');


    setDefault(
        mesinSelect,
        '-- Pilih Mesin --'
    );


    setDefault(
        subSelect,
        '-- Pilih Sub Mesin --'
    );


    if (!id_jenis) {
        return;
    }


    setLoading(
        mesinSelect,
        'Memuat Mesin...'
    );


    fetch(
        'get_mesin.php?id_jenis=' +
        encodeURIComponent(id_jenis),
        {
            method: 'GET',
            cache: 'no-store'
        }
    )

    .then(
        response =>
        {
            if (!response.ok) {

                throw new Error(
                    'HTTP ' +
                    response.status
                );
            }


            return response.text();
        }
    )

    .then(
        data =>
        {
            mesinSelect.innerHTML =
                data;


            mesinSelect.disabled =
                false;


            if (
                restore &&
                selectedMesin
            ) {

                mesinSelect.value =
                    String(
                        selectedMesin
                    );


                if (
                    mesinSelect.value ===
                    String(selectedMesin)
                ) {

                    loadSubMesinForm(
                        selectedMesin,
                        true
                    );
                }
            }
        }
    )

    .catch(
        error =>
        {
            console.error(
                'Gagal memuat Mesin:',
                error
            );


            setDefault(
                mesinSelect,
                '-- Gagal memuat Mesin --'
            );


            alert(
                'Gagal memuat data Mesin.'
            );
        }
    );
}


/* =========================================================
   LOAD SUB MESIN
========================================================= */

function loadSubMesinForm(
    id_mesin,
    restore = false
)
{
    const subSelect =
        getElement('form_sub_mesin');


    setDefault(
        subSelect,
        '-- Pilih Sub Mesin --'
    );


    if (!id_mesin) {
        return;
    }


    setLoading(
        subSelect,
        'Memuat Sub Mesin...'
    );


    fetch(
        'get_sub_mesin.php?id_mesin=' +
        encodeURIComponent(id_mesin),
        {
            method: 'GET',
            cache: 'no-store'
        }
    )

    .then(
        response =>
        {
            if (!response.ok) {

                throw new Error(
                    'HTTP ' +
                    response.status
                );
            }


            return response.text();
        }
    )

    .then(
        data =>
        {
            subSelect.innerHTML =
                data;


            subSelect.disabled =
                false;


            if (
                restore &&
                selectedSubMesin
            ) {

                subSelect.value =
                    String(
                        selectedSubMesin
                    );
            }
        }
    )

    .catch(
        error =>
        {
            console.error(
                'Gagal memuat Sub Mesin:',
                error
            );


            setDefault(
                subSelect,
                '-- Gagal memuat Sub Mesin --'
            );


            alert(
                'Gagal memuat data Sub Mesin.'
            );
        }
    );
}


/* =========================================================
   RESTORE DROPDOWN SETELAH ERROR
========================================================= */

document.addEventListener(
    'DOMContentLoaded',
    function()
    {
        const lokasiSelect =
            getElement('form_lokasi');


        if (
            lokasiSelect &&
            lokasiSelect.value
        ) {

            <?php if (
                $_SERVER['REQUEST_METHOD'] === 'POST'
            ) : ?>

            loadAreaByLokasi(
                lokasiSelect.value,
                true
            );

            <?php endif; ?>
        }
    }
);


/* =========================================================
   SUBMIT FORM
========================================================= */

const formKomponen =
    getElement('formKomponen');


const btnSimpan =
    getElement('btnSimpan');


if (formKomponen) {

    formKomponen.addEventListener(
        'submit',
        function(event)
        {
            const namaKomponen =
                formKomponen.querySelector(
                    '[name="nama_bagian"]'
                );


            const subMesin =
                getElement(
                    'form_sub_mesin'
                );


            /* ---------------------------------------------
               VALIDASI NAMA KOMPONEN
            --------------------------------------------- */

            if (
                namaKomponen &&
                namaKomponen.value.trim() === ''
            ) {

                event.preventDefault();

                namaKomponen.focus();

                alert(
                    'Nama Komponen wajib diisi.'
                );

                return;
            }


            /* ---------------------------------------------
               VALIDASI SUB MESIN
            --------------------------------------------- */

            if (
                subMesin &&
                !subMesin.value
            ) {

                event.preventDefault();

                subMesin.focus();

                alert(
                    'Sub Mesin wajib dipilih.'
                );

                return;
            }


            /* ---------------------------------------------
               VALIDASI GAMBAR
            --------------------------------------------- */

            const fileInput =
                getElement('input-gambar');


            if (
                fileInput &&
                fileInput.files &&
                fileInput.files[0]
            ) {

                const file =
                    fileInput.files[0];


                if (
                    file.size >
                    2 * 1024 * 1024
                ) {

                    event.preventDefault();

                    alert(
                        'Ukuran gambar maksimal 2MB.'
                    );

                    return;
                }


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

                    event.preventDefault();

                    alert(
                        'Format gambar harus JPG, JPEG, PNG, atau WEBP.'
                    );

                    return;
                }
            }


            /* ---------------------------------------------
               BUTTON LOADING
            --------------------------------------------- */

            if (btnSimpan) {

                btnSimpan.disabled =
                    true;


                btnSimpan.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-1"></span>' +
                    ' Menyimpan...';
            }
        }
    );
}

</script>


<?php
include "../template/footer.php";
?>
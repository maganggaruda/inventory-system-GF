<?php
include "../koneksi.php";

$error = "";

/* =========================================================
   NILAI AWAL FORM
========================================================= */

$val_id_mesin       = intval($_POST['id_mesin'] ?? 0);
$val_nama_sub_mesin = trim($_POST['nama_sub_mesin'] ?? '');
$val_serial_number  = trim($_POST['serial_number'] ?? '');
$val_keterangan     = trim($_POST['keterangan'] ?? '');


/* =========================================================
   PROSES SIMPAN
========================================================= */

if (isset($_POST['simpan'])) {

    $val_id_mesin       = intval($_POST['id_mesin'] ?? 0);
    $val_nama_sub_mesin = trim($_POST['nama_sub_mesin'] ?? '');
    $val_serial_number  = trim($_POST['serial_number'] ?? '');
    $val_keterangan     = trim($_POST['keterangan'] ?? '');

    $nama_gambar = null;
    $uploaded_file_path = null;


    /* =====================================================
       VALIDASI FORM
    ===================================================== */

    if ($val_id_mesin <= 0) {

        $error = "Mesin Induk wajib dipilih.";

    } elseif ($val_nama_sub_mesin === '') {

        $error = "Nama Sub Mesin wajib diisi.";

    }


    /* =====================================================
       CEK MESIN INDUK
    ===================================================== */

    if (empty($error)) {

        $stmt_check_mesin = mysqli_prepare(
            $conn,
            "SELECT id FROM mesin WHERE id = ? LIMIT 1"
        );

        if ($stmt_check_mesin) {

            mysqli_stmt_bind_param(
                $stmt_check_mesin,
                "i",
                $val_id_mesin
            );

            mysqli_stmt_execute(
                $stmt_check_mesin
            );

            $result_check_mesin =
                mysqli_stmt_get_result(
                    $stmt_check_mesin
                );

            $mesin_exists =
                mysqli_num_rows(
                    $result_check_mesin
                ) > 0;

            mysqli_stmt_close(
                $stmt_check_mesin
            );

            if (!$mesin_exists) {

                $error =
                    "Mesin Induk yang dipilih tidak ditemukan.";
            }

        } else {

            $error =
                "Gagal memeriksa data Mesin Induk.";
        }
    }


    /* =====================================================
       VALIDASI SERIAL NUMBER
    ===================================================== */

    if (empty($error) && $val_serial_number !== '') {

        $stmt_check_sn = mysqli_prepare(
            $conn,
            "
            SELECT id
            FROM sub_mesin
            WHERE serial_number = ?
            LIMIT 1
            "
        );

        if ($stmt_check_sn) {

            mysqli_stmt_bind_param(
                $stmt_check_sn,
                "s",
                $val_serial_number
            );

            mysqli_stmt_execute(
                $stmt_check_sn
            );

            $result_check_sn =
                mysqli_stmt_get_result(
                    $stmt_check_sn
                );

            $sn_exists =
                mysqli_num_rows(
                    $result_check_sn
                ) > 0;

            mysqli_stmt_close(
                $stmt_check_sn
            );

            if ($sn_exists) {

                $error =
                    "Serial Number Sub Mesin sudah digunakan.";
            }
        }
    }


    /* =====================================================
       UPLOAD FOTO
    ===================================================== */

    if (
        empty($error) &&
        isset($_FILES['gambar'])
    ) {

        $upload_error =
            $_FILES['gambar']['error'] ?? UPLOAD_ERR_NO_FILE;


        /* ---------------------------------------------
           ADA FILE
        --------------------------------------------- */

        if ($upload_error !== UPLOAD_ERR_NO_FILE) {

            if ($upload_error !== UPLOAD_ERR_OK) {

                $error =
                    "Terjadi kesalahan saat mengunggah foto.";

            } else {

                $fileTmpPath =
                    $_FILES['gambar']['tmp_name'];

                $fileName =
                    $_FILES['gambar']['name'];

                $fileSize =
                    intval($_FILES['gambar']['size']);


                /* -----------------------------------------
                   CEK UKURAN
                ----------------------------------------- */

                if ($fileSize > 2 * 1024 * 1024) {

                    $error =
                        "Ukuran foto terlalu besar! Maksimal 2MB.";

                } else {


                    /* -------------------------------------
                       CEK EXTENSION
                    ------------------------------------- */

                    $fileExtension =
                        strtolower(
                            pathinfo(
                                $fileName,
                                PATHINFO_EXTENSION
                            )
                        );

                    $allowedExtensions = [
                        'jpg',
                        'jpeg',
                        'png',
                        'webp'
                    ];


                    if (
                        !in_array(
                            $fileExtension,
                            $allowedExtensions,
                            true
                        )
                    ) {

                        $error =
                            "Format foto tidak valid! "
                            . "Hanya JPG, JPEG, PNG, dan WEBP.";

                    } else {


                        /* ---------------------------------
                           CEK MIME TYPE
                        --------------------------------- */

                        $allowedMimeTypes = [
                            'image/jpeg',
                            'image/png',
                            'image/webp'
                        ];

                        $mimeType = '';

                        if (
                            function_exists('finfo_open')
                        ) {

                            $finfo =
                                finfo_open(
                                    FILEINFO_MIME_TYPE
                                );

                            if ($finfo) {

                                $mimeType =
                                    finfo_file(
                                        $finfo,
                                        $fileTmpPath
                                    );

                                finfo_close(
                                    $finfo
                                );
                            }
                        }


                        if (
                            !empty($mimeType) &&
                            !in_array(
                                $mimeType,
                                $allowedMimeTypes,
                                true
                            )
                        ) {

                            $error =
                                "File yang diunggah bukan merupakan "
                                . "gambar yang valid.";

                        } else {


                            /* -----------------------------
                               BUAT FOLDER
                            ----------------------------- */

                            $targetDir =
                                "../uploads/sub_mesin/";

                            if (
                                !is_dir($targetDir)
                            ) {

                                if (
                                    !mkdir(
                                        $targetDir,
                                        0777,
                                        true
                                    )
                                ) {

                                    $error =
                                        "Folder upload foto tidak dapat dibuat.";
                                }
                            }


                            /* -----------------------------
                               NAMA FILE
                            ----------------------------- */

                            if (empty($error)) {

                                $nama_gambar =
                                    "submesin_"
                                    . date('YmdHis')
                                    . "_"
                                    . bin2hex(
                                        random_bytes(5)
                                    )
                                    . "."
                                    . $fileExtension;


                                $targetPath =
                                    $targetDir
                                    . $nama_gambar;


                                /* -------------------------
                                   PINDAHKAN FILE
                                ------------------------- */

                                if (
                                    move_uploaded_file(
                                        $fileTmpPath,
                                        $targetPath
                                    )
                                ) {

                                    $uploaded_file_path =
                                        $targetPath;

                                } else {

                                    $error =
                                        "Gagal mengunggah foto "
                                        . "sub mesin ke server.";

                                    $nama_gambar = null;
                                }
                            }
                        }
                    }
                }
            }
        }
    }


    /* =====================================================
       INSERT DATABASE
    ===================================================== */

    if (empty($error)) {

        $stmt_insert = mysqli_prepare(
            $conn,
            "
            INSERT INTO sub_mesin
            (
                id_mesin,
                nama_sub_mesin,
                serial_number,
                keterangan,
                gambar
            )
            VALUES (?, ?, ?, ?, ?)
            "
        );


        if ($stmt_insert) {

            mysqli_stmt_bind_param(
                $stmt_insert,
                "issss",
                $val_id_mesin,
                $val_nama_sub_mesin,
                $val_serial_number,
                $val_keterangan,
                $nama_gambar
            );


            if (
                mysqli_stmt_execute(
                    $stmt_insert
                )
            ) {

                mysqli_stmt_close(
                    $stmt_insert
                );

                header(
                    "Location: index.php"
                );

                exit;

            } else {

                $error =
                    "Gagal menyimpan data Sub Mesin: "
                    . mysqli_stmt_error(
                        $stmt_insert
                    );

                mysqli_stmt_close(
                    $stmt_insert
                );


                /* -----------------------------------------
                   HAPUS FOTO JIKA INSERT GAGAL
                ----------------------------------------- */

                if (
                    !empty($uploaded_file_path) &&
                    file_exists($uploaded_file_path)
                ) {

                    unlink(
                        $uploaded_file_path
                    );
                }
            }

        } else {

            $error =
                "Gagal menyiapkan proses penyimpanan: "
                . mysqli_error($conn);


            /* ---------------------------------------------
               HAPUS FOTO JIKA QUERY GAGAL
            --------------------------------------------- */

            if (
                !empty($uploaded_file_path) &&
                file_exists($uploaded_file_path)
            ) {

                unlink(
                    $uploaded_file_path
                );
            }
        }
    }
}


/* =========================================================
   DATA MESIN UNTUK DROPDOWN
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
   HEADER
========================================================= */

include "../template/header.php";
?>


<style>

/* =========================================================
   PAGE
========================================================= */

.submachine-add-page {
    width: 100%;
}


/* =========================================================
   PAGE HEADER
========================================================= */

.submachine-add-header {

    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 16px;

    padding: 18px 22px;

    margin-bottom: 20px;

    box-shadow:
        0 3px 12px rgba(15, 23, 42, .04);
}

.submachine-add-header-inner {

    display: flex;

    align-items: center;

    gap: 14px;
}

.submachine-back {

    width: 42px;

    height: 42px;

    flex-shrink: 0;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    border-radius: 10px;

    background: #f8fafc;

    border: 1px solid #e2e8f0;

    color: #475569;

    text-decoration: none;

    transition: .2s;
}

.submachine-back:hover {

    background: #005baa;

    border-color: #005baa;

    color: #fff;

    transform: translateX(-2px);
}

.submachine-add-title {

    margin: 0;

    color: #172033;

    font-size: 22px;

    font-weight: 800;
}

.submachine-add-subtitle {

    color: #64748b;

    font-size: 12px;

    margin-top: 3px;
}


/* =========================================================
   MAIN FORM CARD
========================================================= */

.submachine-form-card {

    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 16px;

    overflow: hidden;

    box-shadow:
        0 3px 12px rgba(15, 23, 42, .04);
}


/* =========================================================
   FORM HEADER
========================================================= */

.submachine-form-header {

    padding: 17px 20px;

    border-bottom: 1px solid #e5e7eb;

    display: flex;

    align-items: center;

    gap: 10px;
}

.submachine-form-icon {

    width: 38px;

    height: 38px;

    border-radius: 10px;

    background: #eef5ff;

    color: #005baa;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 18px;
}

.submachine-form-title {

    margin: 0;

    color: #172033;

    font-size: 16px;

    font-weight: 750;
}

.submachine-form-subtitle {

    color: #94a3b8;

    font-size: 11px;

    margin-top: 2px;
}


/* =========================================================
   FORM BODY
========================================================= */

.submachine-form-body {

    padding: 24px;
}


/* =========================================================
   FORM LABEL
========================================================= */

.submachine-label {

    display: block;

    color: #334155;

    font-size: 13px;

    font-weight: 700;

    margin-bottom: 7px;
}

.submachine-label .required {

    color: #dc2626;
}


/* =========================================================
   INPUT
========================================================= */

.submachine-input,
.submachine-select,
.submachine-textarea {

    width: 100%;

    border: 1px solid #dbe3ea;

    border-radius: 9px;

    background: #fff;

    color: #172033;

    font-size: 13px;

    transition: .2s;
}

.submachine-input,
.submachine-select {

    min-height: 43px;

    padding: 9px 12px;
}

.submachine-textarea {

    padding: 11px 12px;

    resize: vertical;

    min-height: 110px;
}

.submachine-input:focus,
.submachine-select:focus,
.submachine-textarea:focus {

    border-color: #0076c8;

    outline: none;

    box-shadow:
        0 0 0 3px rgba(0, 118, 200, .08);
}


/* =========================================================
   INPUT GROUP
========================================================= */

.submachine-input-group {

    position: relative;
}

.submachine-input-icon {

    position: absolute;

    left: 13px;

    top: 50%;

    transform: translateY(-50%);

    color: #94a3b8;

    pointer-events: none;

    z-index: 2;
}

.submachine-input.with-icon {

    padding-left: 38px;
}


/* =========================================================
   HELP TEXT
========================================================= */

.submachine-help {

    display: block;

    margin-top: 6px;

    color: #94a3b8;

    font-size: 11px;

    line-height: 1.5;
}


/* =========================================================
   MACHINE SELECT INFO
========================================================= */

.selected-machine-info {

    display: none;

    margin-top: 8px;

    padding: 9px 11px;

    background: #f8fafc;

    border: 1px solid #e5e7eb;

    border-radius: 8px;

    font-size: 11px;

    color: #64748b;
}

.selected-machine-info.show {

    display: block;
}

.selected-machine-info strong {

    color: #334155;
}


/* =========================================================
   PHOTO UPLOAD
========================================================= */

.photo-upload-box {

    border: 1px dashed #cbd5e1;

    background: #f8fafc;

    border-radius: 12px;

    padding: 14px;

    transition: .2s;
}

.photo-upload-box:hover {

    border-color: #0076c8;

    background: #f7fbff;
}

.photo-preview-wrapper {

    display: none;

    align-items: center;

    gap: 12px;

    margin-bottom: 12px;

    padding: 10px;

    background: #ffffff;

    border: 1px solid #e2e8f0;

    border-radius: 10px;
}

.photo-preview-wrapper.show {

    display: flex;
}

.photo-preview {

    width: 70px;

    height: 70px;

    object-fit: cover;

    border-radius: 9px;

    border: 1px solid #dbe3ea;

    background: #f8fafc;
}

.photo-preview-name {

    font-size: 12px;

    font-weight: 700;

    color: #334155;

    word-break: break-word;
}

.photo-preview-size {

    color: #94a3b8;

    font-size: 10px;

    margin-top: 2px;
}


/* =========================================================
   ERROR
========================================================= */

.submachine-error {

    border: none;

    border-radius: 10px;

    background: #fff1f2;

    color: #991b1b;

    padding: 11px 13px;

    font-size: 12px;

    display: flex;

    align-items: flex-start;

    gap: 9px;

    margin-bottom: 20px;
}

.submachine-error i {

    font-size: 16px;

    flex-shrink: 0;
}


/* =========================================================
   FORM FOOTER
========================================================= */

.submachine-form-footer {

    margin-top: 25px;

    padding-top: 18px;

    border-top: 1px solid #e5e7eb;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    flex-wrap: wrap;
}

.submachine-form-footer-left {

    color: #94a3b8;

    font-size: 11px;
}

.submachine-form-actions {

    display: flex;

    align-items: center;

    gap: 8px;
}

.btn-submachine-save {

    background:
        linear-gradient(
            135deg,
            #005baa,
            #0076c8
        );

    border: none;

    color: #fff;

    min-height: 41px;

    padding: 9px 18px;

    border-radius: 9px;

    font-size: 13px;

    font-weight: 700;

    transition: .2s;
}

.btn-submachine-save:hover {

    color: #fff;

    transform: translateY(-1px);

    box-shadow:
        0 6px 16px rgba(0, 91, 170, .18);
}

.btn-submachine-cancel {

    min-height: 41px;

    padding: 9px 18px;

    border-radius: 9px;

    font-size: 13px;

    font-weight: 600;

    background: #fff;

    border: 1px solid #dbe3ea;

    color: #475569;

    text-decoration: none;

    display: inline-flex;

    align-items: center;

    justify-content: center;
}

.btn-submachine-cancel:hover {

    background: #f8fafc;

    color: #334155;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 768px) {

    .submachine-add-header {

        padding: 15px;

        border-radius: 13px;
    }

    .submachine-add-header-inner {

        align-items: flex-start;
    }

    .submachine-back {

        width: 38px;

        height: 38px;
    }

    .submachine-add-title {

        font-size: 19px;
    }

    .submachine-add-subtitle {

        font-size: 11px;

        line-height: 1.5;
    }

    .submachine-form-header {

        padding: 14px 15px;
    }

    .submachine-form-body {

        padding: 17px 15px;
    }

    .submachine-form-footer {

        align-items: stretch;

        flex-direction: column;
    }

    .submachine-form-footer-left {

        order: 2;
    }

    .submachine-form-actions {

        width: 100%;

        display: grid;

        grid-template-columns: 1fr 1fr;
    }

    .btn-submachine-save,
    .btn-submachine-cancel {

        width: 100%;
    }

    .photo-preview {

        width: 60px;

        height: 60px;
    }
}


@media (max-width: 480px) {

    .submachine-add-header {

        margin-bottom: 14px;
    }

    .submachine-add-title {

        font-size: 17px;
    }

    .submachine-add-subtitle {

        font-size: 10px;
    }

    .submachine-form-card {

        border-radius: 13px;
    }

    .submachine-form-title {

        font-size: 14px;
    }

    .submachine-form-body {

        padding: 15px 12px;
    }

    .submachine-label {

        font-size: 12px;
    }

    .submachine-input,
    .submachine-select {

        min-height: 41px;

        font-size: 12px;
    }

    .submachine-textarea {

        font-size: 12px;
    }

    .submachine-form-actions {

        grid-template-columns: 1fr;

    }

    .btn-submachine-save,
    .btn-submachine-cancel {

        min-height: 42px;
    }

    .photo-preview-wrapper {

        align-items: flex-start;
    }
}

</style>


<div class="container-fluid p-0 submachine-add-page">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="submachine-add-header">

        <div class="submachine-add-header-inner">

            <a
                href="index.php"
                class="submachine-back"
                title="Kembali"
            >

                <i class="bi bi-arrow-left"></i>

            </a>


            <div>

                <h2 class="submachine-add-title">

                    Tambah Sub Mesin

                </h2>

                <div class="submachine-add-subtitle">

                    Tambahkan sub mesin baru dan hubungkan
                    dengan mesin induknya.

                </div>

            </div>

        </div>

    </div>



    <!-- =====================================================
         FORM CARD
    ====================================================== -->

    <div class="submachine-form-card">


        <!-- =================================================
             FORM HEADER
        ================================================== -->

        <div class="submachine-form-header">

            <div class="submachine-form-icon">

                <i class="bi bi-diagram-3"></i>

            </div>

            <div>

                <div class="submachine-form-title">

                    Form Sub Mesin Baru

                </div>

                <div class="submachine-form-subtitle">

                    Lengkapi informasi sub mesin berikut.

                </div>

            </div>

        </div>



        <!-- =================================================
             FORM BODY
        ================================================== -->

        <div class="submachine-form-body">


            <!-- ERROR -->

            <?php if (!empty($error)): ?>

                <div class="submachine-error">

                    <i class="bi bi-exclamation-triangle-fill"></i>

                    <div>

                        <?= htmlspecialchars($error) ?>

                    </div>

                </div>

            <?php endif; ?>



            <!-- =================================================
                 FORM
            ================================================== -->

            <form
                method="POST"
                enctype="multipart/form-data"
                id="formSubMesin"
            >


                <div class="row g-4">


                    <!-- =================================================
                         MESIN INDUK
                    ================================================== -->

                    <div class="col-12 col-md-6">

                        <label class="submachine-label">

                            Mesin Induk

                            <span class="required">*</span>

                        </label>


                        <div class="submachine-input-group">

                            <i class="bi bi-gear-wide-connected submachine-input-icon"></i>

                            <select
                                name="id_mesin"
                                id="id_mesin"
                                class="submachine-select"
                                required
                            >

                                <option value="">
                                    -- Pilih Mesin Induk --
                                </option>


                                <?php if ($q_mesin): ?>

                                    <?php while ($m = mysqli_fetch_assoc($q_mesin)): ?>

                                        <option
                                            value="<?= intval($m['id']) ?>"
                                            data-sn="<?= htmlspecialchars($m['serial_number'] ?? '', ENT_QUOTES) ?>"
                                            <?= (
                                                $val_id_mesin ==
                                                intval($m['id'])
                                            )
                                                ? 'selected'
                                                : ''
                                            ?>
                                        >

                                            <?= htmlspecialchars(
                                                $m['nama_mesin']
                                            ) ?>

                                        </option>

                                    <?php endwhile; ?>

                                <?php endif; ?>

                            </select>

                        </div>


                        <div
                            class="selected-machine-info"
                            id="selectedMachineInfo"
                        >

                            <i class="bi bi-info-circle me-1"></i>

                            SN Mesin:

                            <strong id="selectedMachineSN">
                                -
                            </strong>

                        </div>


                        <small class="submachine-help">

                            Pilih mesin induk tempat sub mesin ini
                            terpasang.

                        </small>

                    </div>



                    <!-- =================================================
                         NAMA SUB MESIN
                    ================================================== -->

                    <div class="col-12 col-md-6">

                        <label class="submachine-label">

                            Nama Sub Mesin

                            <span class="required">*</span>

                        </label>


                        <div class="submachine-input-group">

                            <i class="bi bi-diagram-3 submachine-input-icon"></i>

                            <input
                                type="text"
                                name="nama_sub_mesin"
                                class="submachine-input with-icon"
                                value="<?= htmlspecialchars(
                                    $val_nama_sub_mesin
                                ) ?>"
                                placeholder="Contoh: Conveyor Feeder"
                                maxlength="150"
                                required
                            >

                        </div>


                        <small class="submachine-help">

                            Masukkan nama bagian atau sub-sistem
                            dari mesin induk.

                        </small>

                    </div>



                    <!-- =================================================
                         SERIAL NUMBER
                    ================================================== -->

                    <div class="col-12 col-md-6">

                        <label class="submachine-label">

                            Serial Number Sub Mesin

                        </label>


                        <div class="submachine-input-group">

                            <i class="bi bi-upc-scan submachine-input-icon"></i>

                            <input
                                type="text"
                                name="serial_number"
                                class="submachine-input with-icon"
                                value="<?= htmlspecialchars(
                                    $val_serial_number
                                ) ?>"
                                placeholder="Contoh: SM-SN-001"
                                maxlength="100"
                            >

                        </div>


                        <small class="submachine-help">

                            Nomor seri sub mesin jika tersedia.
                            Boleh dikosongkan.

                        </small>

                    </div>



                    <!-- =================================================
                         FOTO
                    ================================================== -->

                    <div class="col-12 col-md-6">

                        <label class="submachine-label">

                            Foto Sub Mesin

                        </label>


                        <div class="photo-upload-box">


                            <!-- PREVIEW -->

                            <div
                                class="photo-preview-wrapper"
                                id="photoPreviewWrapper"
                            >

                                <img
                                    src=""
                                    id="photoPreview"
                                    class="photo-preview"
                                    alt="Preview Foto"
                                >

                                <div>

                                    <div
                                        class="photo-preview-name"
                                        id="photoPreviewName"
                                    >
                                    </div>

                                    <div
                                        class="photo-preview-size"
                                        id="photoPreviewSize"
                                    >
                                    </div>

                                </div>

                            </div>


                            <input
                                type="file"
                                name="gambar"
                                id="gambar"
                                class="form-control"
                                accept="image/jpeg,image/png,image/webp"
                            >


                            <small class="submachine-help">

                                Format: JPG, JPEG, PNG, WEBP.
                                Maksimal 2MB.

                            </small>

                        </div>

                    </div>



                    <!-- =================================================
                         KETERANGAN
                    ================================================== -->

                    <div class="col-12">

                        <label class="submachine-label">

                            Keterangan / Deskripsi

                        </label>


                        <textarea
                            name="keterangan"
                            class="submachine-textarea"
                            rows="5"
                            maxlength="1000"
                            placeholder="Masukkan fungsi, posisi, spesifikasi singkat, atau catatan sub mesin..."
                        ><?= htmlspecialchars(
                            $val_keterangan
                        ) ?></textarea>


                        <small class="submachine-help">

                            Jelaskan fungsi atau informasi tambahan
                            mengenai sub mesin.

                        </small>

                    </div>


                </div>



                <!-- =================================================
                     FOOTER FORM
                ================================================== -->

                <div class="submachine-form-footer">


                    <div class="submachine-form-footer-left">

                        <i class="bi bi-info-circle me-1"></i>

                        Field bertanda
                        <span class="text-danger">*</span>
                        wajib diisi.

                    </div>


                    <div class="submachine-form-actions">


                        <a
                            href="index.php"
                            class="btn-submachine-cancel"
                        >

                            <i class="bi bi-x-lg me-1"></i>

                            Batal

                        </a>


                        <button
                            type="submit"
                            name="simpan"
                            class="btn-submachine-save"
                            id="btnSimpan"
                        >

                            <i class="bi bi-check-lg me-1"></i>

                            Simpan Sub Mesin

                        </button>


                    </div>

                </div>


            </form>

        </div>

    </div>

</div>



<script>

/* =========================================================
   MESIN INDUK INFO
========================================================= */

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const mesinSelect =
            document.getElementById("id_mesin");

        const machineInfo =
            document.getElementById(
                "selectedMachineInfo"
            );

        const machineSN =
            document.getElementById(
                "selectedMachineSN"
            );


        function updateMachineInfo() {

            const selectedOption =
                mesinSelect.options[
                    mesinSelect.selectedIndex
                ];


            if (
                mesinSelect.value &&
                selectedOption
            ) {

                const sn =
                    selectedOption.getAttribute(
                        "data-sn"
                    );


                if (sn) {

                    machineSN.textContent = sn;

                    machineInfo.classList.add(
                        "show"
                    );

                } else {

                    machineSN.textContent =
                        "Tidak tersedia";

                    machineInfo.classList.add(
                        "show"
                    );
                }

            } else {

                machineInfo.classList.remove(
                    "show"
                );

                machineSN.textContent = "-";
            }
        }


        if (mesinSelect) {

            mesinSelect.addEventListener(
                "change",
                updateMachineInfo
            );

            updateMachineInfo();
        }


        /* =====================================================
           PREVIEW FOTO
        ===================================================== */

        const gambarInput =
            document.getElementById("gambar");

        const previewWrapper =
            document.getElementById(
                "photoPreviewWrapper"
            );

        const preview =
            document.getElementById(
                "photoPreview"
            );

        const previewName =
            document.getElementById(
                "photoPreviewName"
            );

        const previewSize =
            document.getElementById(
                "photoPreviewSize"
            );


        if (gambarInput) {

            gambarInput.addEventListener(
                "change",
                function () {

                    const file =
                        this.files[0];


                    if (!file) {

                        previewWrapper.classList.remove(
                            "show"
                        );

                        preview.removeAttribute(
                            "src"
                        );

                        return;
                    }


                    /* -----------------------------------------
                       CEK UKURAN DI CLIENT
                    ----------------------------------------- */

                    if (
                        file.size >
                        2 * 1024 * 1024
                    ) {

                        alert(
                            "Ukuran foto terlalu besar! "
                            + "Maksimal 2MB."
                        );

                        this.value = "";

                        previewWrapper.classList.remove(
                            "show"
                        );

                        return;
                    }


                    /* -----------------------------------------
                       PREVIEW
                    ----------------------------------------- */

                    const reader =
                        new FileReader();


                    reader.onload =
                        function (event) {

                            preview.src =
                                event.target.result;

                            previewName.textContent =
                                file.name;

                            previewSize.textContent =
                                formatFileSize(
                                    file.size
                                );

                            previewWrapper.classList.add(
                                "show"
                            );
                        };


                    reader.readAsDataURL(file);

                }
            );
        }


        /* =====================================================
           FORMAT FILE SIZE
        ===================================================== */

        function formatFileSize(bytes) {

            if (bytes === 0) {

                return "0 Bytes";
            }


            const units = [
                "Bytes",
                "KB",
                "MB"
            ];

            const i =
                Math.floor(
                    Math.log(bytes) /
                    Math.log(1024)
                );


            return (
                parseFloat(
                    (
                        bytes /
                        Math.pow(
                            1024,
                            i
                        )
                    ).toFixed(2)
                )
                + " "
                + units[i]
            );
        }


        /* =====================================================
           SUBMIT BUTTON
        ===================================================== */

        const form =
            document.getElementById(
                "formSubMesin"
            );

        const btnSimpan =
            document.getElementById(
                "btnSimpan"
            );


        if (form && btnSimpan) {

            form.addEventListener(
                "submit",
                function () {

                    if (
                        !form.checkValidity()
                    ) {

                        return;
                    }


                    btnSimpan.disabled =
                        true;

                    btnSimpan.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>'
                        + 'Menyimpan...';

                }
            );
        }

    }
);

</script>


<?php include "../template/footer.php"; ?>
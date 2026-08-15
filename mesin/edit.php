<?php
include "../koneksi.php";

/* =========================================================
   AMBIL ID MESIN
========================================================= */

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    header("Location: index.php");
    exit;
}


/* =========================================================
   AMBIL DATA MESIN
========================================================= */

$stmt_get = mysqli_prepare($conn, "
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

if (!$stmt_get) {
    die("Query data mesin gagal: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt_get, "i", $id);
mysqli_stmt_execute($stmt_get);

$result = mysqli_stmt_get_result($stmt_get);
$data = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt_get);


/* =========================================================
   CEK DATA
========================================================= */

if (!$data) {
    header("Location: index.php");
    exit;
}


/* =========================================================
   VARIABEL FORM
========================================================= */

$error = "";

$val_serial_number  = $data['serial_number'] ?? '';
$val_nama_mesin     = $data['nama_mesin'] ?? '';
$val_id_area        = $data['id_area'] ?? '';
$val_id_jenis_mesin = $data['id_jenis_mesin'] ?? '';
$val_keterangan     = $data['keterangan'] ?? '';
$val_gambar         = $data['gambar'] ?? '';


/* =========================================================
   PROSES UPDATE
========================================================= */

if (isset($_POST['update'])) {

    $val_serial_number  = trim($_POST['serial_number'] ?? '');
    $val_nama_mesin     = trim($_POST['nama_mesin'] ?? '');
    $val_id_area        = intval($_POST['id_area'] ?? 0);
    $val_id_jenis_mesin = intval($_POST['id_jenis_mesin'] ?? 0);
    $val_keterangan     = trim($_POST['keterangan'] ?? '');

    /*
     * Gambar lama tetap digunakan
     * jika user tidak upload gambar baru.
     */
    $nama_gambar_baru = $val_gambar;

    /*
     * Menyimpan nama gambar lama untuk
     * dihapus setelah database berhasil update.
     */
    $gambar_lama = $val_gambar;

    /*
     * Flag untuk mengetahui apakah
     * gambar baru berhasil diupload.
     */
    $gambar_baru_terupload = false;

    /*
     * Lokasi folder upload
     */
    $targetDir = "../uploads/mesin/";


    /* =====================================================
       VALIDASI FORM
    ===================================================== */

    if (empty($val_nama_mesin)) {

        $error = "Nama Mesin wajib diisi!";

    } elseif ($val_id_area <= 0) {

        $error = "Area wajib dipilih!";

    } elseif ($val_id_jenis_mesin <= 0) {

        $error = "Jenis Mesin wajib dipilih!";

    }


    /* =====================================================
       VALIDASI AREA
    ===================================================== */

    if (empty($error)) {

        $stmt_area = mysqli_prepare(
            $conn,
            "SELECT id FROM area_bagian WHERE id = ? LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $stmt_area,
            "i",
            $val_id_area
        );

        mysqli_stmt_execute($stmt_area);

        $result_area = mysqli_stmt_get_result($stmt_area);

        if (mysqli_num_rows($result_area) === 0) {
            $error = "Area yang dipilih tidak ditemukan.";
        }

        mysqli_stmt_close($stmt_area);
    }


    /* =====================================================
       VALIDASI JENIS MESIN
       Harus sesuai dengan AREA
    ===================================================== */

    if (empty($error)) {

        $stmt_jenis = mysqli_prepare($conn, "
            SELECT id
            FROM jenis_mesin
            WHERE id = ?
              AND id_area = ?
            LIMIT 1
        ");

        if ($stmt_jenis) {

            mysqli_stmt_bind_param(
                $stmt_jenis,
                "ii",
                $val_id_jenis_mesin,
                $val_id_area
            );

            mysqli_stmt_execute($stmt_jenis);

            $result_jenis = mysqli_stmt_get_result($stmt_jenis);

            if (mysqli_num_rows($result_jenis) === 0) {

                $error =
                    "Jenis Mesin tidak sesuai dengan Area yang dipilih.";
            }

            mysqli_stmt_close($stmt_jenis);
        }
    }


    /* =====================================================
       PROSES UPLOAD GAMBAR BARU
    ===================================================== */

    if (
        empty($error) &&
        isset($_FILES['gambar']) &&
        $_FILES['gambar']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['gambar']['error'] !== UPLOAD_ERR_OK) {

            $error = "Terjadi kesalahan saat mengunggah gambar.";

        } else {

            $fileTmpPath = $_FILES['gambar']['tmp_name'];
            $fileName    = $_FILES['gambar']['name'];
            $fileSize    = $_FILES['gambar']['size'];

            $fileExtension = strtolower(
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


            /* =============================================
               VALIDASI EXTENSION
            ============================================= */

            if (!in_array(
                $fileExtension,
                $allowedExtensions,
                true
            )) {

                $error =
                    "Format gambar tidak valid! " .
                    "Hanya diperbolehkan JPG, JPEG, PNG, dan WEBP.";

            }


            /* =============================================
               VALIDASI UKURAN
            ============================================= */

            elseif ($fileSize > 2 * 1024 * 1024) {

                $error =
                    "Ukuran gambar terlalu besar! Maksimal 2MB.";

            }


            /* =============================================
               VALIDASI MIME TYPE
            ============================================= */

            else {

                $finfo = finfo_open(FILEINFO_MIME_TYPE);

                $mimeType = finfo_file(
                    $finfo,
                    $fileTmpPath
                );

                finfo_close($finfo);

                $allowedMimeTypes = [
                    'image/jpeg',
                    'image/png',
                    'image/webp'
                ];

                if (!in_array(
                    $mimeType,
                    $allowedMimeTypes,
                    true
                )) {

                    $error =
                        "File yang diupload bukan gambar yang valid.";

                }
            }


            /* =============================================
               UPLOAD FILE
            ============================================= */

            if (empty($error)) {

                if (!is_dir($targetDir)) {

                    if (!mkdir(
                        $targetDir,
                        0777,
                        true
                    )) {

                        $error =
                            "Folder upload gambar tidak dapat dibuat.";
                    }
                }
            }


            if (empty($error)) {

                /*
                 * Nama file dibuat unik.
                 */
                $nama_gambar_baru =
                    "mesin_" .
                    time() .
                    "_" .
                    bin2hex(random_bytes(5)) .
                    "." .
                    $fileExtension;


                $targetPath =
                    $targetDir .
                    $nama_gambar_baru;


                if (
                    move_uploaded_file(
                        $fileTmpPath,
                        $targetPath
                    )
                ) {

                    $gambar_baru_terupload = true;

                } else {

                    $error =
                        "Gagal mengunggah gambar baru ke server.";
                }
            }
        }
    }


    /* =====================================================
       UPDATE DATABASE
    ===================================================== */

    if (empty($error)) {

        $stmt_update = mysqli_prepare($conn, "
            UPDATE mesin
            SET
                serial_number = ?,
                nama_mesin = ?,
                id_area = ?,
                id_jenis_mesin = ?,
                keterangan = ?,
                gambar = ?
            WHERE id = ?
        ");


        if (!$stmt_update) {

            /*
             * Jika query gagal dibuat,
             * hapus gambar baru yang sudah terlanjur diupload.
             */
            if (
                $gambar_baru_terupload &&
                !empty($nama_gambar_baru) &&
                file_exists(
                    $targetDir . $nama_gambar_baru
                )
            ) {

                unlink(
                    $targetDir . $nama_gambar_baru
                );
            }

            $error =
                "Query update gagal: " .
                mysqli_error($conn);

        } else {

            mysqli_stmt_bind_param(
                $stmt_update,
                "ssiissi",
                $val_serial_number,
                $val_nama_mesin,
                $val_id_area,
                $val_id_jenis_mesin,
                $val_keterangan,
                $nama_gambar_baru,
                $id
            );


            if (mysqli_stmt_execute($stmt_update)) {

                mysqli_stmt_close($stmt_update);


                /* =========================================
                   HAPUS GAMBAR LAMA
                   Setelah database berhasil diupdate
                ========================================= */

                if (
                    $gambar_baru_terupload &&
                    !empty($gambar_lama) &&
                    $gambar_lama !== $nama_gambar_baru &&
                    file_exists(
                        $targetDir . $gambar_lama
                    )
                ) {

                    unlink(
                        $targetDir . $gambar_lama
                    );
                }


                /* =========================================
                   REDIRECT
                ========================================= */

                header(
                    "Location: detail.php?id=" .
                    $id
                );

                exit;

            } else {

                /*
                 * Jika update database gagal,
                 * hapus gambar baru agar tidak menjadi
                 * file yatim di server.
                 */
                if (
                    $gambar_baru_terupload &&
                    !empty($nama_gambar_baru) &&
                    file_exists(
                        $targetDir . $nama_gambar_baru
                    )
                ) {

                    unlink(
                        $targetDir . $nama_gambar_baru
                    );
                }


                $error =
                    "Gagal memperbarui data: " .
                    mysqli_stmt_error(
                        $stmt_update
                    );

                mysqli_stmt_close($stmt_update);
            }
        }
    }
}


/* =========================================================
   HEADER TEMPLATE
========================================================= */

include "../template/header.php";
?>


<style>

/* =========================================================
   PAGE
========================================================= */

.machine-edit-page {
    width: 100%;
}


/* =========================================================
   HEADER
========================================================= */

.edit-page-header {

    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 16px;

    padding: 18px 20px;

    margin-bottom: 20px;

    box-shadow:
        0 2px 8px rgba(15, 23, 42, .04);
}

.edit-page-header-inner {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    flex-wrap: wrap;
}

.edit-page-left {

    display: flex;

    align-items: center;

    gap: 12px;

    min-width: 0;
}

.edit-back {

    width: 42px;

    height: 42px;

    flex-shrink: 0;

    border-radius: 11px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    background: #f8fafc;

    border: 1px solid #e2e8f0;

    color: #475569;

    text-decoration: none;

    transition: .2s;
}

.edit-back:hover {

    background: #005baa;

    border-color: #005baa;

    color: #ffffff;
}

.edit-page-title {

    font-size: 21px;

    font-weight: 800;

    color: #172033;

    margin: 0;
}

.edit-page-subtitle {

    font-size: 12px;

    color: #94a3b8;

    margin-top: 3px;
}


/* =========================================================
   MAIN CARD
========================================================= */

.edit-card {

    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 16px;

    overflow: hidden;

    box-shadow:
        0 2px 8px rgba(15, 23, 42, .035);
}

.edit-card-header {

    padding: 16px 20px;

    border-bottom: 1px solid #e5e7eb;

    display: flex;

    align-items: center;

    gap: 10px;
}

.edit-card-header-icon {

    width: 38px;

    height: 38px;

    border-radius: 10px;

    background: #eef5ff;

    color: #0066b3;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 18px;

    flex-shrink: 0;
}

.edit-card-title {

    font-size: 15px;

    font-weight: 750;

    color: #172033;

    margin: 0;
}

.edit-card-subtitle {

    font-size: 11px;

    color: #94a3b8;

    margin-top: 2px;
}

.edit-card-body {

    padding: 22px;
}


/* =========================================================
   FORM
========================================================= */

.form-label-custom {

    font-size: 12px;

    font-weight: 700;

    color: #334155;

    margin-bottom: 6px;
}

.required {

    color: #dc2626;
}

.form-control,
.form-select {

    border-color: #dbe1e8;

    color: #172033;

    font-size: 13px;

    min-height: 40px;

    border-radius: 9px;

    transition: .2s;
}

.form-control:focus,
.form-select:focus {

    border-color: #0066b3;

    box-shadow:
        0 0 0 .2rem rgba(0, 102, 179, .10);
}

textarea.form-control {

    min-height: 105px;

    resize: vertical;
}

.form-help {

    font-size: 10.5px;

    color: #94a3b8;

    margin-top: 5px;
}


/* =========================================================
   SELECT LOADING
========================================================= */

.select-loading {

    position: relative;
}

.select-loading::after {

    content: "";

    position: absolute;

    width: 15px;

    height: 15px;

    border: 2px solid #dbeafe;

    border-top-color: #0066b3;

    border-radius: 50%;

    right: 13px;

    top: 13px;

    animation:
        spin .7s linear infinite;

    pointer-events: none;
}

@keyframes spin {

    to {
        transform: rotate(360deg);
    }

}


/* =========================================================
   IMAGE PREVIEW
========================================================= */

.image-preview-card {

    display: flex;

    align-items: center;

    gap: 14px;

    padding: 12px;

    background: #f8fafc;

    border: 1px solid #e2e8f0;

    border-radius: 12px;

    max-width: 100%;

    margin-bottom: 10px;
}

.image-preview {

    width: 80px;

    height: 80px;

    flex-shrink: 0;

    border-radius: 10px;

    object-fit: cover;

    background: #ffffff;

    border: 1px solid #e2e8f0;
}

.image-preview-info {

    min-width: 0;
}

.image-preview-title {

    font-size: 12px;

    font-weight: 700;

    color: #334155;

    margin-bottom: 3px;
}

.image-preview-name {

    font-size: 10.5px;

    color: #64748b;

    word-break: break-all;
}


/* =========================================================
   NEW IMAGE PREVIEW
========================================================= */

.new-image-preview-wrapper {

    display: none;

    margin-top: 12px;

    padding: 10px;

    border: 1px dashed #93c5fd;

    background: #f8fbff;

    border-radius: 10px;
}

.new-image-preview-wrapper.show {

    display: flex;

    align-items: center;

    gap: 12px;
}

.new-image-preview {

    width: 65px;

    height: 65px;

    object-fit: cover;

    border-radius: 8px;

    border: 1px solid #dbeafe;
}


/* =========================================================
   ALERT
========================================================= */

.custom-alert {

    border-radius: 10px;

    border: 0;

    font-size: 12px;
}


/* =========================================================
   BUTTON AREA
========================================================= */

.form-actions {

    border-top: 1px solid #e5e7eb;

    margin-top: 22px;

    padding-top: 18px;

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 10px;

    flex-wrap: wrap;
}

.form-actions-left {

    display: flex;

    gap: 8px;

    flex-wrap: wrap;
}

.form-actions .btn {

    border-radius: 9px;

    font-size: 12px;

    font-weight: 650;

    min-height: 38px;
}


/* =========================================================
   INFORMATION SIDE CARD
========================================================= */

.info-card {

    background: #f8fafc;

    border: 1px solid #e2e8f0;

    border-radius: 12px;

    padding: 15px;

    height: 100%;
}

.info-card-title {

    font-size: 12px;

    font-weight: 750;

    color: #334155;

    margin-bottom: 10px;
}

.info-item {

    display: flex;

    align-items: flex-start;

    gap: 9px;

    margin-bottom: 10px;

    font-size: 11px;

    color: #64748b;
}

.info-item:last-child {

    margin-bottom: 0;
}

.info-item i {

    color: #0066b3;

    font-size: 14px;

    margin-top: 1px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 991.98px) {

    .edit-card-body {

        padding: 18px;
    }

}

@media (max-width: 767.98px) {

    .edit-page-header {

        padding: 14px;
    }

    .edit-page-title {

        font-size: 18px;
    }

    .edit-page-subtitle {

        font-size: 10.5px;

        max-width: 230px;
    }

    .edit-back {

        width: 38px;

        height: 38px;
    }

    .edit-card-header {

        padding: 14px;
    }

    .edit-card-body {

        padding: 15px;
    }

    .form-actions {

        align-items: stretch;

        flex-direction: column;
    }

    .form-actions-left {

        width: 100%;
    }

    .form-actions-left .btn {

        flex: 1;
    }

    .image-preview-card {

        align-items: flex-start;
    }

    .image-preview {

        width: 65px;

        height: 65px;
    }

}

@media (max-width: 480px) {

    .edit-page-left {

        align-items: flex-start;
    }

    .edit-page-title {

        font-size: 16px;
    }

    .edit-page-subtitle {

        line-height: 1.4;
    }

    .edit-card-header-icon {

        width: 34px;

        height: 34px;

        font-size: 16px;
    }

    .edit-card-title {

        font-size: 14px;
    }

    .form-actions-left {

        flex-direction: column;
    }

    .form-actions-left .btn {

        width: 100%;
    }

}

</style>


<div class="container-fluid p-0 machine-edit-page">


    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div class="edit-page-header">

        <div class="edit-page-header-inner">

            <div class="edit-page-left">

                <a
                    href="detail.php?id=<?= $id ?>"
                    class="edit-back"
                    title="Kembali ke Detail Mesin"
                >
                    <i class="bi bi-arrow-left"></i>
                </a>

                <div>

                    <h2 class="edit-page-title">
                        Edit Data Mesin
                    </h2>

                    <div class="edit-page-subtitle">
                        Perbarui informasi mesin yang terdaftar
                        pada sistem maintenance
                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->

    <div class="row g-3">


        <!-- =================================================
             FORM
        ================================================== -->

        <div class="col-lg-9">

            <div class="edit-card">

                <!-- CARD HEADER -->

                <div class="edit-card-header">

                    <div class="edit-card-header-icon">

                        <i class="bi bi-pencil-square"></i>

                    </div>

                    <div>

                        <h5 class="edit-card-title">
                            Form Edit Mesin
                        </h5>

                        <div class="edit-card-subtitle">
                            Perbarui data utama mesin dan informasi
                            penempatannya.
                        </div>

                    </div>

                </div>


                <!-- CARD BODY -->

                <div class="edit-card-body">


                    <!-- ERROR -->

                    <?php if (!empty($error)): ?>

                        <div
                            class="alert alert-danger custom-alert d-flex align-items-start gap-2 mb-4"
                            role="alert"
                        >

                            <i class="bi bi-exclamation-triangle-fill mt-1"></i>

                            <div>
                                <?= htmlspecialchars($error) ?>
                            </div>

                        </div>

                    <?php endif; ?>


                    <!-- FORM -->

                    <form
                        method="POST"
                        enctype="multipart/form-data"
                        id="formEditMesin"
                    >


                        <!-- =================================
                             IDENTITAS MESIN
                        ================================== -->

                        <div class="row g-3">


                            <!-- SERIAL NUMBER -->

                            <div class="col-md-6">

                                <label class="form-label-custom">

                                    Serial Number

                                </label>

                                <input
                                    type="text"
                                    name="serial_number"
                                    class="form-control"
                                    value="<?= htmlspecialchars($val_serial_number) ?>"
                                    placeholder="Contoh: SN-2024-001"
                                    autocomplete="off"
                                >

                                <div class="form-help">

                                    Nomor seri atau identitas unik mesin.

                                </div>

                            </div>


                            <!-- NAMA MESIN -->

                            <div class="col-md-6">

                                <label class="form-label-custom">

                                    Nama Mesin

                                    <span class="required">*</span>

                                </label>

                                <input
                                    type="text"
                                    name="nama_mesin"
                                    class="form-control"
                                    value="<?= htmlspecialchars($val_nama_mesin) ?>"
                                    placeholder="Contoh: Mesin Packing Line 1"
                                    required
                                    autocomplete="off"
                                >

                                <div class="form-help">

                                    Nama resmi mesin yang digunakan di area produksi.

                                </div>

                            </div>


                            <!-- AREA -->

                            <div class="col-md-6">

                                <label class="form-label-custom">

                                    Area Bagian

                                    <span class="required">*</span>

                                </label>

                                <select
                                    name="id_area"
                                    id="id_area"
                                    class="form-select"
                                    required
                                >

                                    <option value="">
                                        -- Pilih Area --
                                    </option>

                                    <?php

                                    $q_area = mysqli_query(
                                        $conn,
                                        "
                                        SELECT
                                            id,
                                            nama_area,
                                            lokasi
                                        FROM area_bagian
                                        ORDER BY nama_area ASC
                                        "
                                    );

                                    if ($q_area):

                                        while (
                                            $area =
                                            mysqli_fetch_assoc($q_area)
                                        ):

                                            $selected =
                                                (
                                                    (string)$val_id_area ===
                                                    (string)$area['id']
                                                )
                                                ? 'selected'
                                                : '';

                                    ?>

                                        <option
                                            value="<?= intval($area['id']) ?>"
                                            <?= $selected ?>
                                        >
                                            <?= htmlspecialchars($area['nama_area']) ?>

                                            <?php if (!empty($area['lokasi'])): ?>

                                                - <?= htmlspecialchars($area['lokasi']) ?>

                                            <?php endif; ?>

                                        </option>

                                    <?php

                                        endwhile;

                                    endif;

                                    ?>

                                </select>

                                <div class="form-help">

                                    Pilih area tempat mesin berada.

                                </div>

                            </div>


                            <!-- JENIS MESIN -->

                            <div class="col-md-6">

                                <label class="form-label-custom">

                                    Jenis Mesin

                                    <span class="required">*</span>

                                </label>

                                <div id="jenisMesinWrapper">

                                    <select
                                        name="id_jenis_mesin"
                                        id="id_jenis_mesin"
                                        class="form-select"
                                        required
                                        <?= empty($val_id_area) ? 'disabled' : '' ?>
                                    >

                                        <option value="">
                                            -- Pilih Jenis Mesin --
                                        </option>

                                        <?php

                                        /*
                                         * Jika area sudah ada,
                                         * tampilkan jenis mesin
                                         * sesuai area.
                                         */

                                        if (!empty($val_id_area)):

                                            $stmt_jenis_awal =
                                                mysqli_prepare(
                                                    $conn,
                                                    "
                                                    SELECT
                                                        id,
                                                        nama_jenis_mesin
                                                    FROM jenis_mesin
                                                    WHERE id_area = ?
                                                    ORDER BY nama_jenis_mesin ASC
                                                    "
                                                );

                                            if ($stmt_jenis_awal):

                                                mysqli_stmt_bind_param(
                                                    $stmt_jenis_awal,
                                                    "i",
                                                    $val_id_area
                                                );

                                                mysqli_stmt_execute(
                                                    $stmt_jenis_awal
                                                );

                                                $result_jenis_awal =
                                                    mysqli_stmt_get_result(
                                                        $stmt_jenis_awal
                                                    );

                                                while (
                                                    $jenis =
                                                    mysqli_fetch_assoc(
                                                        $result_jenis_awal
                                                    )
                                                ):

                                                    $selected_jenis =
                                                        (
                                                            (string)$val_id_jenis_mesin ===
                                                            (string)$jenis['id']
                                                        )
                                                        ? 'selected'
                                                        : '';

                                        ?>

                                                    <option
                                                        value="<?= intval($jenis['id']) ?>"
                                                        <?= $selected_jenis ?>
                                                    >
                                                        <?= htmlspecialchars($jenis['nama_jenis_mesin']) ?>
                                                    </option>

                                        <?php

                                                endwhile;

                                                mysqli_stmt_close(
                                                    $stmt_jenis_awal
                                                );

                                            endif;

                                        endif;

                                        ?>

                                    </select>

                                </div>

                                <div class="form-help">

                                    Jenis mesin mengikuti area yang dipilih.

                                </div>

                            </div>


                            <!-- KETERANGAN -->

                            <div class="col-12">

                                <label class="form-label-custom">

                                    Keterangan / Deskripsi

                                </label>

                                <textarea
                                    name="keterangan"
                                    class="form-control"
                                    rows="4"
                                    placeholder="Tambahkan keterangan mengenai mesin..."
                                ><?= htmlspecialchars($val_keterangan) ?></textarea>

                                <div class="form-help">

                                    Gunakan untuk mencatat informasi tambahan
                                    mengenai mesin.

                                </div>

                            </div>


                            <!-- =================================
                                 FOTO MESIN
                            ================================== -->

                            <div class="col-12">

                                <label class="form-label-custom">

                                    Foto Mesin

                                </label>


                                <!-- FOTO LAMA -->

                                <?php

                                $foto_lama_path =
                                    "../uploads/mesin/" .
                                    $val_gambar;

                                if (
                                    !empty($val_gambar) &&
                                    file_exists($foto_lama_path)
                                ):

                                ?>

                                    <div class="image-preview-card">

                                        <img
                                            src="<?= htmlspecialchars($foto_lama_path) ?>"
                                            alt="Foto Mesin Saat Ini"
                                            class="image-preview"
                                        >

                                        <div class="image-preview-info">

                                            <div class="image-preview-title">

                                                Foto Saat Ini

                                            </div>

                                            <div class="image-preview-name">

                                                <?= htmlspecialchars($val_gambar) ?>

                                            </div>

                                        </div>

                                    </div>

                                <?php else: ?>

                                    <div
                                        class="alert alert-light border small text-muted py-2 px-3"
                                    >

                                        <i class="bi bi-image me-1"></i>

                                        Belum ada foto mesin.

                                    </div>

                                <?php endif; ?>


                                <!-- INPUT FOTO -->

                                <input
                                    type="file"
                                    name="gambar"
                                    id="gambar"
                                    class="form-control"
                                    accept="image/png,image/jpeg,image/jpg,image/webp"
                                >

                                <div class="form-help">

                                    Biarkan kosong jika tidak ingin mengganti
                                    foto. Format JPG, JPEG, PNG, atau WEBP.
                                    Maksimal 2MB.

                                </div>


                                <!-- PREVIEW FOTO BARU -->

                                <div
                                    class="new-image-preview-wrapper"
                                    id="newImagePreviewWrapper"
                                >

                                    <img
                                        src=""
                                        alt="Preview Foto Baru"
                                        class="new-image-preview"
                                        id="newImagePreview"
                                    >

                                    <div>

                                        <div class="fw-semibold small text-dark">
                                            Preview Foto Baru
                                        </div>

                                        <div
                                            class="small text-muted"
                                            id="newImageName"
                                        ></div>

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- =================================
                             ACTION
                        ================================== -->

                        <div class="form-actions">

                            <div class="form-actions-left">

                                <button
                                    type="submit"
                                    name="update"
                                    id="btnUpdate"
                                    class="btn btn-primary px-4"
                                >

                                    <i class="bi bi-check-lg me-1"></i>

                                    Simpan Perubahan

                                </button>

                                <a
                                    href="detail.php?id=<?= $id ?>"
                                    class="btn btn-light border px-4"
                                >

                                    <i class="bi bi-x-lg me-1"></i>

                                    Batal

                                </a>

                            </div>

                        </div>


                    </form>

                </div>

            </div>

        </div>


        <!-- =================================================
             INFORMATION
        ================================================== -->

        <div class="col-lg-3">

            <div class="info-card">

                <div class="info-card-title">

                    <i class="bi bi-info-circle text-primary me-1"></i>

                    Informasi Edit

                </div>


                <div class="info-item">

                    <i class="bi bi-check-circle"></i>

                    <div>
                        Perubahan data mesin akan langsung
                        tersimpan ke database.
                    </div>

                </div>


                <div class="info-item">

                    <i class="bi bi-diagram-3"></i>

                    <div>
                        Jenis mesin hanya dapat dipilih berdasarkan
                        area yang dipilih.
                    </div>

                </div>


                <div class="info-item">

                    <i class="bi bi-image"></i>

                    <div>
                        Foto lama akan diganti otomatis apabila
                        kamu mengupload foto baru.
                    </div>

                </div>


                <div class="info-item">

                    <i class="bi bi-file-earmark-image"></i>

                    <div>
                        Format foto yang diperbolehkan:
                        JPG, JPEG, PNG, dan WEBP.
                    </div>

                </div>


                <div class="info-item">

                    <i class="bi bi-speedometer2"></i>

                    <div>
                        Ukuran maksimal foto adalah 2MB.
                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<script>

/* =========================================================
   DROPDOWN AREA → JENIS MESIN
========================================================= */

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const areaSelect =
            document.getElementById("id_area");

        const jenisSelect =
            document.getElementById("id_jenis_mesin");

        const jenisWrapper =
            document.getElementById(
                "jenisMesinWrapper"
            );


        /*
         * Fungsi load jenis mesin.
         */

        function loadJenisMesin(
            idArea,
            selectedJenis = ""
        ) {

            if (!idArea) {

                jenisSelect.innerHTML =
                    '<option value="">-- Pilih Jenis Mesin --</option>';

                jenisSelect.disabled = true;

                jenisWrapper.classList.remove(
                    "select-loading"
                );

                return;
            }


            /*
             * Loading state
             */

            jenisSelect.disabled = true;

            jenisSelect.innerHTML =
                '<option value="">Memuat jenis mesin...</option>';

            jenisWrapper.classList.add(
                "select-loading"
            );


            fetch(
                "../master/get_jenis_mesin.php?id_area=" +
                encodeURIComponent(idArea)
            )

            .then(
                function (response) {

                    if (!response.ok) {
                        throw new Error(
                            "Gagal mengambil data jenis mesin."
                        );
                    }

                    return response.text();
                }
            )

            .then(
                function (data) {

                    jenisSelect.innerHTML = data;

                    jenisSelect.disabled = false;

                    /*
                     * Kembalikan jenis mesin lama
                     * setelah data berhasil dimuat.
                     */

                    if (selectedJenis !== "") {

                        jenisSelect.value =
                            selectedJenis;
                    }

                }
            )

            .catch(
                function (error) {

                    console.error(error);

                    jenisSelect.innerHTML =
                        '<option value="">Gagal memuat jenis mesin</option>';

                    jenisSelect.disabled = true;

                    alert(
                        "Gagal memuat data Jenis Mesin. " +
                        "Silakan coba lagi."
                    );
                }
            )

            .finally(
                function () {

                    jenisWrapper.classList.remove(
                        "select-loading"
                    );

                }
            );
        }


        /*
         * Ketika Area berubah,
         * jenis mesin direset dan dimuat ulang.
         */

        areaSelect.addEventListener(
            "change",
            function () {

                const idArea =
                    this.value;

                loadJenisMesin(
                    idArea,
                    ""
                );
            }
        );


        /*
         * Nilai awal dari database.
         */

        const initialArea =
            areaSelect.value;

        const initialJenis =
            "<?= htmlspecialchars(
                (string)$val_id_jenis_mesin,
                ENT_QUOTES,
                'UTF-8'
            ) ?>";


        /*
         * Jika area sudah dipilih,
         * pastikan jenis mesin tersedia.
         */

        if (initialArea !== "") {

            loadJenisMesin(
                initialArea,
                initialJenis
            );

        }

    }
);


/* =========================================================
   PREVIEW FOTO BARU
========================================================= */

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const inputGambar =
            document.getElementById("gambar");

        const previewWrapper =
            document.getElementById(
                "newImagePreviewWrapper"
            );

        const previewImage =
            document.getElementById(
                "newImagePreview"
            );

        const previewName =
            document.getElementById(
                "newImageName"
            );


        if (!inputGambar) {
            return;
        }


        inputGambar.addEventListener(
            "change",
            function () {

                const file =
                    this.files[0];


                if (!file) {

                    previewWrapper.classList.remove(
                        "show"
                    );

                    previewImage.src = "";

                    previewName.textContent = "";

                    return;
                }


                /*
                 * Validasi ukuran frontend.
                 */

                if (
                    file.size >
                    2 * 1024 * 1024
                ) {

                    alert(
                        "Ukuran gambar terlalu besar! " +
                        "Maksimal 2MB."
                    );

                    this.value = "";

                    previewWrapper.classList.remove(
                        "show"
                    );

                    return;
                }


                /*
                 * Validasi format frontend.
                 */

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
                        "Format gambar tidak valid! " +
                        "Gunakan JPG, JPEG, PNG, atau WEBP."
                    );

                    this.value = "";

                    previewWrapper.classList.remove(
                        "show"
                    );

                    return;
                }


                /*
                 * Tampilkan preview.
                 */

                const reader =
                    new FileReader();


                reader.onload =
                    function (event) {

                        previewImage.src =
                            event.target.result;

                        previewName.textContent =
                            file.name;

                        previewWrapper.classList.add(
                            "show"
                        );
                    };


                reader.readAsDataURL(file);

            }
        );

    }
);


/* =========================================================
   DISABLE BUTTON SAAT SUBMIT
========================================================= */

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const form =
            document.getElementById(
                "formEditMesin"
            );

        const button =
            document.getElementById(
                "btnUpdate"
            );


        if (!form || !button) {
            return;
        }


        form.addEventListener(
            "submit",
            function () {

                /*
                 * Jangan disable select sebelum
                 * browser mengirim nilai form.
                 */

                button.disabled = true;

                button.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-1"></span>' +
                    'Menyimpan...';

            }
        );

    }
);

</script>


<?php include "../template/footer.php"; ?>
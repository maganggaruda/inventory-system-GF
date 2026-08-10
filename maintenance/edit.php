<?php
include "../koneksi.php";
date_default_timezone_set('Asia/Jakarta');

$error = "";

// 1. Ambil ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 2. Query data maintenance berdasarkan ID
$stmt = mysqli_prepare($conn, "SELECT * FROM riwayat_maintenance WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='index.php';</script>";
    exit;
}

// 3. Proses Update Data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_maintenance'])) {
    $id_komponen        = $_POST['id_komponen'] ?? '';
    $status             = $_POST['status'] ?? '';
    $teknisi            = trim($_POST['teknisi'] ?? '');
    $tindakan           = trim($_POST['tindakan'] ?? '');
    $jenis              = $_POST['jenis'] ?? '';
    $sparepart          = trim($_POST['sparepart'] ?? '');
    $catatan            = trim($_POST['catatan'] ?? '');
    $input_tanggal      = $_POST['tanggal'] ?? date('Y-m-d'); 
    $tanggal_lengkap    = $input_tanggal . ' ' . date('H:i:s');

    // Data spesifikasi lengkap yang direkam ke riwayat
    $serial_number      = trim($_POST['serial_number'] ?? '');
    $nama_bagian        = trim($_POST['nama_bagian'] ?? '');
    $kategori           = trim($_POST['kategori'] ?? '');
    $nama_mesin         = trim($_POST['nama_mesin'] ?? '');
    $nama_sub_mesin     = trim($_POST['nama_sub_mesin'] ?? '');
    $lokasi_penempatan  = trim($_POST['lokasi_penempatan'] ?? '');
    $brand              = trim($_POST['brand'] ?? '');
    $tipe               = trim($_POST['tipe'] ?? '');
    $part_number        = trim($_POST['part_number'] ?? '');
    $daya               = trim($_POST['daya'] ?? '');
    $io_address         = trim($_POST['io_address'] ?? '');
    $input_voltage      = trim($_POST['input_voltage'] ?? '');
    $frekuensi_input    = trim($_POST['frekuensi_input'] ?? '');
    $arus_input         = trim($_POST['arus_input'] ?? '');
    $output             = trim($_POST['output'] ?? '');
    $frekuensi_output   = trim($_POST['frekuensi_output'] ?? '');
    $ip_rating          = trim($_POST['ip_rating'] ?? '');

    // Proses Upload Gambar Maintenance Baru (Jika ada)
    $foto_nama = $data['gambar']; // Pertahankan foto lama secara default
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file_error = $_FILES['foto']['error'];
        if ($file_error === UPLOAD_ERR_OK) {
            $file_tmp   = $_FILES['foto']['tmp_name'];
            $file_name  = $_FILES['foto']['name'];
            $file_size  = $_FILES['foto']['size'];
            $file_ext   = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($file_ext, $allowed_ext) && $file_size <= 5 * 1024 * 1024) {
                $target_dir = "../uploads/maintenance/";
                if (!file_exists($target_dir)) {
                    @mkdir($target_dir, 0777, true);
                }
                $foto_baru = time() . '_' . uniqid() . '.' . $file_ext;
                if (move_uploaded_file($file_tmp, $target_dir . $foto_baru)) {
                    // Hapus foto lama jika ada
                    if (!empty($data['gambar']) && file_exists($target_dir . $data['gambar'])) {
                        @unlink($target_dir . $data['gambar']);
                    }
                    $foto_nama = $foto_baru;
                }
            } else {
                $error = "Format file tidak didukung atau ukuran melebihi 5MB.";
            }
        }
    }

    if (empty($error)) {
        if (!empty($id_komponen) && !empty($tindakan)) {
            $stmt = mysqli_prepare($conn, "
                UPDATE riwayat_maintenance SET 
                    id_komponen = ?, tanggal = ?, status = ?, teknisi = ?, tindakan = ?, jenis = ?, 
                    serial_number = ?, nama_bagian = ?, kategori = ?, nama_mesin = ?, nama_sub_mesin = ?, lokasi_penempatan = ?, 
                    brand = ?, tipe = ?, part_number = ?, daya = ?, io_address = ?, input_voltage = ?, frekuensi_input = ?, 
                    arus_input = ?, output = ?, frekuensi_output = ?, ip_rating = ?, sparepart = ?, catatan = ?, gambar = ?
                WHERE id = ?
            ");
            
            if ($stmt) {
                mysqli_stmt_bind_param(
                    $stmt, 
                    "isssssssssssssssssssssssssi", 
                    $id_komponen, $tanggal_lengkap, $status, $teknisi, $tindakan, $jenis, 
                    $serial_number, $nama_bagian, $kategori, $nama_mesin, $nama_sub_mesin, $lokasi_penempatan, 
                    $brand, $tipe, $part_number, $daya, $io_address, $input_voltage, $frekuensi_input, 
                    $arus_input, $output, $frekuensi_output, $ip_rating, $sparepart, $catatan, $foto_nama, $id
                );
                
                if (mysqli_stmt_execute($stmt)) {
                    if ($status === 'Selesai') {
                        $stmt_update = mysqli_prepare($conn, "UPDATE komponen SET kondisi = 'Baik' WHERE id = ?");
                        mysqli_stmt_bind_param($stmt_update, "i", $id_komponen);
                        mysqli_stmt_execute($stmt_update);
                        mysqli_stmt_close($stmt_update);
                    }

                    mysqli_stmt_close($stmt);
                    echo "<script>alert('Data maintenance berhasil diperbarui!'); window.location='index.php';</script>";
                    exit;
                } else {
                    $error = "Gagal memperbarui data ke database: " . mysqli_error($conn);
                }
            }
        } else {
            $error = "Mohon pilih komponen dan isi tindakan perbaikan!";
        }
    }
}

// Ambil data komponen beserta mesin & sub-mesin untuk dropdown
$q_komponen = mysqli_query($conn, "
    SELECT k.*, m.nama_mesin, sm.nama_sub_mesin 
    FROM komponen k 
    LEFT JOIN mesin m ON k.id_mesin = m.id 
    LEFT JOIN sub_mesin sm ON k.id_sub_mesin = sm.id 
    ORDER BY k.nama_bagian ASC
");

include "../template/header.php";
?>

<!-- CSS Select2 & Theme Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<div class="container-fluid mb-2 px-3 py-2">
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="index.php" class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="bi bi-arrow-left fs-5"></i>
            </a>
            <div>
                <h2 class="fw-bold text-dark m-0">Edit Catatan Maintenance & Spesifikasi</h2>
                <p class="text-muted small m-0 mt-1">Perbarui catatan atau status pemeliharaan mesin beserta spesifikasi teknis</p>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger border-0 rounded-3 mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <input type="hidden" name="update_maintenance" value="1">

            <!-- PILIH KOMPONEN UTAMA -->
            <div class="mb-4">
                <h6 class="fw-bold text-primary small text-uppercase mb-3" style="color: #0056a6 !important;">
                    <i class="bi bi-search me-2"></i> Pilih Komponen / Part
                </h6>
                <div class="row g-3">
                    <div class="col-12">
                        <select name="id_komponen" id="select-komponen" class="form-select border-light-subtle rounded-3" required>
                            <option value="">-- Ketik untuk mencari Komponen, Nama Bagian, atau Serial Number --</option>
                            <?php while ($k = mysqli_fetch_assoc($q_komponen)) : ?>
                                <option value="<?= $k['id'] ?>" 
                                    <?= ($data['id_komponen'] == $k['id']) ? 'selected' : '' ?>
                                    data-sn="<?= htmlspecialchars($k['serial_number'] ?? '') ?>"
                                    data-namabagian="<?= htmlspecialchars($k['nama_bagian'] ?? '') ?>"
                                    data-kategori="<?= htmlspecialchars($k['kategori'] ?? '') ?>"
                                    data-mesin="<?= htmlspecialchars($k['nama_mesin'] ?? '') ?>"
                                    data-submesin="<?= htmlspecialchars($k['nama_sub_mesin'] ?? '') ?>"
                                    data-lokasi="<?= htmlspecialchars($k['lokasi'] ?? '') ?>"
                                    data-brand="<?= htmlspecialchars($k['brand'] ?? '') ?>"
                                    data-tipe="<?= htmlspecialchars($k['tipe'] ?? '') ?>"
                                    data-pn="<?= htmlspecialchars($k['part_number'] ?? '') ?>"
                                    data-daya="<?= htmlspecialchars($k['daya'] ?? '') ?>"
                                    data-io="<?= htmlspecialchars($k['io_address'] ?? '') ?>"
                                    data-vol="<?= htmlspecialchars($k['input_voltage'] ?? '') ?>"
                                    data-freqin="<?= htmlspecialchars($k['frekuensi_input'] ?? '') ?>"
                                    data-arus="<?= htmlspecialchars($k['arus_input'] ?? '') ?>"
                                    data-out="<?= htmlspecialchars($k['output'] ?? '') ?>"
                                    data-freqout="<?= htmlspecialchars($k['frekuensi_output'] ?? '') ?>"
                                    data-ip="<?= htmlspecialchars($k['ip_rating'] ?? '') ?>">
                                    <?= htmlspecialchars($k['nama_bagian']) ?> — [SN: <?= htmlspecialchars($k['serial_number'] ?: '-') ?>] (Mesin: <?= htmlspecialchars($k['nama_mesin'] ?: 'Tanpa Mesin') ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
            </div>

            <hr class="my-4 border-light-subtle">

            <!-- INFORMASI UMUM & FOTO -->
            <div class="mb-4">
                <h6 class="fw-bold text-primary small text-uppercase mb-3" style="color: #0056a6 !important;">
                    <i class="bi bi-info-circle me-2"></i> Informasi Umum & Foto
                </h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">Serial Number (SN)</label>
                        <input type="text" name="serial_number" id="f-sn" class="form-control border-light-subtle rounded-3 bg-white" value="<?= htmlspecialchars($data['serial_number'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">Nama Bagian <span class="text-danger">*</span></label>
                        <input type="text" name="nama_bagian" id="f-namabagian" class="form-control border-light-subtle rounded-3 bg-white" value="<?= htmlspecialchars($data['nama_bagian'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">Kategori</label>
                        <input type="text" name="kategori" id="f-kategori" class="form-control border-light-subtle rounded-3 bg-white" value="<?= htmlspecialchars($data['kategori'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">Mesin Induk</label>
                        <input type="text" name="nama_mesin" id="f-mesin" class="form-control border-light-subtle rounded-3 bg-white" value="<?= htmlspecialchars($data['nama_mesin'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">Sub Mesin</label>
                        <input type="text" name="nama_sub_mesin" id="f-submesin" class="form-control border-light-subtle rounded-3 bg-white" value="<?= htmlspecialchars($data['nama_sub_mesin'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">Lokasi Penempatan</label>
                        <input type="text" name="lokasi_penempatan" id="f-lokasi" class="form-control border-light-subtle rounded-3 bg-white" value="<?= htmlspecialchars($data['lokasi_penempatan'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark">Foto Dokumentasi / Bukti Maintenance</label>
                        <input type="file" name="foto" id="input-foto" class="form-control border-light-subtle rounded-3" accept="image/png, image/jpeg, image/jpg, image/webp">
                        <div class="form-text text-muted">Format: JPG, JPEG, PNG, WEBP. Maksimal 5 MB. Biarkan kosong jika tidak ingin mengubah foto.</div>
                        
                        <!-- Preview Gambar (Baru atau Lama) -->
                        <div id="preview-container" class="mt-3 <?= empty($data['gambar']) ? 'd-none' : '' ?>">
                            <div class="position-relative d-inline-block">
                                <img id="image-preview" src="<?= !empty($data['gambar']) ? '../uploads/maintenance/' . htmlspecialchars($data['gambar']) : '' ?>" alt="Preview Foto" class="img-thumbnail rounded-3 shadow-sm" style="max-height: 200px;">
                                <button type="button" id="btn-remove-foto" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 rounded-circle">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="my-4 border-light-subtle">

            <!-- SPESIFIKASI & BRAND -->
            <div class="mb-4">
                <h6 class="fw-bold text-primary small text-uppercase mb-3" style="color: #0056a6 !important;">
                    <i class="bi bi-cpu me-2"></i> Spesifikasi & Brand
                </h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">Brand / Merk</label>
                        <input type="text" name="brand" id="f-brand" class="form-control border-light-subtle rounded-3 bg-white" value="<?= htmlspecialchars($data['brand'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">Tipe</label>
                        <input type="text" name="tipe" id="f-tipe" class="form-control border-light-subtle rounded-3 bg-white" value="<?= htmlspecialchars($data['tipe'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">Part Number</label>
                        <input type="text" name="part_number" id="f-pn" class="form-control border-light-subtle rounded-3 bg-white" value="<?= htmlspecialchars($data['part_number'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-dark">Daya</label>
                        <input type="text" name="daya" id="f-daya" class="form-control border-light-subtle rounded-3 bg-white" value="<?= htmlspecialchars($data['daya'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-dark">IO Address</label>
                        <input type="text" name="io_address" id="f-io" class="form-control border-light-subtle rounded-3 bg-white" value="<?= htmlspecialchars($data['io_address'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-dark">Input Voltage</label>
                        <input type="text" name="input_voltage" id="f-vol" class="form-control border-light-subtle rounded-3 bg-white" value="<?= htmlspecialchars($data['input_voltage'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-dark">Frekuensi Input</label>
                        <input type="text" name="frekuensi_input" id="f-freqin" class="form-control border-light-subtle rounded-3 bg-white" value="<?= htmlspecialchars($data['frekuensi_input'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-dark">Arus Input</label>
                        <input type="text" name="arus_input" id="f-arus" class="form-control border-light-subtle rounded-3 bg-white" value="<?= htmlspecialchars($data['arus_input'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-dark">Output</label>
                        <input type="text" name="output" id="f-out" class="form-control border-light-subtle rounded-3 bg-white" value="<?= htmlspecialchars($data['output'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-dark">Frekuensi Output</label>
                        <input type="text" name="frekuensi_output" id="f-freqout" class="form-control border-light-subtle rounded-3 bg-white" value="<?= htmlspecialchars($data['frekuensi_output'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-dark">IP Rating</label>
                        <input type="text" name="ip_rating" id="f-ip" class="form-control border-light-subtle rounded-3 bg-white" value="<?= htmlspecialchars($data['ip_rating'] ?? '') ?>">
                    </div>
                </div>
            </div>

            <hr class="my-4 border-light-subtle">

            <!-- STATUS & TINDAKAN MAINTENANCE -->
            <div class="mb-4">
                <h6 class="fw-bold text-primary small text-uppercase mb-3" style="color: #0056a6 !important;">
                    <i class="bi bi-tools me-2"></i> Status & Tindakan Maintenance
                </h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">Tanggal Maintenance <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control border-light-subtle rounded-3" value="<?= htmlspecialchars(!empty($data['tanggal']) ? date('Y-m-d', strtotime($data['tanggal'])) : date('Y-m-d')) ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">Jenis Maintenance</label>
                        <select name="jenis" class="form-select border-light-subtle rounded-3">
                            <option value="Preventive" <?= (($data['jenis'] ?? '') == 'Preventive') ? 'selected' : '' ?>>Preventive</option>
                            <option value="Corrective" <?= (($data['jenis'] ?? '') == 'Corrective') ? 'selected' : '' ?>>Corrective</option>
                            <option value="Breakdown" <?= (($data['jenis'] ?? '') == 'Breakdown') ? 'selected' : '' ?>>Breakdown</option>
                            <option value="Predictive" <?= (($data['jenis'] ?? '') == 'Predictive') ? 'selected' : '' ?>>Predictive</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">Status Pekerjaan <span class="text-danger">*</span></label>
                        <select name="status" class="form-select border-light-subtle rounded-3" required>
                            <option value="Selesai" <?= (($data['status'] ?? '') == 'Selesai') ? 'selected' : '' ?>>Selesai</option>
                            <option value="Proses" <?= (($data['status'] ?? '') == 'Proses') ? 'selected' : '' ?>>Proses</option>
                            <option value="Pending" <?= (($data['status'] ?? '') == 'Pending') ? 'selected' : '' ?>>Pending</option>
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold text-dark">Teknisi / Petugas</label>
                        <input type="text" name="teknisi" class="form-control border-light-subtle rounded-3" placeholder="Contoh: Nama Teknisi / Tim Maintenance" value="<?= htmlspecialchars($data['teknisi'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark">Tindakan Perbaikan / Maintenance <span class="text-danger">*</span></label>
                        <textarea name="tindakan" class="form-control border-light-subtle rounded-3" rows="3" placeholder="Rincian pekerjaan yang dilakukan..." required><?= htmlspecialchars($data['tindakan'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark">Sparepart Yang Digunakan / Diganti</label>
                        <input type="text" name="sparepart" class="form-control border-light-subtle rounded-3" placeholder="Contoh: Bearing 6204 (1 Pcs)" value="<?= htmlspecialchars($data['sparepart'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark">Catatan / Keterangan Tambahan</label>
                        <textarea name="catatan" class="form-control border-light-subtle rounded-3" rows="2" placeholder="Catatan lanjutan..."><?= htmlspecialchars($data['catatan'] ?? '') ?></textarea>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="d-flex gap-2 pt-3">
                <button type="submit" class="btn btn-primary fw-semibold px-4 rounded-3" style="background-color: #0056a6; border: none;">
                    <i class="bi bi-save me-1"></i> Update Data Maintenance
                </button>
                <a href="index.php" class="btn btn-light border px-4 rounded-3">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php include "../template/footer.php"; ?>

<!-- Script jQuery & Select2 Aman (Mencegah Double Load) -->
<script>
if (typeof jQuery == 'undefined') {
    document.write('<script src="https://code.jquery.com/jquery-3.6.0.min.js"><\/script>');
}
</script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    if (typeof jQuery !== 'undefined') {
        $(document).ready(function() {
            $('#select-komponen').select2({
                theme: 'bootstrap-5',
                placeholder: '-- Ketik untuk mencari Komponen / Serial Number --',
                allowClear: true,
                width: '100%'
            });

            // Otomatis isi seluruh field spesifikasi saat komponen dipilih
            $('#select-komponen').on('change', function() {
                var opt = $(this).find(':selected');
                
                $('#f-sn').val(opt.data('sn') || '');
                $('#f-namabagian').val(opt.data('namabagian') || '');
                $('#f-kategori').val(opt.data('kategori') || '');
                $('#f-mesin').val(opt.data('mesin') || '');
                $('#f-submesin').val(opt.data('submesin') || '');
                $('#f-lokasi').val(opt.data('lokasi') || '');
                $('#f-brand').val(opt.data('brand') || '');
                $('#f-tipe').val(opt.data('tipe') || '');
                $('#f-pn').val(opt.data('pn') || '');
                $('#f-daya').val(opt.data('daya') || '');
                $('#f-io').val(opt.data('io') || '');
                $('#f-vol').val(opt.data('vol') || '');
                $('#f-freqin').val(opt.data('freqin') || '');
                $('#f-arus').val(opt.data('arus') || '');
                $('#f-out').val(opt.data('out') || '');
                $('#f-freqout').val(opt.data('freqout') || '');
                $('#f-ip').val(opt.data('ip') || '');
            });

            // Handle Preview Foto
            $('#input-foto').on('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#image-preview').attr('src', e.target.result);
                        $('#preview-container').removeClass('d-none');
                    };
                    reader.readAsDataURL(file);
                }
            });

            $('#btn-remove-foto').on('click', function() {
                $('#input-foto').val('');
                // Jika ingin mereset preview kembali ke kosong (atau membiarkan foto lama jika batal ganti)
                $('#preview-container').addClass('d-none');
                $('#image-preview').attr('src', '');
            });
        });
    }
});
</script>
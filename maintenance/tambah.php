<?php
include "../koneksi.php";
date_default_timezone_set('Asia/Jakarta'); // Memastikan waktu WIB

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_komponen = mysqli_real_escape_string($conn, $_POST['id_komponen']);
    $status      = mysqli_real_escape_string($conn, $_POST['status']);
    $teknisi     = mysqli_real_escape_string($conn, $_POST['teknisi']);
    $tindakan    = mysqli_real_escape_string($conn, $_POST['tindakan']);
    $jenis       = mysqli_real_escape_string($conn, $_POST['jenis'] ?? '');
    $sparepart   = mysqli_real_escape_string($conn, $_POST['sparepart'] ?? '');
    $catatan     = mysqli_real_escape_string($conn, $_POST['catatan'] ?? '');

    // 1. Ambil tanggal dari input form (contoh: 2026-08-08)
    $input_tanggal = $_POST['tanggal']; 

    // 2. Ambil jam, menit, detik saat ini
    $jam_sekarang = date('H:i:s');

    // 3. Gabungkan tanggal & jam (Contoh: 2026-08-08 09:17:06)
    $tanggal_lengkap = $input_tanggal . ' ' . $jam_sekarang;

    // 4. Insert ke database
    $query = mysqli_query($conn, "
        INSERT INTO riwayat_maintenance (id_komponen, tanggal, status, teknisi, tindakan, jenis, sparepart, catatan) 
        VALUES ('$id_komponen', '$tanggal_lengkap', '$status', '$teknisi', '$tindakan', '$jenis', '$sparepart', '$catatan')
    ");

    if ($query) {
        // Jika status yang diinput adalah 'Selesai', otomatis update kondisi komponen menjadi 'Baik'
        if ($status == 'Selesai' && !empty($id_komponen)) {
            mysqli_query($conn, "UPDATE komponen SET kondisi = 'Baik' WHERE id = '$id_komponen'");
        }

        echo "<script>alert('Data maintenance berhasil ditambahkan!'); window.location='index.php';</script>";
        exit;
    } else {
        $error = "Gagal menyimpan data: " . mysqli_error($conn);
    }
}

// Ambil daftar komponen beserta nama mesin
$q_komponen = mysqli_query($conn, "
    SELECT k.id, k.nama_bagian, k.kode_mesin, m.nama_mesin 
    FROM komponen k 
    LEFT JOIN mesin m ON k.id_mesin = m.id 
    ORDER BY k.nama_bagian ASC
");

include "../template/header.php";
?>

<!-- Import CSS Select2 & Theme Bootstrap 5 -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<div class="container-fluid p-0">

  <!-- Header Card -->
  <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
    <div class="d-flex align-items-center gap-3">
      <a href="index.php" class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
        <i class="bi bi-arrow-left fs-5"></i>
      </a>
      <div>
        <h2 class="fw-bold text-dark m-0">Tambah Catatan Maintenance</h2>
        <p class="text-muted small m-0 mt-1">Lengkapi formulir di bawah ini untuk mencatat tindakan maintenance baru</p>
      </div>
    </div>
  </div>

  <!-- Form Content Card -->
  <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
    <h5 class="fw-bold text-primary mb-4 d-flex align-items-center" style="color: #0056a6 !important;">
      <i class="bi bi-plus-circle me-2"></i> Form Maintenance
    </h5>

    <?php if (!empty($error)) : ?>
      <div class="alert alert-danger border-0 rounded-3 mb-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error; ?>
      </div>
    <?php endif; ?>

    <form method="POST">
      
      <!-- Section: INFORMASI UTAMA -->
      <div class="mb-4">
        <h6 class="fw-bold text-primary small text-uppercase mb-3 d-flex align-items-center" style="letter-spacing: 0.5px; color: #0056a6 !important;">
          <i class="bi bi-info-circle me-2"></i> INFORMASI UTAMA
        </h6>
        
        <div class="row g-3">
          <!-- Dropdown Komponen -->
          <div class="col-md-6">
            <label class="form-label fw-semibold text-dark">Komponen Terkait <span class="text-danger">*</span></label>
            <select name="id_komponen" id="select-komponen" class="form-select border-light-subtle rounded-3" required>
              <option value="">-- Cari / Pilih Komponen --</option>
              <?php while ($k = mysqli_fetch_assoc($q_komponen)) : ?>
                <option value="<?= $k['id'] ?>">
                  <?= htmlspecialchars($k['nama_bagian']) ?> — <?= htmlspecialchars($k['nama_mesin'] ?: 'Tanpa Mesin') ?> (<?= htmlspecialchars($k['kode_mesin'] ?: '-') ?>)
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label class="form-label fw-semibold text-dark">Tanggal Maintenance <span class="text-danger">*</span></label>
            <input type="date" name="tanggal" class="form-control border-light-subtle rounded-3" value="<?= date('Y-m-d') ?>" required>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-semibold text-dark">Jenis Maintenance</label>
            <select name="jenis" class="form-select border-light-subtle rounded-3">
              <option value="Preventive">Preventive</option>
              <option value="Corrective">Corrective</option>
              <option value="Breakdown">Breakdown</option>
              <option value="Predictive">Predictive</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label fw-semibold text-dark">Teknisi / Petugas</label>
            <input type="text" name="teknisi" class="form-control border-light-subtle rounded-3" placeholder="Contoh: Nama Teknisi / Tim Maintenance">
          </div>

          <div class="col-md-4">
            <label class="form-label fw-semibold text-dark">Status Pekerjaan <span class="text-danger">*</span></label>
            <select name="status" class="form-select border-light-subtle rounded-3" required>
              <option value="Selesai">Selesai</option>
              <option value="Proses">Proses</option>
              <option value="Pending">Pending</option>
            </select>
          </div>
        </div>
      </div>

      <hr class="my-4 border-light-subtle">

      <!-- Section: RINCIAN MAINTENANCE -->
      <div class="mb-4">
        <h6 class="fw-bold text-primary small text-uppercase mb-3 d-flex align-items-center" style="letter-spacing: 0.5px; color: #0056a6 !important;">
          <i class="bi bi-wrench me-2"></i> RINCIAN MAINTENANCE
        </h6>

        <div class="row g-3">
          <div class="col-12">
            <label class="form-label fw-semibold text-dark">Tindakan Perbaikan / Maintenance <span class="text-danger">*</span></label>
            <textarea name="tindakan" class="form-control border-light-subtle rounded-3" rows="3" placeholder="Rincian pekerjaan yang dilakukan..." required></textarea>
          </div>

          <div class="col-12">
            <label class="form-label fw-semibold text-dark">Sparepart Yang Digunakan / Diganti</label>
            <input type="text" name="sparepart" class="form-control border-light-subtle rounded-3" placeholder="Contoh: Bearing 6204 (1 Pcs), O-Ring Kit">
          </div>

          <div class="col-12">
            <label class="form-label fw-semibold text-dark">Catatan / Keterangan Tambahan</label>
            <textarea name="catatan" class="form-control border-light-subtle rounded-3" rows="2" placeholder="Catatan lanjutan, rekomendasi tindakan berikutnya..."></textarea>
          </div>
        </div>
      </div>

      <!-- Submit Actions -->
      <div class="d-flex gap-2 pt-3">
        <button type="submit" name="simpan" class="btn btn-primary fw-semibold px-4 rounded-3" style="background-color: #0056a6; border: none;">
          <i class="bi bi-save me-1"></i> Simpan Data
        </button>
        <a href="index.php" class="btn btn-light border px-4 rounded-3">Batal</a>
      </div>

    </form>
  </div>

</div>

<?php include "../template/footer.php"; ?>

<!-- Import Script jQuery & Select2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
  $(document).ready(function() {
    $('#select-komponen').select2({
      theme: 'bootstrap-5',
      placeholder: '-- Ketik untuk mencari Komponen / Kode Mesin --',
      allowClear: true,
      width: '100%'
    });
  });
</script>
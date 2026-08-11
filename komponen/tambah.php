<?php
include "../koneksi.php";

$error = "";

if (isset($_POST['simpan'])) {
    $serial_number      = trim($_POST['serial_number']);
    $id_area            = !empty($_POST['id_area']) ? intval($_POST['id_area']) : NULL;
    $id_jenis_mesin     = !empty($_POST['id_jenis_mesin']) ? intval($_POST['id_jenis_mesin']) : NULL;
    $id_mesin           = !empty($_POST['id_mesin']) ? intval($_POST['id_mesin']) : NULL;
    $id_sub_mesin       = !empty($_POST['id_sub_mesin']) ? intval($_POST['id_sub_mesin']) : NULL;
    
    $mesin_str      = "";
    $sub_mesin_str  = "";

    // Ambil nama mesin jika ID dipilih
    if ($id_mesin) {
        $stmt_m = mysqli_prepare($conn, "SELECT nama_mesin FROM mesin WHERE id = ?");
        mysqli_stmt_bind_param($stmt_m, "i", $id_mesin);
        mysqli_stmt_execute($stmt_m);
        $res_m = mysqli_stmt_get_result($stmt_m);
        if ($rm = mysqli_fetch_assoc($res_m)) { 
            $mesin_str = $rm['nama_mesin']; 
        }
        mysqli_stmt_close($stmt_m);
    }

    // Ambil nama sub mesin jika ID dipilih
    if ($id_sub_mesin) {
        $stmt_s = mysqli_prepare($conn, "SELECT nama_sub_mesin FROM sub_mesin WHERE id = ?");
        mysqli_stmt_bind_param($stmt_s, "i", $id_sub_mesin);
        mysqli_stmt_execute($stmt_s);
        $res_s = mysqli_stmt_get_result($stmt_s);
        if ($rs = mysqli_fetch_assoc($res_s)) { 
            $sub_mesin_str = $rs['nama_sub_mesin']; 
        }
        mysqli_stmt_close($stmt_s);
    }

    $nama_bagian      = trim($_POST['nama_bagian']);
    $kategori         = trim($_POST['kategori']);
    $brand            = trim($_POST['brand']);
    $tipe             = trim($_POST['tipe']);
    $part_number      = trim($_POST['part_number']);
    $daya             = trim($_POST['daya']);
    $io_address       = trim($_POST['io_address']);
    $input_voltage    = trim($_POST['input_voltage']);
    $frekuensi_input  = trim($_POST['frekuensi_input']);
    $arus_input       = trim($_POST['arus_input']);
    $output           = trim($_POST['output']);
    $frekuensi_output = trim($_POST['frekuensi_output']);
    $ip_rating        = trim($_POST['ip_rating']);
    $lokasi           = trim($_POST['lokasi']);
    $kondisi          = trim($_POST['kondisi']);
    $keterangan       = trim($_POST['keterangan']);

    // Process Upload Gambar
    $nama_gambar = "";
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp   = $_FILES['gambar']['tmp_name'];
        $file_name  = $_FILES['gambar']['name'];
        $file_size  = $_FILES['gambar']['size'];
        $file_ext   = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp'];
        $max_size     = 2 * 1024 * 1024; // Limit 2MB

        if (!in_array($file_ext, $allowed_exts)) {
            $error = "Format file gambar harus JPG, JPEG, PNG, atau WEBP!";
        } elseif ($file_size > $max_size) {
            $error = "Ukuran gambar tidak boleh lebih dari 2MB!";
        } else {
            $nama_gambar = "KMP_" . time() . "_" . rand(100, 999) . "." . $file_ext;
            $target_dir  = "../uploads/komponen/";

            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            if (!move_uploaded_file($file_tmp, $target_dir . $nama_gambar)) {
                $error = "Gagal mengunggah gambar komponen.";
            }
        }
    }

    if (empty($nama_bagian)) {
        $error = "Nama Bagian wajib diisi!";
    }

    if (empty($error)) {
        $query = "INSERT INTO komponen (
                    serial_number, id_sub_mesin, mesin, sub_mesin, nama_bagian, kategori, 
                    brand, tipe, part_number, daya, io_address, input_voltage, 
                    frekuensi_input, arus_input, output, frekuensi_output, ip_rating, 
                    lokasi, kondisi, keterangan, gambar
                  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param(
            $stmt, 
            "sisssssssssssssssssss", 
            $serial_number, $id_sub_mesin, $mesin_str, $sub_mesin_str, $nama_bagian, $kategori,
            $brand, $tipe, $part_number, $daya, $io_address, $input_voltage,
            $frekuensi_input, $arus_input, $output, $frekuensi_output, $ip_rating,
            $lokasi, $kondisi, $keterangan, $nama_gambar
        );

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: index.php");
            exit;
        } else {
            $error = "Gagal menyimpan data: " . mysqli_error($conn);
        }
    }
}

// Ambil data Area / Bagian untuk dropdown pertama
$q_area = mysqli_query($conn, "SELECT id, nama_area FROM area_bagian ORDER BY nama_area ASC");

include "../template/header.php";
?>

<div class="container-fluid p-0">

  <!-- HEADER -->
  <div class="dashboard-header mb-3 py-3 px-4">
    <div class="d-flex align-items-center gap-3">
      <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px; flex-shrink: 0;">
        <i class="bi bi-arrow-left fs-5"></i>
      </a>
      <div>
        <h3 class="dashboard-title m-0 fs-4 fw-bold">Tambah Komponen Baru</h3>
        <p class="dashboard-subtitle m-0 small text-muted">Lengkapi formulir di bawah ini untuk mendaftarkan komponen/part baru</p>
      </div>
    </div>
  </div>

  <!-- FORM CARD -->
  <div class="content-card mb-3">
    <div class="card-header-custom py-2 px-3">
      <h6 class="card-title-custom m-0 fw-bold">
        <i class="bi bi-plus-circle me-2"></i>Form Input Komponen
      </h6>
    </div>
    
    <div class="card-body-custom p-3">
      <?php if (!empty($error)) : ?>
        <div class="alert alert-danger border-0 d-flex align-items-center py-2 px-3 mb-3" role="alert">
          <i class="bi bi-exclamation-triangle-fill fs-6 me-2"></i>
          <div class="small"><?= htmlspecialchars($error); ?></div>
        </div>
      <?php endif; ?>

      <form method="POST" enctype="multipart/form-data">
        <!-- Section 1 -->
        <h6 class="fw-bold text-primary mb-3 small"><i class="bi bi-info-circle me-1"></i> INFORMASI UMUM & FOTO</h6>
        <div class="row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label fw-semibold text-dark small mb-1">Serial Number (SN)</label>
            <input type="text" name="serial_number" class="form-control form-control-sm" placeholder="Contoh: SN-8829102" value="<?= isset($_POST['serial_number']) ? htmlspecialchars($_POST['serial_number']) : '' ?>">
          </div>
          <div class="col-md-5">
            <label class="form-label fw-semibold text-dark small mb-1">Nama Bagian <span class="text-danger">*</span></label>
            <input type="text" name="nama_bagian" class="form-control form-control-sm" placeholder="Contoh: Inverter / Motor Conveyor" required value="<?= isset($_POST['nama_bagian']) ? htmlspecialchars($_POST['nama_bagian']) : '' ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold text-dark small mb-1">Kategori</label>
            <input type="text" name="kategori" class="form-control form-control-sm" placeholder="Contoh: Kelistrikan / Mekanik" value="<?= isset($_POST['kategori']) ? htmlspecialchars($_POST['kategori']) : '' ?>">
          </div>
          
          <!-- Tambahan Dropdown Area & Jenis Mesin agar sinkron dengan database baru -->
          <div class="col-md-3">
            <label class="form-label fw-semibold text-dark small mb-1">Area / Bagian</label>
            <select name="id_area" id="form_area" class="form-select form-select-sm" onchange="loadJenisMesin(this.value)">
              <option value="">-- Pilih Area --</option>
              <?php while ($a = mysqli_fetch_assoc($q_area)) : ?>
                <option value="<?= $a['id'] ?>">
                  <?= htmlspecialchars($a['nama_area']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold text-dark small mb-1">Jenis Mesin</label>
            <select name="id_jenis_mesin" id="form_jenis_mesin" class="form-select form-select-sm" onchange="loadMesin(this.value)">
              <option value="">-- Pilih Jenis Mesin --</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold text-dark small mb-1">Mesin Induk</label>
            <select name="id_mesin" id="form_mesin" class="form-select form-select-sm" onchange="loadSubMesinForm(this.value)">
              <option value="">-- Pilih Mesin --</option>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold text-dark small mb-1">Sub Mesin</label>
            <select name="id_sub_mesin" id="form_sub_mesin" class="form-select form-select-sm">
              <option value="">-- Pilih Sub Mesin --</option>
            </select>
          </div>

          <div class="col-md-12">
            <label class="form-label fw-semibold text-dark small mb-1">Lokasi Penempatan</label>
            <input type="text" name="lokasi" class="form-control form-control-sm" placeholder="Contoh: Panel Utama / Line 1" value="<?= isset($_POST['lokasi']) ? htmlspecialchars($_POST['lokasi']) : '' ?>">
          </div>
          <div class="col-md-12">
            <label class="form-label fw-semibold text-dark small mb-1">Foto Komponen / Part</label>
            <input type="file" name="gambar" class="form-control form-control-sm" accept="image/png, image/jpeg, image/jpg, image/webp">
            <div class="form-text text-muted small">Format disarankan: JPG, PNG, WEBP (Maksimal 2MB).</div>
          </div>
        </div>

        <hr class="my-3 text-muted opacity-25">

        <!-- Section 2 -->
        <h6 class="fw-bold text-primary mb-3 small"><i class="bi bi-tools me-1"></i> SPESIFIKASI & BRAND</h6>
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label fw-semibold text-dark small mb-1">Brand / Merk</label>
            <input type="text" name="brand" class="form-control form-control-sm" placeholder="Contoh: Schneider / Siemens" value="<?= isset($_POST['brand']) ? htmlspecialchars($_POST['brand']) : '' ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold text-dark small mb-1">Tipe</label>
            <input type="text" name="tipe" class="form-control form-control-sm" placeholder="Contoh: ATV320 / CPU 314C-2" value="<?= isset($_POST['tipe']) ? htmlspecialchars($_POST['tipe']) : '' ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold text-dark small mb-1">Part Number</label>
            <input type="text" name="part_number" class="form-control form-control-sm" placeholder="Contoh: PN-99201" value="<?= isset($_POST['part_number']) ? htmlspecialchars($_POST['part_number']) : '' ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold text-dark small mb-1">Daya</label>
            <input type="text" name="daya" class="form-control form-control-sm" placeholder="Contoh: 1.5 kW" value="<?= isset($_POST['daya']) ? htmlspecialchars($_POST['daya']) : '' ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold text-dark small mb-1">IO Address</label>
            <input type="text" name="io_address" class="form-control form-control-sm" placeholder="Contoh: I:0/1" value="<?= isset($_POST['io_address']) ? htmlspecialchars($_POST['io_address']) : '' ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold text-dark small mb-1">Input Voltage</label>
            <input type="text" name="input_voltage" class="form-control form-control-sm" placeholder="Contoh: 380V AC" value="<?= isset($_POST['input_voltage']) ? htmlspecialchars($_POST['input_voltage']) : '' ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold text-dark small mb-1">Frekuensi Input</label>
            <input type="text" name="frekuensi_input" class="form-control form-control-sm" placeholder="Contoh: 50/60 Hz" value="<?= isset($_POST['frekuensi_input']) ? htmlspecialchars($_POST['frekuensi_input']) : '' ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold text-dark small mb-1">Arus Input</label>
            <input type="text" name="arus_input" class="form-control form-control-sm" placeholder="Contoh: 5A" value="<?= isset($_POST['arus_input']) ? htmlspecialchars($_POST['arus_input']) : '' ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold text-dark small mb-1">Output</label>
            <input type="text" name="output" class="form-control form-control-sm" placeholder="Contoh: 0-220V" value="<?= isset($_POST['output']) ? htmlspecialchars($_POST['output']) : '' ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold text-dark small mb-1">Frekuensi Output</label>
            <input type="text" name="frekuensi_output" class="form-control form-control-sm" placeholder="Contoh: 0-400 Hz" value="<?= isset($_POST['frekuensi_output']) ? htmlspecialchars($_POST['frekuensi_output']) : '' ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold text-dark small mb-1">IP Rating</label>
            <input type="text" name="ip_rating" class="form-control form-control-sm" placeholder="Contoh: IP65" value="<?= isset($_POST['ip_rating']) ? htmlspecialchars($_POST['ip_rating']) : '' ?>">
          </div>
        </div>

        <hr class="my-3 text-muted opacity-25">

        <!-- Section 3 -->
        <h6 class="fw-bold text-primary mb-3 small"><i class="bi bi-card-checklist me-1"></i> STATUS KOMPONEN</h6>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label fw-semibold text-dark small mb-1">Kondisi <span class="text-danger">*</span></label>
            <select name="kondisi" class="form-select form-select-sm" required>
              <option value="Baik" <?= (isset($_POST['kondisi']) && $_POST['kondisi'] === 'Baik') ? 'selected' : '' ?>>Baik</option>
              <option value="Perlu Pemeriksaan" <?= (isset($_POST['kondisi']) && $_POST['kondisi'] === 'Perlu Pemeriksaan') ? 'selected' : '' ?>>Perlu Pemeriksaan</option>
              <option value="Dalam Perbaikan" <?= (isset($_POST['kondisi']) && $_POST['kondisi'] === 'Dalam Perbaikan') ? 'selected' : '' ?>>Dalam Perbaikan</option>
            </select>
          </div>
          <div class="col-md-8">
            <label class="form-label fw-semibold text-dark small mb-1">Keterangan Tambahan</label>
            <textarea name="keterangan" class="form-control form-control-sm" rows="2" placeholder="Catatan kondisi atau deskripsi tambahan..."><?= isset($_POST['keterangan']) ? htmlspecialchars($_POST['keterangan']) : '' ?></textarea>
          </div>
        </div>

        <div class="border-top mt-3 pt-3 d-flex align-items-center gap-2">
          <button type="submit" name="simpan" class="btn btn-primary px-4 btn-sm fw-semibold">
            <i class="bi bi-check-lg me-1"></i> Simpan Data
          </button>
          <a href="index.php" class="btn btn-light border px-4 btn-sm fw-semibold text-secondary">
            Batal
          </a>
        </div>
      </form>

    </div>
  </div>

</div>

<script>
function loadJenisMesin(id_area) {
  const jenisSelect = document.getElementById('form_jenis_mesin');
  const mesinSelect = document.getElementById('form_mesin');
  const subSelect = document.getElementById('form_sub_mesin');
  
  jenisSelect.innerHTML = '<option value="">-- Pilih Jenis Mesin --</option>';
  mesinSelect.innerHTML = '<option value="">-- Pilih Mesin --</option>';
  subSelect.innerHTML = '<option value="">-- Pilih Sub Mesin --</option>';

  if (!id_area) return;

  fetch('get_jenis_mesin.php?id_area=' + id_area)
    .then(response => response.text())
    .then(data => { jenisSelect.innerHTML = data; })
    .catch(err => console.error('Gagal memuat Jenis Mesin:', err));
}

function loadMesin(id_jenis) {
  const mesinSelect = document.getElementById('form_mesin');
  const subSelect = document.getElementById('form_sub_mesin');
  
  mesinSelect.innerHTML = '<option value="">-- Pilih Mesin --</option>';
  subSelect.innerHTML = '<option value="">-- Pilih Sub Mesin --</option>';

  if (!id_jenis) return;

  fetch('get_mesin.php?id_jenis=' + id_jenis)
    .then(response => response.text())
    .then(data => { mesinSelect.innerHTML = data; })
    .catch(err => console.error('Gagal memuat Mesin:', err));
}

function loadSubMesinForm(id_mesin) {
  const subSelect = document.getElementById('form_sub_mesin');
  subSelect.innerHTML = '<option value="">-- Pilih Sub Mesin --</option>';

  if (!id_mesin) return;

  fetch('get_sub_mesin.php?id_mesin=' + id_mesin)
    .then(response => response.text())
    .then(data => { subSelect.innerHTML = data; })
    .catch(err => console.error('Gagal memuat Sub Mesin:', err));
}
</script>

<?php include "../template/footer.php"; ?>
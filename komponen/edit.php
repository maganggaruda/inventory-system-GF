<?php
include "../koneksi.php";

$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

$data = mysqli_query($conn, "SELECT * FROM komponen WHERE id='$id'");
$d    = mysqli_fetch_assoc($data);

if (!$d) {
    header("Location: index.php");
    exit;
}

$error = "";

if (isset($_POST['update'])) {
    $kode_mesin       = mysqli_real_escape_string($conn, trim($_POST['kode_mesin']));
    $id_mesin         = !empty($_POST['id_mesin']) ? mysqli_real_escape_string($conn, $_POST['id_mesin']) : 'NULL';
    $id_sub_mesin     = !empty($_POST['id_sub_mesin']) ? mysqli_real_escape_string($conn, $_POST['id_sub_mesin']) : 'NULL';
    
    $mesin_str = "";
    $sub_mesin_str = "";
    if ($id_mesin !== 'NULL') {
        $qm = mysqli_query($conn, "SELECT nama_mesin FROM mesin WHERE id = $id_mesin");
        if ($rm = mysqli_fetch_assoc($qm)) { $mesin_str = mysqli_real_escape_string($conn, $rm['nama_mesin']); }
    }
    if ($id_sub_mesin !== 'NULL') {
        $qs = mysqli_query($conn, "SELECT nama_sub_mesin FROM sub_mesin WHERE id = $id_sub_mesin");
        if ($rs = mysqli_fetch_assoc($qs)) { $sub_mesin_str = mysqli_real_escape_string($conn, $rs['nama_sub_mesin']); }
    }

    $nama_bagian      = mysqli_real_escape_string($conn, trim($_POST['nama_bagian']));
    $kategori         = mysqli_real_escape_string($conn, trim($_POST['kategori']));
    $brand            = mysqli_real_escape_string($conn, trim($_POST['brand']));
    $tipe             = mysqli_real_escape_string($conn, trim($_POST['tipe']));
    $part_number      = mysqli_real_escape_string($conn, trim($_POST['part_number']));
    $daya             = mysqli_real_escape_string($conn, trim($_POST['daya']));
    $io_address       = mysqli_real_escape_string($conn, trim($_POST['io_address']));
    $input_voltage    = mysqli_real_escape_string($conn, trim($_POST['input_voltage']));
    $frekuensi_input  = mysqli_real_escape_string($conn, trim($_POST['frekuensi_input']));
    $arus_input       = mysqli_real_escape_string($conn, trim($_POST['arus_input']));
    $output           = mysqli_real_escape_string($conn, trim($_POST['output']));
    $frekuensi_output = mysqli_real_escape_string($conn, trim($_POST['frekuensi_output']));
    $ip_rating        = mysqli_real_escape_string($conn, trim($_POST['ip_rating']));
    $lokasi           = mysqli_real_escape_string($conn, trim($_POST['lokasi']));
    $kondisi          = mysqli_real_escape_string($conn, $_POST['kondisi']);
    $keterangan       = mysqli_real_escape_string($conn, trim($_POST['keterangan']));

    if (empty($nama_bagian)) {
        $error = "Nama Bagian wajib diisi!";
    } else {
        $query_update = "UPDATE komponen SET 
                            kode_mesin='$kode_mesin',
                            id_mesin=$id_mesin,
                            id_sub_mesin=$id_sub_mesin,
                            mesin='$mesin_str',
                            sub_mesin='$sub_mesin_str',
                            nama_bagian='$nama_bagian',
                            kategori='$kategori',
                            brand='$brand',
                            tipe='$tipe',
                            part_number='$part_number',
                            daya='$daya',
                            io_address='$io_address',
                            input_voltage='$input_voltage',
                            frekuensi_input='$frekuensi_input',
                            arus_input='$arus_input',
                            output='$output',
                            frekuensi_output='$frekuensi_output',
                            ip_rating='$ip_rating',
                            lokasi='$lokasi',
                            kondisi='$kondisi',
                            keterangan='$keterangan'
                         WHERE id='$id'";

        if (mysqli_query($conn, $query_update)) {
            header("Location: index.php");
            exit;
        } else {
            $error = "Gagal memperbarui data: " . mysqli_error($conn);
        }
    }
}

$q_mesin = mysqli_query($conn, "SELECT id, nama_mesin FROM mesin ORDER BY nama_mesin ASC");

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
        <h3 class="dashboard-title m-0 fs-4 fw-bold">Edit Komponen</h3>
        <p class="dashboard-subtitle m-0 small text-muted">Perbarui rincian spesifikasi dan data komponen</p>
      </div>
    </div>
  </div>

  <!-- FORM CARD -->
  <div class="content-card mb-3">
    <div class="card-header-custom py-2 px-3">
      <h6 class="card-title-custom m-0 fw-bold">
        <i class="bi bi-pencil-square me-2"></i>Form Edit Komponen
      </h6>
    </div>
    
    <div class="card-body-custom p-3">
      <?php if (!empty($error)) : ?>
        <div class="alert alert-danger border-0 d-flex align-items-center py-2 px-3 mb-3" role="alert">
          <i class="bi bi-exclamation-triangle-fill fs-6 me-2"></i>
          <div class="small"><?= $error; ?></div>
        </div>
      <?php endif; ?>

      <form method="POST">
        <h6 class="fw-bold text-primary mb-3 small"><i class="bi bi-info-circle me-1"></i> INFORMASI UMUM</h6>
        <div class="row g-3 mb-3">
          <div class="col-md-3">
            <label class="form-label fw-semibold text-dark small mb-1">Kode Mesin</label>
            <input type="text" name="kode_mesin" class="form-control form-control-sm" value="<?= htmlspecialchars($d['kode_mesin']) ?>">
          </div>
          <div class="col-md-5">
            <label class="form-label fw-semibold text-dark small mb-1">Nama Bagian <span class="text-danger">*</span></label>
            <input type="text" name="nama_bagian" class="form-control form-control-sm" value="<?= htmlspecialchars($d['nama_bagian']) ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold text-dark small mb-1">Kategori</label>
            <input type="text" name="kategori" class="form-control form-control-sm" value="<?= htmlspecialchars($d['kategori']) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold text-dark small mb-1">Mesin Induk</label>
            <select name="id_mesin" id="form_mesin" class="form-select form-select-sm" onchange="loadSubMesinForm(this.value)">
              <option value="">-- Pilih Mesin --</option>
              <?php while ($m = mysqli_fetch_assoc($q_mesin)) : ?>
                <option value="<?= $m['id'] ?>" <?= ($m['id'] == $d['id_mesin']) ? 'selected' : '' ?>>
                  <?= htmlspecialchars($m['nama_mesin']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold text-dark small mb-1">Sub Mesin</label>
            <select name="id_sub_mesin" id="form_sub_mesin" class="form-select form-select-sm">
              <option value="">-- Pilih Sub Mesin --</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold text-dark small mb-1">Lokasi Penempatan</label>
            <input type="text" name="lokasi" class="form-control form-control-sm" value="<?= htmlspecialchars($d['lokasi']) ?>">
          </div>
        </div>

        <hr class="my-3 text-muted opacity-25">

        <h6 class="fw-bold text-primary mb-3 small"><i class="bi bi-tools me-1"></i> SPESIFIKASI & BRAND</h6>
        <div class="row g-3 mb-3">
          <div class="col-md-4">
            <label class="form-label fw-semibold text-dark small mb-1">Brand / Merk</label>
            <input type="text" name="brand" class="form-control form-control-sm" value="<?= htmlspecialchars($d['brand']) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold text-dark small mb-1">Tipe</label>
            <input type="text" name="tipe" class="form-control form-control-sm" value="<?= htmlspecialchars($d['tipe']) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label fw-semibold text-dark small mb-1">Part Number</label>
            <input type="text" name="part_number" class="form-control form-control-sm" value="<?= htmlspecialchars($d['part_number']) ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold text-dark small mb-1">Daya</label>
            <input type="text" name="daya" class="form-control form-control-sm" value="<?= htmlspecialchars($d['daya']) ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold text-dark small mb-1">IO Address</label>
            <input type="text" name="io_address" class="form-control form-control-sm" value="<?= htmlspecialchars($d['io_address']) ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold text-dark small mb-1">Input Voltage</label>
            <input type="text" name="input_voltage" class="form-control form-control-sm" value="<?= htmlspecialchars($d['input_voltage']) ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold text-dark small mb-1">Frekuensi Input</label>
            <input type="text" name="frekuensi_input" class="form-control form-control-sm" value="<?= htmlspecialchars($d['frekuensi_input']) ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold text-dark small mb-1">Arus Input</label>
            <input type="text" name="arus_input" class="form-control form-control-sm" value="<?= htmlspecialchars($d['arus_input']) ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold text-dark small mb-1">Output</label>
            <input type="text" name="output" class="form-control form-control-sm" value="<?= htmlspecialchars($d['output']) ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold text-dark small mb-1">Frekuensi Output</label>
            <input type="text" name="frekuensi_output" class="form-control form-control-sm" value="<?= htmlspecialchars($d['frekuensi_output']) ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label fw-semibold text-dark small mb-1">IP Rating</label>
            <input type="text" name="ip_rating" class="form-control form-control-sm" value="<?= htmlspecialchars($d['ip_rating']) ?>">
          </div>
        </div>

        <hr class="my-3 text-muted opacity-25">

        <h6 class="fw-bold text-primary mb-3 small"><i class="bi bi-card-checklist me-1"></i> STATUS KOMPONEN</h6>
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label fw-semibold text-dark small mb-1">Kondisi <span class="text-danger">*</span></label>
            <select name="kondisi" class="form-select form-select-sm" required>
              <option value="Baik" <?= ($d['kondisi'] == 'Baik') ? 'selected' : '' ?>>Baik</option>
              <option value="Perlu Pemeriksaan" <?= ($d['kondisi'] == 'Perlu Pemeriksaan') ? 'selected' : '' ?>>Perlu Pemeriksaan</option>
              <option value="Dalam Perbaikan" <?= ($d['kondisi'] == 'Dalam Perbaikan') ? 'selected' : '' ?>>Dalam Perbaikan</option>
            </select>
          </div>
          <div class="col-md-8">
            <label class="form-label fw-semibold text-dark small mb-1">Keterangan Tambahan</label>
            <textarea name="keterangan" class="form-control form-control-sm" rows="2"><?= htmlspecialchars($d['keterangan']) ?></textarea>
          </div>
        </div>

        <div class="border-top mt-3 pt-3 d-flex align-items-center gap-2">
          <button type="submit" name="update" class="btn btn-warning btn-sm fw-semibold text-dark px-4" style="background-color: var(--warning); border: none;">
            <i class="bi bi-check-lg me-1"></i> Update Data
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
function loadSubMesinForm(id_mesin, selectedSub = '') {
  const xhr = new XMLHttpRequest();
  xhr.open('GET', 'get_sub_mesin.php?id_mesin=' + id_mesin, true);
  xhr.onload = function() {
    if (this.status === 200) {
      document.getElementById('form_sub_mesin').innerHTML = this.responseText;
      if (selectedSub !== '') {
        document.getElementById('form_sub_mesin').value = selectedSub;
      }
    }
  };
  xhr.send();
}

document.addEventListener("DOMContentLoaded", function() {
  const currentMesin = "<?= $d['id_mesin'] ?>";
  const currentSub   = "<?= $d['id_sub_mesin'] ?>";
  if (currentMesin !== "") {
    loadSubMesinForm(currentMesin, currentSub);
  }
});
</script>

<?php include "../template/footer.php"; ?>
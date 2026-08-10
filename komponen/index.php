<?php
include "../koneksi.php";
include "../template/header.php";

// Parameter Filter
$keyword  = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$id_mesin = isset($_GET['id_mesin']) ? intval($_GET['id_mesin']) : 0;
$id_sub   = isset($_GET['id_sub_mesin']) ? intval($_GET['id_sub_mesin']) : 0;
$kondisi  = isset($_GET['kondisi']) ? trim($_GET['kondisi']) : '';

// Konstruksi Prepared Statement Dinamis
$where_conditions = ["1=1"];
$params = [];
$types  = "";

// Filter Keyword (Menggunakan serial_number, bukan kode_mesin)
if (!empty($keyword)) {
    $where_conditions[] = "(k.nama_bagian LIKE ? OR k.serial_number LIKE ? OR k.part_number LIKE ? OR k.brand LIKE ? OR k.kategori LIKE ?)";
    $searchTerm = "%{$keyword}%";
    for ($i = 0; $i < 5; $i++) {
        $params[] = $searchTerm;
        $types   .= "s";
    }
}

if (!empty($id_mesin)) {
    $where_conditions[] = "k.id_mesin = ?";
    $params[] = $id_mesin;
    $types   .= "i";
}

if (!empty($id_sub)) {
    $where_conditions[] = "k.id_sub_mesin = ?";
    $params[] = $id_sub;
    $types   .= "i";
}

if (!empty($kondisi)) {
    $where_conditions[] = "k.kondisi = ?";
    $params[] = $kondisi;
    $types   .= "s";
}

$where_clause = implode(" AND ", $where_conditions);

// Query Utama
$query = "SELECT k.*, m.nama_mesin as nama_mesin_relasi, sm.nama_sub_mesin as nama_sub_relasi 
          FROM komponen k
          LEFT JOIN mesin m ON k.id_mesin = m.id
          LEFT JOIN sub_mesin sm ON k.id_sub_mesin = sm.id
          WHERE $where_clause
          ORDER BY k.id ASC";

$stmt = mysqli_prepare($conn, $query);

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$sql = mysqli_stmt_get_result($stmt);

// Ambil List Mesin Induk untuk Dropdown Filter
$q_mesin = mysqli_query($conn, "SELECT id, nama_mesin FROM mesin ORDER BY nama_mesin ASC");
?>

<div class="container-fluid p-0">

  <!-- PAGE HEADER -->
  <div class="dashboard-header mb-3 py-3 px-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div>
        <h3 class="dashboard-title m-0 fs-4 fw-bold">Data Komponen / Part</h3>
        <p class="dashboard-subtitle m-0 small text-muted">Kelola daftar komponen dan sparepart mesin</p>
      </div>
      <a href="tambah.php" class="btn btn-primary px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
        <i class="bi bi-plus-lg fs-6"></i>
        <span>Tambah Komponen</span>
      </a>
    </div>
  </div>

  <!-- CARD FILTER -->
  <div class="content-card mb-3">
    <div class="card-body-custom p-3">
      <form method="GET" id="filterForm" class="row g-2 align-items-end">
        
        <!-- MESIN -->
        <div class="col-md-3">
          <label class="form-label small fw-semibold text-dark mb-1">Mesin</label>
          <select name="id_mesin" id="filter_mesin" class="form-select form-select-sm" onchange="loadSubMesinFilter(this.value)">
            <option value="">-- Semua Mesin --</option>
            <?php while ($m = mysqli_fetch_assoc($q_mesin)) : ?>
              <option value="<?= $m['id'] ?>" <?= ($id_mesin == $m['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($m['nama_mesin']) ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>

        <!-- SUB MESIN -->
        <div class="col-md-3">
          <label class="form-label small fw-semibold text-dark mb-1">Sub Mesin</label>
          <select name="id_sub_mesin" id="filter_sub_mesin" class="form-select form-select-sm">
            <option value="">-- Semua Sub Mesin --</option>
          </select>
        </div>

        <!-- KONDISI -->
        <div class="col-md-2">
          <label class="form-label small fw-semibold text-dark mb-1">Kondisi</label>
          <select name="kondisi" class="form-select form-select-sm">
            <option value="">-- Semua Kondisi --</option>
            <option value="Baik" <?= ($kondisi === 'Baik') ? 'selected' : '' ?>>Baik</option>
            <option value="Perlu Pemeriksaan" <?= ($kondisi === 'Perlu Pemeriksaan') ? 'selected' : '' ?>>Perlu Pemeriksaan</option>
            <option value="Dalam Perbaikan" <?= ($kondisi === 'Dalam Perbaikan') ? 'selected' : '' ?>>Dalam Perbaikan</option>
          </select>
        </div>

        <!-- KATA KUNCI -->
        <div class="col-md-3">
          <label class="form-label small fw-semibold text-dark mb-1">Kata Kunci</label>
          <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Nama Bagian / SN / Part No..." value="<?= htmlspecialchars($keyword) ?>">
        </div>

        <!-- BUTTON ACTION -->
        <div class="col-md-1 d-flex gap-1">
          <button type="submit" class="btn btn-sm btn-primary w-100" title="Terapkan Filter">
            <i class="bi bi-funnel"></i>
          </button>
          <a href="index.php" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
            <i class="bi bi-arrow-counterclockwise"></i>
          </a>
        </div>
      </form>
    </div>
  </div>

  <!-- TABLE CARD -->
  <div class="content-card mb-3">
    <div class="card-header-custom py-2 px-3 d-flex align-items-center justify-content-between">
      <h6 class="card-title-custom m-0 fw-bold">
        <i class="bi bi-cpu me-2"></i>Daftar Komponen
      </h6>
      <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-7">
        Total: <?= mysqli_num_rows($sql) ?> Data
      </span>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th width="40" class="text-center">NO</th>
            <th width="60" class="text-center">FOTO</th>
            <th>SERIAL NUMBER & NAMA BAGIAN</th>
            <th>BRAND / TIPE / PART NO</th>
            <th>MESIN & SUB MESIN</th>
            <th>KATEGORI</th>
            <th>KONDISI</th>
            <th width="120" class="text-center">AKSI</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $no = 1;
          if ($sql && mysqli_num_rows($sql) > 0) :
            while ($d = mysqli_fetch_assoc($sql)) :

              $badgeKondisi = 'bg-success';
              if ($d['kondisi'] === 'Dalam Perbaikan') {
                  $badgeKondisi = 'bg-danger';
              } elseif ($d['kondisi'] === 'Perlu Pemeriksaan') {
                  $badgeKondisi = 'bg-warning text-dark';
              }

              $nama_m = $d['nama_mesin_relasi'] ?: '-';
              $nama_s = $d['nama_sub_relasi'] ?: '-';
              
              // Cek Keberadaan File Gambar Komponen
              $foto_path = "../uploads/komponen/" . $d['gambar'];
              $has_foto  = !empty($d['gambar']) && file_exists($foto_path);
          ?>
              <tr>
                <td class="text-center fw-medium text-muted"><?= $no++ ?></td>
                
                <!-- THUMBNAIL FOTO -->
                <td class="text-center">
                  <?php if ($has_foto) : ?>
                    <img src="<?= htmlspecialchars($foto_path) ?>" alt="Foto" class="rounded border object-fit-cover" width="40" height="40">
                  <?php else : ?>
                    <div class="rounded border bg-light d-flex align-items-center justify-content-center mx-auto text-muted" style="width: 40px; height: 40px;">
                      <i class="bi bi-image fs-6"></i>
                    </div>
                  <?php endif; ?>
                </td>

                <!-- NAMA & SERIAL NUMBER -->
                <td>
                  <strong class="text-dark d-block"><?= htmlspecialchars($d['nama_bagian'] ?: '-') ?></strong>
                  <span class="badge bg-light text-primary border font-monospace mt-1">
                    <i class="bi bi-qr-code me-1"></i><?= htmlspecialchars($d['serial_number'] ?: '-') ?>
                  </span>
                </td>

                <!-- BRAND, TIPE, PART NUMBER -->
                <td>
                  <span class="fw-semibold text-dark"><?= htmlspecialchars($d['brand'] ?: '-') ?></span> 
                  <small class="text-muted">(<?= htmlspecialchars($d['tipe'] ?: '-') ?>)</small>
                  <br>
                  <small class="text-muted">PN: <?= htmlspecialchars($d['part_number'] ?: '-') ?></small>
                </td>

                <!-- MESIN & SUB MESIN RELASI -->
                <td>
                  <small class="fw-semibold d-block text-dark"><?= htmlspecialchars($nama_m) ?></small>
                  <small class="text-muted"><?= htmlspecialchars($nama_s) ?></small>
                </td>

                <!-- KATEGORI -->
                <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($d['kategori'] ?: '-') ?></span></td>

                <!-- KONDISI -->
                <td><span class="badge <?= $badgeKondisi ?> px-2 py-1"><?= htmlspecialchars($d['kondisi'] ?: 'Baik') ?></span></td>

                <!-- AKSI -->
                <td class="text-center">
                  <div class="btn-group btn-group-sm" role="group">
                    <a href="detail.php?id=<?= $d['id'] ?>" class="btn btn-outline-info" title="Detail Data"><i class="bi bi-eye"></i></a>
                    <a href="edit.php?id=<?= $d['id'] ?>" class="btn btn-outline-warning" title="Edit Data"><i class="bi bi-pencil-square"></i></a>
                    <a href="hapus.php?id=<?= $d['id'] ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus komponen ini?')" class="btn btn-outline-danger" title="Hapus Data"><i class="bi bi-trash"></i></a>
                  </div>
                </td>
              </tr>
            <?php endwhile; 
          else : ?>
            <tr>
              <td colspan="8" class="text-center py-5">
                <div class="text-muted">
                  <i class="bi bi-inbox display-6 d-block mb-2 text-secondary"></i>
                  <p class="mb-0 fw-medium">Data komponen tidak ditemukan atau belum ada.</p>
                </div>
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<script>
// Load Sub-Mesin via Fetch API
function loadSubMesinFilter(id_mesin, selectedSub = '') {
  const subDropdown = document.getElementById('filter_sub_mesin');
  
  if (!id_mesin) {
    subDropdown.innerHTML = '<option value="">-- Semua Sub Mesin --</option>';
    return;
  }

  fetch('get_sub_mesin.php?id_mesin=' + id_mesin)
    .then(response => response.text())
    .then(data => {
      subDropdown.innerHTML = data;
      if (selectedSub !== '') {
        subDropdown.value = selectedSub;
      }
    })
    .catch(err => console.error('Gagal memuat Sub Mesin:', err));
}

// Inisialisasi otomatis jika ada parameter filter dari URL
document.addEventListener("DOMContentLoaded", function() {
  const currentMesin = "<?= $id_mesin ?>";
  const currentSub   = "<?= $id_sub ?>";

  if (currentMesin !== "0" && currentMesin !== "") {
    loadSubMesinFilter(currentMesin, currentSub);
  }
});
</script>

<?php 
mysqli_stmt_close($stmt);
include "../template/footer.php"; 
?>
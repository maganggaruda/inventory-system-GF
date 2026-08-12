<?php
include "../koneksi.php";
include "../template/header.php";

$tgl_mulai    = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : '';
$tgl_selesai  = isset($_GET['tgl_selesai']) ? $_GET['tgl_selesai'] : '';
$jenis        = isset($_GET['jenis']) ? $_GET['jenis'] : 'maintenance';
$id_mesin     = isset($_GET['id_mesin']) ? $_GET['id_mesin'] : '';
$id_sub_mesin = isset($_GET['id_sub_mesin']) ? $_GET['id_sub_mesin'] : '';
$cari_komp    = isset($_GET['cari_komponen']) ? trim($_GET['cari_komponen']) : '';

// Query Data Berdasarkan Jenis Laporan & Filter Spesifik
if ($jenis == 'maintenance') {
    $query = "
        SELECT rm.*, k.nama_bagian, m.serial_number, m.nama_mesin 
        FROM riwayat_maintenance rm
        LEFT JOIN komponen k ON rm.id_komponen = k.id
        LEFT JOIN sub_mesin sm ON k.id_sub_mesin = sm.id
        LEFT JOIN mesin m ON sm.id_mesin = m.id
        WHERE 1=1
    ";
    $params = [];
    $types  = "";

    // Filter Tanggal Bersifat Opsional jika diisi
    if (!empty($tgl_mulai) && !empty($tgl_selesai)) {
        $query .= " AND rm.tanggal BETWEEN ? AND ?";
        $params[] = $tgl_mulai;
        $params[] = $tgl_selesai;
        $types  .= "ss";
    }

    if (!empty($id_mesin)) {
        $query .= " AND sm.id_mesin = ?";
        $params[] = $id_mesin;
        $types .= "i";
    }
    if (!empty($id_sub_mesin)) {
        $query .= " AND k.id_sub_mesin = ?";
        $params[] = $id_sub_mesin;
        $types .= "i";
    }
    if (!empty($cari_komp)) {
        $query .= " AND (k.nama_bagian LIKE ? OR m.serial_number LIKE ?)";
        $params[] = "%$cari_komp%";
        $params[] = "%$cari_komp%";
        $types .= "ss";
    }
    $query .= " ORDER BY rm.tanggal DESC";

    $stmt = mysqli_prepare($conn, $query);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $sql = mysqli_stmt_get_result($stmt);
} else {
    $query = "
        SELECT k.*, m.nama_mesin, m.serial_number, sm.nama_sub_mesin
        FROM komponen k
        LEFT JOIN sub_mesin sm ON k.id_sub_mesin = sm.id
        LEFT JOIN mesin m ON sm.id_mesin = m.id
        WHERE 1=1
    ";
    $params = [];
    $types  = "";

    if (!empty($id_mesin)) {
        $query .= " AND sm.id_mesin = ?";
        $params[] = $id_mesin;
        $types .= "i";
    }
    if (!empty($id_sub_mesin)) {
        $query .= " AND k.id_sub_mesin = ?";
        $params[] = $id_sub_mesin;
        $types .= "i";
    }
    if (!empty($cari_komp)) {
        $query .= " AND (k.nama_bagian LIKE ? OR m.serial_number LIKE ?)";
        $params[] = "%$cari_komp%";
        $params[] = "%$cari_komp%";
        $types .= "ss";
    }
    $query .= " ORDER BY k.id DESC";

    $stmt = mysqli_prepare($conn, $query);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $sql = mysqli_stmt_get_result($stmt);
}
?>

<div class="container-fluid p-0">

  <!-- Header Card Terpisah (Design System Garudafood) -->
  <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h2 class="fw-bold text-dark m-0">Laporan & Rekapitulasi</h2>
        <p class="text-muted small m-0 mt-1">Cetak laporan riwayat pemeliharaan dan inventaris mesin per kategori</p>
      </div>
    </div>
  </div>

  <!-- Filter Card -->
  <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
    <form method="GET" class="row g-3 align-items-end">
      <div class="col-md-2">
        <label class="form-label small fw-semibold text-dark">Jenis Laporan</label>
        <select name="jenis" class="form-select border-light-subtle rounded-3" onchange="this.form.submit()">
          <option value="maintenance" <?= $jenis == 'maintenance' ? 'selected' : '' ?>>Riwayat Maintenance</option>
          <option value="komponen" <?= $jenis == 'komponen' ? 'selected' : '' ?>>Inventaris Komponen</option>
        </select>
      </div>

      <!-- Filter Mesin -->
      <div class="col-md-2">
        <label class="form-label small fw-semibold text-dark">Filter Mesin</label>
        <select name="id_mesin" class="form-select border-light-subtle rounded-3">
          <option value="">-- Semua Mesin --</option>
          <?php
          $q_mesin = mysqli_query($conn, "SELECT id, nama_mesin FROM mesin ORDER BY nama_mesin ASC");
          while($m = mysqli_fetch_assoc($q_mesin)):
              $sel = ($id_mesin == $m['id']) ? 'selected' : '';
          ?>
              <option value="<?= $m['id'] ?>" <?= $sel ?>><?= htmlspecialchars($m['nama_mesin']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- Filter Sub Mesin -->
      <div class="col-md-2">
        <label class="form-label small fw-semibold text-dark">Filter Sub Mesin</label>
        <select name="id_sub_mesin" class="form-select border-light-subtle rounded-3">
          <option value="">-- Semua Sub Mesin --</option>
          <?php 
          $q_sub = mysqli_query($conn, "SELECT id, nama_sub_mesin FROM sub_mesin ORDER BY nama_sub_mesin ASC");
          while($s = mysqli_fetch_assoc($q_sub)):
              $sel = ($id_sub_mesin == $s['id']) ? 'selected' : '';
          ?>
              <option value="<?= $s['id'] ?>" <?= $sel ?>><?= htmlspecialchars($s['nama_sub_mesin']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- Cari Komponen -->
      <div class="col-md-2">
        <label class="form-label small fw-semibold text-dark">Cari Komponen</label>
        <input type="text" name="cari_komponen" class="form-control border-light-subtle rounded-3" value="<?= htmlspecialchars($cari_komp) ?>" placeholder="Nama bagian / SN...">
      </div>

      <?php if ($jenis == 'maintenance') : ?>
        <div class="col-md-2">
          <label class="form-label small fw-semibold text-dark">Dari Tanggal</label>
          <input type="date" name="tgl_mulai" class="form-control border-light-subtle rounded-3" value="<?= htmlspecialchars($tgl_mulai) ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold text-dark">Sampai Tanggal</label>
          <input type="date" name="tgl_selesai" class="form-control border-light-subtle rounded-3" value="<?= htmlspecialchars($tgl_selesai) ?>">
        </div>
      <?php endif; ?>

      <div class="col-md-12 d-flex gap-2 mt-3">
        <button type="submit" class="btn btn-primary rounded-3 fw-semibold px-4" style="background-color: #0056a6; border: none;">
          <i class="bi bi-funnel me-1"></i> Filter
        </button>
        
        <a href="index.php" class="btn btn-light border rounded-3 fw-semibold px-3">
          Reset
        </a>

        <!-- Tombol Cetak / PDF -->
        <a href="cetak.php?jenis=<?= urlencode($jenis) ?>&tgl_mulai=<?= urlencode($tgl_mulai) ?>&tgl_selesai=<?= urlencode($tgl_selesai) ?>&id_mesin=<?= urlencode($id_mesin) ?>&id_sub_mesin=<?= urlencode($id_sub_mesin) ?>&cari_komponen=<?= urlencode($cari_komp) ?>" target="_blank" class="btn btn-outline-danger rounded-3 fw-semibold px-3 ms-auto">
          <i class="bi bi-printer me-1"></i> Cetak PDF
        </a>

        <!-- Tombol Export Excel -->
        <a href="export_excel.php?jenis=<?= urlencode($jenis) ?>&tgl_mulai=<?= urlencode($tgl_mulai) ?>&tgl_selesai=<?= urlencode($tgl_selesai) ?>&id_mesin=<?= urlencode($id_mesin) ?>&id_sub_mesin=<?= urlencode($id_sub_mesin) ?>&cari_komponen=<?= urlencode($cari_komp) ?>" class="btn btn-success rounded-3 fw-semibold px-3" style="background-color: #198754; border: none;">
          <i class="bi bi-file-earmark-excel me-1"></i> Excel (.xls)
        </a>
      </div>
    </form>
  </div>

  <!-- Preview Table Card -->
  <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
    <div class="p-4 border-bottom d-flex justify-content-between align-items-center">
      <h5 class="fw-bold text-primary m-0 d-flex align-items-center" style="color: #0056a6 !important;">
        <i class="bi bi-file-earmark-text me-2"></i> Preview Data Laporan (<?= ucfirst($jenis) ?>)
      </h5>
      <span class="badge bg-light text-primary border rounded-pill px-3 py-2 fw-semibold">Total: <?= $sql ? mysqli_num_rows($sql) : 0 ?> Data</span>
    </div>

    <div class="table-responsive p-3">
      <?php if ($jenis == 'maintenance') : ?>
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light">
            <tr class="text-muted small text-uppercase">
              <th width="40">NO</th>
              <th width="110">TANGGAL</th>
              <th>MESIN</th>
              <th>KOMPONEN</th>
              <th>DETAIL PERBAIKAN / TINDAKAN</th>
              <th>TEKNISI</th>
              <th width="140" class="text-center">STATUS & AKSI</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;
            if ($sql && mysqli_num_rows($sql) > 0) :
              while ($d = mysqli_fetch_assoc($sql)) :
                $badgeStatus = 'bg-success';
                if (($d['status'] ?? '') == 'Proses') {
                    $badgeStatus = 'bg-warning text-dark';
                } elseif (($d['status'] ?? '') == 'Pending') {
                    $badgeStatus = 'bg-danger';
                }
            ?>
                <tr>
                  <td class="text-secondary"><?= $no++ ?></td>
                  <td><span class="fw-semibold text-dark"><?= !empty($d['tanggal']) ? date('d/m/Y', strtotime($d['tanggal'])) : '-' ?></span></td>
                  <td><span class="fw-semibold text-primary" style="color: #0056a6 !important;"><?= htmlspecialchars($d['nama_mesin'] ?? '-') ?></span></td>
                  <td>
                    <strong class="text-dark d-block"><?= htmlspecialchars($d['nama_bagian'] ?? '-') ?></strong>
                    <small class="text-muted">SN: <?= htmlspecialchars($d['serial_number'] ?? '-') ?></small>
                  </td>
                  <td><small class="text-dark"><?= htmlspecialchars($d['tindakan'] ?? '-') ?></small></td>
                  <td><small class="text-muted"><i class="bi bi-person me-1"></i><?= htmlspecialchars($d['teknisi'] ?? '-') ?></small></td>
                  <td class="text-center">
                    <div class="d-flex justify-content-center align-items-center gap-1">
                      <span class="badge <?= $badgeStatus ?> px-2 py-2 rounded-pill"><?= htmlspecialchars($d['status'] ?? '-') ?></span>
                      <?php if (!empty($d['id'])) : ?>
                        <a href="cetak.php?jenis=single_maintenance&id=<?= $d['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1" title="Download / Cetak Per Baris">
                          <i class="bi bi-download"></i>
                        </a>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endwhile;
            else : ?>
              <tr>
                <td colspan="7" class="text-center text-muted py-5">
                  <i class="bi bi-inbox fs-1 text-secondary opacity-50 d-block mb-2"></i>
                  Data tidak ditemukan berdasarkan filter tersebut.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>

      <?php else : ?>
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light">
            <tr class="text-muted small text-uppercase">
              <th width="40">NO</th>
              <th>NAMA KOMPONEN</th>
              <th>SN MESIN</th>
              <th>MESIN INDUK</th>
              <th>SUB MESIN</th>
              <th width="160" class="text-center">KONDISI & AKSI</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;
            if ($sql && mysqli_num_rows($sql) > 0) :
              while ($d = mysqli_fetch_assoc($sql)) :
                $kondisi = $d['kondisi'] ?? 'Baik';
                $badgeKondisi = ($kondisi == 'Baik') ? 'bg-success' : (($kondisi == 'Perlu Pemeriksaan') ? 'bg-warning text-dark' : 'bg-danger');
            ?>
                <tr>
                  <td class="text-secondary"><?= $no++ ?></td>
                  <td><strong class="text-dark"><?= htmlspecialchars($d['nama_bagian'] ?? '-') ?></strong></td>
                  <td><span class="badge bg-light text-dark border fw-semibold"><?= htmlspecialchars($d['serial_number'] ?? '-') ?></span></td>
                  <td><?= htmlspecialchars($d['nama_mesin'] ?? '-') ?></td>
                  <td><?= htmlspecialchars($d['nama_sub_mesin'] ?? '-') ?></td>
                  <td class="text-center">
                    <div class="d-flex justify-content-center align-items-center gap-1">
                      <span class="badge <?= $badgeKondisi ?> px-2 py-2 rounded-pill"><?= htmlspecialchars($kondisi) ?></span>
                      <?php if (!empty($d['id'])) : ?>
                        <a href="cetak.php?jenis=single_komponen&id=<?= $d['id'] ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1" title="Download / Cetak Per Baris">
                          <i class="bi bi-download"></i>
                        </a>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endwhile;
            else : ?>
              <tr>
                <td colspan="6" class="text-center text-muted py-5">
                  <i class="bi bi-inbox fs-1 text-secondary opacity-50 d-block mb-2"></i>
                  Data komponen kosong.
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </div>

</div>

<?php 
if (isset($stmt)) {
    mysqli_stmt_close($stmt);
}
include "../template/footer.php"; 
?>
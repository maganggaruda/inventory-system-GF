<?php
include "../koneksi.php";
include "../template/header.php";

$tgl_mulai   = isset($_GET['tgl_mulai']) ? $_GET['tgl_mulai'] : date('Y-m-01');
$tgl_selesai = isset($_GET['tgl_selesai']) ? $_GET['tgl_selesai'] : date('Y-m-d');
$jenis       = isset($_GET['jenis']) ? $_GET['jenis'] : 'maintenance';

// Query Data Berdasarkan Jenis Laporan (k.part_number disesuaikan ke k.kode_mesin)
if ($jenis == 'maintenance') {
    $sql = mysqli_query($conn, "
        SELECT rm.*, k.nama_bagian, k.kode_mesin, m.nama_mesin 
        FROM riwayat_maintenance rm
        LEFT JOIN komponen k ON rm.id_komponen = k.id
        LEFT JOIN mesin m ON k.id_mesin = m.id
        WHERE rm.tanggal BETWEEN '$tgl_mulai' AND '$tgl_selesai'
        ORDER BY rm.tanggal DESC
    ");
} else {
    $sql = mysqli_query($conn, "
        SELECT k.*, m.nama_mesin, sm.nama_sub_mesin
        FROM komponen k
        LEFT JOIN mesin m ON k.id_mesin = m.id
        LEFT JOIN sub_mesin sm ON k.id_sub_mesin = sm.id
        ORDER BY k.id DESC
    ");
}
?>

<div class="container-fluid p-0">

  <!-- Header Card Terpisah (Design System Garudafood) -->
  <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center">
      <div>
        <h2 class="fw-bold text-dark m-0">Laporan & Rekapitulasi</h2>
        <p class="text-muted small m-0 mt-1">Cetak laporan riwayat pemeliharaan dan inventaris mesin</p>
      </div>
    </div>
  </div>

  <!-- Filter Card -->
  <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
    <form method="GET" class="row g-3 align-items-end">
      <div class="col-md-3">
        <label class="form-label small fw-semibold text-dark">Jenis Laporan</label>
        <select name="jenis" class="form-select border-light-subtle rounded-3" onchange="this.form.submit()">
          <option value="maintenance" <?= $jenis == 'maintenance' ? 'selected' : '' ?>>Riwayat Maintenance</option>
          <option value="komponen" <?= $jenis == 'komponen' ? 'selected' : '' ?>>Inventaris Komponen</option>
        </select>
      </div>

      <?php if ($jenis == 'maintenance') : ?>
        <div class="col-md-2">
          <label class="form-label small fw-semibold text-dark">Dari Tanggal</label>
          <input type="date" name="tgl_mulai" class="form-control border-light-subtle rounded-3" value="<?= $tgl_mulai ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label small fw-semibold text-dark">Sampai Tanggal</label>
          <input type="date" name="tgl_selesai" class="form-control border-light-subtle rounded-3" value="<?= $tgl_selesai ?>">
        </div>
      <?php endif; ?>

      <div class="<?= ($jenis == 'maintenance') ? 'col-md-5' : 'col-md-9' ?> d-flex gap-2">
        <button type="submit" class="btn btn-primary rounded-3 fw-semibold px-4" style="background-color: #0056a6; border: none;">
          <i class="bi bi-funnel me-1"></i> Filter
        </button>
        
        <!-- Tombol Cetak / PDF -->
        <a href="cetak.php?jenis=<?= $jenis ?>&tgl_mulai=<?= $tgl_mulai ?>&tgl_selesai=<?= $tgl_selesai ?>" target="_blank" class="btn btn-outline-danger rounded-3 fw-semibold px-3">
          <i class="bi bi-printer me-1"></i> Cetak PDF
        </a>

        <!-- Tombol Export Excel -->
        <a href="export_excel.php?jenis=<?= $jenis ?>&tgl_mulai=<?= $tgl_mulai ?>&tgl_selesai=<?= $tgl_selesai ?>" class="btn btn-success rounded-3 fw-semibold px-3" style="background-color: #198754; border: none;">
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
              <th width="100">STATUS</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;
            if ($sql && mysqli_num_rows($sql) > 0) :
              while ($d = mysqli_fetch_assoc($sql)) :
                $badgeStatus = 'bg-success';
                if ($d['status'] == 'Proses') {
                    $badgeStatus = 'bg-warning text-dark';
                } elseif ($d['status'] == 'Pending') {
                    $badgeStatus = 'bg-danger';
                }
            ?>
                <tr>
                  <td class="text-secondary"><?= $no++ ?></td>
                  <td><span class="fw-semibold text-dark"><?= date('d/m/Y', strtotime($d['tanggal'])) ?></span></td>
                  <td><span class="fw-semibold text-primary" style="color: #0056a6 !important;"><?= htmlspecialchars($d['nama_mesin'] ?: '-') ?></span></td>
                  <td>
                    <strong class="text-dark d-block"><?= htmlspecialchars($d['nama_bagian'] ?: '-') ?></strong>
                    <small class="text-muted">Kode Mesin: <?= htmlspecialchars($d['kode_mesin'] ?: '-') ?></small>
                  </td>
                  <td><small class="text-dark"><?= htmlspecialchars($d['tindakan']) ?></small></td>
                  <td><small class="text-muted"><i class="bi bi-person me-1"></i><?= htmlspecialchars($d['teknisi'] ?? '-') ?></small></td>
                  <td><span class="badge <?= $badgeStatus ?> px-3 py-2 rounded-pill"><?= htmlspecialchars($d['status']) ?></span></td>
                </tr>
              <?php endwhile;
            else : ?>
              <tr>
                <td colspan="7" class="text-center text-muted py-5">
                  <i class="bi bi-inbox fs-1 text-secondary opacity-50 d-block mb-2"></i>
                  Data tidak ditemukan pada rentang tanggal ini.
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
              <th>KODE MESIN</th>
              <th>MESIN INDUK</th>
              <th>SUB MESIN</th>
              <th width="120">KONDISI</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $no = 1;
            if ($sql && mysqli_num_rows($sql) > 0) :
              while ($d = mysqli_fetch_assoc($sql)) :
                $badgeKondisi = ($d['kondisi'] == 'Baik') ? 'bg-success' : (($d['kondisi'] == 'Perlu Pemeriksaan') ? 'bg-warning text-dark' : 'bg-danger');
            ?>
                <tr>
                  <td class="text-secondary"><?= $no++ ?></td>
                  <td><strong class="text-dark"><?= htmlspecialchars($d['nama_bagian']) ?></strong></td>
                  <td><span class="badge bg-light text-dark border fw-semibold"><?= htmlspecialchars($d['kode_mesin'] ?: '-') ?></span></td>
                  <td><?= htmlspecialchars($d['nama_mesin'] ?: '-') ?></td>
                  <td><?= htmlspecialchars($d['nama_sub_mesin'] ?: '-') ?></td>
                  <td><span class="badge <?= $badgeKondisi ?> px-3 py-2 rounded-pill"><?= htmlspecialchars($d['kondisi']) ?></span></td>
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

<?php include "../template/footer.php"; ?>
<?php
include "../koneksi.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = mysqli_prepare($conn, "
    SELECT 
        rm.*, 
        m.nama_mesin, 
        m.serial_number as serial_number_induk,
        m.lokasi as lokasi_m,
        sm.nama_sub_mesin,
        k.nama_bagian as komponen_nama_bagian
    FROM riwayat_maintenance rm
    LEFT JOIN komponen k ON rm.id_komponen = k.id
    LEFT JOIN sub_mesin sm ON k.id_sub_mesin = sm.id
    LEFT JOIN mesin m ON sm.id_mesin = m.id
    WHERE rm.id = ?
");

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$d = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$d) {
    header("Location: index.php");
    exit;
}

include "../template/header.php";

$badgeStatus = 'bg-success';
if (($d['status'] ?? '') == 'Proses') $badgeStatus = 'bg-warning text-dark';
if (($d['status'] ?? '') == 'Pending') $badgeStatus = 'bg-danger';
?>

<div class="container-fluid px-3 py-2">

  <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
      <div class="d-flex align-items-center gap-3">
        <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px; flex-shrink: 0;">
          <i class="bi bi-arrow-left fs-5"></i>
        </a>
        <div>
          <h3 class="fw-bold text-dark mb-0 fs-4">Detail Maintenance</h3>
          <p class="text-muted small mb-0">Informasi riwayat pemeliharaan, perbaikan, & spesifikasi komponen</p>
        </div>
      </div>
      <div>
        <a href="edit.php?id=<?= $d['id'] ?>" class="btn btn-warning text-dark fw-semibold rounded-3 px-3 shadow-sm text-nowrap">
          <i class="bi bi-pencil-square me-1"></i> Edit Maintenance
        </a>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-lg-7 col-xl-8">
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
          <h6 class="fw-bold text-primary mb-0 fs-6"><i class="bi bi-tools me-2"></i>Ringkasan Tindakan</h6>
          <span class="badge <?= $badgeStatus ?> px-3 py-2 rounded-pill fs-6 fw-normal"><?= htmlspecialchars(!empty($d['status']) ? $d['status'] : 'Selesai') ?></span>
        </div>
        <div class="card-body p-4">
          <div class="mb-4">
            <label class="form-label text-muted small fw-bold text-uppercase mb-2">Tindakan Maintenance</label>
            <div class="p-3 bg-light rounded-3 text-dark border"><?= !empty($d['tindakan']) ? nl2br(htmlspecialchars($d['tindakan'])) : '-' ?></div>
          </div>
          <div class="mb-4">
            <label class="form-label text-muted small fw-bold text-uppercase mb-2">Sparepart Diganti</label>
            <div class="p-3 bg-light rounded-3 text-dark border"><?= !empty($d['sparepart']) ? nl2br(htmlspecialchars($d['sparepart'])) : '-' ?></div>
          </div>
          <div class="mb-4">
            <label class="form-label text-muted small fw-bold text-uppercase mb-2">Foto Dokumentasi</label>
            <div>
              <?php if (!empty($d['foto']) && file_exists("../uploads/maintenance/" . $d['foto'])): ?>
                <a href="../uploads/maintenance/<?= htmlspecialchars($d['foto']) ?>" target="_blank"><img src="../uploads/maintenance/<?= htmlspecialchars($d['foto']) ?>" class="img-fluid rounded-3 border shadow-sm" style="max-height: 300px;"></a>
              <?php else: ?>
                <div class="p-3 bg-light rounded-3 text-muted border fst-italic">Tidak ada foto dokumentasi yang diunggah.</div>
              <?php endif; ?>
            </div>
          </div>
          <hr class="text-muted opacity-25 my-4">
          <div class="row g-3">
            <div class="col-sm-4"><label class="form-label text-muted small fw-bold text-uppercase mb-1">Tanggal</label><div class="fw-bold text-dark"><?= !empty($d['tanggal']) ? date('d F Y', strtotime($d['tanggal'])) : '-' ?></div></div>
            <div class="col-sm-4"><label class="form-label text-muted small fw-bold text-uppercase mb-1">Teknisi</label><div class="fw-bold text-dark"><?= htmlspecialchars($d['teknisi'] ?? '-') ?></div></div>
            <div class="col-sm-4"><label class="form-label text-muted small fw-bold text-uppercase mb-1">Jenis</label><div><span class="badge bg-info text-dark rounded-pill px-3 py-2"><?= htmlspecialchars($d['jenis'] ?? '-') ?></span></div></div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-5 col-xl-4">
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 border-0"><h6 class="fw-bold text-primary mb-0 fs-6"><i class="bi bi-cpu me-2"></i>Perangkat Terkait</h6></div>
        <div class="card-body p-4">
          <div class="mb-3"><label class="form-label text-muted small fw-bold text-uppercase mb-1">Nama Komponen</label><div class="fw-bold text-dark fs-5"><?= htmlspecialchars(!empty($d['nama_bagian']) ? $d['nama_bagian'] : ($d['komponen_nama_bagian'] ?? '-')) ?></div></div>
          <div class="mb-3"><label class="form-label text-muted small fw-bold text-uppercase mb-1">Mesin Induk</label><div class="fw-semibold text-dark"><?= htmlspecialchars(!empty($d['nama_mesin']) ? $d['nama_mesin'] : '-') ?></div></div>
          <div><label class="form-label text-muted small fw-bold text-uppercase mb-1">Sub Mesin</label><div class="fw-semibold text-dark"><?= htmlspecialchars(!empty($d['nama_sub_mesin']) ? $d['nama_sub_mesin'] : '-') ?></div></div>
        </div>
      </div>

      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 border-0"><h6 class="fw-bold text-primary mb-0 fs-6"><i class="bi bi-list-columns-reverse me-2"></i>Spesifikasi Teknis</h6></div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-bordered mb-0 align-middle">
              <tbody>
                <tr><td class="bg-light text-muted small fw-bold text-uppercase px-3 py-2" style="width: 40%;">Brand / Merk</td><td class="fw-bold text-dark px-3 py-2"><?= htmlspecialchars($d['brand'] ?? '-') ?></td></tr>
                <tr><td class="bg-light text-muted small fw-bold text-uppercase px-3 py-2">Tipe</td><td class="fw-bold text-dark px-3 py-2"><?= htmlspecialchars($d['tipe'] ?? '-') ?></td></tr>
                <tr><td class="bg-light text-muted small fw-bold text-uppercase px-3 py-2">Part Number</td><td class="fw-bold text-dark font-monospace px-3 py-2"><?= htmlspecialchars($d['part_number'] ?? '-') ?></td></tr>
                <tr><td class="bg-light text-muted small fw-bold text-uppercase px-3 py-2">Daya</td><td class="fw-bold text-dark px-3 py-2"><?= htmlspecialchars($d['daya'] ?? '-') ?></td></tr>
                <tr><td class="bg-light text-muted small fw-bold text-uppercase px-3 py-2">IO Address</td><td class="fw-bold text-dark font-monospace px-3 py-2"><?= htmlspecialchars($d['io_address'] ?? '-') ?></td></tr>
                <tr><td class="bg-light text-muted small fw-bold text-uppercase px-3 py-2">Input Voltage</td><td class="fw-bold text-dark px-3 py-2"><?= htmlspecialchars($d['input_voltage'] ?? '-') ?></td></tr>
                <tr><td class="bg-light text-muted small fw-bold text-uppercase px-3 py-2">Frekuensi Input</td><td class="fw-bold text-dark px-3 py-2"><?= htmlspecialchars($d['frekuensi_input'] ?? '-') ?></td></tr>
                <tr><td class="bg-light text-muted small fw-bold text-uppercase px-3 py-2">Arus Input</td><td class="fw-bold text-dark px-3 py-2"><?= htmlspecialchars($d['arus_input'] ?? '-') ?></td></tr>
                <tr><td class="bg-light text-muted small fw-bold text-uppercase px-3 py-2">Output</td><td class="fw-bold text-dark px-3 py-2"><?= htmlspecialchars($d['output'] ?? '-') ?></td></tr>
                <tr><td class="bg-light text-muted small fw-bold text-uppercase px-3 py-2">Frekuensi Output</td><td class="fw-bold text-dark px-3 py-2"><?= htmlspecialchars($d['frekuensi_output'] ?? '-') ?></td></tr>
                <tr><td class="bg-light text-muted small fw-bold text-uppercase px-3 py-2">IP Rating</td><td class="fw-bold text-dark px-3 py-2"><?= htmlspecialchars($d['ip_rating'] ?? '-') ?></td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include "../template/footer.php"; ?>
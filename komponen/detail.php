<?php
include "../koneksi.php";

$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

$sql = mysqli_query($conn, "
    SELECT k.*, m.nama_mesin as nama_m, m.lokasi as lokasi_m, sm.nama_sub_mesin as nama_s 
    FROM komponen k
    LEFT JOIN mesin m ON k.id_mesin = m.id
    LEFT JOIN sub_mesin sm ON k.id_sub_mesin = sm.id
    WHERE k.id = '$id'
");
$d = mysqli_fetch_assoc($sql);

if (!$d) {
    header("Location: index.php");
    exit;
}

include "../template/header.php";

$badgeKondisi = 'bg-success';
if ($d['kondisi'] == 'Dalam Perbaikan') {
    $badgeKondisi = 'bg-danger';
} elseif ($d['kondisi'] == 'Perlu Pemeriksaan') {
    $badgeKondisi = 'bg-warning text-dark';
}
?>

<div class="container-fluid p-0">

  <!-- HEADER -->
  <div class="dashboard-header mb-3 py-3 px-4">
    <div class="d-flex align-items-center gap-3">
      <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px; flex-shrink: 0;">
        <i class="bi bi-arrow-left fs-5"></i>
      </a>
      <div class="flex-grow-1 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <h3 class="dashboard-title m-0 fs-4 fw-bold">Detail Komponen</h3>
          <p class="dashboard-subtitle m-0 small text-muted">Informasi teknis dan status spesifikasi part</p>
        </div>
        <a href="edit.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-warning text-dark fw-semibold px-3">
          <i class="bi bi-pencil-square me-1"></i> Edit Data
        </a>
      </div>
    </div>
  </div>

  <!-- CONTENT CARD -->
  <div class="content-card mb-3">
    <div class="card-header-custom py-2 px-3">
      <h6 class="card-title-custom m-0 fw-bold">
        <i class="bi bi-info-circle me-2"></i>Rincian Data Komponen
      </h6>
    </div>

    <div class="card-body-custom p-4">
      <div class="row g-4">
        <!-- Kolom Kiri -->
        <div class="col-md-6">
          <table class="table table-borderless align-middle mb-0">
            <tr>
              <th width="150" class="text-muted fw-normal small">Nama Bagian</th>
              <td class="fw-bold fs-5 text-dark"><?= htmlspecialchars($d['nama_bagian'] ?: '-') ?></td>
            </tr>
            <tr>
              <th class="text-muted fw-normal small">Kode Mesin</th>
              <td><span class="badge bg-light text-primary border font-monospace fs-6"><i class="bi bi-qr-code me-1"></i><?= htmlspecialchars($d['kode_mesin'] ?: '-') ?></span></td>
            </tr>
            <tr>
              <th class="text-muted fw-normal small">Kondisi</th>
              <td><span class="badge <?= $badgeKondisi ?> px-3 py-2"><?= htmlspecialchars($d['kondisi'] ?: 'Baik') ?></span></td>
            </tr>
            <tr>
              <th class="text-muted fw-normal small">Mesin Induk</th>
              <td class="fw-semibold text-dark"><?= htmlspecialchars($d['nama_m'] ?: ($d['mesin'] ?: '-')) ?></td>
            </tr>
            <tr>
              <th class="text-muted fw-normal small">Sub Mesin</th>
              <td class="fw-semibold text-dark"><?= htmlspecialchars($d['nama_s'] ?: ($d['sub_mesin'] ?: '-')) ?></td>
            </tr>
            <tr>
              <th class="text-muted fw-normal small">Kategori</th>
              <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($d['kategori'] ?: '-') ?></span></td>
            </tr>
            <tr>
              <th class="text-muted fw-normal small">Lokasi</th>
              <td class="text-dark"><i class="bi bi-geo-alt text-danger me-1"></i><?= htmlspecialchars($d['lokasi'] ?: ($d['lokasi_m'] ?: '-')) ?></td>
            </tr>
          </table>
        </div>

        <!-- Kolom Kanan: Spesifikasi Teknis -->
        <div class="col-md-6 border-start ps-md-4">
          <h6 class="fw-bold text-primary mb-3 small"><i class="bi bi-cpu me-1"></i> SPESIFIKASI TEKNIS</h6>
          <table class="table table-sm table-borderless mb-0">
            <tr><th width="140" class="text-muted fw-normal small">Brand / Merk</th><td class="fw-semibold text-dark"><?= htmlspecialchars($d['brand'] ?: '-') ?></td></tr>
            <tr><th class="text-muted fw-normal small">Tipe</th><td class="fw-semibold text-dark"><?= htmlspecialchars($d['tipe'] ?: '-') ?></td></tr>
            <tr><th class="text-muted fw-normal small">Part Number</th><td class="fw-semibold text-dark"><?= htmlspecialchars($d['part_number'] ?: '-') ?></td></tr>
            <tr><th class="text-muted fw-normal small">Daya</th><td class="text-dark"><?= htmlspecialchars($d['daya'] ?: '-') ?></td></tr>
            <tr><th class="text-muted fw-normal small">IO Address</th><td class="text-dark"><?= htmlspecialchars($d['io_address'] ?: '-') ?></td></tr>
            <tr><th class="text-muted fw-normal small">Input Voltage</th><td class="text-dark"><?= htmlspecialchars($d['input_voltage'] ?: '-') ?></td></tr>
            <tr><th class="text-muted fw-normal small">Frekuensi Input</th><td class="text-dark"><?= htmlspecialchars($d['frekuensi_input'] ?: '-') ?></td></tr>
            <tr><th class="text-muted fw-normal small">Arus Input</th><td class="text-dark"><?= htmlspecialchars($d['arus_input'] ?: '-') ?></td></tr>
            <tr><th class="text-muted fw-normal small">Output</th><td class="text-dark"><?= htmlspecialchars($d['output'] ?: '-') ?></td></tr>
            <tr><th class="text-muted fw-normal small">Frekuensi Output</th><td class="text-dark"><?= htmlspecialchars($d['frekuensi_output'] ?: '-') ?></td></tr>
            <tr><th class="text-muted fw-normal small">IP Rating</th><td class="text-dark"><?= htmlspecialchars($d['ip_rating'] ?: '-') ?></td></tr>
          </table>
        </div>
      </div>

      <hr class="my-3 text-muted opacity-25">

      <div>
        <h6 class="fw-bold text-dark mb-2 small">Keterangan / Catatan Tambahan:</h6>
        <div class="p-3 bg-light rounded text-muted small border">
          <?= nl2br(htmlspecialchars($d['keterangan'] ?: 'Tidak ada keterangan tambahan.')) ?>
        </div>
      </div>

    </div>
  </div>

</div>

<?php include "../template/footer.php"; ?>
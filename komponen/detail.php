<?php
include "../koneksi.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Query dengan JOIN lengkap ke area_bagian
$stmt = mysqli_prepare($conn, "
    SELECT 
        k.*, 
        m.nama_mesin as nama_m, 
        m.serial_number as sn_mesin, 
        sm.nama_sub_mesin as nama_s,
        ab.lokasi as lokasi_aktual
    FROM komponen k
    LEFT JOIN sub_mesin sm ON k.id_sub_mesin = sm.id
    LEFT JOIN mesin m ON sm.id_mesin = m.id
    LEFT JOIN jenis_mesin jm ON m.id_jenis_mesin = jm.id
    LEFT JOIN area_bagian ab ON jm.id_area = ab.id
    WHERE k.id = ?
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

$badgeKondisi = 'bg-success';
if (($d['kondisi'] ?? '') == 'Dalam Perbaikan') {
    $badgeKondisi = 'bg-danger';
} elseif (($d['kondisi'] ?? '') == 'Perlu Pemeriksaan') {
    $badgeKondisi = 'bg-warning text-dark';
}
?>

<div class="container-fluid p-0">
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

  <div class="content-card mb-3">
    <div class="card-header-custom py-2 px-3">
      <h6 class="card-title-custom m-0 fw-bold"><i class="bi bi-info-circle me-2"></i>Rincian Data Komponen</h6>
    </div>

    <div class="card-body-custom p-4">
      <div class="row g-4">
        <div class="col-lg-5 col-md-6">
          <div class="text-center p-3 mb-3 bg-light rounded border">
            <?php 
              $gambar_path = "../uploads/komponen/" . ($d['gambar'] ?? '');
              if (!empty($d['gambar']) && file_exists($gambar_path)) : 
            ?>
              <img src="<?= htmlspecialchars($gambar_path) ?>" alt="Foto Komponen" class="img-fluid rounded shadow-sm" style="max-height: 250px; object-fit: contain;">
            <?php else : ?>
              <div class="py-4 text-muted">
                <i class="bi bi-image fs-1 opacity-50 d-block mb-1"></i>
                <span class="small">Tidak ada foto komponen</span>
              </div>
            <?php endif; ?>
          </div>

          <table class="table table-borderless align-middle mb-0">
            <tr>
              <th width="140" class="text-muted fw-normal small">Nama Bagian</th>
              <td class="fw-bold fs-5 text-dark"><?= htmlspecialchars($d['nama_bagian'] ?? '-') ?></td>
            </tr>
            <tr>
              <th class="text-muted fw-normal small">SN Komponen</th>
              <td><span class="badge bg-light text-dark border font-monospace fs-6"><i class="bi bi-barcode me-1"></i><?= htmlspecialchars(!empty($d['serial_number']) ? $d['serial_number'] : '-') ?></span></td>
            </tr>
            <tr>
              <th class="text-muted fw-normal small">SN Mesin Induk</th>
              <td><span class="badge bg-light text-primary border font-monospace fs-6"><i class="bi bi-qr-code me-1"></i><?= htmlspecialchars(!empty($d['sn_mesin']) ? $d['sn_mesin'] : '-') ?></span></td>
            </tr>
            <tr>
              <th class="text-muted fw-normal small">Kondisi</th>
              <td><span class="badge <?= $badgeKondisi ?> px-3 py-2"><?= htmlspecialchars(!empty($d['kondisi']) ? $d['kondisi'] : 'Baik') ?></span></td>
            </tr>
            <tr>
              <th class="text-muted fw-normal small">Mesin Induk</th>
              <td class="fw-semibold text-dark"><?= htmlspecialchars(!empty($d['nama_m']) ? $d['nama_m'] : ($d['mesin'] ?? '-')) ?></td>
            </tr>
            <tr>
              <th class="text-muted fw-normal small">Sub Mesin</th>
              <td class="fw-semibold text-dark"><?= htmlspecialchars(!empty($d['nama_s']) ? $d['nama_s'] : ($d['sub_mesin'] ?? '-')) ?></td>
            </tr>
            <tr>
              <th class="text-muted fw-normal small">Kategori</th>
              <td><span class="badge bg-light text-dark border"><?= htmlspecialchars(!empty($d['kategori']) ? $d['kategori'] : '-') ?></span></td>
            </tr>
            <tr>
              <th class="text-muted fw-normal small">Lokasi</th>
              <td class="text-dark">
                <i class="bi bi-geo-alt text-danger me-1"></i>
                <?= htmlspecialchars(!empty($d['lokasi_aktual']) ? $d['lokasi_aktual'] : ($d['lokasi'] ?? '-')) ?>
              </td>
            </tr>
          </table>
        </div>

        <div class="col-lg-7 col-md-6 border-start-md ps-md-4">
          <h6 class="fw-bold text-primary mb-3 small"><i class="bi bi-cpu me-1"></i> SPESIFIKASI TEKNIS</h6>
          <table class="table table-sm table-borderless mb-0">
            <tr><th width="150" class="text-muted fw-normal small">Brand / Merk</th><td class="fw-semibold text-dark"><?= htmlspecialchars(!empty($d['brand']) ? $d['brand'] : '-') ?></td></tr>
            <tr><th class="text-muted fw-normal small">Tipe</th><td class="fw-semibold text-dark"><?= htmlspecialchars(!empty($d['tipe']) ? $d['tipe'] : '-') ?></td></tr>
            <tr><th class="text-muted fw-normal small">Part Number</th><td class="fw-semibold text-dark"><?= htmlspecialchars(!empty($d['part_number']) ? $d['part_number'] : '-') ?></td></tr>
            <tr><th class="text-muted fw-normal small">Daya</th><td class="text-dark"><?= htmlspecialchars(!empty($d['daya']) ? $d['daya'] : '-') ?></td></tr>
            <tr><th class="text-muted fw-normal small">IO Address</th><td class="text-dark"><?= htmlspecialchars(!empty($d['io_address']) ? $d['io_address'] : '-') ?></td></tr>
            <tr><th class="text-muted fw-normal small">Input Voltage</th><td class="text-dark"><?= htmlspecialchars(!empty($d['input_voltage']) ? $d['input_voltage'] : '-') ?></td></tr>
            <tr><th class="text-muted fw-normal small">Frekuensi Input</th><td class="text-dark"><?= htmlspecialchars(!empty($d['frekuensi_input']) ? $d['frekuensi_input'] : '-') ?></td></tr>
            <tr><th class="text-muted fw-normal small">Arus Input</th><td class="text-dark"><?= htmlspecialchars(!empty($d['arus_input']) ? $d['arus_input'] : '-') ?></td></tr>
            <tr><th class="text-muted fw-normal small">Output</th><td class="text-dark"><?= htmlspecialchars(!empty($d['output']) ? $d['output'] : '-') ?></td></tr>
            <tr><th class="text-muted fw-normal small">Frekuensi Output</th><td class="text-dark"><?= htmlspecialchars(!empty($d['frekuensi_output']) ? $d['frekuensi_output'] : '-') ?></td></tr>
            <tr><th class="text-muted fw-normal small">IP Rating</th><td class="text-dark"><?= htmlspecialchars(!empty($d['ip_rating']) ? $d['ip_rating'] : '-') ?></td></tr>
          </table>
        </div>
      </div>

      <hr class="my-4 text-muted opacity-25">

      <div>
        <h6 class="fw-bold text-dark mb-2 small"><i class="bi bi-journal-text me-1"></i> Keterangan / Catatan Tambahan:</h6>
        <div class="p-3 bg-light rounded text-muted small border">
          <?= nl2br(htmlspecialchars(!empty($d['keterangan']) ? $d['keterangan'] : 'Tidak ada keterangan tambahan.')) ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include "../template/footer.php"; ?>
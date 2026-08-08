<?php
include "../koneksi.php";

$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

// Query data riwayat maintenance beserta relasinya
$query = mysqli_query($conn, "
    SELECT 
        rm.*, 
        k.nama_bagian, 
        k.kode_mesin as kode_komponen, 
        k.kategori,
        m.nama_mesin, 
        m.kode_mesin as kode_mesin_induk,
        m.lokasi as lokasi_m,
        sm.nama_sub_mesin
    FROM riwayat_maintenance rm
    LEFT JOIN komponen k ON rm.id_komponen = k.id
    LEFT JOIN mesin m ON k.id_mesin = m.id
    LEFT JOIN sub_mesin sm ON k.id_sub_mesin = sm.id
    WHERE rm.id = '$id'
");

$d = mysqli_fetch_assoc($query);

if (!$d) {
    header("Location: index.php");
    exit;
}

include "../template/header.php";

// Set badge status maintenance
$badgeStatus = 'bg-success';
if ($d['status'] == 'Proses') $badgeStatus = 'bg-warning text-dark';
if ($d['status'] == 'Pending') $badgeStatus = 'bg-danger';
?>

<div class="container-fluid px-3 py-2">

  <!-- HEADER BAR (KARTU PUTIH) -->
  <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
      <div class="d-flex align-items-center gap-3">
        <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px; flex-shrink: 0;">
          <i class="bi bi-arrow-left fs-5"></i>
        </a>
        <div>
          <h3 class="fw-bold text-dark mb-0 fs-4">Detail Maintenance</h3>
          <p class="text-muted small mb-0">Informasi riwayat pemeliharaan & perbaikan komponen</p>
        </div>
      </div>
      <div>
        <a href="edit.php?id=<?= $d['id'] ?>" class="btn btn-warning text-dark fw-semibold rounded-3 px-3 shadow-sm text-nowrap">
          <i class="bi bi-pencil-square me-1"></i> Edit Maintenance
        </a>
      </div>
    </div>
  </div>

  <!-- CONTENT GRID -->
  <div class="row g-4">
    
    <!-- KARTU KIRI: RINGKASAN TINDAKAN -->
    <div class="col-lg-7 col-xl-8">
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
          <h6 class="fw-bold text-primary mb-0 fs-6">
            <i class="bi bi-tools me-2"></i>Ringkasan Tindakan
          </h6>
          <span class="badge <?= $badgeStatus ?> px-3 py-2 rounded-pill fs-6 fw-normal">
            <?= htmlspecialchars($d['status'] ?: 'Selesai') ?>
          </span>
        </div>
        <div class="card-body p-4">
          
          <!-- Box Tindakan Maintenance -->
          <div class="mb-4">
            <label class="form-label text-muted small fw-bold text-uppercase mb-2">Tindakan Maintenance</label>
            <div class="p-3 bg-light rounded-3 text-dark border">
              <?= !empty($d['tindakan']) ? nl2br(htmlspecialchars($d['tindakan'])) : '<span class="text-muted italic">Tidak ada rincian tindakan.</span>' ?>
            </div>
          </div>

          <!-- Box Sparepart Diganti -->
          <div class="mb-4">
            <label class="form-label text-muted small fw-bold text-uppercase mb-2">Sparepart Diganti</label>
            <div class="p-3 bg-light rounded-3 text-dark border">
              <?= !empty($d['sparepart_diganti']) ? nl2br(htmlspecialchars($d['sparepart_diganti'])) : '<span class="text-muted">-</span>' ?>
            </div>
          </div>

          <hr class="text-muted opacity-25 my-4">

          <!-- Detail Informasi Grid -->
          <div class="row g-3">
            <div class="col-sm-4">
              <label class="form-label text-muted small fw-bold text-uppercase mb-1">Tanggal Maintenance</label>
              <div class="fw-bold text-dark fs-6">
                <i class="bi bi-calendar-event me-2 text-primary"></i><?= isset($d['tanggal']) ? date('d F Y', strtotime($d['tanggal'])) : '-' ?>
              </div>
            </div>
            
            <div class="col-sm-4">
              <label class="form-label text-muted small fw-bold text-uppercase mb-1">Teknisi / Pelaksana</label>
              <div class="fw-bold text-dark fs-6">
                <i class="bi bi-person me-2 text-primary"></i><?= htmlspecialchars($d['teknisi'] ?: '-') ?>
              </div>
            </div>

            <div class="col-sm-4">
              <label class="form-label text-muted small fw-bold text-uppercase mb-1">Jenis Maintenance</label>
              <div>
                <span class="badge bg-info text-dark rounded-pill px-3 py-2 fw-semibold">
                  <i class="bi bi-wrench me-1"></i><?= htmlspecialchars($d['jenis_maintenance'] ?? $d['jenis'] ?? 'Corrective') ?>
                </span>
              </div>
            </div>
          </div>

          <!-- Catatan Tambahan -->
          <?php if (!empty($d['keterangan'])): ?>
            <div class="mt-4">
              <label class="form-label text-muted small fw-bold text-uppercase mb-1">Catatan / Keterangan Tambahan</label>
              <p class="small text-muted mb-0 bg-light p-3 rounded-3 border"><?= nl2br(htmlspecialchars($d['keterangan'])) ?></p>
            </div>
          <?php endif; ?>

        </div>
      </div>
    </div>

    <!-- KARTU KANAN: PERANGKAT TERKAIT -->
    <div class="col-lg-5 col-xl-4">
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 border-0">
          <h6 class="fw-bold text-primary mb-0 fs-6">
            <i class="bi bi-cpu me-2"></i>Perangkat Terkait
          </h6>
        </div>
        <div class="card-body p-4">
          
          <!-- Nama Komponen -->
          <div class="mb-3">
            <label class="form-label text-muted small fw-bold text-uppercase mb-1">Nama Komponen</label>
            <div class="fw-bold text-dark fs-5"><?= htmlspecialchars($d['nama_bagian'] ?: '-') ?></div>
            <?php if (!empty($d['kode_mesin_induk'])): ?>
              <small class="text-primary font-monospace fw-semibold d-block mt-1">
                <i class="bi bi-qr-code me-1"></i>Kode Mesin: <?= htmlspecialchars($d['kode_mesin_induk']) ?>
              </small>
            <?php endif; ?>
          </div>

          <hr class="text-muted opacity-25 my-3">

          <!-- Mesin Induk -->
          <div class="mb-3">
            <label class="form-label text-muted small fw-bold text-uppercase mb-1">Mesin Induk</label>
            <div class="fw-semibold text-dark">
              <i class="bi bi-gear text-primary me-2"></i><?= htmlspecialchars($d['nama_mesin'] ?: '-') ?>
            </div>
          </div>

          <!-- Sub Mesin -->
          <div class="mb-3">
            <label class="form-label text-muted small fw-bold text-uppercase mb-1">Sub Mesin</label>
            <div class="fw-semibold text-dark">
              <i class="bi bi-diagram-2 text-primary me-2"></i><?= htmlspecialchars($d['nama_sub_mesin'] ?: '-') ?>
            </div>
          </div>

          <!-- Kategori & Lokasi -->
          <?php if (!empty($d['kategori']) || !empty($d['lokasi_m'])): ?>
            <hr class="text-muted opacity-25 my-3">
            
            <?php if (!empty($d['kategori'])): ?>
              <div class="mb-3">
                <label class="form-label text-muted small fw-bold text-uppercase mb-1">Kategori Perangkat</label>
                <div><span class="badge bg-light text-dark border px-2 py-1"><?= htmlspecialchars($d['kategori']) ?></span></div>
              </div>
            <?php endif; ?>

            <?php if (!empty($d['lokasi_m'])): ?>
              <div>
                <label class="form-label text-muted small fw-bold text-uppercase mb-1">Lokasi Perangkat</label>
                <div class="small text-dark fw-semibold">
                  <i class="bi bi-geo-alt text-danger me-1"></i><?= htmlspecialchars($d['lokasi_m']) ?>
                </div>
              </div>
            <?php endif; ?>
          <?php endif; ?>

        </div>
      </div>
    </div>

  </div>

</div>

<?php include "../template/footer.php"; ?>
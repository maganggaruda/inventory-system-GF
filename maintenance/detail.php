<?php
include "../koneksi.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Query data riwayat maintenance beserta relasi komponen dan spesifikasinya
$stmt = mysqli_prepare($conn, "
    SELECT 
        rm.*, 
        k.*, 
        m.nama_mesin, 
        m.serial_number as serial_number_induk,
        m.lokasi as lokasi_m,
        sm.nama_sub_mesin
    FROM riwayat_maintenance rm
    LEFT JOIN komponen k ON rm.id_komponen = k.id
    LEFT JOIN mesin m ON k.id_mesin = m.id
    LEFT JOIN sub_mesin sm ON k.id_sub_mesin = sm.id
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

// Set badge status maintenance
$badgeStatus = 'bg-success';
if (($d['status'] ?? '') == 'Proses') $badgeStatus = 'bg-warning text-dark';
if (($d['status'] ?? '') == 'Pending') $badgeStatus = 'bg-danger';
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
            <?= htmlspecialchars(!empty($d['status']) ? $d['status'] : 'Selesai') ?>
          </span>
        </div>
        <div class="card-body p-4">
          
          <!-- Box Tindakan Maintenance -->
          <div class="mb-4">
            <label class="form-label text-muted small fw-bold text-uppercase mb-2">Tindakan Maintenance</label>
            <div class="p-3 bg-light rounded-3 text-dark border">
              <?= !empty($d['tindakan']) ? nl2br(htmlspecialchars($d['tindakan'])) : '<span class="text-muted fst-italic">Tidak ada rincian tindakan.</span>' ?>
            </div>
          </div>

          <!-- Box Sparepart Diganti -->
          <div class="mb-4">
            <label class="form-label text-muted small fw-bold text-uppercase mb-2">Sparepart Diganti</label>
            <div class="p-3 bg-light rounded-3 text-dark border">
              <?php 
              $sparepart_val = !empty($d['sparepart']) ? $d['sparepart'] : ($d['sparepart_diganti'] ?? '');
              echo !empty($sparepart_val) ? nl2br(htmlspecialchars($sparepart_val)) : '<span class="text-muted">-</span>';
              ?>
            </div>
          </div>

          <!-- FOTO DOKUMENTASI -->
          <div class="mb-4">
            <label class="form-label text-muted small fw-bold text-uppercase mb-2">Foto Dokumentasi</label>
            <div>
              <?php if (!empty($d['foto']) && file_exists("../uploads/maintenance/" . $d['foto'])): ?>
                <a href="../uploads/maintenance/<?= htmlspecialchars($d['foto']) ?>" target="_blank" title="Klik untuk memperbesar">
                  <img src="../uploads/maintenance/<?= htmlspecialchars($d['foto']) ?>" alt="Foto Dokumentasi" class="img-fluid rounded-3 border shadow-sm" style="max-height: 300px; object-fit: contain;">
                </a>
                <div class="form-text text-muted small mt-1">Klik gambar untuk melihat ukuran penuh.</div>
              <?php else: ?>
                <div class="p-3 bg-light rounded-3 text-muted border fst-italic">Tidak ada foto dokumentasi yang diunggah.</div>
              <?php endif; ?>
            </div>
          </div>

          <hr class="text-muted opacity-25 my-4">

          <!-- Detail Informasi Grid -->
          <div class="row g-3">
            <div class="col-sm-4">
              <label class="form-label text-muted small fw-bold text-uppercase mb-1">Tanggal Maintenance</label>
              <div class="fw-bold text-dark fs-6">
                <i class="bi bi-calendar-event me-2 text-primary"></i><?= !empty($d['tanggal']) ? date('d F Y', strtotime($d['tanggal'])) : '-' ?>
              </div>
            </div>
            
            <div class="col-sm-4">
              <label class="form-label text-muted small fw-bold text-uppercase mb-1">Teknisi / Pelaksana</label>
              <div class="fw-bold text-dark fs-6">
                <i class="bi bi-person me-2 text-primary"></i><?= htmlspecialchars(!empty($d['teknisi']) ? $d['teknisi'] : '-') ?>
              </div>
            </div>

            <div class="col-sm-4">
              <label class="form-label text-muted small fw-bold text-uppercase mb-1">Jenis Maintenance</label>
              <div>
                <span class="badge bg-info text-dark rounded-pill px-3 py-2 fw-semibold">
                  <i class="bi bi-wrench me-1"></i><?= htmlspecialchars($d['jenis'] ?? $d['jenis_maintenance'] ?? 'Corrective') ?>
                </span>
              </div>
            </div>
          </div>

          <!-- Catatan Tambahan -->
          <?php 
          $catatan_val = !empty($d['catatan']) ? $d['catatan'] : ($d['keterangan'] ?? '');
          if (!empty($catatan_val)): 
          ?>
            <div class="mt-4">
              <label class="form-label text-muted small fw-bold text-uppercase mb-1">Catatan / Keterangan Tambahan</label>
              <p class="small text-muted mb-0 bg-light p-3 rounded-3 border"><?= nl2br(htmlspecialchars($catatan_val)) ?></p>
            </div>
          <?php endif; ?>

        </div>
      </div>
    </div>

    <!-- KARTU KANAN: PERANGKAT TERKAIT & SPESIFIKASI TEKNIS -->
    <div class="col-lg-5 col-xl-4">
      
      <!-- KARTU PERANGKAT TERKAIT -->
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
            <div class="fw-bold text-dark fs-5"><?= htmlspecialchars(!empty($d['nama_bagian']) ? $d['nama_bagian'] : '-') ?></div>
            <?php if (!empty($d['serial_number'])): ?>
              <small class="text-primary font-monospace fw-semibold d-block mt-1">
                <i class="bi bi-qr-code me-1"></i>SN: <?= htmlspecialchars($d['serial_number']) ?>
              </small>
            <?php endif; ?>
          </div>

          <hr class="text-muted opacity-25 my-3">

          <!-- Mesin Induk -->
          <div class="mb-3">
            <label class="form-label text-muted small fw-bold text-uppercase mb-1">Mesin Induk</label>
            <div class="fw-semibold text-dark">
              <i class="bi bi-gear text-primary me-2"></i><?= htmlspecialchars(!empty($d['nama_mesin']) ? $d['nama_mesin'] : '-') ?>
            </div>
            <?php if (!empty($d['serial_number_induk'])): ?>
              <small class="text-muted d-block mt-1 ms-4 font-monospace">SN Mesin: <?= htmlspecialchars($d['serial_number_induk']) ?></small>
            <?php endif; ?>
          </div>

          <!-- Sub Mesin -->
          <div class="mb-3">
            <label class="form-label text-muted small fw-bold text-uppercase mb-1">Sub Mesin</label>
            <div class="fw-semibold text-dark">
              <i class="bi bi-diagram-2 text-primary me-2"></i><?= htmlspecialchars(!empty($d['nama_sub_mesin']) ? $d['nama_sub_mesin'] : '-') ?>
            </div>
          </div>

          <!-- Kategori & Lokasi -->
          <?php if (!empty($d['kategori']) || !empty($d['lokasi_m']) || !empty($d['lokasi'])): ?>
            <hr class="text-muted opacity-25 my-3">
            
            <?php if (!empty($d['kategori'])): ?>
              <div class="mb-3">
                <label class="form-label text-muted small fw-bold text-uppercase mb-1">Kategori Perangkat</label>
                <div><span class="badge bg-light text-dark border px-2 py-1"><?= htmlspecialchars($d['kategori']) ?></span></div>
              </div>
            <?php endif; ?>

            <?php 
            $lokasi_perangkat = !empty($d['lokasi']) ? $d['lokasi'] : ($d['lokasi_m'] ?? '');
            if (!empty($lokasi_perangkat)): 
            ?>
              <div>
                <label class="form-label text-muted small fw-bold text-uppercase mb-1">Lokasi Penempatan</label>
                <div class="small text-dark fw-semibold">
                  <i class="bi bi-geo-alt text-danger me-1"></i><?= htmlspecialchars($lokasi_perangkat) ?>
                </div>
              </div>
            <?php endif; ?>
          <?php endif; ?>

        </div>
      </div>

      <!-- KARTU SPESIFIKASI TEKNIS (Gaya Tabel Seperti Gambar) -->
      <?php 
      $has_specs = !empty($d['brand']) || !empty($d['merk']) || !empty($d['tipe']) || !empty($d['part_number']) || !empty($d['daya']) || !empty($d['io_address']) || !empty($d['input_voltage']) || !empty($d['frekuensi_input']) || !empty($d['arus_input']) || !empty($d['output']) || !empty($d['frekuensi_output']) || !empty($d['ip_rating']);
      if ($has_specs):
      ?>
      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white py-3 border-0">
          <h6 class="fw-bold text-primary mb-0 fs-6">
            <i class="bi bi-cpu me-2"></i>Spesifikasi Teknis
          </h6>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-bordered mb-0 align-middle">
              <tbody>
                <?php $brand_val = !empty($d['brand']) ? $d['brand'] : ($d['merk'] ?? ''); ?>
                <?php if (!empty($brand_val)): ?>
                <tr>
                  <td class="bg-light text-muted small fw-bold text-uppercase px-3 py-2" style="width: 40%;">Brand / Merk</td>
                  <td class="fw-bold text-dark px-3 py-2"><?= htmlspecialchars($brand_val) ?></td>
                </tr>
                <?php endif; ?>

                <?php if (!empty($d['tipe'])): ?>
                <tr>
                  <td class="bg-light text-muted small fw-bold text-uppercase px-3 py-2">Tipe</td>
                  <td class="fw-bold text-dark px-3 py-2"><?= htmlspecialchars($d['tipe']) ?></td>
                </tr>
                <?php endif; ?>

                <?php if (!empty($d['part_number'])): ?>
                <tr>
                  <td class="bg-light text-muted small fw-bold text-uppercase px-3 py-2">Part Number</td>
                  <td class="fw-bold text-dark font-monospace px-3 py-2"><?= htmlspecialchars($d['part_number']) ?></td>
                </tr>
                <?php endif; ?>

                <?php if (!empty($d['daya'])): ?>
                <tr>
                  <td class="bg-light text-muted small fw-bold text-uppercase px-3 py-2">Daya</td>
                  <td class="fw-bold text-dark px-3 py-2"><?= htmlspecialchars($d['daya']) ?></td>
                </tr>
                <?php endif; ?>

                <tr>
                  <td class="bg-light text-muted small fw-bold text-uppercase px-3 py-2">IO Address</td>
                  <td class="fw-bold text-dark font-monospace px-3 py-2"><?= !empty($d['io_address']) ? htmlspecialchars($d['io_address']) : '-' ?></td>
                </tr>

                <?php if (!empty($d['input_voltage'])): ?>
                <tr>
                  <td class="bg-light text-muted small fw-bold text-uppercase px-3 py-2">Input Voltage</td>
                  <td class="fw-bold text-dark px-3 py-2"><?= htmlspecialchars($d['input_voltage']) ?></td>
                </tr>
                <?php endif; ?>

                <?php if (!empty($d['frekuensi_input'])): ?>
                <tr>
                  <td class="bg-light text-muted small fw-bold text-uppercase px-3 py-2">Frekuensi Input</td>
                  <td class="fw-bold text-dark px-3 py-2"><?= htmlspecialchars($d['frekuensi_input']) ?></td>
                </tr>
                <?php endif; ?>

                <?php if (!empty($d['arus_input'])): ?>
                <tr>
                  <td class="bg-light text-muted small fw-bold text-uppercase px-3 py-2">Arus Input</td>
                  <td class="fw-bold text-dark px-3 py-2"><?= htmlspecialchars($d['arus_input']) ?></td>
                </tr>
                <?php endif; ?>

                <?php if (!empty($d['output'])): ?>
                <tr>
                  <td class="bg-light text-muted small fw-bold text-uppercase px-3 py-2">Output</td>
                  <td class="fw-bold text-dark px-3 py-2"><?= htmlspecialchars($d['output']) ?></td>
                </tr>
                <?php endif; ?>

                <?php if (!empty($d['frekuensi_output'])): ?>
                <tr>
                  <td class="bg-light text-muted small fw-bold text-uppercase px-3 py-2">Frekuensi Output</td>
                  <td class="fw-bold text-dark px-3 py-2"><?= htmlspecialchars($d['frekuensi_output']) ?></td>
                </tr>
                <?php endif; ?>

                <?php if (!empty($d['ip_rating'])): ?>
                <tr>
                  <td class="bg-light text-muted small fw-bold text-uppercase px-3 py-2">IP Rating</td>
                  <td class="fw-bold text-dark px-3 py-2"><?= htmlspecialchars($d['ip_rating']) ?></td>
                </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <?php endif; ?>

    </div>

  </div>

</div>

<?php include "../template/footer.php"; ?>
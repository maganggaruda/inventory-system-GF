<?php
include "../koneksi.php";
include "../template/header.php";

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

// Menggunakan JOIN agar bisa menampilkan nama Area & Jenis Mesin
$query_str = "SELECT m.*, jm.nama_jenis_mesin, a.nama_area 
             FROM mesin m
             LEFT JOIN jenis_mesin jm ON m.id_jenis_mesin = jm.id
             LEFT JOIN area_bagian a ON m.id_area = a.id";

if (!empty($keyword)) {
    $kw = "%" . $keyword . "%";
    $query_str .= " WHERE (m.nama_mesin LIKE ? OR m.serial_number LIKE ? OR a.nama_area LIKE ?)";
    $stmt = mysqli_prepare($conn, $query_str . " ORDER BY m.id DESC");
    mysqli_stmt_bind_param($stmt, "sss", $kw, $kw, $kw);
    mysqli_stmt_execute($stmt);
    $sql = mysqli_stmt_get_result($stmt);
} else {
    $sql = mysqli_query($conn, $query_str . " ORDER BY m.id DESC");
}
?>

<div class="container-fluid p-0">
  <!-- PAGE HEADER -->
  <div class="dashboard-header mb-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
      <div>
        <h2 class="dashboard-title m-0">Data Mesin Induk</h2>
        <p class="dashboard-subtitle m-0">Kelola daftar mesin utama berdasarkan Area dan Jenis</p>
      </div>
      <a href="tambah.php" class="btn btn-primary px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
        <i class="bi bi-plus-lg fs-6"></i> Tambah Mesin
      </a>
    </div>
  </div>

  <!-- TABLE CARD -->
  <div class="content-card">
    <div class="card-header-custom d-flex align-items-center justify-content-between">
      <h5 class="card-title-custom m-0"><i class="bi bi-gear-wide-connected me-2"></i>Daftar Mesin Terdaftar</h5>
      <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-7">
        Total: <?= $sql ? mysqli_num_rows($sql) : 0 ?> Mesin
      </span>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead>
          <tr>
            <th class="text-center">No</th>
            <th>Nama Mesin</th>
            <th>Area / Bagian</th>
            <th>Jenis Mesin</th>
            <th>Serial Number</th>
            <th class="text-center">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php 
          $no = 1;
          if ($sql && mysqli_num_rows($sql) > 0) :
            while ($d = mysqli_fetch_assoc($sql)) : ?>
              <tr>
                <td class="text-center text-muted"><?= $no++ ?></td>
                <td>
                  <strong class="text-dark"><?= htmlspecialchars($d['nama_mesin']) ?></strong>
                </td>
                <td><i class="bi bi-geo-alt text-danger me-1"></i><?= htmlspecialchars($d['nama_area'] ?? '-') ?></td>
                <td><span class="badge bg-info-subtle text-info"><?= htmlspecialchars($d['nama_jenis_mesin'] ?? '-') ?></span></td>
                <td><code class="text-primary fw-bold"><?= htmlspecialchars($d['serial_number'] ?? '-') ?></code></td>
                <td class="text-center">
                  <div class="btn-group btn-group-sm">
                    <!-- Tombol Detail dengan tambahan data-atribut gambar & keterangan -->
                    <button type="button" class="btn btn-outline-info btn-detail" 
                            data-bs-toggle="modal" data-bs-target="#modalDetailMesin"
                            data-nama="<?= htmlspecialchars($d['nama_mesin']) ?>"
                            data-area="<?= htmlspecialchars($d['nama_area'] ?? '-') ?>"
                            data-jenis="<?= htmlspecialchars($d['nama_jenis_mesin'] ?? '-') ?>"
                            data-sn="<?= htmlspecialchars($d['serial_number'] ?? '-') ?>"
                            data-keterangan="<?= htmlspecialchars($d['keterangan'] ?? '-') ?>"
                            data-gambar="<?= htmlspecialchars($d['gambar'] ?? '') ?>">
                      <i class="bi bi-eye"></i>
                    </button>
                    <a href="edit.php?id=<?= $d['id'] ?>" class="btn btn-outline-warning"><i class="bi bi-pencil-square"></i></a>
                    <!-- Tombol Hapus dengan konfirmasi JavaScript -->
                    <a href="hapus.php?id=<?= $d['id'] ?>" class="btn btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus mesin <?= htmlspecialchars($d['nama_mesin']) ?>?')"><i class="bi bi-trash"></i></a>
                  </div>
                </td>
              </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- MODAL DETAIL MESIN -->
<div class="modal fade" id="modalDetailMesin" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title fw-bold" id="modalDetailLabel"><i class="bi bi-info-circle me-2"></i>Detail Informasi Mesin</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-5 text-center">
            <div id="container-gambar" class="border rounded p-2 bg-light d-flex align-items-center justify-content-center" style="min-height: 200px;">
              <img id="detail-gambar" src="" alt="Foto Mesin" class="img-fluid rounded" style="max-height: 220px; object-fit: cover; display: none;">
              <span id="no-image-text" class="text-muted small">Tidak ada foto</span>
            </div>
          </div>
          <div class="col-md-7">
            <table class="table table-borderless table-sm mb-0">
              <tr>
                <td width="35%" class="fw-semibold text-muted">Nama Mesin</td>
                <td width="5%">:</td>
                <td><strong id="detail-nama" class="text-dark">-</strong></td>
              </tr>
              <tr>
                <td class="fw-semibold text-muted">Serial Number</td>
                <td>:</td>
                <td><code id="detail-sn" class="text-primary">-</code></td>
              </tr>
              <tr>
                <td class="fw-semibold text-muted">Area / Bagian</td>
                <td>:</td>
                <td id="detail-area">-</td>
              </tr>
              <tr>
                <td class="fw-semibold text-muted">Jenis Mesin</td>
                <td>:</td>
                <td id="detail-jenis">-</td>
              </tr>
              <tr>
                <td class="fw-semibold text-muted">Keterangan</td>
                <td>:</td>
                <td id="detail-keterangan" class="text-secondary">-</td>
              </tr>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- SCRIPT UNTUK MODAL DETAIL -->
<script>
document.addEventListener('DOMContentLoaded', function () {
  const modal = document.getElementById('modalDetailMesin');
  modal.addEventListener('show.bs.modal', function (event) {
    const btn = event.relatedTarget;
    
    document.getElementById('detail-nama').textContent = btn.getAttribute('data-nama');
    document.getElementById('detail-area').textContent = btn.getAttribute('data-area');
    document.getElementById('detail-jenis').textContent = btn.getAttribute('data-jenis');
    document.getElementById('detail-sn').textContent = btn.getAttribute('data-sn');
    document.getElementById('detail-keterangan').textContent = btn.getAttribute('data-keterangan') || '-';

    // Handle Tampilan Gambar
    const namaGambar = btn.getAttribute('data-gambar');
    const imgElement = document.getElementById('detail-gambar');
    const noImgText = document.getElementById('no-image-text');

    if (namaGambar && namaGambar !== '') {
      imgElement.src = '../uploads/mesin/' + namaGambar;
      imgElement.style.display = 'block';
      noImgText.style.display = 'none';
    } else {
      imgElement.src = '';
      imgElement.style.display = 'none';
      noImgText.style.display = 'block';
    }
  });
});
</script>

<?php include "../template/footer.php"; ?>
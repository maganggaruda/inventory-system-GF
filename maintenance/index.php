<?php
include "../koneksi.php";

// Filter Parameters
$filter_mesin     = isset($_GET['mesin']) ? $_GET['mesin'] : '';
$filter_sub_mesin = isset($_GET['sub_mesin']) ? $_GET['sub_mesin'] : '';
$filter_status    = isset($_GET['status']) ? $_GET['status'] : '';
$keyword          = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

// Build Query Condition (Perbaikan: Hubungkan id_mesin lewat sub_mesin -> sm.id_mesin)
$where_clauses = [];

if (!empty($filter_mesin)) {
    $where_clauses[] = "sm.id_mesin = '" . mysqli_real_escape_string($conn, $filter_mesin) . "'";
}
if (!empty($filter_sub_mesin)) {
    $where_clauses[] = "k.id_sub_mesin = '" . mysqli_real_escape_string($conn, $filter_sub_mesin) . "'";
}
if (!empty($filter_status)) {
    $where_clauses[] = "rm.status = '" . mysqli_real_escape_string($conn, $filter_status) . "'";
}
if (!empty($keyword)) {
    $kw = mysqli_real_escape_string($conn, $keyword);
    $where_clauses[] = "(k.nama_bagian LIKE '%$kw%' OR m.nama_mesin LIKE '%$kw%' OR m.serial_number LIKE '%$kw%' OR rm.teknisi LIKE '%$kw%' OR rm.tindakan LIKE '%$kw%')";
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// Fetch Data Maintenance (Perbaikan: JOIN mesin lewat sub_mesin)
$query_maint = mysqli_query($conn, "
    SELECT 
        rm.*,
        k.nama_bagian,
        m.nama_mesin,
        m.serial_number,
        sm.nama_sub_mesin
    FROM riwayat_maintenance rm
    LEFT JOIN komponen k ON rm.id_komponen = k.id
    LEFT JOIN sub_mesin sm ON k.id_sub_mesin = sm.id
    LEFT JOIN mesin m ON sm.id_mesin = m.id
    $where_sql
    ORDER BY rm.tanggal DESC
");

$data_maint_list = [];
if ($query_maint && mysqli_num_rows($query_maint) > 0) {
    while ($row = mysqli_fetch_assoc($query_maint)) {
        $data_maint_list[] = $row;
    }
}

// Fetch Mesin & Sub Mesin untuk Filter
$list_mesin = mysqli_query($conn, "SELECT id, nama_mesin FROM mesin ORDER BY nama_mesin ASC");
$list_sub_mesin = mysqli_query($conn, "SELECT id, nama_sub_mesin FROM sub_mesin ORDER BY nama_sub_mesin ASC");

include "../template/header.php";
?>

<div class="container-fluid mb-2 px-3 py-2">

    <!-- HEADER BAR (KARTU PUTIH) -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-0 fs-4">Riwayat Maintenance</h3>
                <p class="text-muted small mb-0">Kelola dan pantau seluruh catatan pemeliharaan mesin</p>
            </div>
            <div>
                <a href="tambah.php" class="btn btn-primary fw-semibold rounded-3 px-3 shadow-sm text-nowrap">
                    + Tambah Maintenance
                </a>
            </div>
        </div>
    </div>

    <!-- CARD FILTER -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="" class="row g-2 align-items-end">
                
                <!-- Filter Mesin -->
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Mesin</label>
                    <select name="mesin" class="form-select form-select-sm rounded-3">
                        <option value="">-- Semua Mesin --</option>
                        <?php if ($list_mesin): ?>
                            <?php while ($m = mysqli_fetch_assoc($list_mesin)): ?>
                                <option value="<?= $m['id'] ?>" <?= $filter_mesin == $m['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($m['nama_mesin']) ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Filter Sub Mesin -->
                <div class="col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Sub Mesin</label>
                    <select name="sub_mesin" class="form-select form-select-sm rounded-3">
                        <option value="">-- Semua Sub Mesin --</option>
                        <?php if ($list_sub_mesin): ?>
                            <?php while ($sm = mysqli_fetch_assoc($list_sub_mesin)): ?>
                                <option value="<?= $sm['id'] ?>" <?= $filter_sub_mesin == $sm['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sm['nama_sub_mesin']) ?>
                                </option>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Filter Status -->
                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm rounded-3">
                        <option value="">-- Semua Status --</option>
                        <option value="Selesai" <?= $filter_status == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                        <option value="Proses" <?= $filter_status == 'Proses' ? 'selected' : '' ?>>Proses</option>
                        <option value="Pending" <?= $filter_status == 'Pending' ? 'selected' : '' ?>>Pending</option>
                    </select>
                </div>

                <!-- Input Kata Kunci & Button -->
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted mb-1">Kata Kunci</label>
                    <div class="d-flex gap-2">
                        <input type="text" name="keyword" class="form-control form-control-sm rounded-3" placeholder="Nama Bagian / Serial Number / Teknisi..." value="<?= htmlspecialchars($keyword) ?>">
                        
                        <!-- Tombol Filter Centered -->
                        <button type="submit" class="btn btn-primary btn-sm rounded-3 d-inline-flex align-items-center justify-content-center p-0" style="width: 45px; height: 40px;" title="Filter">
                            <i class="bi bi-funnel fs-6"></i>
                        </button>
                        
                        <!-- Tombol Reset Centered -->
                        <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-3 d-inline-flex align-items-center justify-content-center p-0" style="width: 40px; height: 40px;" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise fs-6"></i>
                        </a>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- TABLE CARD -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-primary mb-0 fs-6">
                <i class="bi bi-tools me-2"></i>Daftar Log Maintenance
            </h5>
            <span class="badge bg-light text-primary border rounded-pill px-3 py-2">
                Total: <?= count($data_maint_list) ?> Data
            </span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-uppercase small text-muted">
                            <th width="50" class="text-center">NO</th>
                            <th width="110">TANGGAL</th>
                            <th>KOMPONEN / MESIN</th>
                            <th>JENIS & TEKNISI</th>
                            <th>TINDAKAN & SPAREPART</th>
                            <th class="text-center" width="100">STATUS</th>
                            <th class="text-center" width="130">AKSI</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($data_maint_list)): ?>
                            <?php $no = 1; foreach ($data_maint_list as $m): ?>
                                <?php
                                $badge_status = "bg-success";
                                if ($m['status'] == "Pending") $badge_status = "bg-danger";
                                if ($m['status'] == "Proses") $badge_status = "bg-warning text-dark";
                                ?>
                                <tr>
                                    <td class="text-center text-muted fw-normal"><?=$no++?></td>
                                    <td>
                                        <strong class="text-dark"><?= date('d/m/Y', strtotime($m['tanggal'])) ?></strong>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($m['nama_bagian'] ?? '-') ?></div>
                                        <small class="text-muted">
                                            <i class="bi bi-gear me-1"></i><?= htmlspecialchars($m['nama_mesin'] ?? '-') ?>
                                            <?php if (!empty($m['serial_number'])): ?>
                                                (SN: <span class="text-primary font-monospace"><?= htmlspecialchars($m['serial_number']) ?></span>)
                                            <?php endif; ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark rounded-pill mb-1 fw-normal"><?= htmlspecialchars($m['jenis_maintenance'] ?? $m['jenis'] ?? 'Maintenance') ?></span><br>
                                        <small class="text-muted"><i class="bi bi-person me-1"></i><?= htmlspecialchars($m['teknisi'] ?? '-') ?></small>
                                    </td>
                                    <td>
                                        <div><strong>Tindakan:</strong> <?= htmlspecialchars($m['tindakan'] ?? '-') ?></div>
                                        <small class="text-muted"><strong>Sparepart:</strong> <?= htmlspecialchars($m['sparepart_diganti'] ?? '-') ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?=$badge_status?> rounded-pill px-3 py-2"><?=$m['status']?></span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group border rounded-3 overflow-hidden shadow-sm" role="group">
                                            <a href="detail.php?id=<?=$m['id']?>" 
                                               class="btn btn-white text-info btn-sm px-2 py-1 border-end d-inline-flex align-items-center justify-content-center" 
                                               style="border-color: #0dcaf0 !important;" 
                                               title="Lihat Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <a href="edit.php?id=<?=$m['id']?>" 
                                               class="btn btn-white text-warning btn-sm border-end d-inline-flex align-items-center justify-content-center" 
                                               style="border-color: #ffc107 !important;" 
                                               title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <a href="hapus.php?id=<?=$m['id']?>" 
                                               class="btn btn-white text-danger btn-sm d-inline-flex align-items-center justify-content-center" 
                                               style="border-color: #dc3545 !important;" 
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus log maintenance ini?')" 
                                               title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox display-6"></i><br>Belum ada data log maintenance.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include "../template/footer.php"; ?>
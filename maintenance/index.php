<?php
include "../koneksi.php";
date_default_timezone_set('Asia/Jakarta');

// ==========================================
// 1. LOGIKA SIMPAN DATA (POST)
// ==========================================
$error = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['simpan_maintenance'])) {
    $id_komponen   = $_POST['id_komponen'] ?? '';
    $status        = $_POST['status'] ?? '';
    $teknisi       = trim($_POST['teknisi'] ?? '');
    $tindakan      = trim($_POST['tindakan'] ?? '');
    $jenis         = $_POST['jenis'] ?? '';
    $sparepart     = trim($_POST['sparepart'] ?? '');
    $catatan       = trim($_POST['catatan'] ?? '');
    $input_tanggal = $_POST['tanggal'] ?? date('Y-m-d'); 

    // Gabungkan Tanggal & Jam saat ini
    $tanggal_lengkap = $input_tanggal . ' ' . date('H:i:s');

    if (!empty($id_komponen) && !empty($tindakan)) {
        // Prepared Statement (Aman dari SQL Injection)
        $stmt = mysqli_prepare($conn, "
            INSERT INTO riwayat_maintenance (id_komponen, tanggal, status, teknisi, tindakan, jenis, sparepart, catatan) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmt, "isssssss", $id_komponen, $tanggal_lengkap, $status, $teknisi, $tindakan, $jenis, $sparepart, $catatan);
        
        if (mysqli_stmt_execute($stmt)) {
            // Update Kondisi Komponen jika Selesai
            if ($status == 'Selesai') {
                $stmt_update = mysqli_prepare($conn, "UPDATE komponen SET kondisi = 'Baik' WHERE id = ?");
                mysqli_stmt_bind_param($stmt_update, "i", $id_komponen);
                mysqli_stmt_execute($stmt_update);
            }

            echo "<script>alert('Data maintenance berhasil ditambahkan!'); window.location='index.php';</script>";
            exit;
        } else {
            $error = "Gagal menyimpan data: " . mysqli_error($conn);
        }
    } else {
        $error = "Mohon lengkapi semua bidang wajib (*)!";
    }
}

// Navigasi Halaman (Halaman Utama / Form Tambah)
$page = $_GET['page'] ?? 'list';

include "../template/header.php";
?>

<div class="container-fluid mb-2 px-3 py-2">

<?php if ($page == 'tambah'): ?>
    <!-- ========================================== -->
    <!-- 2. HALAMAN FORM TAMBAH DATA                -->
    <!-- ========================================== -->
    <?php
    // PERBAIKAN: Mengambil m.serial_number, bukan m.kode_mesin
    $q_komponen = mysqli_query($conn, "
        SELECT k.id, k.nama_bagian, m.nama_mesin, m.serial_number 
        FROM komponen k 
        LEFT JOIN mesin m ON k.id_mesin = m.id 
        ORDER BY k.nama_bagian ASC
    ");
    ?>

    <!-- Import CSS Select2 & Theme Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="index.php" class="btn btn-outline-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <i class="bi bi-arrow-left fs-5"></i>
            </a>
            <div>
                <h2 class="fw-bold text-dark m-0">Tambah Catatan Maintenance</h2>
                <p class="text-muted small m-0 mt-1">Lengkapi formulir di bawah ini untuk mencatat tindakan maintenance baru</p>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
        <h5 class="fw-bold text-primary mb-4 d-flex align-items-center" style="color: #0056a6 !important;">
            <i class="bi bi-plus-circle me-2"></i> Form Maintenance
        </h5>

        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger border-0 rounded-3 mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="index.php?page=tambah">
            <input type="hidden" name="simpan_maintenance" value="1">

            <!-- Section: INFORMASI UTAMA -->
            <div class="mb-4">
                <h6 class="fw-bold text-primary small text-uppercase mb-3 d-flex align-items-center" style="letter-spacing: 0.5px; color: #0056a6 !important;">
                    <i class="bi bi-info-circle me-2"></i> INFORMASI UTAMA
                </h6>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Komponen Terkait <span class="text-danger">*</span></label>
                        <select name="id_komponen" id="select-komponen" class="form-select border-light-subtle rounded-3" required>
                            <option value="">-- Cari / Pilih Komponen --</option>
                            <?php while ($k = mysqli_fetch_assoc($q_komponen)) : ?>
                                <option value="<?= $k['id'] ?>">
                                    <?= htmlspecialchars($k['nama_bagian']) ?> — <?= htmlspecialchars($k['nama_mesin'] ?: 'Tanpa Mesin') ?> (SN: <?= htmlspecialchars($k['serial_number'] ?: '-') ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Tanggal Maintenance <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal" class="form-control border-light-subtle rounded-3" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">Jenis Maintenance</label>
                        <select name="jenis" class="form-select border-light-subtle rounded-3">
                            <option value="Preventive">Preventive</option>
                            <option value="Corrective">Corrective</option>
                            <option value="Breakdown">Breakdown</option>
                            <option value="Predictive">Predictive</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">Teknisi / Petugas</label>
                        <input type="text" name="teknisi" class="form-control border-light-subtle rounded-3" placeholder="Contoh: Nama Teknisi / Tim Maintenance">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">Status Pekerjaan <span class="text-danger">*</span></label>
                        <select name="status" class="form-select border-light-subtle rounded-3" required>
                            <option value="Selesai">Selesai</option>
                            <option value="Proses">Proses</option>
                            <option value="Pending">Pending</option>
                        </select>
                    </div>
                </div>
            </div>

            <hr class="my-4 border-light-subtle">

            <!-- Section: RINCIAN MAINTENANCE -->
            <div class="mb-4">
                <h6 class="fw-bold text-primary small text-uppercase mb-3 d-flex align-items-center" style="letter-spacing: 0.5px; color: #0056a6 !important;">
                    <i class="bi bi-wrench me-2"></i> RINCIAN MAINTENANCE
                </h6>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark">Tindakan Perbaikan / Maintenance <span class="text-danger">*</span></label>
                        <textarea name="tindakan" class="form-control border-light-subtle rounded-3" rows="3" placeholder="Rincian pekerjaan yang dilakukan..." required></textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark">Sparepart Yang Digunakan / Diganti</label>
                        <input type="text" name="sparepart" class="form-control border-light-subtle rounded-3" placeholder="Contoh: Bearing 6204 (1 Pcs), O-Ring Kit">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark">Catatan / Keterangan Tambahan</label>
                        <textarea name="catatan" class="form-control border-light-subtle rounded-3" rows="2" placeholder="Catatan lanjutan, rekomendasi tindakan berikutnya..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Submit Actions -->
            <div class="d-flex gap-2 pt-3">
                <button type="submit" class="btn btn-primary fw-semibold px-4 rounded-3" style="background-color: #0056a6; border: none;">
                    <i class="bi bi-save me-1"></i> Simpan Data
                </button>
                <a href="index.php" class="btn btn-light border px-4 rounded-3">Batal</a>
            </div>

        </form>
    </div>

    <!-- Import Script jQuery & Select2 -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#select-komponen').select2({
                theme: 'bootstrap-5',
                placeholder: '-- Ketik untuk mencari Komponen / Serial Number --',
                allowClear: true,
                width: '100%'
            });
        });
    </script>

<?php else: ?>
    <!-- ========================================== -->
    <!-- 3. HALAMAN DAFTAR DATA LOG (LIST)          -->
    <!-- ========================================== -->
    <?php
    $filter_mesin     = $_GET['mesin'] ?? '';
    $filter_sub_mesin = $_GET['sub_mesin'] ?? '';
    $filter_status    = $_GET['status'] ?? '';
    $keyword          = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

    $where_clauses = [];
    $params = [];
    $types = "";

    if (!empty($filter_mesin)) {
        $where_clauses[] = "k.id_mesin = ?";
        $params[] = $filter_mesin;
        $types .= "i";
    }
    if (!empty($filter_sub_mesin)) {
        $where_clauses[] = "k.id_sub_mesin = ?";
        $params[] = $filter_sub_mesin;
        $types .= "i";
    }
    if (!empty($filter_status)) {
        $where_clauses[] = "rm.status = ?";
        $params[] = $filter_status;
        $types .= "s";
    }
    if (!empty($keyword)) {
        // PERBAIKAN: Menggunakan m.serial_number untuk pencarian keyword
        $where_clauses[] = "(k.nama_bagian LIKE ? OR m.serial_number LIKE ? OR rm.teknisi LIKE ? OR rm.tindakan LIKE ?)";
        $kw = "%" . $keyword . "%";
        array_push($params, $kw, $kw, $kw, $kw);
        $types .= "ssss";
    }

    $where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";

    // PERBAIKAN: Mengambil m.serial_number dari tabel mesin
    $sql = "
        SELECT 
            rm.*,
            k.nama_bagian,
            m.nama_mesin,
            m.serial_number,
            sm.nama_sub_mesin
        FROM riwayat_maintenance rm
        LEFT JOIN komponen k ON rm.id_komponen = k.id
        LEFT JOIN mesin m ON k.id_mesin = m.id
        LEFT JOIN sub_mesin sm ON k.id_sub_mesin = sm.id
        $where_sql
        ORDER BY rm.tanggal DESC
    ";

    $stmt = mysqli_prepare($conn, $sql);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $query_maint = mysqli_stmt_get_result($stmt);

    $data_maint_list = [];
    if ($query_maint) {
        while ($row = mysqli_fetch_assoc($query_maint)) {
            $data_maint_list[] = $row;
        }
    }

    $list_mesin = mysqli_query($conn, "SELECT id, nama_mesin FROM mesin ORDER BY nama_mesin ASC");
    $list_sub_mesin = mysqli_query($conn, "SELECT id, nama_sub_mesin FROM sub_mesin ORDER BY nama_sub_mesin ASC");
    ?>

    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h3 class="fw-bold text-dark mb-0 fs-4">Riwayat Maintenance</h3>
                <p class="text-muted small mb-0">Kelola dan pantau seluruh catatan pemeliharaan mesin</p>
            </div>
            <div>
                <a href="index.php?page=tambah" class="btn btn-primary fw-semibold rounded-3 px-3 shadow-sm text-nowrap">
                    + Tambah Maintenance
                </a>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="index.php" class="row g-2 align-items-end">
                
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

                <div class="col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm rounded-3">
                        <option value="">-- Semua Status --</option>
                        <option value="Selesai" <?= $filter_status == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                        <option value="Proses" <?= $filter_status == 'Proses' ? 'selected' : '' ?>>Proses</option>
                        <option value="Pending" <?= $filter_status == 'Pending' ? 'selected' : '' ?>>Pending</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-bold text-muted mb-1">Kata Kunci</label>
                    <div class="d-flex gap-2">
                        <input type="text" name="keyword" class="form-control form-control-sm rounded-3" placeholder="Nama Bagian / Serial Number / Teknisi..." value="<?= htmlspecialchars($keyword) ?>">

                        <button type="submit" class="btn btn-primary btn-sm rounded-3 d-inline-flex align-items-center justify-content-center p-0" style="width: 45px; height: 31px;" title="Filter">
                            <i class="bi bi-funnel fs-6"></i>
                        </button>
                        
                        <a href="index.php" class="btn btn-outline-secondary btn-sm rounded-3 d-inline-flex align-items-center justify-content-center p-0" style="width: 40px; height: 31px;" title="Reset Filter">
                            <i class="bi bi-arrow-counterclockwise fs-6"></i>
                        </a>
                    </div>
                </div>

            </form>
        </div>
    </div>

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
                                        <span class="badge bg-info text-dark rounded-pill mb-1 fw-normal"><?= htmlspecialchars($m['jenis'] ?? $m['jenis_maintenance'] ?? 'Maintenance') ?></span><br>
                                        <small class="text-muted"><i class="bi bi-person me-1"></i><?= htmlspecialchars($m['teknisi'] ?? '-') ?></small>
                                    </td>
                                    <td>
                                        <div><strong>Tindakan:</strong> <?= htmlspecialchars($m['tindakan'] ?? '-') ?></div>
                                        <small class="text-muted"><strong>Sparepart:</strong> <?= htmlspecialchars($m['sparepart'] ?? $m['sparepart_diganti'] ?? '-') ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?=$badge_status?> rounded-pill px-3 py-2"><?=$m['status']?></span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group shadow-sm rounded-3" role="group">
                                            <a href="detail.php?id=<?=$m['id']?>" 
                                               class="btn btn-outline-info btn-sm px-2 py-1 d-inline-flex align-items-center justify-content-center" 
                                               title="Lihat Detail">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <a href="edit.php?id=<?=$m['id']?>" 
                                               class="btn btn-outline-warning btn-sm px-2 py-1 d-inline-flex align-items-center justify-content-center" 
                                               title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <a href="hapus.php?id=<?=$m['id']?>" 
                                               class="btn btn-outline-danger btn-sm px-2 py-1 d-inline-flex align-items-center justify-content-center" 
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
<?php endif; ?>

</div>

<?php include "../template/footer.php"; ?>
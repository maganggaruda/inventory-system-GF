<?php
include "../koneksi.php";

/* ===========================
   PROSES UPDATE STATUS & KONDISI
=========================== */

// 1. Update Status Maintenance (Otomatis update kondisi komponen)
if (isset($_POST['update_status_maintenance'])) {
    $id_maint = intval($_POST['id_maintenance']);
    $status_baru = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Update status riwayat maintenance
    mysqli_query($conn, "UPDATE riwayat_maintenance SET status = '$status_baru' WHERE id = $id_maint");
    
    // Jika diubah jadi 'Selesai', otomatis ubah kondisi komponen terkait jadi 'Baik'
    if ($status_baru == 'Selesai') {
        $get_maint = mysqli_query($conn, "SELECT id_komponen FROM riwayat_maintenance WHERE id = $id_maint");
        if ($data_maint = mysqli_fetch_assoc($get_maint)) {
            $id_komp = $data_maint['id_komponen'];
            if ($id_komp) {
                mysqli_query($conn, "UPDATE komponen SET kondisi = 'Baik' WHERE id = $id_komp");
            }
        }
    }
    
    header("Location: index.php");
    exit;
}

// 2. Update Kondisi Komponen
if (isset($_POST['update_kondisi_komponen'])) {
    $id_komp = intval($_POST['id_komponen']);
    $kondisi_baru = mysqli_real_escape_string($conn, $_POST['kondisi']);
    
    mysqli_query($conn, "UPDATE komponen SET kondisi = '$kondisi_baru' WHERE id = $id_komp");
    header("Location: index.php");
    exit;
}

include "../template/header.php";

/* ===========================
   STATISTIK
=========================== */

$d_total_mesin = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT COUNT(*) total FROM mesin")
)['total'];

$d_total_komponen = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT COUNT(*) total FROM komponen")
)['total'];

$d_komponen_perhatian = mysqli_fetch_assoc(
    mysqli_query($conn,"SELECT COUNT(*) total FROM komponen WHERE kondisi!='Baik'")
)['total'];

$bulan_ini = date('Y-m');

$d_maint_bulan_ini = mysqli_fetch_assoc(
    mysqli_query($conn,"
    SELECT COUNT(*) total
    FROM riwayat_maintenance
    WHERE DATE_FORMAT(tanggal,'%Y-%m')='$bulan_ini'
    ")
)['total'];


/* ===========================
   MAINTENANCE (AMBIL DATA)
=========================== */

$q_maintenance = mysqli_query($conn,"
SELECT
rm.*,
k.nama_bagian,
m.nama_mesin

FROM riwayat_maintenance rm

LEFT JOIN komponen k
ON rm.id_komponen=k.id

LEFT JOIN mesin m
ON k.id_mesin=m.id

ORDER BY rm.tanggal DESC
LIMIT 5
");

// Simpan data maintenance ke array untuk penanganan Modal terpisah
$data_maintenance_list = [];
if($q_maintenance && mysqli_num_rows($q_maintenance) > 0) {
    while($row = mysqli_fetch_assoc($q_maintenance)) {
        $data_maintenance_list[] = $row;
    }
}


/* ===========================
   KOMPONEN (AMBIL DATA)
=========================== */

$q_komponen = mysqli_query($conn,"
SELECT
k.*,
m.nama_mesin

FROM komponen k

LEFT JOIN mesin m
ON k.id_mesin=m.id

WHERE kondisi!='Baik'

ORDER BY k.id DESC

LIMIT 5
");

// Simpan data komponen ke array untuk penanganan Modal terpisah
$data_komponen_list = [];
if($q_komponen && mysqli_num_rows($q_komponen) > 0) {
    while($row = mysqli_fetch_assoc($q_komponen)) {
        $data_komponen_list[] = $row;
    }
}
?>

<div class="container-fluid">

    <!-- HEADER -->
    <div class="dashboard-header mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h2 class="dashboard-title">
                    Inventory & Maintenance System
                </h2>
                <p class="dashboard-subtitle">
                    PT Garudafood Putra Putri Jaya Tbk — Monitoring & Rekapitulasi Pemeliharaan.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end">
    <span class="dashboard-date d-inline-flex align-items-center gap-2">
        <i class="bi bi-calendar3"></i>
        <span><?=date('d F Y')?></span>
        <span class="border-start ps-2 ms-1 text-muted">
            <i class="bi bi-clock me-1"></i><span id="jam-realtime">--:--:--</span> WIB
        </span>
    </span>
</div>
        </div>
    </div>

    <!-- CARD STATISTIK -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="stat-box blue">
                <div>
                    <div class="stat-label">TOTAL MESIN</div>
                    <div class="stat-number"><?=$d_total_mesin?></div>
                    <div class="stat-desc">Mesin Terdaftar</div>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-gear-wide-connected"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-box cyan">
                <div>
                    <div class="stat-label">TOTAL KOMPONEN</div>
                    <div class="stat-number"><?=$d_total_komponen?></div>
                    <div class="stat-desc">Komponen Aktif</div>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-cpu"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-box orange">
                <div>
                    <div class="stat-label">PERLU PERHATIAN</div>
                    <div class="stat-number"><?=$d_komponen_perhatian?></div>
                    <div class="stat-desc">Warning</div>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="stat-box green">
                <div>
                    <div class="stat-label">MAINTENANCE</div>
                    <div class="stat-number"><?=$d_maint_bulan_ini?></div>
                    <div class="stat-desc">Bulan Ini</div>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-tools"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- QUICK ACTION -->
    <div class="content-card mb-4">
        <div class="card-body-custom">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h5 class="fw-bold">
                        <i class="bi bi-lightning-charge-fill text-warning"></i> Quick Action
                    </h5>
                    <p class="text-muted mb-0">Tambah data dengan cepat.</p>
                </div>
                <div class="d-flex gap-2 mt-3 mt-lg-0">
                    <a href="../mesin/tambah.php" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Mesin
                    </a>
                    <a href="../komponen/tambah.php" class="btn btn-primary">
                        <i class="bi bi-cpu"></i> Komponen
                    </a>
                    <a href="../maintenance/tambah.php" class="btn btn-warning">
                        <i class="bi bi-tools"></i> Maintenance
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- ================= MAINTENANCE TERBARU ================= -->
        <div class="col-lg-8">
            <div class="content-card h-100">
                <div class="card-header-custom">
                    <div>
                        <h5 class="card-title-custom">
                            <i class="bi bi-clock-history me-2"></i> Maintenance Terbaru
                        </h5>
                        <small class="text-muted">5 aktivitas maintenance terakhir</small>
                    </div>
                    <a href="../maintenance/index.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
                </div>
                <div class="card-body-custom p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th width="130">Tanggal</th>
                                    <th>Komponen</th>
                                    <th>Tindakan</th>
                                    <th width="120" class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if(!empty($data_maintenance_list)): ?>
                                <?php foreach($data_maintenance_list as $m): ?>
                                <?php
                                $badge = "bg-success";
                                if($m['status'] == "Pending"){
                                    $badge = "bg-danger";
                                }
                                if($m['status'] == "Proses"){
                                    $badge = "bg-warning text-dark";
                                }
                                ?>
                                <tr>
                                    <td>
                                        <strong><?=date('d M Y', strtotime($m['tanggal']))?></strong><br>
                                        <small class="text-muted"><?=date('H:i', strtotime($m['tanggal']))?></small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-circle me-3">
                                                <i class="bi bi-cpu"></i>
                                            </div>
                                            <div>
                                                <strong><?=htmlspecialchars($m['nama_bagian'])?></strong><br>
                                                <small class="text-muted"><?=htmlspecialchars($m['nama_mesin'])?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?=htmlspecialchars($m['tindakan'])?></td>
                                    <td class="text-center">
                                        <!-- BADGE KLIK -->
                                        <button type="button" 
                                                class="btn badge <?=$badge?> border-0 px-3 py-2 rounded-pill fw-semibold shadow-sm" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalStatusMaint<?=$m['id']?>" 
                                                title="Klik untuk ubah status">
                                            <?=$m['status']?> <i class="bi bi-pencil-fill ms-1" style="font-size: 9px;"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <i class="bi bi-inbox display-6 text-secondary"></i><br><br>
                                        Belum ada data maintenance.
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ================= KOMPONEN BERMASALAH ================= -->
        <div class="col-lg-4">
            <div class="content-card h-100">
                <div class="card-header-custom">
                    <div>
                        <h5 class="card-title-custom">
                            <i class="bi bi-exclamation-triangle text-danger me-2"></i> Komponen Bermasalah
                        </h5>
                        <small class="text-muted">Kondisi selain Baik</small>
                    </div>
                </div>
                <div class="card-body-custom p-0">
                    <?php if(!empty($data_komponen_list)): ?>
                        <ul class="list-group list-group-flush">
                        <?php foreach($data_komponen_list as $k): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <strong><?=htmlspecialchars($k['nama_bagian'])?></strong><br>
                                    <small class="text-muted"><?=htmlspecialchars($k['nama_mesin'])?></small>
                                </div>

                                <!-- BADGE KLIK -->
                                <button type="button" 
                                        class="btn badge bg-danger border-0 px-3 py-2 rounded-pill fw-semibold shadow-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#modalKondisiKomp<?=$k['id']?>" 
                                        title="Klik untuk ubah kondisi">
                                    <?=$k['kondisi']?> <i class="bi bi-pencil-fill ms-1" style="font-size: 9px;"></i>
                                </button>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center p-5">
                            <i class="bi bi-check-circle text-success display-5"></i>
                            <p class="mt-3 mb-0">Semua komponen dalam kondisi baik.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div> <!-- container-fluid -->


<!-- =========================================================
     MODAL CONTAINERS (Ditaruh diluar tabel agar tidak berantakan)
========================================================= -->

<!-- Modal Update Status Maintenance -->
<?php foreach($data_maintenance_list as $m): ?>
<div class="modal fade" id="modalStatusMaint<?=$m['id']?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold">Update Status Maintenance</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <input type="hidden" name="id_maintenance" value="<?=$m['id']?>">
                    <label class="form-label small text-muted">Pilih Status Baru:</label>
                    <select name="status" class="form-select rounded-3">
                        <option value="Proses" <?=$m['status']=='Proses'?'selected':''?>>Proses</option>
                        <option value="Selesai" <?=$m['status']=='Selesai'?'selected':''?>>Selesai</option>
                        <option value="Pending" <?=$m['status']=='Pending'?'selected':''?>>Pending</option>
                    </select>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" name="update_status_maintenance" class="btn btn-primary btn-sm rounded-3 w-100">Simpan Status</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- Modal Update Kondisi Komponen -->
<?php foreach($data_komponen_list as $k): ?>
<div class="modal fade" id="modalKondisiKomp<?=$k['id']?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold">Update Kondisi Komponen</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <input type="hidden" name="id_komponen" value="<?=$k['id']?>">
                    <label class="form-label small text-muted">Ubah Kondisi Menjadi:</label>
                    <select name="kondisi" class="form-select rounded-3">
                        <option value="Baik" <?=$k['kondisi']=='Baik'?'selected':''?>>Baik (Selesai/Aman)</option>
                        <option value="Perlu Pemeriksaan" <?=$k['kondisi']=='Perlu Pemeriksaan'?'selected':''?>>Perlu Pemeriksaan</option>
                        <option value="Dalam Perbaikan" <?=$k['kondisi']=='Dalam Perbaikan'?'selected':''?>>Dalam Perbaikan</option>
                        <option value="Rusak" <?=$k['kondisi']=='Rusak'?'selected':''?>>Rusak</option>
                    </select>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="submit" name="update_kondisi_komponen" class="btn btn-primary btn-sm rounded-3 w-100">Simpan Kondisi</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php include "../template/footer.php"; ?>
<script>
    function updateClock() {
        const now = new Date();
        const hours   = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        
        const timeString = `${hours}:${minutes}:${seconds}`;
        
        const clockElement = document.getElementById('jam-realtime');
        if (clockElement) {
            clockElement.textContent = timeString;
        }
    }

    // Jalankan fungsi pertama kali
    updateClock();
    
    // Perbarui jam setiap 1 detik (1000 ms)
    setInterval(updateClock, 1000);
</script>
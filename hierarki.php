<?php
include "koneksi.php";
include "template/header.php";

// Ambil keyword pencarian jika ada
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$kw = "%" . $keyword . "%";

// Query Area dengan filter pencarian fleksibel ke seluruh relasi
if (!empty($keyword)) {
    $query_area = mysqli_prepare($conn, "SELECT DISTINCT a.* FROM area_bagian a 
        LEFT JOIN jenis_mesin jm ON jm.id_area = a.id
        LEFT JOIN mesin m ON m.id_jenis_mesin = jm.id
        LEFT JOIN sub_mesin sm ON sm.id_mesin = m.id
        LEFT JOIN komponen k ON k.id_sub_mesin = sm.id
        WHERE a.nama_area LIKE ? 
           OR jm.nama_jenis_mesin LIKE ? 
           OR m.nama_mesin LIKE ? 
           OR m.serial_number LIKE ? 
           OR sm.nama_sub_mesin LIKE ? 
           OR k.nama_bagian LIKE ? 
           OR k.serial_number LIKE ?
        ORDER BY a.nama_area ASC");
    mysqli_stmt_bind_param($query_area, "sssssss", $kw, $kw, $kw, $kw, $kw, $kw, $kw);
    mysqli_stmt_execute($query_area);
    $query_area = mysqli_stmt_get_result($query_area);
} else {
    $query_area = mysqli_query($conn, "SELECT * FROM area_bagian ORDER BY nama_area ASC");
}
?>

<div class="container-fluid mb-4 px-3 py-2">
    <!-- Header Halaman & Form Search -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
            <div>
                <h2 class="fw-bold text-dark m-0"><i class="bi bi-diagram-3-fill me-2 text-primary" style="color: #0056a6 !important;"></i> Direktori Mesin & Komponen</h2>
                <p class="text-muted small m-0 mt-1">Kumpulan Folder Data Mesin PT Garudafood</p>
            </div>
            <div>
                <a href="mesin/tambah.php" class="btn btn-primary fw-semibold px-3 py-2 rounded-3 shadow-sm" style="background-color: #0056a6; border: none;">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Mesin Baru
                </a>
            </div>
        </div>

        <!-- Input Pencarian Universal -->
        <form method="GET" action="" class="row g-2">
            <div class="col-md-10">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="keyword" class="form-control border-start-0 ps-0" placeholder="Cari berdasarkan Area, Jenis Mesin, Nama Mesin, Serial Number, Sub Mesin, atau Komponen..." value="<?= htmlspecialchars($keyword); ?>">
                </div>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-semibold" style="background-color: #0056a6; border: none;">Cari</button>
                <?php if (!empty($keyword)) : ?>
                    <a href="index.php" class="btn btn-outline-secondary" title="Reset Pencarian"><i class="bi bi-arrow-counterclockwise"></i></a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- LEVEL 1: FOLDER AREA BAGIAN -->
    <div class="d-flex flex-column gap-3">
        <?php if ($query_area && mysqli_num_rows($query_area) > 0) : ?>
            <?php while ($area = mysqli_fetch_assoc($query_area)) : ?>
                <div class="card border border-primary-subtle shadow-sm rounded-4 bg-white overflow-hidden">
                    <!-- Header Area -->
                    <div class="card-header bg-primary text-white p-3 d-flex align-items-center gap-3" style="background-color: #0056a6 !important;">
                        <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 40px; height: 40px; flex-shrink: 0;">
                            <i class="bi bi-folder-fill fs-5"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold m-0 text-white"><i class="bi bi-geo-alt-fill me-1"></i> Area: <?= htmlspecialchars($area['nama_area']); ?></h5>
                            <small class="text-white-50">Folder Area Bagian</small>
                        </div>
                    </div>

                    <div class="card-body p-3 bg-light">
                        <?php
                        $id_area = $area['id'];
                        // LEVEL 2: FOLDER JENIS MESIN BERDASARKAN AREA (dengan filter pencarian jika ada keyword)
                        if (!empty($keyword)) {
                            $q_jenis_str = "SELECT DISTINCT jm.* FROM jenis_mesin jm 
                                LEFT JOIN mesin m ON m.id_jenis_mesin = jm.id
                                LEFT JOIN sub_mesin sm ON sm.id_mesin = m.id
                                LEFT JOIN komponen k ON k.id_sub_mesin = sm.id
                                WHERE jm.id_area = ? AND (jm.nama_jenis_mesin LIKE ? OR m.nama_mesin LIKE ? OR m.serial_number LIKE ? OR sm.nama_sub_mesin LIKE ? OR k.nama_bagian LIKE ? OR k.serial_number LIKE ?)
                                ORDER BY jm.nama_jenis_mesin ASC";
                            $stmt_j = mysqli_prepare($conn, $q_jenis_str);
                            mysqli_stmt_bind_param($stmt_j, "issssss", $id_area, $kw, $kw, $kw, $kw, $kw, $kw);
                            mysqli_stmt_execute($stmt_j);
                            $query_jenis = mysqli_stmt_get_result($stmt_j);
                        } else {
                            $query_jenis = mysqli_query($conn, "SELECT * FROM jenis_mesin WHERE id_area = '$id_area' ORDER BY nama_jenis_mesin ASC");
                        }
                        ?>

                        <?php if ($query_jenis && mysqli_num_rows($query_jenis) > 0) : ?>
                            <div class="d-flex flex-column gap-3 ps-md-3">
                                <?php while ($jenis = mysqli_fetch_assoc($query_jenis)) : ?>
                                    <div class="card border border-warning-subtle shadow-sm rounded-3 bg-white">
                                        <!-- Header Jenis Mesin -->
                                        <div class="card-header bg-warning-subtle p-2 px-3 d-flex align-items-center gap-2 border-bottom">
                                            <i class="bi bi-folder2-open text-warning fs-5"></i>
                                            <span class="fw-bold text-dark">Jenis Mesin: <?= htmlspecialchars($jenis['nama_jenis_mesin']); ?></span>
                                        </div>

                                        <div class="card-body p-3">
                                            <?php
                                            $id_jenis = $jenis['id'];
                                            // LEVEL 3: FOLDER MESIN BERDASARKAN JENIS MESIN
                                            if (!empty($keyword)) {
                                                $q_mesin_str = "SELECT DISTINCT m.* FROM mesin m 
                                                    LEFT JOIN sub_mesin sm ON sm.id_mesin = m.id
                                                    LEFT JOIN komponen k ON k.id_sub_mesin = sm.id
                                                    WHERE m.id_jenis_mesin = ? AND (m.nama_mesin LIKE ? OR m.serial_number LIKE ? OR sm.nama_sub_mesin LIKE ? OR k.nama_bagian LIKE ? OR k.serial_number LIKE ?)
                                                    ORDER BY m.nama_mesin ASC";
                                                $stmt_m = mysqli_prepare($conn, $q_mesin_str);
                                                mysqli_stmt_bind_param($stmt_m, "isssss", $id_jenis, $kw, $kw, $kw, $kw, $kw);
                                                mysqli_stmt_execute($stmt_m);
                                                $query_mesin = mysqli_stmt_get_result($stmt_m);
                                            } else {
                                                $query_mesin = mysqli_query($conn, "SELECT * FROM mesin WHERE id_jenis_mesin = '$id_jenis' ORDER BY nama_mesin ASC");
                                            }
                                            ?>

                                            <?php if ($query_mesin && mysqli_num_rows($query_mesin) > 0) : ?>
                                                <div class="d-flex flex-column gap-3 ps-md-3">
                                                    <?php while ($mesin = mysqli_fetch_assoc($query_mesin)) : ?>
                                                        <div class="card border border-secondary-subtle shadow-sm rounded-3 bg-white">
                                                            <!-- Header Mesin Induk -->
                                                            <div class="card-header bg-light p-2 px-3 d-flex justify-content-between align-items-center">
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <i class="bi bi-cpu text-info fs-5"></i>
                                                                    <div>
                                                                        <strong class="text-dark"><?= htmlspecialchars($mesin['nama_mesin']); ?></strong>
                                                                        <?php if (!empty($mesin['serial_number'])) : ?>
                                                                            <code class="ms-2 small text-muted">(SN: <?= htmlspecialchars($mesin['serial_number']); ?>)</code>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                </div>
                                                                <a href="mesin/edit.php?id=<?= $mesin['id']; ?>" class="btn btn-xs btn-outline-primary py-0 px-2 small">
                                                                    <i class="bi bi-pencil"></i> Edit Mesin
                                                                </a>
                                                            </div>

                                                            <div class="card-body p-3">
                                                                <?php
                                                                $id_mesin = $mesin['id'];
                                                                // LEVEL 4: FOLDER SUB MESIN BERDASARKAN MESIN
                                                                if (!empty($keyword)) {
                                                                    $q_sub_str = "SELECT DISTINCT sm.* FROM sub_mesin sm 
                                                                        LEFT JOIN komponen k ON k.id_sub_mesin = sm.id
                                                                        WHERE sm.id_mesin = ? AND (sm.nama_sub_mesin LIKE ? OR k.nama_bagian LIKE ? OR k.serial_number LIKE ?)
                                                                        ORDER BY sm.nama_sub_mesin ASC";
                                                                    $stmt_s = mysqli_prepare($conn, $q_sub_str);
                                                                    mysqli_stmt_bind_param($stmt_s, "isss", $id_mesin, $kw, $kw, $kw);
                                                                    mysqli_stmt_execute($stmt_s);
                                                                    $query_sub = mysqli_stmt_get_result($stmt_s);
                                                                } else {
                                                                    $query_sub = mysqli_query($conn, "SELECT * FROM sub_mesin WHERE id_mesin = '$id_mesin' ORDER BY nama_sub_mesin ASC");
                                                                }
                                                                ?>

                                                                <?php if ($query_sub && mysqli_num_rows($query_sub) > 0) : ?>
                                                                    <div class="d-flex flex-column gap-3 ps-md-3">
                                                                        <?php while ($sub = mysqli_fetch_assoc($query_sub)) : ?>
                                                                            <div class="card border border-light-subtle rounded-3 bg-light p-2 px-3">
                                                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                                                    <h6 class="fw-bold text-secondary m-0 small">
                                                                                        <i class="bi bi-folder me-1 text-secondary"></i> Sub Mesin: <?= htmlspecialchars($sub['nama_sub_mesin']); ?>
                                                                                    </h6>
                                                                                    <a href="sub_mesin/edit.php?id=<?= $sub['id']; ?>" class="text-decoration-none text-muted" style="font-size: 11px;">
                                                                                        <i class="bi bi-pencil-square"></i> Edit
                                                                                    </a>
                                                                                </div>

                                                                                <?php
                                                                                $id_sub = $sub['id'];
                                                                                // LEVEL 5: TABEL KOMPONEN DALAM SUB MESIN
                                                                                if (!empty($keyword)) {
                                                                                    $q_k_str = "SELECT * FROM komponen WHERE id_sub_mesin = ? AND (nama_bagian LIKE ? OR serial_number LIKE ? OR kategori LIKE ? OR brand LIKE ? OR tipe LIKE ?) ORDER BY nama_bagian ASC";
                                                                                    $stmt_k = mysqli_prepare($conn, $q_k_str);
                                                                                    mysqli_stmt_bind_param($stmt_k, "isssss", $id_sub, $kw, $kw, $kw, $kw, $kw);
                                                                                    mysqli_stmt_execute($stmt_k);
                                                                                    $query_komponen = mysqli_stmt_get_result($stmt_k);
                                                                                } else {
                                                                                    $query_komponen = mysqli_query($conn, "SELECT * FROM komponen WHERE id_sub_mesin = '$id_sub' ORDER BY nama_bagian ASC");
                                                                                }
                                                                                ?>

                                                                                <?php if ($query_komponen && mysqli_num_rows($query_komponen) > 0) : ?>
                                                                                    <div class="table-responsive mt-1">
                                                                                        <table class="table table-sm table-bordered bg-white rounded-3 m-0 small align-middle">
                                                                                            <thead class="table-light">
                                                                                                <tr>
                                                                                                    <th>Nama Bagian / Komponen</th>
                                                                                                    <th>Serial Number (SN)</th>
                                                                                                    <th>Kategori</th>
                                                                                                    <th>Brand / Tipe</th>
                                                                                                    <th>Kondisi</th>
                                                                                                    <th class="text-center">Aksi</th>
                                                                                                </tr>
                                                                                            </thead>
                                                                                            <tbody>
                                                                                                <?php while ($k = mysqli_fetch_assoc($query_komponen)) : ?>
                                                                                                    <tr>
                                                                                                        <td class="fw-semibold"><i class="bi bi-gear me-1 text-muted"></i><?= htmlspecialchars($k['nama_bagian']); ?></td>
                                                                                                        <td><code><?= htmlspecialchars($k['serial_number'] ?: '-'); ?></code></td>
                                                                                                        <td><?= htmlspecialchars($k['kategori'] ?: '-'); ?></td>
                                                                                                        <td><?= htmlspecialchars($k['brand'] ?: '-'); ?> <?= htmlspecialchars($k['tipe'] ? '(' . $k['tipe'] . ')' : ''); ?></td>
                                                                                                        <td>
                                                                                                            <?php if (($k['kondisi'] ?? '') == 'Baik') : ?>
                                                                                                                <span class="badge bg-success">Baik</span>
                                                                                                            <?php elseif (($k['kondisi'] ?? '') == 'Rusak') : ?>
                                                                                                                <span class="badge bg-danger">Rusak</span>
                                                                                                            <?php else : ?>
                                                                                                                <span class="badge bg-warning text-dark">Perbaikan</span>
                                                                                                            <?php endif; ?>
                                                                                                        </td>
                                                                                                        <td class="text-center">
                                                                                                            <a href="komponen/detail.php?id=<?= $k['id']; ?>" class="btn btn-xs btn-outline-info px-2 py-0" title="Detail">
                                                                                                                <i class="bi bi-eye"></i>
                                                                                                            </a>
                                                                                                        </td>
                                                                                                    </tr>
                                                                                                <?php endwhile; ?>
                                                                                            </tbody>
                                                                                        </table>
                                                                                    </div>
                                                                                <?php else : ?>
                                                                                    <p class="text-muted m-0 fst-italic" style="font-size: 11px;">Belum ada komponen terdaftar pada sub mesin ini.</p>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        <?php endwhile; ?>
                                                                    </div>
                                                                <?php else : ?>
                                                                    <p class="text-muted small m-0 fst-italic">Belum ada sub mesin terdaftar untuk mesin ini.</p>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    <?php endwhile; ?>
                                                </div>
                                            <?php else : ?>
                                                <p class="text-muted small m-0 fst-italic">Belum ada mesin terdaftar untuk jenis mesin ini.</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php else : ?>
                            <p class="text-muted small m-0 fst-italic ps-md-3">Belum ada jenis mesin terdaftar di area ini.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else : ?>
            <div class="card border-0 shadow-sm rounded-4 bg-white p-5 text-center">
                <p class="text-muted m-0">Tidak ditemukan data yang sesuai dengan kata kunci "<strong><?= htmlspecialchars($keyword); ?></strong>".</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include "template/footer.php"; ?>
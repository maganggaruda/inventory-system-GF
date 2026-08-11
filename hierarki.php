<?php
include "koneksi.php";
include "template/header.php";

// Ambil semua data mesin beserta sub mesin dan komponennya
$query_mesin = mysqli_query($conn, "SELECT * FROM mesin ORDER BY nama_mesin ASC");
?>

<div class="container-fluid mb-4 px-3 py-2">
    <!-- Header Halaman -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <h2 class="fw-bold text-dark m-0"><i class="bi bi-diagram-3-fill me-2 text-primary" style="color: #0056a6 !important;"></i> Daftar Mesin</h2>
                <p class="text-muted small m-0 mt-1">Struktur lengkap daftar mesin, sub mesin, beserta komponen dan part di dalamnya</p>
            </div>
            <div>
                <a href="mesin/tambah.php" class="btn btn-primary fw-semibold px-3 py-2 rounded-3 shadow-sm" style="background-color: #0056a6; border: none;">
                    <i class="bi bi-plus-lg me-1"></i> Tambah Mesin Baru
                </a>
            </div>
        </div>
    </div>

    <!-- Daftar Hierarki Mesin -->
    <div class="row g-4">
        <?php if (mysqli_num_rows($query_mesin) > 0) : ?>
            <?php while ($mesin = mysqli_fetch_assoc($query_mesin)) : ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                        <!-- Header Mesin Induk -->
                        <div class="card-header bg-light border-bottom p-3 d-flex flex-wrap justify-content-between align-items-center gap-2" style="background-color: #f8f9fa !important;">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 45px; height: 45px; background-color: #0056a6 !important; flex-shrink: 0;">
                                    <i class="bi bi-cpu fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-dark m-0"><?= htmlspecialchars($mesin['nama_mesin']); ?></h5>
                                    <span class="badge bg-secondary text-white mt-1">Lokasi: <?= htmlspecialchars($mesin['lokasi'] ?: 'Tidak ada lokasi'); ?></span>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="mesin/edit.php?id=<?= $mesin['id']; ?>" class="btn btn-sm btn-outline-primary rounded-3 px-3">
                                    <i class="bi bi-pencil me-1"></i> Edit Mesin
                                </a>
                            </div>
                        </div>

                        <!-- Body (Sub Mesin & Komponen) -->
                        <div class="card-body p-4">
                            <?php
                            $id_mesin = $mesin['id'];
                            $query_sub = mysqli_query($conn, "SELECT * FROM sub_mesin WHERE id_mesin = '$id_mesin' ORDER BY nama_sub_mesin ASC");
                            ?>

                            <?php if (mysqli_num_rows($query_sub) > 0) : ?>
                                <div class="d-flex flex-column gap-3">
                                    <?php while ($sub = mysqli_fetch_assoc($query_sub)) : ?>
                                        <div class="card border border-light-subtle rounded-3 bg-light p-3">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <h6 class="fw-bold text-secondary m-0">
                                                    <i class="bi bi-folder2-open me-2 text-warning"></i><?= htmlspecialchars($sub['nama_sub_mesin']); ?>
                                                </h6>
                                                <a href="sub_mesin/edit.php?id=<?= $sub['id']; ?>" class="text-decoration-none small text-muted">
                                                    <i class="bi bi-pencil-square"></i> Edit Sub Mesin
                                                </a>
                                            </div>

                                            <!-- Komponen dalam Sub Mesin -->
                                            <?php
                                            $id_sub = $sub['id'];
                                            $query_komponen = mysqli_query($conn, "SELECT * FROM komponen WHERE id_sub_mesin = '$id_sub' ORDER BY nama_bagian ASC");
                                            ?>

                                            <?php if (mysqli_num_rows($query_komponen) > 0) : ?>
                                                <div class="table-responsive mt-2">
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
                                                                    <td class="fw-semibold"><?= htmlspecialchars($k['nama_bagian']); ?></td>
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
                                                                        <a href="komponen/detail.php?id=<?= $k['id']; ?>" class="btn btn-xs btn-outline-info px-2 py-1" title="Detail">
                                                                            <i class="bi bi-eye"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            <?php endwhile; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php else : ?>
                                                <p class="text-muted small m-0 fst-italic">Belum ada komponen terdaftar pada sub mesin ini.</p>
                                            <?php endif; ?>

                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else : ?>
                                <p class="text-muted small m-0 fst-italic py-2">Belum ada sub mesin terdaftar untuk mesin ini.</p>
                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else : ?>
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 bg-white p-5 text-center">
                    <p class="text-muted m-0">Belum ada data mesin yang terdaftar di dalam sistem.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include "template/footer.php"; ?>
<?php
include "../koneksi.php";

$id_mesin = isset($_GET['id_mesin']) ? intval($_GET['id_mesin']) : 0;

echo '<option value="">-- Pilih Sub Mesin --</option>';

if ($id_mesin > 0) {
    $stmt = mysqli_prepare($conn, "SELECT id, nama_sub_mesin FROM sub_mesin WHERE id_mesin = ? ORDER BY nama_sub_mesin ASC");
    mysqli_stmt_bind_param($stmt, "i", $id_mesin);
    mysqli_stmt_execute($stmt);
    $q = mysqli_stmt_get_result($stmt);
    
    while ($r = mysqli_fetch_assoc($q)) {
        echo '<option value="' . $r['id'] . '">' . htmlspecialchars($r['nama_sub_mesin']) . '</option>';
    }
    mysqli_stmt_close($stmt);
}
?>
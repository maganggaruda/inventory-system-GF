<?php
include "../koneksi.php";

$id_mesin = isset($_GET['id_mesin']) ? mysqli_real_escape_string($conn, $_GET['id_mesin']) : '';

if (!empty($id_mesin)) {
    $q = mysqli_query($conn, "SELECT id, nama_sub_mesin FROM sub_mesin WHERE id_mesin = '$id_mesin' ORDER BY nama_sub_mesin ASC");
    echo '<option value="">-- Semua Sub Mesin --</option>';
    while ($r = mysqli_fetch_assoc($q)) {
        echo '<option value="' . $r['id'] . '">' . htmlspecialchars($r['nama_sub_mesin']) . '</option>';
    }
} else {
    echo '<option value="">-- Semua Sub Mesin --</option>';
}
?>
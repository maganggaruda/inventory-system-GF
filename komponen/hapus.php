<?php
include "../koneksi.php";

$id = isset($_GET['id']) ? mysqli_real_escape_string($conn, $_GET['id']) : '';

if (!empty($id)) {
    mysqli_query($conn, "DELETE FROM komponen WHERE id='$id'");
}

header("Location: index.php");
exit;
?>
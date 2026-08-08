<?php
/**
 * File Koneksi Database
 * PT Garudafood Inventory & Maintenance Information System
 */

$host     = "localhost";
$username = "root";
$password = "";
$database = "db_inventory"; // Database Anda

// Membuat koneksi
$conn = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal : " . mysqli_connect_error());
}

// Opsional: Buat alias $koneksi agar kompatibel jika ada file yang memakai $koneksi
$koneksi = $conn;

// Set timezone Indonesia (WIB)
date_default_timezone_set('Asia/Jakarta');
?>
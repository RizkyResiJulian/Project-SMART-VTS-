<?php
include 'koneksi.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ambil data dari form
$id_petugas = $_POST['id_petugas'];
$username = $_POST['username'];
$nama = $_POST['nama'];
$shift = $_POST['shift'];
$tanggal = $_POST['tanggal'];

// Validasi input
if (empty($id_petugas) || empty($username) || empty($nama) || empty($shift) || empty($tanggal)) {
    die("Semua data harus diisi.");
}

// Gunakan prepared statements untuk keamanan
$stmt = mysqli_prepare($conn, "INSERT INTO daftar_petugas (id_petugas, username, nama, shift, tanggal) VALUES (?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "sssss", $id_petugas, $username, $nama, $shift, $tanggal);

if (mysqli_stmt_execute($stmt)) {
    header("Location: data_petugas.php");
    exit;
} else {
    echo "Error: " . mysqli_error($conn);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>

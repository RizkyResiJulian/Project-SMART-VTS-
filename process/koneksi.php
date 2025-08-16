<?php
$host = "localhost"; // atau alamat host yang digunakan
$user = "root"; // username database
$password = ""; // password database
$dbname = "update_ai"; // nama database

// Koneksi ke database
$conn = mysqli_connect($host, $user, $password, $dbname);

// Cek koneksi
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>

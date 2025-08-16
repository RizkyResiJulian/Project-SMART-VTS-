<?php
session_start();
include 'koneksi.php'; // Pastikan file koneksi.php ada dan berfungsi dengan baik
// Di awal file dashboard.php atau file yang dilindungi
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 3600)) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=true");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Cek apakah parameter 'id' ada
if (isset($_GET['MMSI'])) {
    $MMSI = $_GET['MMSI'];

    // Query untuk menghapus data kapal berdasarkan id
    $sql = "DELETE FROM data_kapal WHERE MMSI = '$MMSI'";

    if (mysqli_query($conn, $sql)) {
        // Jika berhasil, redirect ke halaman data_kapal.php
        header("Location: data_kapal.php");
        exit;
    } else {
        // Jika gagal, tampilkan pesan error
        echo "Terjadi kesalahan saat menghapus data kapal!";
    }
} else {
    // Jika tidak ada id yang diberikan, tampilkan pesan error
    echo "MMSI tidak ditemukan!";
}
?>

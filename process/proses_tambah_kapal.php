<?php
include 'koneksi.php';

// Ambil data dari form
$MMSI = $_POST['MMSI'];
$id_petugas = trim(string: $_POST['id_petugas'] ?? '');
$callsign = $_POST['callsign'];
$nama_kapal = $_POST['nama_kapal'];
$jenis_kapal = $_POST['jenis_kapal'];
$GT = $_POST['GT'];
$LOA = $_POST['LOA'];
$beam = $_POST['beam'];
$draft = $_POST['draft'];
$zona = $_POST['zona'];

// Query untuk menyimpan data
$sql = "INSERT INTO data_kapal (MMSI, id_petugas, callsign, nama_kapal, jenis_kapal, GT, LOA, beam, draft, zona)
        VALUES ('$MMSI', '$id_petugas', '$callsign', '$nama_kapal', '$jenis_kapal', '$GT', '$LOA', '$beam', '$draft', '$zona')";

if (mysqli_query($conn, $sql)) {
    // Redirect ke halaman data_kapal.php setelah data berhasil disimpan
    header("Location: data_kapal.php");
    exit; // Pastikan script berhenti setelah redirect
} else {
    echo "Error: " . $sql . "<br>" . mysqli_error($conn);
}

mysqli_close($conn);
?>

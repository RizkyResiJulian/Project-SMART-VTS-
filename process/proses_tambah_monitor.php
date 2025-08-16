<?php
session_start();
include 'koneksi.php';

// Set timezone ke Jakarta
date_default_timezone_set('Asia/Jakarta');

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Ambil data dari form
$id_monitor = trim(string: $_POST['id_monitor'] ?? '');
$id_petugas = trim(string: $_POST['id_petugas'] ?? '');
$waktu = trim($_POST['waktu'] ?? '');
$pelabuhan = trim($_POST['pelabuhan'] ?? '');
$maksud_kedatangan = trim($_POST['maksud_kedatangan'] ?? '');
$id_agen = trim($_POST['id_agen'] ?? '');
$MMSI = trim($_POST['MMSI'] ?? '');
$latitude = trim($_POST['latitude'] ?? '');
$longitude = trim($_POST['longitude'] ?? '');
$pelabuhan_asal = trim($_POST['pelabuhan_asal'] ?? '');
$pelabuhan_tujuan = trim($_POST['pelabuhan_tujuan'] ?? '');
$draft_depan = trim($_POST['draft_depan'] ?? '');
$draft_belakang = trim($_POST['draft_belakang'] ?? '');
$ETA = trim($_POST['ETA'] ?? '');
$ATA = trim($_POST['ATA'] ?? '');
$waktu_keberangkatan = trim($_POST['waktu_keberangkatan'] ?? '');
$jenis_muatan = trim($_POST['jenis_muatan'] ?? '');
$jumlah_muatan = trim($_POST['jumlah_muatan'] ?? '');
$jumlah_kru = trim($_POST['jumlah_kru'] ?? '');
$info_penting = isset($_POST['info_penting']) ? trim($_POST['info_penting']) : null;
$nama_kapten = isset($_POST['nama_kapten']) ? trim($_POST['nama_kapten']) : null;
$keterangan_form = trim($_POST['keterangan'] ?? '');

// Jika $waktu kosong, isi dengan waktu sekarang
if (empty($waktu)) {
    $waktu = date('Y-m-d H:i:s'); // format MySQL datetime
}

// Inisialisasi variabel tambahan
$nama_agen = $waktu_sandar = $posisi_sandar = $keterangan = "";

// Jika id_agen diisi, ambil data agen
if (!empty($id_agen)) {
    $sql_agen = "SELECT * FROM data_agen WHERE id_agen = '$id_agen'";
    $result_agen = mysqli_query($conn, $sql_agen);

    if (mysqli_num_rows($result_agen) > 0) {
        $row_agen = mysqli_fetch_assoc($result_agen);
        $nama_agen = $row_agen['nama_agen'];
        $MMSI = $row_agen['MMSI'];
        $keterangan = $row_agen['keterangan'];
    } else {
        echo "Error: id_agen tidak ditemukan dalam database data_agen.";
        exit;
    }
} else {
    if (empty($MMSI)) {
        echo "Error: MMSI wajib diisi jika id_agen tidak dipilih.";
        exit;
    }
    $keterangan = $keterangan_form;
}

// Cek kapal berdasarkan MMSI
$sql_kapal = "SELECT * FROM data_kapal WHERE MMSI = '$MMSI'";
$result_kapal = mysqli_query($conn, $sql_kapal);

if (mysqli_num_rows($result_kapal) == 0) {
    echo "Error: MMSI tidak ditemukan dalam database data_kapal.";
    exit;
}

// Cek apakah id_agen sudah ada di data_monitor
if (!empty($id_agen)) {
    $sql_cek = "SELECT id_monitor FROM data_monitor WHERE id_agen = '$id_agen'";
    $result_cek = mysqli_query($conn, $sql_cek);

    if (mysqli_num_rows($result_cek) > 0) {
        echo "Error: Agen ini sudah terdaftar di data_monitor.";
        exit;
    }
}

// Insert ke data_monitor
$sql_monitor = "INSERT INTO data_monitor (
    id_monitor, id_petugas, waktu, pelabuhan, maksud_kedatangan, id_agen, MMSI, latitude, longitude, pelabuhan_asal, pelabuhan_tujuan, draft_depan,
    draft_belakang, ETA, ATA, waktu_keberangkatan, 
    jenis_muatan, jumlah_muatan, jumlah_kru, info_penting, nama_kapten, keterangan
) VALUES (
    '$id_monitor', '$id_petugas', '$waktu', '$pelabuhan', '$maksud_kedatangan', " .
    (empty($id_agen) ? "NULL" : "'$id_agen'") . ",
    '$MMSI', $latitude, $longitude, '$pelabuhan_asal', '$pelabuhan_tujuan', '$draft_depan', '$draft_belakang', '$ETA', '$ATA', '$waktu_keberangkatan', 
    '$jenis_muatan', '$jumlah_muatan', '$jumlah_kru', " . 
    (is_null($info_penting) ? "NULL" : "'$info_penting'") . ",
    '$nama_kapten', '$keterangan'
)";

if (mysqli_query($conn, $sql_monitor)) {
    $_SESSION['message'] = "Data berhasil ditambahkan!";
    header("Location: data_monitor.php");
    exit;
} else {
    echo "Error: " . mysqli_error($conn);
}

mysqli_close($conn);
?>

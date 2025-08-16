<?php
session_start();
include 'koneksi.php';
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
$username = $_SESSION['username'];

// Ambil data petugas berdasarkan username login
$sql = "SELECT * FROM daftar_petugas WHERE username = '$username'";
$result_petugas = mysqli_query($conn, $sql);

if ($result_petugas && mysqli_num_rows($result_petugas) > 0) {
    $daftar_petugas = mysqli_fetch_assoc($result_petugas);
    $id_petugas = htmlspecialchars($daftar_petugas['id_petugas']);
}

// Ambil ID kapal terakhir dan buat ID baru
$sql = "SELECT MAX(id_monitor) AS last_id FROM data_monitor";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

$last_id = $row['last_id']; // Ambil ID terakhir
$next_id = 1;

if ($last_id) {
    $last_number = (int)substr($last_id, -3); // Ambil 3 digit terakhir
    $next_id = $last_number + 1; // Menambah 1
}

$new_id = "" . str_pad($next_id, 3, "0", STR_PAD_LEFT); // Format menjadi KAPAL-001
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kapal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #74ebd5 0%, #acb6e5 100%);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            padding-bottom: 50px;
        }
        .container {
            max-width: 600px;
            padding: 40px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .btn-back {
            position: fixed;
            top: 20px;
            left: 20px;
            width: 50px;
            height: 50px;
            background: #6c757d;
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 22px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            z-index: 999;
            transition: 0.3s;
        }
        .btn-back:hover {
            background: #495057;
        }
    </style>
</head>
<body>

<a href="data_monitor.php" class="btn btn-back">
    <i class="bi bi-arrow-left"></i>
</a>

<div class="container mt-5">
    <h1 class="text-center mb-4">Tambah Monitor</h1>
    <form action="proses_tambah_monitor.php" method="POST">
        <div class="mb-3">
            <label for="id_monitor" class="form-label">No</label>
            <input type="text" class="form-control" id="id_monitor" name="id_monitor" value="<?= $new_id; ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="id_petugas" class="form-label">Id Petugas</label>
            <input type="text" class="form-control" id="id_petugas" name="id_petugas" value="<?= $id_petugas; ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="waktu" class="form-label">Waktu</label>
            <input type="datetime-local" class="form-control" id="waktu" name="waktu">
        </div>
        <div class="mb-3">
            <label for="pelabuhan" class="form-label">Pelabuhan</label>
            <input type="text" class="form-control" id="pelabuhan" name="pelabuhan" placeholder="Masukkan Pelabuhan">
        </div>
        <div class="mb-3">
            <label for="maksud_kedatangan" class="form-label">Maksud Kedatangan</label>
            <input type="text" class="form-control" id="maksud_kedatangan" name="maksud_kedatangan" placeholder="Masukkan Maksud Kedatangan">
        </div>
        <div class="mb-3">
            <label for="id_agen" class="form-label">Id Agen</label>
            <input type="text" class="form-control" id="id_agen" name="id_agen" placeholder="Masukkan Id Agen">
        </div>
        <div class="mb-3">
            <label for="latitude" class="form-label">Latitude</label>
            <input type="text" class="form-control" id="latitude" name="latitude" placeholder="Masukkan Koordinat Lintang">
        </div>
        <div class="mb-3">
            <label for="longitude" class="form-label">Longitude</label>
            <input type="text" class="form-control" id="longitude" name="longitude" placeholder="Masukkan Koordinat Bujur">
        </div>
        <div class="mb-3">
            <label for="MMSI" class="form-label">MMSI</label>
            <input type="text" class="form-control" id="MMSI" name="MMSI" placeholder="Masukkan MMSI">
        </div>
        <div class="mb-3">
            <label for="pelabuhan_asal" class="form-label">Pelabuhan Asal</label>
            <input type="text" class="form-control" id="pelabuhan_asal" name="pelabuhan_asal" placeholder="Masukkan Pelabuhan Asal">
        </div>
        <div class="mb-3">
            <label for="pelabuhan_tujuan" class="form-label">Pelabuhan Tujuan</label>
            <input type="text" class="form-control" id="pelabuhan_tujuan" name="pelabuhan_tujuan" placeholder="Masukkan Pelabuhan Tujuan">
        </div>
        <div class="mb-3">
            <label for="draft_depan" class="form-label">Draft Depan (Meter)</label>
            <input type="number" class="form-control" id="draft_depan" name="draft_depan" placeholder="Masukkan Draft Depan">
        </div>
        <div class="mb-3">
            <label for="draft_belakang" class="form-label">Draft Belakang (Meter)</label>
            <input type="number" class="form-control" id="draft_belakang" name="draft_belakang" placeholder="Masukkan Draft Belakang">
        </div>
        <div class="mb-3">
            <label for="ETA" class="form-label">ETA</label>
            <input type="datetime-local" class="form-control" id="ETA" name="ETA">
        </div>
        <div class="mb-3">
            <label for="ATA" class="form-label">ATA</label>
            <input type="datetime-local" class="form-control" id="ATA" name="ATA">
        </div>
        <div class="mb-3">
            <label for="waktu_keberangkatan" class="form-label">Waktu Keberangkatan</label>
            <input type="datetime-local" class="form-control" id="waktu_keberangkatan" name="waktu_keberangkatan">
        </div>
        <div class="mb-3">
            <label for="jenis_muatan" class="form-label">Jenis Muatan</label>
            <input type="text" class="form-control" id="jenis_muatan" name="jenis_muatan" placeholder="Masukkan Jenis Muatan Kapal">
        </div>
        <div class="mb-3">
            <label for="jumlah_muatan" class="form-label">Jumlah Muatan</label>
            <input type="text" class="form-control" id="jumlah_muatan" name="jumlah_muatan" placeholder="Masukkan Jumlah Muatan">
        </div>
        <div class="mb-3">
            <label for="jumlah_kru" class="form-label">Jumlah Kru (Include Kapten)</label>
            <input type="number" class="form-control" id="jumlah_kru" name="jumlah_kru" placeholder="Masukkan Jumlah Kru">
        </div>
        <div class="mb-3">
            <label for="info_penting" class="form-label">Informasi Penting</label>
            <input type="text" class="form-control" id="info_penting" name="info_penting" placeholder="Jika Ada Masukkan Informasi Penting">
        </div>
        <div class="mb-3">
            <label for="nama_kapten" class="form-label">Nama Kapten</label>
            <input type="text" class="form-control" id="nama_kapten" name="nama_kapten" placeholder="Masukkan Nama Lengkap Kapten">
        </div>
        <button type="submit" class="btn btn-primary w-100">Tambah Monitor</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.getElementById('MMSI').addEventListener('blur', function() {
    var mmsi = this.value;
    
    if(mmsi) {
        // Fetch the ship data based on MMSI
        fetch('get_ship_data.php?MMSI=' + mmsi)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Fill the form fields with the fetched data
                    document.getElementById('nama_kapal').value = data.nama_kapal;
                    document.getElementById('jenis_kapal').value = data.jenis_kapal;
                    document.getElementById('callsign').value = data.callsign;
                    document.getElementById('GT').value = data.GT;
                    document.getElementById('LOA').value = data.LOA;
                    document.getElementById('beam').value = data.beam;
                    document.getElementById('draft').value = data.draft;
                } else {
                    alert('Data kapal tidak ditemukan.');
                }
            });
    }
});
</script>

<script>
document.getElementById('id_agen').addEventListener('blur', function() {
    var id_agen = this.value;
    
    if(id_agen) {
        // Fetch the agen data based on id_agen
        fetch('get_agen_data.php?id_agen=' + id_agen)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Fill the form fields with the fetched data
                    document.getElementById('nama_agen').value = data.nama_agen;
                    document.getElementById('waktu_sandar').value = data.waktu_sandar;
                    document.getElementById('posisi_sandar').value = data.posisi_sandar;
                    document.getElementById('keterangan').value = data.keterangan;
                } else {
                    alert('Data agen tidak ditemukan.');
                }
            });
    }
});
</script>

</body>
</html>

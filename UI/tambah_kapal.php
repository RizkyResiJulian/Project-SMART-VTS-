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

<a href="data_kapal.php" class="btn btn-back">
    <i class="bi bi-arrow-left"></i>
</a>

<div class="container mt-5">
    <h1 class="text-center mb-4">Tambah Data Kapal</h1>
    <form action="proses_tambah_kapal.php" method="POST">
        <div class="mb-3">
            <label for="id_petugas" class="form-label">Id Petugas</label>
            <input type="text" class="form-control" id="id_petugas" name="id_petugas" value="<?= $id_petugas; ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="MMSI" class="form-label">MMSI</label>
            <input type="number" class="form-control" id="MMSI" name="MMSI" placeholder="Masukkan MMSI" required>
        </div>
        <div class="mb-3">
            <label for="nama_kapal" class="form-label">Nama Kapal</label>
            <input type="text" class="form-control" id="nama_kapal" name="nama_kapal" placeholder="Masukkan Nama Kapal" required>
        </div>
        <div class="mb-3">
            <label for="callsign" class="form-label">Callsign</label>
            <input type="text" class="form-control" id="callsign" name="callsign" placeholder="Masukkan Callsign" required>
        </div>
        <div class="mb-3">
            <label for="jenis_kapal" class="form-label">Jenis Kapal</label>
            <select class="form-control" id="jenis_kapal" name="jenis_kapal" required>
            <option value="TANKER">TANKER</option>
                <option value="LNG/LPG CARRIER">LNG/LPG CARRIER</option>
                <option value="CARGO VESSEL">CARGO VESSEL</option>
                <option value="CONTAINER VESSEL">CONTAINER VESSEL</option>
                <option value="BULK CARRIER">BULK CARRIER</option>
                <option value="RORO">RORO</option>
                <option value="PASSANGER VESSEL">PASSANGER VESSEL</option>
                <option value="LIVESTOCK CARRIER">LIVESTOCK CARRIER</option>
                <option value="TUG/TOW">TUG/TOW</option>
                <option value="GOVERNMENT VESSEL">GOVERNMENT VESSEL</option>
                <option value="FISHING VESSEL">FISHING VESSEL</option>
                <option value="OTHERS">OTHERS</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="GT" class="form-label">GT(Ton)</label>
            <input type="number" class="form-control" id="GT" name="GT" placeholder="Masukkan Berat Kapal (GT)" required>
        </div>
        <div class="mb-3">
            <label for="LOA" class="form-label">LOA (Meter)</label>
            <input type="number" class="form-control" id="LOA" name="LOA" placeholder="Masukkan Panjang LOA" required>
        </div>
        <div class="mb-3">
            <label for="beam" class="form-label">Beam (Meter)</label>
            <input type="number" class="form-control" id="beam" name="beam" placeholder="Masukkan Beam" required>
        </div>
        <div class="mb-3">
            <label for="draft" class="form-label">Draft (Meter)</label>
            <input type="number" class="form-control" id="draft" name="draft" placeholder="Masukkan Draft" required>
        </div>
        <div class="mb-3">
            <label for="zona" class="form-label">Zona</label>
            <input type="text" class="form-control" id="zona" name="zona" placeholder="Masukkan Zona Labuh" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Tambah Kapal</button>
    </form>
</div>
</body>
</html>

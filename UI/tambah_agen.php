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
$sql = "SELECT MAX(id_agen) AS last_id FROM data_agen";
$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

$last_id = $row['last_id']; // Ambil ID terakhir
$next_id = 1;

if ($last_id) {
    $last_number = (int)substr($last_id, -3); // Ambil 3 digit terakhir
    $next_id = $last_number + 1; // Menambah 1
}

$new_id = "AGEN-" . str_pad($next_id, 3, "0", STR_PAD_LEFT); // Format menjadi KAPAL-001
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Agen</title>
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

<a href="data_agen.php" class="btn btn-back">
    <i class="bi bi-arrow-left"></i>
</a>

<div class="container mt-5">
    <h1 class="text-center mb-4">Tambah Data Agen</h1>
    <form action="proses_tambah_agen.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="id_agen" class="form-label">Id Agen</label>
            <input type="text" class="form-control" id="id_agen" name="id_agen" value="<?= $new_id; ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="id_petugas" class="form-label">Id Petugas</label>
            <input type="text" class="form-control" id="id_petugas" name="id_petugas" value="<?= $id_petugas; ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="nama_agen" class="form-label">Nama Agen</label>
            <input type="text" class="form-control" id="nama_agen" name="nama_agen" placeholder="Masukkan Nama Agen" required>
        </div>
        <div class="mb-3">
            <label for="MMSI" class="form-label">MMSI</label>
            <input type="text" class="form-control" id="MMSI" name="MMSI" placeholder="Masukkan MMSI" required>
        </div>
        <div class="mb-3">
            <label for="nama_kapten" class="form-label">Nama Kapten</label>
            <input type="text" class="form-control" id="nama_kapten" name="nama_kapten" placeholder="Masukkan Nama Kapten" required>
        </div>
        <div class="mb-3">
            <label for="keterangan" class="form-label">Keterangan</label>
            <select class="form-control" id="keterangan" name="keterangan" required>
            <option value="In">In</option>
                <option value="Out">Out</option>
                <option value="Passing">Passing</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="nama_file" class="form-label">Upload File PKK (PDF/JPG)</label>
            <input type="file" class="form-control" id="nama_file" name="nama_file" accept=".pdf,.jpg,.jpeg" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Tambah Agen</button>
    </form>
</div>
</body>
</html>

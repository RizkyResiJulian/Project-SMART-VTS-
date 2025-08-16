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

// Ambil ID kapal terakhir dan buat ID baru
$sql = "SELECT MAX(id_petugas) AS last_id FROM daftar_petugas";
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet"> <!-- Bootstrap Icons -->
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            padding: 40px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .form-label {
            font-weight: 600;
        }
        .btn-submit {
            background-color: #007bff;
            color: white;
        }
        .btn-submit:hover {
            background-color: #0056b3;
        }
        /* Tombol kembali bulat dengan panah */
        .btn-back {
            position: fixed;
            top: 10px;
            left: 10px;
            width: 50px;
            height: 50px;
            background-color: #007bff;
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 20px;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .btn-back:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <!-- Tombol Kembali -->
    <a href="data_petugas.php" class="btn-back">
        <i class="bi bi-arrow-left"></i> <!-- Ikon panah kiri -->
    </a>

<div class="container mt-5">
    <h1 class="text-center mb-4">Tambah Petugas</h1>
    <form action="proses_tambah_petugas.php" method="POST">
        <div class="mb-3">
            <label for="id_petugas" class="form-label">Nomor</label>
            <input type="text" class="form-control" id="id_petugas" name="id_petugas" value="<?= $new_id; ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="nama" class="form-label">Nama Petugas</label>
            <input type="text" class="form-control" id="nama" name="nama" placeholder="Masukkan Nama Lengkap">
        </div>
        <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" class="form-control" id="username" name="username" placeholder="Pastikan Username Sudah Diberikan">
        </div>
        <div class="mb-3">
            <label for="shift" class="form-label">Shift Jaga</label>
            <select class="form-control" id="shift" name="shift">
                <option value="Pagi">Pagi</option>
                <option value="Siang">Siang</option>
                <option value="Malam">Malam</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="tanggal" class="form-label">Tanggal</label>
            <input type="datetime-local" class="form-control" id="tanggal" name="tanggal">
        </div>
        <button type="submit" class="btn btn-submit w-100">Tambah Petugas</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

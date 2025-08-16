<?php
session_start();
include 'koneksi.php';

// Tetap di atas sebelum HTML
date_default_timezone_set('Asia/Jakarta');
$hour = date('H'); // Ambil jam dalam format 24 jam

// Di awal file dashboard.php atau file yang dilindungi
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 3600)) {
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=true");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

if ($hour >= 5 && $hour < 17) {
    $banner_image = 'port1.jpg'; // Siang
} else {
    $banner_image = 'port.jpg'; // Malam
}

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Ambil tanggal hari ini dalam format Y-m-d
$today = date('Y-m-d');

// Mengambil data jumlah kapal yang passing hari ini
$query_kapal_passing = "SELECT COUNT(*) AS total_passing FROM data_monitor WHERE keterangan = 'Passing' AND DATE(waktu) = '$today'";
$result_passing = mysqli_query($conn, $query_kapal_passing);
$row_passing = mysqli_fetch_assoc($result_passing);
$total_passing = $row_passing['total_passing'] ?? 0;

// Mengambil data kapal yang masuk hari ini
$query_kapal_masuk = "SELECT COUNT(*) AS total_masuk FROM data_monitor WHERE keterangan = 'In' AND DATE(ATA) = '$today'";
$result_masuk = mysqli_query($conn, $query_kapal_masuk);
$row_masuk = mysqli_fetch_assoc($result_masuk);
$total_masuk = $row_masuk['total_masuk'] ?? 0;

// Mengambil data kapal yang keluar hari ini
$query_kapal_keluar = "SELECT COUNT(*) AS total_keluar FROM data_monitor WHERE keterangan = 'Out' AND DATE(waktu_keberangkatan) = '$today'";
$result_keluar = mysqli_query($conn, $query_kapal_keluar);
$row_keluar = mysqli_fetch_assoc($result_keluar);
$total_keluar = $row_keluar['total_keluar'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - VTS Cirebon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet"> <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

    <style>
    body {
        background-color: #f8f9fa;
    }
    .banner {
        position: relative;
        background-image: url('<?= $banner_image ?>');
        background-size: cover;
        background-position: center;
        height: 270px; /* dari 275px jadi 160px */
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        text-shadow: 2px 2px 5px rgba(0,0,0,0.7);
    }
    .banner .overlay {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 100%;
        background: rgba(0, 0, 0, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .banner-text {
        position: relative;
        z-index: 2;
    }
    .soft-navbar {
        background: linear-gradient(90deg, rgb(14, 111, 196) 0%, rgb(20, 216, 226) 100%);
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        padding-top: 0.2rem;   /* lebih ramping */
        padding-bottom: 0.3rem;
    }
    .soft-navbar .navbar-brand {
        font-weight: 400;
        font-size: 0.95rem;
        padding-left: 10px;
        color: #ffffff !important;
        text-shadow: 0px 1px 2px rgba(0, 0, 0, 0.2);
    }
    .btn-gradient-logout {
        background: transparent;
        border: 1.5px solid #fff;
        color: white;
        padding: 6px 14px;
        border-radius: 20px;
        font-weight: normal;
        font-size: 0.85rem;
        transition: 0.3s;
    }
    .btn-gradient-logout:hover {
        background-color: rgba(255,255,255,0.15);
        transform: scale(1.05);
    }
    .btn-logout {
        background-color: #ff4d4d;
        border: none;
        font-weight: bold;
    }
    .btn-logout:hover {
        background-color: #e60000;
    }
    .stats-card {
    display: flex;
    align-items: center;
    background-color: #ffffff;
    border-radius: 12px;
    padding: 15px 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    transition: 0.3s;
    min-height: 100px;
    }
    .stats-card:hover {
        transform: translateY(-4px);
    }
    .stats-icon {
        font-size: 2.2rem;
        color: #007bff;
        flex-shrink: 0;
    }
    .btn-gradient-logout {
        background: linear-gradient(45deg, #ff4d4d, #ff6f61);
        border: none;
        color: white;
        padding: 10px 20px;
        border-radius: 30px;
        font-weight: bold;
        transition: all 0.4s ease;
        box-shadow: 0px 4px 10px rgba(255, 77, 77, 0.5);
        font-size: 1rem;
    }
    .btn-gradient-logout:hover {
        background: linear-gradient(45deg, #ff6f61, #ff4d4d);
        transform: scale(1.05);
        color: white;
    }
    .btn-main {
        margin-top: 50px;
        margin-bottom: 60px;
    }
    .btn-gradient {
        background: linear-gradient(45deg, #007bff, #00c6ff);
        border: none;
        color: white;
        padding: 15px 30px;
        border-radius: 50px;
        transition: 0.4s;
        font-weight: bold;
        box-shadow: 0px 4px 10px rgba(0, 123, 255, 0.5);
    }
    .btn-gradient:hover {
        background: linear-gradient(45deg, #00c6ff, #007bff);
        transform: scale(1.05);
        color: white;
    }
    .footer {
        background-color: #343a40;
        color: white;
        padding: 15px 0; /* Dulu 20px, sekarang lebih ramping */
        margin-top: 20px; /* Boleh disesuaikan */
        font-size: 0.85rem; /* Sedikit lebih kecil */
    }
    .footer span {
        font-size: 0.9rem; /* Tambahan, agar tulisan lebih ramping */
    }
    .btn-icon-logout {
    background: transparent;
    border: none;
    color: #fff;
    font-size: 1.25rem;
    padding: 8px 12px;
    border-radius: 50%;
    transition: background-color 0.3s ease, transform 0.2s;
    }
    .btn-icon-logout:hover {
        background-color: rgba(255, 255, 255, 0.2);
        transform: rotate(-5deg) scale(1.1);
    }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg soft-navbar">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Created by Rizky Resi Julian</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <a href="logout.php" class="btn btn-icon-logout" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </nav>
    <!-- Header dengan Banner -->
    <div class="banner" style="background-image: url('<?= $banner_image ?>');">
        <div class="overlay">
            <div class="container text-center banner-text">
                <h1 class="display-4 animate__animated animate__fadeInDown">S.M.A.R.T VTS</h1>
                <p class="lead animate__animated animate__fadeInUp">Ship Management Autometed Reporting Technology</p>
            </div>
        </div>
    </div>
    <!-- Container untuk Konten -->
    <div class="container mt-5">
    <div class="row g-4">
        <!-- Kapal Passing -->
        <div class="col-md-4">
            <div class="stats-card d-flex align-items-center gap-3">
                <div class="stats-icon">
                    <i class="fas fa-ship"></i>
                </div>
                <div>
                    <h5 class="mb-1">Passing</h5>
                    <h3 class="mb-0"><?= $total_passing; ?></h3>
                    <small class="text-muted">Hari Ini</small>
                </div>
            </div>
        </div>
        <!-- Kapal Masuk -->
        <div class="col-md-4">
            <div class="stats-card d-flex align-items-center gap-3">
                <div class="stats-icon">
                    <i class="fas fa-arrow-circle-down"></i>
                </div>
                <div>
                    <h5 class="mb-1">Masuk</h5>
                    <h3 class="mb-0"><?= $total_masuk; ?></h3>
                    <small class="text-muted">Hari Ini</small>
                </div>
            </div>
        </div>
        <!-- Kapal Keluar -->
        <div class="col-md-4">
            <div class="stats-card d-flex align-items-center gap-3">
                <div class="stats-icon">
                    <i class="fas fa-arrow-circle-up"></i>
                </div>
                <div>
                    <h5 class="mb-1">Keluar</h5>
                    <h3 class="mb-0"><?= $total_keluar; ?></h3>
                    <small class="text-muted">Hari Ini</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tombol Navigasi -->
    <div class="row btn-main text-center">
        <div class="col-md-12 d-flex justify-content-center flex-wrap gap-3">
            <a href="data_monitor.php" class="btn btn-gradient btn-lg d-flex align-items-center gap-2">
                <i class="bi bi-speedometer2"></i> Data Monitor
            </a>
            <a href="data_kapal.php" class="btn btn-gradient btn-lg d-flex align-items-center gap-2">
                <i class="fas fa-ship"></i> Data Kapal
            </a>
            <a href="data_agen.php" class="btn btn-gradient btn-lg d-flex align-items-center gap-2">
                <i class="fas fa-user-tie"></i> Data Agen
            </a>
            <a href="data_petugas.php" class="btn btn-gradient btn-lg d-flex align-items-center gap-2">
                <i class="fas fa-users-cog"></i> Data Petugas
            </a>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="footer text-center">
    <div class="container">
        <span><i class="bi bi-geo-alt-fill"></i> VTS Cirebon | Jl. Ambon No.7, Panjunan, Kec. Lemahwungkuk, Kota Cirebon, Jawa Barat 45112 | </span>
        <span>© 2025 All Rights Reserved</span>
    </div>
</footer>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
});
</script>


    <!-- Script -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

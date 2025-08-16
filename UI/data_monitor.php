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

// Ambil data monitor, data agen dan data kapal dari database
$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$where = '';

if ($filter == 'today') {
    $where = "WHERE DATE(dm.waktu) = CURDATE()";
} elseif ($filter == 'week') {
    $where = "WHERE WEEK(dm.waktu) = WEEK(CURDATE()) AND YEAR(dm.waktu) = YEAR(CURDATE())";
} elseif ($filter == 'month') {
    $where = "WHERE MONTH(dm.waktu) = MONTH(CURDATE()) AND YEAR(dm.waktu) = YEAR(CURDATE())";
}

$sql = "
    SELECT 
        dm.id_monitor, 
        dp.nama,
        dm.waktu, dm.pelabuhan, dm.maksud_kedatangan,
        dm.latitude, dm.longitude,
        dk.MMSI, dk.nama_kapal, dk.callsign, dk.jenis_kapal,
        dk.GT, dk.LOA, dk.beam, dk.draft, dk.zona,
        dm.draft_depan, dm.draft_belakang,
        dm.pelabuhan_asal, dm.pelabuhan_tujuan, dm.ETA, dm.ATA, dm.waktu_keberangkatan,
        dm.jenis_muatan, dm.jumlah_muatan, dm.jumlah_kru, dm.info_penting, dm.nama_kapten,
        da.id_agen, da.nama_agen, da.keterangan
    FROM 
        data_monitor dm
    LEFT JOIN 
        daftar_petugas dp ON dm.id_petugas = dp.id_petugas
    LEFT JOIN 
        data_kapal dk ON dm.MMSI = dk.MMSI
    LEFT JOIN
        data_agen da ON dm.id_agen = da.id_agen
    $where
    ORDER BY dm.waktu DESC
";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Monitor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #74ebd5 0%, #acb6e5 100%);
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
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
        .container {
            padding-top: 100px;
            padding-bottom: 50px;
        }
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            background-color: white;
        }
        h1 {
            font-weight: 600;
            color: #2d3436;
        }
        .btn-success, .btn-danger {
            border-radius: 10px;
            font-weight: 500;
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        .table-hover tbody tr:hover {
            background-color: #f1f3f5;
        }
        .search-box {
            max-width: 300px;
            margin-bottom: 15px;
        }
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
        .pagination .page-link {
            border-radius: 8px;
            margin: 0 3px;
            transition: background 0.3s;
        }
        .pagination .page-link:hover {
            background-color: #4fc3f7;
            color: white;
        }
        /* Animasi masuk */
        .fade-in {
            animation: fadeIn 1s ease-in;
        }
        @keyframes fadeIn {
            0% {opacity: 0; transform: translateY(-10px);}
            100% {opacity: 1; transform: translateY(0);}
        }
    </style>
</head>
<body>

<!-- Tombol Kembali -->
<a href="dashboard.php" class="btn btn-back">
    <i class="bi bi-arrow-left"></i>
</a>

<div class="container fade-in">
    <div class="header mb-4 d-flex justify-content-between align-items-center">
        <h1>Data Monitor</h1>
        <div>
            <!-- Tombol AI VTS -->
            <a href="index.php" class="btn btn-primary me-2">
                <i class="bi bi-robot"></i> AI VTS
            </a>
            <a href="tambah_monitor.php" class="btn btn-success me-2">
                <i class="bi bi-plus-circle me-1"></i> Tambah Monitor
            </a>
            <a href="cetak_laporan.php" class="btn btn-danger" title="Cetak Laporan Harian">
                <i class="bi bi-printer"></i> Cetak
            </a>
        </div>
    </div>

    <div class="card p-4">
    <div class="d-flex justify-content-between flex-wrap align-items-center mb-3">
        <input type="text" id="searchInput" class="form-control form-control-sm me-2" style="max-width: 250px;" placeholder="Cari Monitor...">

        <form method="GET" class="d-flex align-items-center">
            <select name="filter" class="form-select form-select-sm me-2" style="min-width: 120px;">
                <option value="">Semua</option>
                <option value="today" <?= (isset($_GET['filter']) && $_GET['filter'] == 'today') ? 'selected' : '' ?>>Hari Ini</option>
                <option value="week" <?= (isset($_GET['filter']) && $_GET['filter'] == 'week') ? 'selected' : '' ?>>Minggu Ini</option>
                <option value="month" <?= (isset($_GET['filter']) && $_GET['filter'] == 'month') ? 'selected' : '' ?>>Bulan Ini</option>
            </select>
            <button type="submit" class="btn btn-secondary btn-sm">Terapkan</button>
        </form>
    </div>

        <!-- Tabel Data Monitor -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover text-center align-middle" id="monitorTable">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th>Nama Petugas</th>
                        <th>Waktu (LT)</th>
                        <th>Pelabuhan</th>
                        <th>Maksud Kedatangan</th>
                        <th>Id Agen</th>
                        <th>MMSI</th>
                        <th>Koordinat</th>
                        <th>Nama Kapal</th>
                        <th>Callsign</th>
                        <th>Jenis Kapal</th>
                        <th>GT</th>
                        <th>LOA</th>
                        <th>Beam</th>
                        <th>Draft</th>
                        <th>Draft Depan</th>
                        <th>Draft Belakang</th>
                        <th>Zona Kapal</th>
                        <th>Pelabuhan Asal</th>
                        <th>Pelabuhan Tujuan</th>
                        <th>ETA</th>
                        <th>ATA</th>
                        <th>Waktu Keberangkatan</th>
                        <th>Jenis Muatan</th>
                        <th>Jumlah Muatan</th>
                        <th>Jumlah Kru</th>
                        <th>Info Penting</th>
                        <th>Nama Kapten</th>
                        <th>Nama Agen</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id_monitor']); ?></td>
                                <td><?= htmlspecialchars($row['nama']); ?></td>
                                <td><?= htmlspecialchars($row['waktu']); ?></td>
                                <td><?= htmlspecialchars($row['pelabuhan']); ?></td>
                                <td><?php echo empty($row['maksud_kedatangan']) ? '-' : $row['maksud_kedatangan']; ?></td>
                                <td><?php echo empty($row['id_agen']) ? '-' : $row['id_agen']; ?></td>
                                <td><?= htmlspecialchars($row['MMSI']); ?></td>
                                <td><?= htmlspecialchars($row['latitude'] . ', ' . $row['longitude']); ?></td>
                                <td><?= htmlspecialchars($row['nama_kapal']); ?></td>
                                <td><?= htmlspecialchars($row['callsign']); ?></td>
                                <td><?= htmlspecialchars($row['jenis_kapal']); ?></td>
                                <td><?= htmlspecialchars($row['GT']); ?></td>
                                <td><?= htmlspecialchars($row['LOA']); ?></td>
                                <td><?= htmlspecialchars($row['beam']); ?></td>
                                <td><?= htmlspecialchars($row['draft']); ?></td>
                                <td><?= htmlspecialchars($row['draft_depan']); ?></td> 
                                <td><?= htmlspecialchars($row['draft_belakang']); ?></td>
                                <td><?= htmlspecialchars($row['zona']); ?></td>                
                                <td><?= htmlspecialchars($row['pelabuhan_asal']); ?></td>
                                <td><?= htmlspecialchars($row['pelabuhan_tujuan']); ?></td>
                                <td><?= htmlspecialchars($row['ETA']); ?></td>
                                <td><?php echo empty($row['ATA']) ? '-' : $row['ATA']; ?></td>
                                <td><?php echo empty($row['waktu_keberangkatan']) ? '-' : $row['waktu_keberangkatan']; ?></td>
                                <td><?= htmlspecialchars($row['jenis_muatan']); ?></td>
                                <td><?= htmlspecialchars($row['jumlah_muatan']); ?></td>
                                <td><?= htmlspecialchars($row['jumlah_kru']); ?></td>
                                <td style="color: <?= empty($row['info_penting']) ? 'inherit' : 'red'; ?>; font-weight: <?= empty($row['info_penting']) ? 'normal' : 'bold'; ?>">
                                    <?= empty($row['info_penting']) ? '-' : htmlspecialchars($row['info_penting']); ?>
                                </td>
                                <td><?php echo empty($row['nama_kapten']) ? '-' : $row['nama_kapten']; ?></td>
                                <td><?php echo empty($row['nama_agen']) ? '-' : $row['nama_agen']; ?></td>
                                <td><?php echo empty($row['keterangan']) ? 'Passing' : $row['keterangan']; ?></td>
                                <td>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="edit_monitor.php?id=<?= htmlspecialchars($row['id_monitor']); ?>" class="btn btn-warning btn-sm">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="hapus_monitor.php?id=<?= htmlspecialchars($row['id_monitor']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                        <a href="cetak_individual.php?id=<?= htmlspecialchars($row['id_monitor']); ?>" class="btn btn-info btn-sm" target="_blank">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="23" class="text-center">Tidak ada data Monitor</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Search Functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    var searchValue = this.value.toLowerCase();
    var rows = document.querySelectorAll('#monitorTable tbody tr');
    rows.forEach(function(row) {
        var rowText = row.innerText.toLowerCase();
        row.style.display = rowText.includes(searchValue) ? '' : 'none';
    });
});

// Pagination
const rowsPerPage = 10;
const table = document.getElementById('monitorTable');
const rows = table.getElementsByTagName('tr');
const pagination = document.getElementById('pagination');

function displayTable(page) {
    const start = (page - 1) * rowsPerPage + 1;
    const end = start + rowsPerPage;
    for (let i = 1; i < rows.length; i++) {
        rows[i].style.display = (i >= start && i < end) ? '' : 'none';
    }
}

function setupPagination() {
    pagination.innerHTML = '';
    const pageCount = Math.ceil((rows.length - 1) / rowsPerPage);
    for (let i = 1; i <= pageCount; i++) {
        const li = document.createElement('li');
        li.className = 'page-item';
        li.innerHTML = `<a class="page-link" href="#">${i}</a>`;
        li.addEventListener('click', function(e) {
            e.preventDefault();
            displayTable(i);
            document.querySelectorAll('.page-item').forEach(el => el.classList.remove('active'));
            li.classList.add('active');
        });
        pagination.appendChild(li);
    }
    if (pagination.firstChild) pagination.firstChild.classList.add('active');
}

if (rows.length > rowsPerPage + 1) {
    setupPagination();
    displayTable(1);
}
</script>
</body>
</html>

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

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$sql =  "
    SELECT 
        dk.MMSI, 
        dp.nama,
        dk.MMSI, dk.nama_kapal, dk.callsign, dk.jenis_kapal,
        dk.GT, dk.LOA, dk.beam, dk.draft, dk.zona
    FROM 
        data_kapal dk
    LEFT JOIN 
        daftar_petugas dp ON dk.id_petugas = dp.id_petugas
";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kapal</title>
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
            background: #6c5ce7;
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 22px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            z-index: 999;
            transition: background 0.3s;
        }
        .btn-back:hover {
            background: #5a4dc8;
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
        }
        /* Animasi masuk */
        .fade-in {
            animation: fadeIn 1s ease-in;
        }
        @keyframes fadeIn {
            0% {opacity: 0; transform: translateY(-10px);}
            100% {opacity: 1; transform: translateY(0);}
        }
        .btn-back {
            position: fixed;
            top: 20px;
            left: 20px;
            width: 50px;
            height: 50px;
            background-color: #6c757d;
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, background-color 0.3s ease;
            border: none;
        }

        .btn-back:hover {
            background-color: #495057;
            transform: scale(1.1);
        }

        .btn-back i {
            font-size: 24px;
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
        <h1>Data Kapal</h1>
        <div>
            <a href="tambah_kapal.php" class="btn btn-success me-2">
                <i class="bi bi-plus-circle me-1"></i> Tambah Kapal
            </a>
            <a href="cetak_laporan.php" class="btn btn-danger" title="Cetak Laporan Harian">
                <i class="bi bi-printer"></i> Cetak
            </a>
        </div>
    </div>

    <div class="card p-4">
        <input type="text" id="searchInput" class="form-control search-box" placeholder="Cari kapal...">

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle text-center">
                <thead class="table-primary">
                    <tr>
                        <th>MMSI</th>
                        <th>Nama Petugas</th>
                        <th>Nama Kapal</th>
                        <th>Callsign</th>
                        <th>Jenis Kapal</th>
                        <th>GT</th>
                        <th>LOA</th>
                        <th>Beam</th>
                        <th>Draft</th>
                        <th>Zona</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="kapalTable">
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['MMSI']); ?></td>
                                <td><?= htmlspecialchars($row['nama']); ?></td>
                                <td><?= htmlspecialchars($row['nama_kapal']); ?></td>
                                <td><?= htmlspecialchars($row['callsign']); ?></td>
                                <td><?= htmlspecialchars($row['jenis_kapal']); ?></td>
                                <td><?= htmlspecialchars($row['GT']); ?></td>
                                <td><?= htmlspecialchars($row['LOA']); ?></td>
                                <td><?= htmlspecialchars($row['beam']); ?></td>
                                <td><?= htmlspecialchars($row['draft']); ?></td>
                                <td><?= htmlspecialchars($row['zona']); ?></td>
                                <td>
                                    <a href="edit_kapal.php?MMSI=<?= htmlspecialchars($row['MMSI']); ?>" class="btn btn-warning btn-sm me-1">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="hapus_kapal.php?MMSI=<?= htmlspecialchars($row['MMSI']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Pastikan data kapal yang anda hapus tidak terdapat pada Data Agen atau Data Monitor, Yakin ingin menghapus?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center">Tidak ada data kapal</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <nav aria-label="Page navigation">
            <ul class="pagination mt-3" id="pagination"></ul>
        </nav>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Search
document.getElementById('searchInput').addEventListener('keyup', function() {
    var searchValue = this.value.toLowerCase();
    var rows = document.querySelectorAll('#kapalTable tr');
    rows.forEach(function(row) {
        var rowText = row.innerText.toLowerCase();
        row.style.display = rowText.includes(searchValue) ? '' : 'none';
    });
});

// Pagination
const rowsPerPage = 10;
const table = document.getElementById('kapalTable');
const rows = table.getElementsByTagName('tr');
const pagination = document.getElementById('pagination');

function displayTable(page) {
    const start = (page - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    for (let i = 0; i < rows.length; i++) {
        rows[i].style.display = (i >= start && i < end) ? '' : 'none';
    }
}

function setupPagination() {
    pagination.innerHTML = '';
    const pageCount = Math.ceil(rows.length / rowsPerPage);
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

if (rows.length > rowsPerPage) {
    setupPagination();
    displayTable(1);
}
</script>
</body>
</html>

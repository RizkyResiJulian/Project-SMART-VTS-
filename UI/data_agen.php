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

$sql = "
    SELECT 
        da.id_agen, da.nama_agen, da.nama_kapten, da.keterangan, da.MMSI, da.nama_file,
        dp.nama
    FROM 
        data_agen da
    LEFT JOIN 
        daftar_petugas dp ON da.id_petugas = dp.id_petugas
";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Agen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #74ebd5 0%, #acb6e5 100%);
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            padding-bottom: 50px;
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
            padding-top: 80px;
        }
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            background: #fff;
            animation: fadeIn 1s ease;
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
            border-radius: 12px;
            overflow: hidden;
        }
        .table thead {
            background-color: #4fc3f7;
            color: white;
        }
        .table-hover tbody tr:hover {
            background-color: #f1f3f5;
        }
        .table td, .table th {
            vertical-align: middle;
        }
        .table-responsive {
            overflow-x: auto;
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
        @keyframes fadeIn {
            from {opacity: 0; transform: translateY(-20px);}
            to {opacity: 1; transform: translateY(0);}
        }
    </style>
</head>
<body>

<a href="dashboard.php" class="btn btn-back">
    <i class="bi bi-arrow-left"></i>
</a>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Data Agen</h1>
        <div>
            <a href="tambah_agen.php" class="btn btn-success me-2">
                <i class="bi bi-plus-circle me-1"></i> Tambah
            </a>
            <a href="cetak_laporan.php" class="btn btn-danger">
                <i class="bi bi-printer me-1"></i> Cetak
            </a>
        </div>
    </div>

    <div class="card p-4">
        <input type="text" id="searchInput" class="form-control search-box" placeholder="Cari Agen...">

        <div class="table-responsive">
            <table class="table table-bordered table-hover text-center align-middle" id="agenTable">
                <thead>
                    <tr>
                        <th>ID Agen</th>
                        <th>Nama Petugas</th>
                        <th>Nama Agen</th>
                        <th>MMSI</th>
                        <th>Nama Kapten</th>
                        <th>Keterangan</th>
                        <th>File</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['id_agen']); ?></td>
                                <td><?= htmlspecialchars($row['nama']); ?></td>
                                <td><?= htmlspecialchars($row['nama_agen']); ?></td>
                                <td><?= htmlspecialchars($row['MMSI']); ?></td>
                                <td><?= htmlspecialchars($row['nama_kapten']); ?></td>
                                <td><?= htmlspecialchars($row['keterangan']); ?></td>
                                <td>
                                    <?php if (!empty($row['nama_file'])): ?>
                                        <a href="uploads/<?= htmlspecialchars($row['nama_file']); ?>" target="_blank" class="btn btn-primary btn-sm">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Tidak ada file</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit_agen.php?id=<?= htmlspecialchars($row['id_agen']); ?>" class="btn btn-warning btn-sm me-1">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <a href="hapus_agen.php?id=<?= $row['id_agen']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Pastikan Data Agen yang anda hapus tidak terdapat pada Data Monitor,Yakin ingin menghapus agen ini?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">Tidak ada data agen</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <nav>
            <ul class="pagination" id="pagination"></ul>
        </nav>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Search Functionality
document.getElementById('searchInput').addEventListener('keyup', function() {
    var searchValue = this.value.toLowerCase();
    var rows = document.querySelectorAll('#agenTable tbody tr');
    rows.forEach(function(row) {
        var rowText = row.innerText.toLowerCase();
        row.style.display = rowText.includes(searchValue) ? '' : 'none';
    });
});

// Pagination
const rowsPerPage = 10;
const table = document.getElementById('agenTable');
const rows = table.getElementsByTagName('tr');
const pagination = document.getElementById('pagination');

function displayTable(page) {
    const start = (page - 1) * rowsPerPage + 1; // +1 untuk header
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

<?php
session_start();
include 'koneksi.php'; // Pastikan koneksi ke database
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

// Cek apakah ID petugas tersedia
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "ID petugas tidak ditemukan!";
    exit;
}

$id_petugas = $_GET['id'];

// Ambil data petugas berdasarkan ID
$sql = "SELECT * FROM daftar_petugas WHERE id_petugas = '$id_petugas'";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) == 0) {
    echo "Data Petugas tidak ditemukan!";
    exit;
}

$data = mysqli_fetch_assoc($result);

// Proses update data jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $shift = mysqli_real_escape_string($conn, $_POST['shift']);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);

    // Update data petugas
    $update_sql = "
        UPDATE daftar_petugas 
        SET nama = '$nama',
            username = '$username',
            shift = '$shift', 
            tanggal = '$tanggal'
        WHERE id_petugas = '$id_petugas'";

    if (mysqli_query($conn, $update_sql)) {
        header("Location: data_petugas.php?msg=success");
        exit;
    } else {
        $error = "Gagal memperbarui data petugas!";
    }
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
<a href="data_monitor.php" class="btn btn-back">
    <i class="bi bi-arrow-left"></i>
</a>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Edit Data Petugas</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="edit_petugas.php?id=<?= $id_petugas; ?>">
            <div class="mb-3">
                <label for="nama" class="form-label">Nama Petugas</label>
                <input type="text" class="form-control" id="nama" name="nama" 
                       value="<?= htmlspecialchars($data['nama']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" 
                       value="<?= htmlspecialchars($data['username']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="shift" class="form-label">Shift</label>
                <select class="form-control" id="shift" name="shift">
                    <option value="Pagi" <?= $data['shift'] == 'Pagi' ? 'selected' : ''; ?>>Pagi</option>
                    <option value="Siang" <?= $data['shift'] == 'Siang' ? 'selected' : ''; ?>>Siang</option>
                    <option value="Malam" <?= $data['shift'] == 'Malam' ? 'selected' : ''; ?>>Malam</option>
                </select>       
            </div>
            <div class="mb-3">
                <label for="tanggal" class="form-label">Tanggal</label>
                <input type="datetime-local" class="form-control" id="tanggal" name="tanggal" 
                       value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($data['tanggal']))); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

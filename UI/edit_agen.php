<?php
session_start();
include 'koneksi.php'; // Pastikan koneksi ke database
// Di awal file dashboard.php atau file yang dilindungi
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 360)) {
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

// Cek apakah ID Agen tersedia
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "ID Agen tidak ditemukan!";
    exit;
}

$id_agen = $_GET['id'];

// Ambil data agen berdasarkan ID
$sql = "SELECT * FROM data_agen WHERE id_agen = '$id_agen'";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) == 0) {
    echo "Data Agen tidak ditemukan!";
    exit;
}

$data = mysqli_fetch_assoc($result);

// Proses update data jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_agen = mysqli_real_escape_string($conn, $_POST['nama_agen']);
    $MMSI = mysqli_real_escape_string($conn, $_POST['MMSI']);
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    $nama_kapten = mysqli_real_escape_string($conn, $_POST['nama_kapten']);

    // Handle upload file jika ada
    if (isset($_FILES['nama_file']) && $_FILES['nama_file']['error'] == 0) {
        $allowed_types = ['pdf', 'jpg', 'jpeg'];
        $file_name = $_FILES['nama_file']['name'];
        $file_tmp = $_FILES['nama_file']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_types)) {
            $upload_dir = "uploads/"; // Pastikan folder uploads/ sudah ada dan punya izin tulis
            $new_file_name = uniqid() . '.' . $file_ext;
            move_uploaded_file($file_tmp, $upload_dir . $new_file_name);
        } else {
            $error = "Format file tidak didukung! (hanya PDF, JPG, JPEG)";
        }
    } else {
        // Jika tidak upload file baru, pakai file lama
        $new_file_name = $data['nama_file'];
    }

    if (!isset($error)) {
        $update_sql = "UPDATE data_agen 
                        SET nama_agen = ?, MMSI = ?, keterangan = ?, nama_file = ? , nama_kapten = ?
                        WHERE id_agen = ?";
        $stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($stmt, "ssssss", 
            $nama_agen, $MMSI, $keterangan, $new_file_name, $nama_kapten, $id_agen);

        if (mysqli_stmt_execute($stmt)) {
            header("Location: data_agen.php?msg=success");
            exit;
        } else {
            $error = "Gagal memperbarui data Agen!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Agen</title>
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
    <h1 class="text-center mb-4">Edit Data Agen</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

    <form method="POST" action="edit_agen.php?id=<?= $id_agen; ?>">
        <div class="mb-3">
            <label for="id_agen" class="form-label">Id Agen</label>
            <input type="text" class="form-control" id="id_agen" name="id_agen" 
                    value="<?= htmlspecialchars($data['id_agen']); ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="nama_agen" class="form-label">Nama Agen</label>
            <input type="text" class="form-control" id="nama_agen" name="nama_agen" 
                   value="<?= htmlspecialchars($data['nama_agen']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="MMSI" class="form-label">MMSI</label>
            <input type="text" class="form-control" id="MMSI" name="MMSI" 
                   value="<?= htmlspecialchars($data['MMSI']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="nama_kapten" class="form-label">Nama Kapten</label>
            <input type="text" class="form-control" id="nama_kapten" name="nama_kapten" 
                   value="<?= htmlspecialchars($data['nama_kapten']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="keterangan" class="form-label">Keterangan</label>
            <select class="form-control" id="keterangan" name="keterangan">
                <option value="Passing" <?= $data['keterangan'] == 'Passing' ? 'selected' : ''; ?>>Passing</option>
                <option value="In" <?= $data['keterangan'] == 'In' ? 'selected' : ''; ?>>In</option>
                <option value="Out" <?= $data['keterangan'] == 'Out' ? 'selected' : ''; ?>>Out</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="nama_file" class="form-label">Upload File PKK (PDF/JPG)</label>
            <input type="file" class="form-control" id="nama_file" name="nama_file" accept=".pdf,.jpg,.jpeg">
            <?php if (!empty($data['nama_file'])): ?>
                <small class="text-muted">File saat ini: <?= htmlspecialchars($data['nama_file']); ?></small>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

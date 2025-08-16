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
// Cek apakah ID Monitor tersedia
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "ID Monitor tidak ditemukan!";
    exit;
}

$id_monitor = $_GET['id'];

// Ambil data monitor berdasarkan ID
$sql = "SELECT * FROM data_monitor WHERE id_monitor = '$id_monitor'";
$result = mysqli_query($conn, $sql);
if (mysqli_num_rows($result) == 0) {
    echo "Data Monitor tidak ditemukan!";
    exit;
}

$data = mysqli_fetch_assoc($result);

// Proses update data jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $waktu = mysqli_real_escape_string($conn, $_POST['waktu']);
    $pelabuhan = mysqli_real_escape_string($conn, $_POST['pelabuhan']);
    $maksud_kedatangan = !empty($_POST['maksud_kedatangan']) ?  mysqli_real_escape_string($conn, $_POST['maksud_kedatangan']): null;
    $id_agen = !empty($_POST['id_agen']) ?  mysqli_real_escape_string($conn, $_POST['id_agen']): null;
    $MMSI = mysqli_real_escape_string($conn, $_POST['MMSI']);
    $latitude = !empty($_POST['latitude']) ?  mysqli_real_escape_string($conn, $_POST['latitude']): null;
    $longitude = !empty($_POST['longitude']) ?  mysqli_real_escape_string($conn, $_POST['longitude']): null;
    $pelabuhan_asal = mysqli_real_escape_string($conn, $_POST['pelabuhan_asal']);
    $pelabuhan_tujuan = mysqli_real_escape_string($conn, $_POST['pelabuhan_tujuan']);
    $draft_depan = !empty($_POST['draft_depan']) ?  mysqli_real_escape_string($conn, $_POST['draft_depan']): null;
    $draft_belakang = !empty($_POST['draft_belakang']) ?  mysqli_real_escape_string($conn, $_POST['draft_belakang']): null;
    $ETA = mysqli_real_escape_string($conn, $_POST['ETA']);
    $ATA = !empty($_POST['ATA']) ?  mysqli_real_escape_string($conn, $_POST['ATA']): null;
    $waktu_keberangkatan = !empty($_POST['waktu_keberangkatan']) ?  mysqli_real_escape_string($conn, $_POST['waktu_keberangkatan']): null;
    $jenis_muatan = !empty($_POST['jenis_muatan']) ?  mysqli_real_escape_string($conn, $_POST['jenis_muatan']): null;
    $jumlah_muatan = !empty($_POST['jumlah_muatan']) ?  mysqli_real_escape_string($conn, $_POST['jumlah_muatan']): null;
    $jumlah_kru = !empty($_POST['jumlah_kru']) ?  mysqli_real_escape_string($conn, $_POST['jumlah_kru']): null;
    $info_penting = !empty($_POST['info_penting']) ?  mysqli_real_escape_string($conn, $_POST['info_penting']): null;
    $nama_kapten = !empty($_POST['nama_kapten']) ?  mysqli_real_escape_string($conn, $_POST['nama_kapten']): null;
    $keterangan = mysqli_real_escape_string($conn, $_POST['keterangan']);
    // Update data monitor
    $update_sql = "UPDATE data_monitor 
                    SET waktu = ?, pelabuhan = ?, maksud_kedatangan = ?, id_agen = ?, MMSI = ?, latitude = ?, longitude = ?, pelabuhan_asal = ?, 
                        pelabuhan_tujuan = ?, draft_depan = ?, draft_belakang = ?, ETA = ?, ATA = ?, waktu_keberangkatan = ?,
                        jenis_muatan = ?,jumlah_muatan = ?, jumlah_kru = ?, info_penting = ?, nama_kapten = ?, keterangan = ?
                    WHERE id_monitor = ?";

    $stmt = mysqli_prepare($conn, $update_sql);
    mysqli_stmt_bind_param($stmt, "sssssssssddsssssisssi", 
        $waktu, $pelabuhan, $maksud_kedatangan, $id_agen, $MMSI, $latitude, $longitude, $pelabuhan_asal, $pelabuhan_tujuan, 
        $draft_depan, $draft_belakang, $ETA, $ATA, $waktu_keberangkatan, $jenis_muatan, $jumlah_muatan, 
        $jumlah_kru, $info_penting, $nama_kapten, $keterangan, $id_monitor);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: data_monitor.php?msg=success");
        exit;
    } else {
        $error = "Gagal memperbarui data Monitor!";
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
        <h1 class="text-center mb-4">Edit Data Monitor</h1>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="edit_monitor.php?id=<?= $id_monitor; ?>">
            <div class="mb-3">
                <label for="waktu" class="form-label">Waktu</label>
                <input type="datetime-local" class="form-control" id="waktu" name="waktu" 
                       value="<?= htmlspecialchars($data['waktu']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="pelabuhan" class="form-label">Pelabuhan</label>
                <input type="text" class="form-control" id="pelabuhan" name="pelabuhan" 
                       value="<?= htmlspecialchars($data['pelabuhan']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="maksud_kedatangan" class="form-label">Maksud Kedatangan</label>
                <input type="text" class="form-control" id="maksud_kedatangan" name="maksud_kedatangan" 
                       value="<?= htmlspecialchars($data['maksud_kedatangan']); ?>">
            </div>
            <div class="mb-3">
                <label for="id_agen" class="form-label">Id Agen</label>
                <input type="text" class="form-control" id="id_agen" name="id_agen" 
                       value="<?= htmlspecialchars($data['id_agen']); ?>">
            </div>
            <div class="mb-3">
                <label for="MMSI" class="form-label">MMSI</label>
                <input type="text" class="form-control" id="MMSI" name="MMSI" 
                       value="<?= htmlspecialchars($data['MMSI']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="latitude" class="form-label">Latitude</label>
                <input type="text" class="form-control" id="latitude" name="latitude" 
                       value="<?= htmlspecialchars($data['latitude']); ?>">
            </div>
            <div class="mb-3">
                <label for="longitude" class="form-label">Longitude</label>
                <input type="text" class="form-control" id="longitude" name="longitude" 
                       value="<?= htmlspecialchars($data['longitude']); ?>">
            </div>
            <div class="mb-3">
                <label for="pelabuhan_asal" class="form-label">Pelabuhan Asal</label>
                <input type="text" class="form-control" id="pelabuhan_asal" name="pelabuhan_asal" 
                       value="<?= htmlspecialchars($data['pelabuhan_asal']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="pelabuhan_tujuan" class="form-label">Pelabuhan Tujuan</label>
                <input type="text" class="form-control" id="pelabuhan_tujuan" name="pelabuhan_tujuan" 
                       value="<?= htmlspecialchars($data['pelabuhan_tujuan']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="draft_depan" class="form-label">Draft Depan</label>
                <input type="number" class="form-control" id="draft_depan" name="draft_depan" 
                       value="<?= htmlspecialchars($data['draft_depan']); ?>">
            </div>
            <div class="mb-3">
                <label for="draft_belakang" class="form-label">Draft Belakang</label>
                <input type="number" class="form-control" id="draft_belakang" name="draft_belakang" 
                       value="<?= htmlspecialchars($data['draft_belakang']); ?>">
            </div>
            <div class="mb-3">
                <label for="ETA" class="form-label">ETA</label>
                <input type="datetime-local" class="form-control" id="ETA" name="ETA" 
                       value="<?= htmlspecialchars($data['ETA']); ?>">
            </div>
            <div class="mb-3">
                <label for="ATA" class="form-label">ATA</label>
                <input type="datetime-local" class="form-control" id="ATA" name="ATA" 
                       value="<?= htmlspecialchars($data['ATA']); ?>">
            </div>
            <div class="mb-3">
                <label for="waktu_keberangkatan" class="form-label">Waktu Keberangkatan</label>
                <input type="datetime-local" class="form-control" id="waktu_keberangkatan" name="waktu_keberangkatan" 
                       value="<?= htmlspecialchars($data['waktu_keberangkatan']); ?>">
            </div>
            <div class="mb-3">
                <label for="jenis_muatan" class="form-label">Jenis Muatan</label>
                <input type="text" class="form-control" id="jenis_muatan" name="jenis_muatan" 
                       value="<?= htmlspecialchars($data['jenis_muatan']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="jumlah_muatan" class="form-label">Jumlah Muatan</label>
                <input type="text" class="form-control" id="jumlah_muatan" name="jumlah_muatan" 
                       value="<?= htmlspecialchars($data['jumlah_muatan']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="jumlah_kru" class="form-label">Jumlah Kru</label>
                <input type="number" class="form-control" id="jumlah_kru" name="jumlah_kru" 
                       value="<?= htmlspecialchars($data['jumlah_kru']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="info_penting" class="form-label">Informasi Penting</label>
                <input type="text" class="form-control" id="info_penting" name="info_penting" 
                       value="<?= htmlspecialchars($data['info_penting']); ?>">
            </div>
            <div class="mb-3">
                <label for="nama_kapten" class="form-label">Nama Kapten</label>
                <input type="text" class="form-control" id="nama_kapten" name="nama_kapten" 
                       value="<?= htmlspecialchars($data['nama_kapten']); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

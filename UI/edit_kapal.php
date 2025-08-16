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

// Cek apakah MMSI tersedia di parameter GET
if (!isset($_GET['MMSI']) || empty($_GET['MMSI'])) {
    echo "MMSI kapal tidak ditemukan!";
    exit;
}

$MMSI = trim($_GET['MMSI']); // Hapus spasi ekstra di awal/akhir

// Gunakan prepared statement untuk menghindari SQL injection
$stmt = $conn->prepare("SELECT * FROM data_kapal WHERE MMSI = ?");
$stmt->bind_param("s", $MMSI); // Gunakan tipe data string sesuai dengan tipe VARCHAR
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Data kapal tidak ditemukan! MMSI: " . htmlspecialchars($MMSI);
    exit;
}

$data = $result->fetch_assoc();

// Proses update data jika form disubmit
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama_kapal = mysqli_real_escape_string($conn, $_POST['nama_kapal']);
    $callsign = mysqli_real_escape_string($conn, $_POST['callsign']);
    $jenis_kapal = mysqli_real_escape_string($conn, $_POST['jenis_kapal']);
    $GT = mysqli_real_escape_string($conn, $_POST['GT']);
    $LOA = mysqli_real_escape_string($conn, $_POST['LOA']);
    $beam = mysqli_real_escape_string($conn, $_POST['beam']);
    $draft = mysqli_real_escape_string($conn, $_POST['draft']);
    $zona = !empty($_POST['zona']) ?  mysqli_real_escape_string($conn, $_POST['zona']): null;

    // Update data kapal
    $update_stmt = $conn->prepare("
        UPDATE data_kapal 
        SET nama_kapal = ?, callsign = ?, jenis_kapal = ?, GT = ?, LOA = ?, beam = ?, draft = ? , zona = ?
        WHERE MMSI = ?");
    $update_stmt->bind_param("sssddddss", $nama_kapal, $callsign, $jenis_kapal, $GT, $LOA, $beam, $draft, $zona, $MMSI);

    if ($update_stmt->execute()) {
        header("Location: data_kapal.php?msg=success");
        exit;
    } else {
        $error = "Gagal memperbarui data kapal! " . $conn->error;
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

<a href="data_kapal.php" class="btn btn-back">
    <i class="bi bi-arrow-left"></i>
</a>
<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4">Edit Data Kapal</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="MMSI" class="form-label">MMSI</label>
                <input type="text" class="form-control" id="MMSI" name="MMSI" 
                       value="<?= htmlspecialchars($data['MMSI']); ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="callsign" class="form-label">Callsign</label>
                <input type="text" class="form-control" id="callsign" name="callsign" 
                       value="<?= htmlspecialchars($data['callsign']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="nama_kapal" class="form-label">Nama Kapal</label>
                <input type="text" class="form-control" id="nama_kapal" name="nama_kapal" 
                       value="<?= htmlspecialchars($data['nama_kapal']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="jenis_kapal" class="form-label">Jenis Kapal</label>
                <select class="form-control" id="jenis_kapal" name="jenis_kapal">
                    <?php
                    $jenis_kapal_options = [
                        "TANKER", "LNG/LPG CARRIER", "CARGO VESSEL", "CONTAINER VESSEL",
                        "BULK CARRIER", "RORO", "PASSANGER VESSEL", "LIVESTOCK CARRIER",
                        "TUG/TOW", "GOVERNMENT VESSEL", "FISHING VESSEL", "OTHERS"
                    ];
                    foreach ($jenis_kapal_options as $option) {
                        $selected = ($data['jenis_kapal'] == $option) ? "selected" : "";
                        echo "<option value='$option' $selected>$option</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="GT" class="form-label">GT</label>
                <input type="number" class="form-control" id="GT" name="GT" 
                       value="<?= htmlspecialchars($data['GT']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="LOA" class="form-label">LOA</label>
                <input type="number" class="form-control" id="LOA" name="LOA" 
                       value="<?= htmlspecialchars($data['LOA']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="beam" class="form-label">Beam</label>
                <input type="number" class="form-control" id="beam" name="beam" 
                       value="<?= htmlspecialchars($data['beam']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="draft" class="form-label">Draft</label>
                <input type="number" class="form-control" id="draft" name="draft" 
                       value="<?= htmlspecialchars($data['draft']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="zona" class="form-label">Zona</label>
                <input type="text" class="form-control" id="zona" name="zona" 
                       value="<?= htmlspecialchars($data['zona']); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

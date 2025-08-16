<?php
session_start();
include 'koneksi.php';

// Cek login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
$username = $_SESSION['username'];

// Ambil ID petugas
$id_petugas = '';
$sql = "SELECT * FROM daftar_petugas WHERE username = '$username'";
$result_petugas = mysqli_query($conn, $sql);
if ($result_petugas && mysqli_num_rows($result_petugas) > 0) {
    $daftar_petugas = mysqli_fetch_assoc($result_petugas);
    $id_petugas = htmlspecialchars($daftar_petugas['id_petugas']);
}

// Path ke Python dan Script
$pythonPath = 'C:\\Users\\Bismillah\\AppData\\Local\\Programs\\Python\\Python310\\python.exe';
$scriptPaths = [
    'C:\\xampp\\htdocs\\update_vts_cirebon\\ai_vts_in.py',
    'C:\\xampp\\htdocs\\update_vts_cirebon\\ai_vts_out.py'
];
$pidFile = 'python_pid.txt';

$statusMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['stop'])) {
    if (file_exists($pidFile)) {
        $pid = trim(file_get_contents($pidFile));
        if (is_numeric($pid)) {
            shell_exec("taskkill /PID $pid /F");
            unlink($pidFile);
            $statusMessage = "🛑 Proses berhasil dihentikan.";
        } else {
            unlink($pidFile);
            $statusMessage = "⚠️ PID tidak valid. File dihapus.";
        }
    } else {
        $statusMessage = "ℹ️ Tidak ada proses Python yang sedang berjalan.";
    }
} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_petugas_escaped = escapeshellarg($_POST['id_petugas']);
    $mmsi_escaped = escapeshellarg($_POST['mmsi_input']);
    $latitude_escaped = escapeshellarg($_POST['latitude']);
    $longitude_escaped = escapeshellarg($_POST['longitude']);

    if (isset($_POST['start_in'])) {
        $scriptPath = 'C:\\xampp\\htdocs\\update_vts_cirebon\\ai_vts_in.py';
    } elseif (isset($_POST['start_out'])) {
        $scriptPath = 'C:\\xampp\\htdocs\\update_vts_cirebon\\ai_vts_out.py';
    } else {
        $statusMessage = "❌ Tidak ada aksi yang dipilih.";
        return;
    }

    $command = "$pythonPath $scriptPath $mmsi_escaped $id_petugas_escaped $latitude_escaped $longitude_escaped";

    $descriptorspec = [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"]
    ];

    $process = proc_open("start cmd /k \"$command & pause\"", $descriptorspec, $pipes);

    if (is_resource($process)) {
        sleep(1);
        $pid_command = 'wmic process where "CommandLine like \'%' . $_POST['mmsi_input'] . '%\' and Name=\'python.exe\'" get ProcessId /value';
        $pid_output = shell_exec($pid_command);
        preg_match('/ProcessId=(\d+)/', $pid_output, $matches);
        $pid = $matches[1] ?? '';

        if ($pid) {
            file_put_contents($pidFile, $pid);
            $statusMessage = "✅ Program Python dijalankan. PID: <strong>$pid</strong>";
        } else {
            $statusMessage = "❌ Gagal mendapatkan PID. Pastikan Python script berjalan.";
        }

        fclose($pipes[0]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        proc_close($process);
    } else {
        $statusMessage = "❌ Gagal menjalankan program Python.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelaporan Kapal - VTS Cirebon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #74ebd5 0%, #acb6e5 100%);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            overflow-x: hidden;
        }

        .container {
            margin-top: 90px;
            max-width: 750px;
            width: 90%;
        }

        .card {
            border-radius: 25px;
            background: white;
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            color: #333;
        }

        .card-header {
            font-size: 30px;
            font-weight: 600;
            text-align: center;
            color: #007bff;
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #444;
        }

        .form-control {
            border-radius: 12px;
            padding: 14px;
            font-size: 16px;
            border: 2px solid #ddd;
            transition: 0.3s;
        }

        .form-control:focus {
            border-color: #00c6ff;
            box-shadow: 0 0 0 0.2rem rgba(0,198,255,.25);
        }

        .btn-lg {
            padding: 14px 28px;
            font-size: 16px;
            border-radius: 50px;
            transition: all 0.3s ease;
        }

        .btn-lg:hover {
            transform: scale(1.05);
        }

        .chat-box {
            background-color: #f9f9f9;
            border-radius: 15px;
            padding: 20px;
            margin-top: 30px;
            height: 300px;
            overflow-y: auto;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            font-size: 15px;
            color: #333;
        }

        .chat-message {
            margin-bottom: 12px;
        }

        .chat-message .user {
            font-weight: bold;
            color: #007bff;
        }

        .chat-message .ai {
            font-style: italic;
            color: #555;
        }

        .status-box {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .btn-back {
            position: fixed;
            top: 20px;
            left: 20px;
            background-color: #343a40;
            color: #fff;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            font-size: 22px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 999;
            text-decoration: none;
        }

        .btn-back:hover {
            background-color: #495057;
        }
    </style>

</head>
<body>
<a href="data_monitor.php" class="btn btn-back">
    <i class="bi bi-arrow-left"></i>
</a>
<div class="container">
    <div class="card shadow-lg p-4 animate__animated animate__fadeInDown">
        <div class="card-header">
            🛳️ <strong>Sistem Pelaporan Kapal</strong>
        </div>
        <form action="" method="POST">
            <div class="mb-3">
                <label for="id_petugas" class="form-label">Id Petugas:</label>
                <input type="text" class="form-control" id="id_petugas" name="id_petugas" value="<?= $id_petugas; ?>" readonly>
            </div>
            <div class="mb-3">
                <label for="mmsi_input" class="form-label">Masukkan Id Agen:</label>
                <input type="text" class="form-control" id="mmsi_input" name="mmsi_input" required>
            </div>
            <div class="mb-3">
                <label for="latitude" class="form-label">Latitude:</label>
                <input type="text" class="form-control" id="latitude" name="latitude" required>
            </div>
            <div class="mb-3">
                <label for="longitude" class="form-label">Longitude:</label>
                <input type="text" class="form-control" id="longitude" name="longitude" required>
            </div>
           <div class="text-center d-grid gap-3">
                <button type="submit" class="btn btn-primary btn-lg" name="start_in">
                    🚢 Mulai Pelaporan Kedatangan
                </button>
                <button type="submit" class="btn btn-success btn-lg" name="start_out">
                    🛫 Mulai Pelaporan Keberangkatan
                </button>
                <button type="submit" class="btn btn-danger btn-lg" name="stop">
                    ❌ Hentikan Pelaporan
                </button>
            </div>
    </div>

    <div class="chat-box mt-4" id="chat-content">
        <div>Memuat percakapan...</div>
    </div>

    <script>
    setInterval(function() {
        fetch('chat_reader.php')
            .then(response => response.text())
            .then(data => {
                document.getElementById('chat-content').innerHTML = data;
            });
    }, 2000); // update setiap 2 detik
    </script>

</div>
</body>
</html>

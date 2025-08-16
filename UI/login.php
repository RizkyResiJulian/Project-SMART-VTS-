<?php
session_start();
include 'koneksi.php';
date_default_timezone_set('Asia/Jakarta');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    $sql = "SELECT * FROM user WHERE username = '$username'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        $now = date("Y-m-d H:i:s");

        // Cek apakah user sedang diblokir (waktu sekarang < blocked_until)
        if ($user['blocked_until'] && $now < $user['blocked_until']) {
            $error = "Akun Anda diblokir hingga " . date("H:i:s", strtotime($user['blocked_until'])) . ".";
        } elseif (password_verify($password, $user['password'])) {
            // Reset blokir dan login_attempts jika berhasil login
            $reset = "UPDATE user SET login_attempts = 0, blocked_until = NULL WHERE username = '$username'";
            mysqli_query($conn, $reset);

            $_SESSION['username'] = $username;

            // SHIFT logic
            $tanggal = date("Y-m-d");
            $jam = date("H");
            $shift = ($jam >= 6 && $jam < 14) ? 'pagi' : (($jam >= 14 && $jam < 19) ? 'siang' : 'malam');

            $cek_petugas = "SELECT * FROM daftar_petugas WHERE username = ?";
            $stmt_cek = $conn->prepare($cek_petugas);
            $stmt_cek->bind_param("s", $username);
            $stmt_cek->execute();
            $result_cek = $stmt_cek->get_result();

            if ($result_cek->num_rows > 0) {
                $update = "UPDATE daftar_petugas SET tanggal = ?, shift = ? WHERE username = ?";
                $stmt_update = $conn->prepare($update);
                $stmt_update->bind_param("sss", $tanggal, $shift, $username);
                $stmt_update->execute();
            } else {
                $insert = "INSERT INTO daftar_petugas (username, tanggal, shift) VALUES (?, ?, ?)";
                $stmt_insert = $conn->prepare($insert);
                $stmt_insert->bind_param("sss", $username, $tanggal, $shift);
                $stmt_insert->execute();
            }

            header("Location: dashboard.php");
            exit;
        } else {
            // Login gagal
            $login_attempts = $user['login_attempts'] + 1;

            if ($login_attempts >= 3) {
                $blocked_until = date("Y-m-d H:i:s", strtotime("+15 minutes"));
                $update = "UPDATE user SET login_attempts = $login_attempts, blocked_until = '$blocked_until' WHERE username = '$username'";
                $error = "Terlalu banyak percobaan gagal. Akun Anda diblokir selama 15 menit.";
            } else {
                $update = "UPDATE user SET login_attempts = $login_attempts WHERE username = '$username'";
                $error = "Password salah. Percobaan login ke-$login_attempts.";
            }

            mysqli_query($conn, $update);
        }
    } else {
        $error = "Username tidak ditemukan.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dashboard Pelabuhan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80') center/cover no-repeat fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Poppins', sans-serif;
            overflow: hidden;
            position: relative;
        }
        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.4); /* Gradient overlay */
            backdrop-filter: blur(5px);
            z-index: 0;
        }
        .login-container {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            padding: 40px 30px;
            border-radius: 20px;
            box-shadow: 0px 8px 32px rgba(0,0,0,0.4);
            max-width: 400px;
            width: 100%;
            text-align: center;
            animation: fadeInUp 0.8s ease;
        }
        @keyframes zoomIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        @keyframes fadeInUp {
            0% {
                transform: translateY(40px);
                opacity: 0;
            }
            100% {
                transform: translateY(0);
                opacity: 1;
            }
        }
        .login-title {
            font-size: 30px;
            font-weight: bold;
            color: #ffffff;
            margin-bottom: 25px;
        }

        .form-control {
            border-radius: 30px;
            background: rgba(255,255,255,0.7);
            border: none;
            padding-left: 45px;
            height: 45px;
        }

        .form-group {
            position: relative;
            margin-bottom: 20px;
        }

        .form-group i {
            position: absolute;
            left: 15px;
            top: 13px;
            color: #555;
        }

        .btn-login {
            border-radius: 30px;
            background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);
            border: none;
            width: 100%;
            color: white;
            font-weight: bold;
            padding: 12px;
            margin-top: 10px;
            transition: all 0.4s ease;
        }

        .btn-login:hover {
            background: linear-gradient(90deg, #2575fc 0%, #6a11cb 100%);
            transform: scale(1.05);
        }

        .error-message {
            color: #f8d7da;
            background-color: #721c24;
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .logo {
            width: 80px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <div class="login-container">
        <img src="https://cdn-icons-png.flaticon.com/512/3064/3064197.png" alt="Logo" class="logo">
        <h2 class="login-title">Portal Smart VTS</h2>

        <?php if (isset($error)) { ?>
            <div class="error-message"><?= htmlspecialchars($error); ?></div>
        <?php } ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <i class="bi bi-person-fill"></i>
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" required autofocus>
            </div>
            <div class="form-group">
                <i class="bi bi-lock-fill"></i>
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-login">Login</button>
        </form>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

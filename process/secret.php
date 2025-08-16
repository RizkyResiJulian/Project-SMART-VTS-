<?php
// Gunakan: http://localhost/sercret.php?username=namauser
include 'koneksi.php';

if (isset($_GET['username'])) {
    $username = $_GET['username'];
    $sql = "UPDATE user SET blocked_until = NULL, login_attempts = 0 WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    if ($stmt->execute()) {
        echo "Akun '$username' berhasil dibuka kembali.";
    } else {
        echo "Gagal membuka blokir akun.";
    }
} else {
    echo "Masukkan parameter ?username=...";
}
?>

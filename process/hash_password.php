<?php
include 'koneksi.php';

// Username dan password baru
$username = "admin";
$password = "admin123";

// Hash password
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Simpan ke database
$sql = "UPDATE user SET password = '$hashedPassword' WHERE username = '$username'";
if (mysqli_query($conn, $sql)) {
    echo "Password berhasil di-hash!";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>

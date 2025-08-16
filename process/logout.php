<?php
session_start(); // Memulai sesi

// Hapus semua variabel sesi
session_unset(); 

// Hancurkan sesi
session_destroy(); 

// Redirect ke halaman login
header("Location: login.php");
exit;
?>

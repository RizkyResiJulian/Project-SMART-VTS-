<?php
include 'koneksi.php';

// Ambil data dari form
$id_agen = $_POST['id_agen'];
$id_petugas = trim(string: $_POST['id_petugas'] ?? '');
$nama_agen = $_POST['nama_agen'];
$MMSI = $_POST['MMSI'];
$nama_kapten = $_POST['nama_kapten'];
$keterangan = $_POST['keterangan'];

// Untuk file upload
$nama_file = $_FILES['nama_file']['name'];
$ukuran_file = $_FILES['nama_file']['size'];
$tmp_file = $_FILES['nama_file']['tmp_name'];
$tipe_file = pathinfo($nama_file, PATHINFO_EXTENSION);

// Tentukan folder penyimpanan
$upload_dir = "uploads/"; // Pastikan folder ini ada dan writeable

// Validasi file (hanya pdf, jpg, jpeg)
$allowed_types = ['pdf', 'jpg', 'jpeg'];

if (!in_array(strtolower($tipe_file), $allowed_types)) {
    echo "Error: Hanya file PDF, JPG, atau JPEG yang diperbolehkan.";
    exit;
}

// (Opsional) Validasi ukuran file maksimum, misal 5MB
$max_size = 5 * 1024 * 1024; // 5MB
if ($ukuran_file > $max_size) {
    echo "Error: Ukuran file terlalu besar. Maksimal 5MB.";
    exit;
}

// Pindahkan file ke folder tujuan
$new_filename = uniqid('PKK_') . '.' . $tipe_file; // Rename supaya unik
$path_upload = $upload_dir . $new_filename;

if (move_uploaded_file($tmp_file, $path_upload)) {
    // Query untuk menyimpan data ke database
    $sql = "INSERT INTO data_agen (id_agen, id_petugas, nama_agen, MMSI, nama_kapten,keterangan, nama_file)
            VALUES ('$id_agen', '$id_petugas', '$nama_agen', '$MMSI', '$nama_kapten', '$keterangan', '$new_filename')";

    if (mysqli_query($conn, $sql)) {
        // Redirect ke halaman data_agen.php setelah data berhasil disimpan
        header("Location: data_agen.php");
        exit;
    } else {
        echo "Error saat menyimpan ke database: " . mysqli_error($conn);
    }
} else {
    echo "Error saat mengupload file.";
}

mysqli_close($conn);
?>

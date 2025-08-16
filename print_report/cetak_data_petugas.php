<?php
session_start();
require('fpdf/fpdf.php');
include 'koneksi.php';

// ====== Validasi Login ======
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// ====== Fungsi Tambahan ======

// Ubah nama hari ke Bahasa Indonesia
function getDayInIndonesian($day) {
    $days = [
        "Monday"    => "Senin",
        "Tuesday"   => "Selasa",
        "Wednesday" => "Rabu",
        "Thursday"  => "Kamis",
        "Friday"    => "Jumat",
        "Saturday"  => "Sabtu",
        "Sunday"    => "Minggu"
    ];
    return $days[$day] ?? $day;
}

// Tambahkan tanda tangan
function AddSignature($pdf) {
    $pdf->SetFont('Times', '', 12);
    $pdf->SetY(-85); // posisi Y bawah halaman
    $pdf->SetX(105); // posisi X tengah-kanan (A4)

    $pdf->Cell(0, 10, 'KEPALA STASIUN RADIO PANTAI / VTS CIREBON', 0, 1, 'L');
    $pdf->SetX(105);
    $pdf->Cell(0, 10, 'DISTRIK NAVIGASI TIPE B TANJUNG PRIOK', 0, 1, 'L');
    $pdf->Image('ttd2.png', 105, $pdf->GetY(), 40, 20); // posisi & ukuran
    $pdf->Ln(22);
    $pdf->SetX(105);
    $pdf->SetFont('Times', 'BU', 12);
    $pdf->Cell(0, 10, 'SUYADI', 0, 1, 'L');
    $pdf->SetFont('Times', '', 12);
    $pdf->SetX(105);
    $pdf->Cell(0, 10, 'NIP. 19750811 200812 1 002', 0, 1, 'L');
}

// ====== Tanggal dan Nama File ======
$today = date('Y-m-d');
$dayOfWeek = getDayInIndonesian(date('l', strtotime($today)));
$formattedDate = date('d F Y', strtotime($today));
$fileName = "Daily Absence Report ($formattedDate).pdf";

// ====== Kelas PDF ======
class PDF extends FPDF {
    function Header() {
        global $formattedDate;

        $this->SetFont('Times', 'B', 14);
        $this->Cell(0, 10, 'ABSENSI HARIAN OPERATOR VTS', 0, 1, 'C');
        $this->Cell(0, 10, 'VTS OPERATOR DAILY ABSENCE', 0, 1, 'C');
        $this->Cell(0, 10, 'STASIUN RADIO PANTAI / VTS CIREBON', 0, 1, 'C');
        $this->Ln(10);

        $this->SetFont('Times', 'B', 10);
        $this->SetX(20);
        $this->Cell(0, 8, 'Sektor: Cirebon, Patimban, Indramayu', 0, 1, 'L');

        $this->SetFont('Times', 'I', 10);
        $this->SetX(20);
        $this->Cell(0, 6, 'Sector', 0, 1, 'L');
        $this->Ln(8);
        // Menambahkan tanggal di bawah sektor dan operator
        $this->SetFont('Times', 'B', 10);
        $this->SetX(20); // Menyelaraskan posisi tanggal
        $this->Cell(0, 2, 'Tanggal: ' . $formattedDate, 0, 1, 'L'); // Tanggal di kiri
        $this->Ln(10); // Jarak setelah tanggal
    }

    function TableHeader() {
        $this->SetFont('Times', 'B', 12);
        $this->SetFillColor(200, 220, 255);

        $totalWidth = 180; // 90 + 90
        $startX = (210 - $totalWidth) / 2;
        $this->SetX($startX);

        $this->Cell(90, 10, 'Nama Operator VTS', 1, 0, 'C', true);
        $this->Cell(90, 10, 'Shift / Waktu Absen', 1, 1, 'C', true);
    }

    function TableBody($conn, $today) {
        $this->SetFont('Times', '', 12);

        $query = "SELECT nama, shift FROM daftar_petugas WHERE DATE(tanggal) = '$today'";
        $result = mysqli_query($conn, $query);

        $totalWidth = 180;
        $startX = (210 - $totalWidth) / 2;

        while ($row = mysqli_fetch_assoc($result)) {
            $this->SetX($startX);
            $this->Cell(90, 10, $row['nama'], 1, 0, 'C');
            $this->Cell(90, 10, $row['shift'], 1, 1, 'C');
        }
    }
}

// ====== Cetak PDF ======
$pdf = new PDF('P', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetY(80); // posisi awal tabel
$pdf->TableHeader();
$pdf->TableBody($conn, $today);
AddSignature($pdf);
$pdf->Output('', $fileName);
?>

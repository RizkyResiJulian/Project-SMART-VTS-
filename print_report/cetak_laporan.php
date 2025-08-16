<?php
session_start();
require('fpdf/fpdf.php');
include 'koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

// Fungsi untuk menambahkan tanda tangan
function AddSignature($pdf) {

    $pdf->SetFont('Times', '', 14);
    // Menetapkan posisi Y untuk tanda tangan di bawah tabel
    $pdf->SetY(-120); // 50mm dari bawah halaman

    // Menetapkan posisi X untuk tanda tangan di sebelah kanan
    $pdf->SetX(550); // Geser ke kanan

    // Menambahkan teks untuk tanda tangan
    $pdf->Cell(0, 10, 'KEPALA STASIUN RADIO PANTAI / VTS CIREBON', 0, 1, 'L');
    $pdf->Ln(-5); 
    $pdf->SetX(550); // Geser ke kanan
    $pdf->Cell(0, 10, 'DISTRIK NAVIGASI TIPE B TANJUNG PRIOK', 0, 1, 'L');
    $pdf->Image('ttd2.png', 530, $pdf->GetY(), 80, 40); // Sesuaikan posisi dan ukuran gambar
    $pdf->Ln(35); // Jarak sebelum tempat tanda tangan

    $pdf->SetX(550); // Geser ke kanan
    $pdf->SetFont('Times', 'BU', 14); // 'B' untuk bold, 'U' untuk underline
    $pdf->Cell(0, 10, 'SUYADI', 0, 1, 'L'); // Garis untuk tanda tangan
    $pdf->Ln(-5); 
    $pdf->SetX(550); // Geser ke kanan
    $pdf->SetFont('Times', '', 14); // 'B' untuk bold, 'U' untuk underline
    $pdf->Cell(0, 10, 'NIP. 19750811 200812 1 002', 0, 1, 'L'); // Nama pihak yang berwenang
}

// Fungsi untuk mengubah nama hari dari bahasa Inggris ke bahasa Indonesia
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

// Fungsi untuk menambahkan data petugas sebagai teks
function AddPetugasText($pdf, $conn, $today) {
    $shifts = [
        'Pagi' => 'Pagi',
        'Siang' => 'Siang',
        'Malam' => 'Malam'
    ];

    // Set font untuk teks header petugas
    $pdf->SetFont('Times', 'B', 14);
    
    // Set font untuk data petugas
    $pdf->SetFont('Times', '', 14);

    foreach ($shifts as $shift => $shiftLabel) {
        // Ambil nama petugas untuk shift tertentu
        $query = "SELECT nama FROM daftar_petugas WHERE shift = '$shift' AND DATE(tanggal) = '$today'";
        $result = mysqli_query($conn, $query);

        $names = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $names[] = $row['nama'];
        }

        // Format data petugas
        $petugasText = empty($names) ? "" : implode(",\n ", $names);

        // Mengatur posisi X untuk data petugas agar lebih ke kanan
        $pdf->SetX(450); // Geser posisi ke kanan (dimulai dari 150mm dari kiri)
        // Menampilkan data petugas
        $pdf->Cell(0, 10, "$shiftLabel: $petugasText", 0, 1, 'L');
    }
    $pdf->Ln(10); // Tambahkan jarak sebelum elemen berikutnya
}

// Ambil tanggal hari ini
$today = date('Y-m-d');
$dayOfWeek = getDayInIndonesian(date('l', strtotime($today)));
$formattedDate = date('d F Y', strtotime($today));

// Format nama file
$fileName = "Daily Report ($formattedDate).pdf";

// Buat Kelas PDF
class PDF extends FPDF
{
    function Header()
    {
        global $dayOfWeek, $formattedDate;

        // Set font untuk header
        $this->SetFont('Times', 'B', 18);
        $this->Cell(0, 10, 'FORM A2', 0, 1, 'C'); // Judul utama
        $this->Ln(-2);
        $this->Cell(0, 10, 'RINGKASAN LALU LINTAS KAPAL HARIAN', 0, 1, 'C'); // Judul utama
        $this->Ln(-2);
        $this->Cell(0, 10, 'DAILY VESSEL SUMMARY', 0, 1, 'C'); // Judul utama
        $this->Ln(-2);
        $this->Cell(0, 10, 'STASIUN RADIO PANTAI / VTS CIREBON', 0, 1, 'C'); // Judul utama
        $this->Ln(5);

        // Menambahkan tulisan "Sektor: Cirebon, Patimban, Indramayu"
        $this->SetFont('Times', 'B', 14);
        $this->SetX(80); // Menyelaraskan posisi sektor
        $this->Cell(0, 10, 'Sektor                      Cirebon, Patimban, Indramayu', 0, 0, 'L'); // Teks sektor di kiri
        $this->Ln(5); 
        $this->SetFont('Times', 'BI', 14);
        $this->SetX(80); // Menyelaraskan posisi sektor
        $this->Cell(0, 10, 'Sector', 0, 0, 'L');
        $this->Ln(2); 

        // Menambahkan tulisan "Operator VTS" sejajar dengan sektor
        $this->SetX(450); // Geser posisi untuk "Operator VTS" agar sejajar
        $this->Cell(0, 10, 'Operator VTS', 0, 1, 'L');  // Teks "Operator VTS" sejajar

        // Menambahkan tanggal di bawah sektor dan operator
        $this->SetFont('Times', 'B', 14);
        $this->SetX(80); // Menyelaraskan posisi tanggal
        $this->Cell(0, 10, 'Tanggal                   ' . $formattedDate, 0, 1, 'L'); // Tanggal di kiri
        $this->Ln(10); // Jarak setelah tanggal
    }

    function TableHeader()
    {
        $this->SetFont('Times', 'B', 12);
        $this->SetFillColor(200, 220, 255);
        $this->Cell(35, 12, 'Waktu (UTC)', 1, 0, 'C', true);
        $this->Cell(35, 12, 'PORT', 1, 0, 'C', true);
        $this->Cell(50, 12, 'Nama Kapal', 1, 0, 'C', true);
        $this->Cell(40, 12, 'Jenis Kapal', 1, 0, 'C', true);
        $this->Cell(30, 12, 'Callsign', 1, 0, 'C', true);
        $this->Cell(35, 12, 'MMSI', 1, 0, 'C', true);
        $this->Cell(35, 12, 'PelabuhanAsal', 1, 0, 'C', true);
        $this->Cell(35, 12, 'Pelabuhan Tujuan', 1, 0, 'C', true);
        $this->Cell(35, 12, 'ETA', 1, 0, 'C', true);
        $this->Cell(35, 12, 'ATD', 1, 0, 'C', true);
        $this->Cell(35, 12, 'ATA', 1, 0, 'C', true);
        $this->Cell(25, 12, 'GT', 1, 0, 'C', true);
        $this->Cell(25, 12, 'LOA', 1, 0, 'C', true);
        $this->Cell(25, 12, 'Beam', 1, 0, 'C', true);
        $this->Cell(25, 12, 'Draft', 1, 0, 'C', true);
        $this->Cell(45, 12, 'Jenis Muatan', 1, 0, 'C', true);
        $this->Cell(30, 12, 'Jumlah Muatan', 1, 0, 'C', true);
        $this->Cell(80, 12, 'Agen', 1, 0, 'C', true);
        $this->Cell(35, 12, 'Keterangan', 1, 1, 'C', true);
    }

    function TableBody($conn, $today)
    {
        $this->SetFont('Times', '', 11);
        // Ambil data monitor dan data kapal dari database
        $query = "
        SELECT 
            dm.id_monitor, dm.waktu, dm.pelabuhan, 
            dk.nama_kapal, dk.jenis_kapal, dk.callsign, dk.MMSI, 
            dm.pelabuhan_asal, dm.pelabuhan_tujuan, 
            dm.ETA, dm.waktu_keberangkatan, dm.ATA,  
            dk.GT, dk.LOA, dk.beam, dk.draft, 
            dm.jenis_muatan, dm.jumlah_muatan, 
            da.id_agen, da.nama_agen, 
            dm.keterangan
        FROM 
            data_monitor dm
        LEFT JOIN 
            data_kapal dk ON dm.MMSI = dk.MMSI
        LEFT JOIN
            data_agen da ON dm.id_agen = da.id_agen
        WHERE 
            DATE(dm.waktu) = '$today'
        ";
        $result = mysqli_query($conn, $query);

        while ($row = mysqli_fetch_assoc($result)) {
            $this->Cell(35, 10, $row['waktu'], 1, 0, 'C');
            $this->Cell(35, 10, $row['pelabuhan'], 1, 0, 'C');
            $this->Cell(50, 10, $row['nama_kapal'], 1, 0, 'C');
            $this->Cell(40, 10, $row['jenis_kapal'], 1, 0, 'C');
            $this->Cell(30, 10, $row['callsign'], 1, 0, 'C');
            $this->Cell(35, 10, $row['MMSI'], 1, 0, 'C');
            $this->Cell(35, 10, $row['pelabuhan_asal'], 1, 0, 'C');
            $this->Cell(35, 10, $row['pelabuhan_tujuan'], 1, 0, 'C');
            $this->Cell(35, 10, $row['ETA'], 1, 0, 'C');
            $this->Cell(35, 10, $row['waktu_keberangkatan'], 1, 0, 'C');
            $this->Cell(35, 10, $row['ATA'], 1, 0, 'C');
            $this->Cell(25, 10, $row['GT'], 1, 0, 'C');
            $this->Cell(25, 10, $row['LOA'], 1, 0, 'C');
            $this->Cell(25, 10, $row['beam'], 1, 0, 'C');
            $this->Cell(25, 10, $row['draft'], 1, 0, 'C');
            $this->Cell(45, 10, $row['jenis_muatan'], 1, 0, 'C');
            $this->Cell(30, 10, $row['jumlah_muatan'], 1, 0, 'C');
            $this->Cell(80, 10, $row['nama_agen'], 1, 0, 'C');
            $this->MultiCell(35, 10, $row['keterangan'], 1, 'C');
        }
    }
}

// Inisialisasi PDF
$pdf = new PDF('L', 'mm', array(710, 400));
$pdf->AddPage();

// Menambahkan header tabel dan data petugas
$pdf->SetY(60); // Mengatur posisi Y untuk menambahkan data petugas setelah bagian operator VTS
AddPetugasText($pdf, $conn, $today);

// Tambahkan header tabel
$pdf->TableHeader();

// Tambahkan isi tabel
$pdf->TableBody($conn, $today);

// Tambahkan tanda tangan setelah tabel
AddSignature($pdf);

// Output file dengan nama yang sudah diformat
$pdf->Output('', $fileName); // 'D' untuk mengunduh langsung
?>

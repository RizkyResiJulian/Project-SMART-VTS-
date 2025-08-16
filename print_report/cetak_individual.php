<?php
require('fpdf/fpdf.php');
include 'koneksi.php';

if (!isset($_GET['id'])) {
    die("ID tidak ditemukan.");
}

$id_monitor = $_GET['id'];
$query = "SELECT dm.*, dk.*, dp.nama AS nama_petugas, da.nama_agen 
          FROM data_monitor dm 
          LEFT JOIN data_kapal dk ON dm.MMSI = dk.MMSI 
          LEFT JOIN daftar_petugas dp ON dm.id_petugas = dp.id_petugas 
          LEFT JOIN data_agen da ON dm.id_agen = da.id_agen
          WHERE dm.id_monitor = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_monitor);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

if (!$data) {
    die("Data tidak ditemukan.");
}

if (empty($data['info_penting'])) {
    die("Tidak ada info penting yang dapat dicetak.");
}

// Konversi waktu ke format Indonesia
function formatTanggal($datetime) {
    $hari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    $bulan = ['Januari','Februari','Maret','April','Mei','Juni',
              'Juli','Agustus','September','Oktober','November','Desember'];

    $tanggal = date('d', strtotime($datetime));
    $bulanIndo = $bulan[(int)date('m', strtotime($datetime)) - 1];
    $tahun = date('Y', strtotime($datetime));
    $hariIndo = $hari[date('w', strtotime($datetime))];
    $jam = date('H:i', strtotime($datetime));

    return "$hariIndo, $tanggal $bulanIndo $tahun pukul $jam";
}

// Mulai membuat PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->Cell(190,10,'PEMBERITAHUAN KEJADIAN DARURAT KAPAL',0,1,'C');
$pdf->SetFont('Arial','',12);
$pdf->Ln(10);

// Isi surat
$pdf->MultiCell(0,8,
    "Dengan ini kami sampaikan bahwa telah terjadi suatu kondisi darurat yang melibatkan kapal dengan data sebagai berikut:\n", 0);

$pdf->Cell(60,8,'Nama Kapal',0,0); $pdf->Cell(0,8,': ' . $data['nama_kapal'],0,1);
$pdf->Cell(60,8,'MMSI',0,0); $pdf->Cell(0,8,': ' . $data['MMSI'],0,1);
$pdf->Cell(60,8,'Callsign',0,0); $pdf->Cell(0,8,': ' . $data['callsign'],0,1);
$pdf->Cell(60,8,'Jenis Kapal',0,0); $pdf->Cell(0,8,': ' . $data['jenis_kapal'],0,1);
$pdf->Cell(60,8,'GT / LOA / Beam',0,0); $pdf->Cell(0,8,': ' . "{$data['GT']} GT / {$data['LOA']} M / {$data['beam']} M",0,1);
$pdf->Cell(60,8,'Jenis / Jumlah Muatan',0,0); $pdf->Cell(0,8,': ' . "{$data['jenis_muatan']} / {$data['jumlah_muatan']}",0,1);
$pdf->Cell(60,8,'Nama Kapten',0,0); $pdf->Cell(0,8,': ' . ($data['nama_kapten'] ?: '-'),0,1);
$pdf->Cell(60,8,'Jumlah Kru Kapal',0,0); $pdf->Cell(0,8,': ' . "{$data['jumlah_kru']} Orang",0,1);
$pdf->Cell(60,8,'Agen',0,0); $pdf->Cell(0,8,': ' . ($data['nama_agen'] ?: '-'),0,1);
$pdf->Cell(60,8,'Pelabuhan Asal',0,0); $pdf->Cell(0,8,': ' . ($data['pelabuhan_asal'] ?: '-'),0,1);
$pdf->Cell(60,8,'Pelabuhan Tujuan',0,0); $pdf->Cell(0,8,': ' . ($data['pelabuhan_tujuan'] ?: '-'),0,1);
$pdf->Cell(60,8,'Petugas Jaga',0,0); $pdf->Cell(0,8,': ' . ($data['nama_petugas'] ?: '-'),0,1);
$pdf->Cell(60,8,'Koordinat',0,0); $pdf->Cell(0,8,': ' . $data['latitude'] . ', ' . $data['longitude'],0,1);
$pdf->Cell(60,8,'Waktu Kejadian',0,0); $pdf->Cell(0,8,': ' . formatTanggal($data['waktu']),0,1);

$pdf->Ln(10);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,'Info Kejadian:',0,1);
$pdf->SetFont('Arial','',12);
$pdf->MultiCell(0,8, $data['info_penting'], 0);

$pdf->Ln(15);
$pdf->MultiCell(0,8,
    "Demikian surat pemberitahuan ini kami sampaikan untuk menjadi perhatian dan tindak lanjut sebagaimana mestinya.\n\nHormat kami,\n\nManager/Kepala VTS Cirebon", 0);

$pdf->Output('I', 'Laporan_Darurat_Kapal.pdf');
?>

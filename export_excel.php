<?php
session_start();
require 'vendor/autoload.php'; 
include 'config/koneksi.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

if (!isset($_SESSION['id'])) { header("Location: login.php"); exit; }

// 1. DATA FILTER
$user_id   = isset($_GET['user_id']) ? $_GET['user_id'] : $_SESSION['id'];
$atasan_id = isset($_GET['atasan_id']) ? $_GET['atasan_id'] : '';
$bulan     = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun     = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// 2. QUERY DATA
$u = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'"));
$at = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_lengkap, nip FROM users WHERE id='$atasan_id'"));

$nama_bulan = [
    '01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei', '06'=>'Juni',
    '07'=>'Juli', '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'
];

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set Font Utama (Times New Roman sesuai file PDF)
$spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(11);

// --- JUDUL ATAS ---
$sheet->mergeCells('A1:D1');
$sheet->setCellValue('A1', 'LAPORAN CAPAIAN KINERJA BULANAN');
$sheet->mergeCells('A2:D2');
$sheet->setCellValue('A2', 'BULAN: ' . strtoupper($nama_bulan[$bulan]) . ' TAHUN ' . $tahun);
$sheet->getStyle('A1:A2')->getFont()->setBold(true);
$sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// --- IDENTITAS (Rata Kiri Sesuai PDF) ---
$sheet->setCellValue('A4', 'Nama');       $sheet->setCellValue('B4', ': ' . $u['nama_lengkap']);
$sheet->setCellValue('A5', 'Jabatan');    $sheet->setCellValue('B5', ': ' . $u['jabatan']);
$sheet->setCellValue('A6', 'Unit Kerja'); $sheet->setCellValue('B6', ': Kantor Kementerian Agama Kabupaten Simeulue');

// --- HEADER TABEL ---
$sheet->setCellValue('A8', 'No.');
$sheet->setCellValue('B8', 'URAIAN TUGAS/KEGIATAN');
$sheet->setCellValue('C8', 'VOL. KEGIATAN');
$sheet->setCellValue('D8', 'BUKTI DOKUMEN (URL)');

$styleHeader = [
    'font' => ['bold' => true],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER
    ],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
];
$sheet->getStyle('A8:D8')->applyFromArray($styleHeader);

// --- ISI DATA ---
$no = 1;
$row_idx = 9;
$query_lkh = mysqli_query($conn, "SELECT kegiatan, COUNT(*) as total_volume, MAX(link_bukti_dukung) as link_drive, MAX(lampiran) as file_fisik 
    FROM lkh WHERE user_id='$user_id' AND status='disetujui' AND MONTH(tanggal)='$bulan' AND YEAR(tanggal)='$tahun' 
    GROUP BY kegiatan ORDER BY kegiatan ASC");

if(mysqli_num_rows($query_lkh) > 0) {
    while($row = mysqli_fetch_array($query_lkh)) {
        // Logika Bukti Dokumen (URL)
        if(!empty($row['link_drive'])) {
            $bukti = $row['link_drive']; 
        } else {
            $bukti = "Laporan Kegiatan";
        }

        $sheet->setCellValue('A' . $row_idx, $no++);
        $sheet->setCellValue('B' . $row_idx, $row['kegiatan']);
        $sheet->setCellValue('C' . $row_idx, $row['total_volume']);
        $sheet->setCellValue('D' . $row_idx, $bukti);

        // Styling Sel
        $sheet->getStyle('A'.$row_idx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // No Tengah
        $sheet->getStyle('B'.$row_idx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);   // Uraian RATA KIRI
        $sheet->getStyle('C'.$row_idx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Vol Tengah
        $sheet->getStyle('D'.$row_idx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);   // Link RATA KIRI
        
        // Borders dan Wrap Text
        $sheet->getStyle('A'.$row_idx.':D'.$row_idx)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle('B'.$row_idx)->getAlignment()->setWrapText(true);
        
        $row_idx++;
    }
} else {
    $sheet->mergeCells("A$row_idx:D$row_idx");
    $sheet->setCellValue("A$row_idx", "Data tidak ditemukan atau belum disetujui");
    $sheet->getStyle("A$row_idx:D$row_idx")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    $sheet->getStyle("A$row_idx")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $row_idx++;
}

// --- TANDA TANGAN ---
$row_idx += 2;
$tgl_ttd = "Simeulue, " . date('t', strtotime("$tahun-$bulan-01")) . " " . $nama_bulan[$bulan] . " " . $tahun;

// Baris 1 TTD
$sheet->setCellValue('A' . $row_idx, 'Mengetahui,');
$sheet->setCellValue('D' . $row_idx, $tgl_ttd);
$row_idx++;

// Baris 2 TTD
$sheet->setCellValue('A' . $row_idx, 'Atasan Langsung,');
$sheet->setCellValue('D' . $row_idx, 'Pegawai yang bersangkutan,');
$row_idx += 4; // Spasi untuk tanda tangan tangan

// Baris Nama (Bold & Underline)
$nama_at = $at['nama_lengkap'] ?? 'Ansaruddin';
$sheet->setCellValue('A' . $row_idx, $nama_at);
$sheet->getStyle('A'.$row_idx)->getFont()->setBold(true)->setUnderline(true);

$sheet->setCellValue('D' . $row_idx, $u['nama_lengkap']);
$sheet->getStyle('D'.$row_idx)->getFont()->setBold(true)->setUnderline(true);
$row_idx++;

// Baris NIP
$sheet->setCellValue('A' . $row_idx, 'NIP. ' . ($at['nip'] ?? '198005222005011001'));
$sheet->setCellValue('D' . $row_idx, 'NIP. ' . $u['nip']);

// --- PENGATURAN KOLOM (Sangat Penting agar Identik) ---
$sheet->getColumnDimension('A')->setWidth(5);   // Kolom No
$sheet->getColumnDimension('B')->setWidth(70);  // Kolom Uraian (Diperlebar & Rata Kiri)
$sheet->getColumnDimension('C')->setWidth(20);   // Kolom VOL
$sheet->getColumnDimension('D')->setWidth(60);  // Kolom Bukti (Diperlebar agar link tidak potong)

// Ekspor ke Excel
$filename = "LKH_" . str_replace(' ', '_', $u['nama_lengkap']) . ".xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'. $filename .'"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
<?php
session_start();
include 'config/koneksi.php';
require 'vendor/autoload.php'; 

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Ambil parameter dari URL
$user_id   = $_GET['user_id'];
$tgl_awal  = $_GET['tgl_awal'];
$tgl_akhir = $_GET['tgl_akhir'];
$format    = $_GET['format'];

// Ambil bulan dan tahun dari tgl_awal untuk filter Excel agar identik
$bulan = date('m', strtotime($tgl_awal));
$tahun = date('Y', strtotime($tgl_awal));

// 1. Ambil data pegawai/user
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$u = mysqli_fetch_assoc($user_query);

// 2. Ambil data atasan
$id_atasan_terdaftar = $u['atasan_id'];
$atasan_query = mysqli_query($conn, "SELECT nama_lengkap, nip, jabatan FROM users WHERE id='$id_atasan_terdaftar'");
$a = mysqli_fetch_assoc($atasan_query);

$nama_bulan = [
    '01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei', '06'=>'Juni',
    '07'=>'Juli', '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'
];

// --- LOGIKA EXCEL (IDENTIK DENGAN export_excel.php ANDA) ---
if($format == 'excel') {
    if (ob_get_length()) ob_end_clean(); // Mencegah file corrupt

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('LKH Bulanan');

    // Set Font Utama (Times New Roman sesuai file PDF)
    $spreadsheet->getDefaultStyle()->getFont()->setName('Times New Roman')->setSize(11);

    // --- JUDUL ATAS ---
    $sheet->mergeCells('A1:D1');
    $sheet->setCellValue('A1', 'LAPORAN CAPAIAN KINERJA BULANAN');
    $sheet->mergeCells('A2:D2');
    $sheet->setCellValue('A2', 'BULAN: ' . strtoupper($nama_bulan[$bulan]) . ' TAHUN ' . $tahun);
    $sheet->getStyle('A1:A2')->getFont()->setBold(true);
    $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // --- IDENTITAS ---
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

    // --- ISI DATA (Logika Identik dengan export_excel.php) ---
    $no = 1;
    $row_idx = 9;
    $query_lkh = mysqli_query($conn, "SELECT kegiatan, COUNT(*) as total_volume, MAX(link_bukti_dukung) as link_drive, MAX(lampiran) as file_fisik 
        FROM lkh WHERE user_id='$user_id' AND status='disetujui' AND MONTH(tanggal)='$bulan' AND YEAR(tanggal)='$tahun' 
        GROUP BY kegiatan ORDER BY kegiatan ASC");

    if(mysqli_num_rows($query_lkh) > 0) {
        while($row = mysqli_fetch_array($query_lkh)) {
            if(!empty($row['link_drive'])) {
                $bukti = $row['link_drive']; 
            } else {
                $bukti = "Laporan Kegiatan";
            }

            $sheet->setCellValue('A' . $row_idx, $no++);
            $sheet->setCellValue('B' . $row_idx, $row['kegiatan']);
            $sheet->setCellValue('C' . $row_idx, $row['total_volume']);
            $sheet->setCellValue('D' . $row_idx, $bukti);

            $sheet->getStyle('A'.$row_idx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B'.$row_idx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle('C'.$row_idx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D'.$row_idx)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            
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

    $sheet->setCellValue('A' . $row_idx, 'Mengetahui,');
    $sheet->setCellValue('D' . $row_idx, $tgl_ttd);
    $row_idx++;

    $sheet->setCellValue('A' . $row_idx, ($a['jabatan'] ?? 'Atasan Langsung') . ',');
    $sheet->setCellValue('D' . $row_idx, 'Pegawai yang bersangkutan,');
    $row_idx += 4; 

    $sheet->setCellValue('A' . $row_idx, $a['nama_lengkap'] ?? 'Ansaruddin');
    $sheet->getStyle('A'.$row_idx)->getFont()->setBold(true)->setUnderline(true);

    $sheet->setCellValue('D' . $row_idx, $u['nama_lengkap']);
    $sheet->getStyle('D'.$row_idx)->getFont()->setBold(true)->setUnderline(true);
    $row_idx++;

    $sheet->setCellValue('A' . $row_idx, 'NIP. ' . ($a['nip'] ?? '198005222005011001'));
    $sheet->setCellValue('D' . $row_idx, 'NIP. ' . $u['nip']);

    // --- PENGATURAN KOLOM ---
    $sheet->getColumnDimension('A')->setWidth(5);
    $sheet->getColumnDimension('B')->setWidth(70);
    $sheet->getColumnDimension('C')->setWidth(20);
    $sheet->getColumnDimension('D')->setWidth(60);

    $filename = "LKH_" . str_replace(' ', '_', $u['nama_lengkap']) . ".xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="'. $filename .'"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

// --- LOGIKA PDF (KODE ASLI ANDA) ---
$query = mysqli_query($conn, "SELECT 
            UPPER(TRIM(kegiatan)) as kegiatan_key, 
            MAX(kegiatan) as kegiatan_tampil, 
            COUNT(*) as total_volume, 
            MAX(link_bukti_dukung) as link_bukti_dukung, 
            MAX(lampiran) as lampiran 
         FROM lkh 
         WHERE user_id='$user_id' 
         AND tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir' 
         AND status='disetujui' 
         GROUP BY UPPER(TRIM(kegiatan)) 
         ORDER BY kegiatan_tampil ASC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Kinerja Bulanan - KMA 83</title>
    <style>
        body { font-family: "Times New Roman", serif; font-size: 11pt; line-height: 1.3; padding: 0.5cm; color: #000; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .title-section { text-align: center; margin-bottom: 20px; }
        .identitas-table { margin-bottom: 15px; width: 100%; border: none; }
        .identitas-table td { padding: 2px; border: none; vertical-align: top; }
        table.data-table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.data-table th, table.data-table td { border: 1px solid black; padding: 6px 8px; vertical-align: top; word-wrap: break-word; }
        table.data-table th { background: #f2f2f2; text-align: center; }
        .ttd-wrapper { width: 100%; margin-top: 35px; }
        .ttd-box { width: 45%; float: left; text-align: left; }
        .ttd-box-right { width: 45%; float: right; text-align: left; }
        .spacer { height: 70px; }
        .link-url { word-break: break-all; font-size: 9pt; color: blue; text-decoration: underline; }
        @media print { .no-print { display: none; } @page { size: portrait; margin: 1.5cm; } }
    </style>
</head>
<body onload="window.print()">

    <div class="title-section">
        <span class="fw-bold">LAPORAN CAPAIAN KINERJA BULANAN</span><br>
        BULAN: <span class="fw-bold"><?php echo strtoupper($nama_bulan[$bulan]); ?></span> TAHUN <span class="fw-bold"><?php echo $tahun; ?></span>
    </div>

    <table class="identitas-table">
        <tr><td width="15%">Nama</td><td width="2%">:</td><td><?php echo $u['nama_lengkap']; ?></td></tr>
        <tr><td>Jabatan</td><td>:</td><td><?php echo $u['jabatan']; ?></td></tr>
        <tr><td>Unit Kerja</td><td>:</td><td>Kantor Kementerian Agama Kabupaten Simeulue</td></tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="45%">URAIAN TUGAS/KEGIATAN</th>
                <th width="20%">VOL. KEGIATAN</th>
                <th width="40%">BUKTI DOKUMEN (URL)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no=1; 
            if(mysqli_num_rows($query) > 0) {
                while($d = mysqli_fetch_array($query)): 
            ?>
            <tr>
                <td class="text-center"><?php echo $no++; ?></td>
                <td><?php echo $d['kegiatan_tampil']; ?></td>
                <td class="text-center"><?php echo $d['total_volume']; ?></td>
                <td>
                    <?php 
                    if(!empty($d['link_bukti_dukung'])) {
                        $url = $d['link_bukti_dukung'];
                        if (!preg_match("~^(?:f|ht)tps?://~i", $url)) { $url = "https://" . $url; }
                        echo "<a href='".$url."' target='_blank' class='link-url'>".$url."</a>";
                    } elseif(!empty($d['lampiran'])) {
                        $file_path = "https://lkh.kemenagsimeulue.cloud/uploads/" . $d['lampiran'];
                        echo "<a href='".$file_path."' target='_blank' class='link-url'>".$file_path."</a>";
                    } else {
                        echo "<small style='color:gray;'>Laporan Kegiatan</small>";
                    }
                    ?>
                </td>
            </tr>
            <?php endwhile; } else { echo "<tr><td colspan='4' class='text-center'>Data tidak ditemukan.</td></tr>"; } ?>
        </tbody>
    </table>

    <div class="ttd-wrapper">
        <div class="ttd-box">
            Mengetahui,<br>
            <?php echo $a['jabatan'] ?? 'Atasan Langsung'; ?>,
            <div class="spacer" style="height: auto; min-height: 85px; margin: 5px 0;">
                <?php if ($a): 
                    $isi_barcode = "VALIDASI DIGITAL KEMENAG SIMEULUE\nJabatan: ".$a['jabatan']."\nNama: ".$a['nama_lengkap']."\nNIP: ".$a['nip']."\nVerifikasi Sah secara Sistem";
                    $qrcode_url = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($isi_barcode);
                ?>
                    <img src="<?php echo $qrcode_url; ?>" style="width: 80px; height: 80px; border: 1px solid #eee; padding: 2px;">
                <?php else: ?><div style="height: 80px;"></div><?php endif; ?>
            </div>
            <strong><u><?php echo $a['nama_lengkap'] ?? '(Atasan belum di-mapping)'; ?></u></strong><br>
            NIP. <?php echo $a['nip'] ?? '..........................'; ?>
        </div>
        
        <div class="ttd-box-right">
            Simeulue, <?php echo date('d', strtotime($tgl_akhir)); ?> <?php echo $nama_bulan[$bulan]; ?> <?php echo $tahun; ?><br>
            Pegawai yang bersangkutan,
            <div class="spacer" style="height: auto; min-height: 85px; margin: 5px 0;">
                <?php
                $isi_barcode_user = "VALIDASI DIGITAL KEMENAG SIMEULUE\nJabatan: ".$u['jabatan']."\nNama: ".$u['nama_lengkap']."\nNIP: ".$u['nip']."\nLaporan Capaian Kinerja Bulanan";
                $qrcode_url_user = "https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=" . urlencode($isi_barcode_user);
                ?>
                <img src="<?php echo $qrcode_url_user; ?>" style="width: 80px; height: 80px; border: 1px solid #eee; padding: 2px;">
            </div>
            <strong><u><?php echo $u['nama_lengkap']; ?></u></strong><br>
            NIP. <?php echo $u['nip']; ?>
        </div>
        <div style="clear: both;"></div>
    </div>
</body>
</html>
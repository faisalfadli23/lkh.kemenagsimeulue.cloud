<?php
session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['id'])) { header("Location: login.php"); exit; }

$user_id = $_SESSION['id'];
$nama = $_SESSION['nama'];

// Mengambil data lengkap user (NIP, Jabatan, Unit Kerja)
$user_query = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$u = mysqli_fetch_assoc($user_query);

// Filter Bulan & Tahun (Jika dikirim dari dashboard)
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

$nama_bulan = [
    '01'=>'Januari', '02'=>'Februari', '03'=>'Maret', '04'=>'April', '05'=>'Mei', '06'=>'Juni',
    '07'=>'Juli', '08'=>'Agustus', '09'=>'September', '10'=>'Oktober', '11'=>'November', '12'=>'Desember'
];

/**
 * LOGIKA BARCODE ATASAN:
 * Mengambil data atasan dari tabel lkh pada kolom diverifikasi_oleh 
 * untuk menentukan barcode mana yang muncul.
 */
$cek_atasan = mysqli_query($conn, "SELECT diverifikasi_oleh FROM lkh 
                                   WHERE user_id='$user_id' AND status='disetujui' 
                                   AND MONTH(tanggal)='$bulan' AND YEAR(tanggal)='$tahun' LIMIT 1");
$c = mysqli_fetch_assoc($cek_atasan);
$id_atasan = $c['diverifikasi_oleh'] ?? 0;

// Ambil detail data atasan dari tabel users
$data_atasan = mysqli_query($conn, "SELECT nama_lengkap, nip, jabatan FROM users WHERE id='$id_atasan'");
$a = mysqli_fetch_assoc($data_atasan);

$nama_atasan = $a['nama_lengkap'] ?? "(Nama Atasan Langsung)";
$nip_atasan = $a['nip'] ?? "........................................";
$jabatan_atasan = $a['jabatan'] ?? "Atasan Langsung";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Capaian Kinerja Bulanan - <?php echo $nama; ?></title>
    <style>
        body { font-family: "Times New Roman", serif; font-size: 11pt; line-height: 1.3; padding: 0.5cm; color: #000; }
        .text-center { text-align: center; }
        .uppercase { text-transform: uppercase; }
        .fw-bold { font-weight: bold; }
        .title-section { text-align: center; margin-bottom: 25px; }
        .identitas-table { margin-bottom: 15px; border: none; width: 100%; }
        .identitas-table td { padding: 2px; border: none; vertical-align: top; }
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data-table th, table.data-table td { border: 1px solid black; padding: 6px 8px; vertical-align: top; }
        table.data-table th { background-color: #f2f2f2; text-align: center; }
        .ttd-wrapper { width: 100%; margin-top: 30px; }
        .ttd-box { width: 45%; float: left; text-align: left; }
        .ttd-box-right { width: 45%; float: right; text-align: left; }
        .spacer { height: 70px; }
        @media print { .no-print { display: none; } @page { size: portrait; margin: 1.5cm; } }
    </style>
</head>
<body onload="window.print()">

    <div class="title-section">
        <span class="fw-bold text-center">LAPORAN CAPAIAN KINERJA BULANAN</span><br>
        BULAN: <span class="uppercase fw-bold"><?php echo $nama_bulan[$bulan]; ?></span> TAHUN <span class="fw-bold"><?php echo $tahun; ?></span>
    </div>

    <table class="identitas-table">
        <tr>
            <td width="15%">Nama</td>
            <td width="2%">:</td>
            <td><?php echo $u['nama_lengkap']; ?></td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <td><?php echo $u['jabatan']; ?></td>
        </tr>
        <tr>
            <td>Unit Kerja</td>
            <td>:</td>
            <td>Kantor Kementerian Agama Kabupaten Simeulue</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="50%">URAIAN TUGAS/KEGIATAN</th>
                <th width="15%">VOLUME</th>
                <th width="30%">BUKTI DOKUMEN</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $no = 1;
            $query_lkh = mysqli_query($conn, "SELECT 
                                     kegiatan, 
                                     COUNT(*) as total_volume, 
                                     MAX(link_bukti_dukung) as link_drive,
                                     MAX(lampiran) as file_fisik
                                     FROM lkh 
                                     WHERE user_id='$user_id' 
                                     AND status='disetujui' 
                                     AND MONTH(tanggal)='$bulan' 
                                     AND YEAR(tanggal)='$tahun' 
                                     GROUP BY kegiatan 
                                     ORDER BY kegiatan ASC");
            
            if(mysqli_num_rows($query_lkh) > 0) {
                while($row = mysqli_fetch_array($query_lkh)) {
            ?>
            <tr>
                <td class="text-center"><?php echo $no++; ?></td>
                <td><?php echo $row['kegiatan']; ?></td>
                <td class="text-center"><?php echo $row['total_volume']; ?></td>
                <td>
                    <?php 
                        if(!empty($row['link_drive'])) {
                            echo "Link Drive/Dokumentasi Digital"; 
                        } elseif(!empty($row['file_fisik'])) {
                            echo "Dokumen Fisik (Terlampir)";
                        } else {
                            echo "Laporan/Berkas/Dokumen";
                        }
                    ?>
                </td>
            </tr>
            <?php 
                }
            } else {
                echo "<tr><td colspan='4' class='text-center'>Data tidak ditemukan atau belum disetujui.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="ttd-wrapper">
        <div class="ttd-box">
            Mengetahui,<br>
            <?php echo $jabatan_atasan; ?>,
            
            <div class="spacer" style="height: auto; min-height: 80px; margin: 10px 0;">
                <?php if ($id_atasan > 0): ?>
                    <?php
                    // Barcode tetap untuk masing-masing atasan
                    $isi_barcode = "VERIFIKASI DIGITAL KEMENAG SIMEULUE\n" .
                                   "Nama Atasan: " . $nama_atasan . "\n" .
                                   "NIP: " . $nip_atasan . "\n" .
                                   "Status: Dokumen Sah Terverifikasi";
                    
                    $qrcode_url = "https://chart.googleapis.com/chart?chs=100x100&cht=qr&chl=" . urlencode($isi_barcode) . "&choe=UTF-8";
                    ?>
                    <img src="<?php echo $qrcode_url; ?>" alt="QR Code" style="width: 80px; border: 1px solid #eee; padding: 2px;">
                <?php else: ?>
                    <div style="height: 70px;"></div>
                <?php endif; ?>
            </div>
            
            <strong><u><?php echo $nama_atasan; ?></u></strong><br>
            NIP. <?php echo $nip_atasan; ?>
        </div>
        
        <div class="ttd-box-right">
            Simeulue, <?php echo date('t', strtotime("$tahun-$bulan-01")); ?> <?php echo $nama_bulan[$bulan]; ?> <?php echo $tahun; ?><br>
            Pegawai yang bersangkutan,
            <div class="spacer"></div>
            <strong><u><?php echo $u['nama_lengkap']; ?></u></strong><br>
            NIP. <?php echo $u['nip']; ?>
        </div>
        <div style="clear: both;"></div>
    </div>

</body>
</html>
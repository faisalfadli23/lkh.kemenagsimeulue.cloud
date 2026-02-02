<?php
session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_POST['import'])) {
    $user_id = $_SESSION['id'];
    $file = $_FILES['file_lkh']['tmp_name'];

    $zip = new ZipArchive;
    if ($zip->open($file) === TRUE) {
        // 1. Ambil Shared Strings
        $sharedStrings = [];
        $ssData = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssData) {
            $ssXml = simplexml_load_string($ssData);
            foreach ($ssXml->si as $si) {
                $sharedStrings[] = (string) ($si->t ?: $si->r->t);
            }
        }

        // 2. Ambil Sheet 1
        $sheetData = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetData) {
            $sheetXml = simplexml_load_string($sheetData);
            $berhasil = 0;
            $tanggal_default = date('Y-m-d');

            foreach ($sheetXml->sheetData->row as $row) {
                $cells = [];
                foreach ($row->c as $c) {
                    $v = (string) $c->v;
                    $type = (string) $c['t'];
                    $cells[] = ($type == 's' && isset($sharedStrings[$v])) ? $sharedStrings[$v] : $v;
                }

                // --- LOGIKA FILTER KETAT ---
                
                // A. Ambil kolom No (Index 0) dan Uraian (Index 1)
                $no_urut  = isset($cells[0]) ? trim($cells[0]) : '';
                $kegiatan = isset($cells[1]) ? mysqli_real_escape_string($conn, trim($cells[1])) : '';

                // B. HANYA PROSES JIKA:
                // 1. Kolom No adalah ANGKA (1, 2, 3, dst)
                // 2. Kolom Kegiatan TIDAK KOSONG
                if (is_numeric($no_urut) && !empty($kegiatan)) {
                    
                    $hasil_kegiatan = isset($cells[2]) ? mysqli_real_escape_string($conn, trim($cells[2])) : '';
                    $link_bukti     = isset($cells[3]) ? mysqli_real_escape_string($conn, trim($cells[3])) : '';
                    
                    if ($link_bukti == '-') $link_bukti = '';

                    // --- TAMBAHAN LOGIKA VOLUME ---
                    // Mengambil nilai angka dari kolom hasil_kegiatan untuk menentukan jumlah insert
                    // Jika kolom hasil berisi teks (bukan angka), maka dianggap volume = 1
                    $volume = is_numeric($hasil_kegiatan) ? (int)$hasil_kegiatan : 1;

                    // Melakukan insert sebanyak jumlah volume yang ditentukan
                    for ($i = 0; $i < $volume; $i++) {
                        $query = "INSERT INTO lkh (user_id, tanggal, kegiatan, hasil_kegiatan, link_bukti_dukung, status) 
                                  VALUES ('$user_id', '$tanggal_default', '$kegiatan', '$hasil_kegiatan', '$link_bukti', 'proses')";
                        
                        mysqli_query($conn, $query);
                    }
                    
                    // Berhasil dihitung per baris Excel yang diproses
                    $berhasil++;
                }
                // --- END FILTER ---
            }
            $zip->close();

            echo "<script>alert('Sukses! $berhasil data tabel kegiatan berhasil diimport.'); window.location.href='dashboard.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('Gagal membuka file!'); window.history.back();</script>";
    }
}
?>
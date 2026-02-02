<?php
session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$id_user    = $_SESSION['id']; 
$nama_user  = $_SESSION['nama'];
$nama_satker= $_SESSION['nama_satker'];
$tgl        = $_POST['tanggal'];
$isi        = mysqli_real_escape_string($conn, $_POST['kegiatan']);
$hasil_kegiatan = mysqli_real_escape_string($conn, $_POST['hasil_kegiatan']);
$bukti      = mysqli_real_escape_string($conn, $_POST['link_bukti']);

$nama_file_db = ""; 
if (isset($_FILES['lampiran']) && $_FILES['lampiran']['name'] != "") {
    $ekstensi = strtolower(pathinfo($_FILES['lampiran']['name'], PATHINFO_EXTENSION));
    
    // Folder berdasarkan Nama Satker
    $folder_satker = str_replace(' ', '_', preg_replace('/[^A-Za-z0-9 ]/', '', $nama_satker));
    $target_dir = 'uploads/' . $folder_satker . '/';
    
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // Nama file berdasarkan Nama User
    $nama_user_clean = str_replace(' ', '_', preg_replace('/[^A-Za-z0-9 ]/', '', $nama_user));
    $nama_file_baru = $nama_user_clean . "_" . date('Ymd_His') . "_" . rand(100, 999) . "." . $ekstensi;

    if (in_array($ekstensi, ['png', 'jpg', 'jpeg', 'pdf'])) {
        if (move_uploaded_file($_FILES['lampiran']['tmp_name'], $target_dir . $nama_file_baru)) {
            $nama_file_db = $folder_satker . "/" . $nama_file_baru;
        }
    }
}

$sql = "INSERT INTO lkh (user_id, tanggal, kegiatan, hasil_kegiatan, link_bukti_dukung, lampiran, status) 
        VALUES ('$id_user', '$tgl', '$isi', '$hasil_kegiatan', '$bukti', '$nama_file_db', 'proses')";

if (mysqli_query($conn, $sql)) {
    echo "<script>
            alert('Laporan Berhasil Terkirim!');
            window.location='dashboard.php';
          </script>";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
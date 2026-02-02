<?php
session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['id'];
$nama_user = $_SESSION['nama'];
$nama_satker = $_SESSION['nama_satker'];
$tanggal = $_POST['tanggal'];
$kegiatan = mysqli_real_escape_string($conn, $_POST['kegiatan']);
$hasil_kegiatan = mysqli_real_escape_string($conn, $_POST['hasil_kegiatan']);
$link_bukti_dukung = mysqli_real_escape_string($conn, $_POST['link_bukti_dukung']);

$nama_file_db = "";
if (isset($_FILES['lampiran']) && $_FILES['lampiran']['name'] != "") {
    $ekstensi_diperbolehkan = array('png', 'jpg', 'pdf', 'jpeg');
    $x = explode('.', $_FILES['lampiran']['name']);
    $ekstensi = strtolower(end($x));
    $ukuran = $_FILES['lampiran']['size'];
    $file_tmp = $_FILES['lampiran']['tmp_name'];
    
    // 1. Bersihkan Nama Satker untuk nama folder (spasi jadi underscore)
    $folder_satker = str_replace(' ', '_', preg_replace('/[^A-Za-z0-9 ]/', '', $nama_satker));
    $target_dir = 'uploads/' . $folder_satker . '/';

    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0755, true);
    }

    // 2. Bersihkan Nama User untuk nama file
    $nama_user_clean = str_replace(' ', '_', preg_replace('/[^A-Za-z0-9 ]/', '', $nama_user));
    $nama_file_baru = $nama_user_clean . "_" . date('Ymd_His') . "_" . rand(100, 999) . "." . $ekstensi;

    if (in_array($ekstensi, $ekstensi_diperbolehkan) === true) {
        if ($ukuran < 2044070) {
            if (move_uploaded_file($file_tmp, $target_dir . $nama_file_baru)) {
                $nama_file_db = $folder_satker . "/" . $nama_file_baru;
            }
        } else {
            echo "<script>alert('Gagal! Ukuran file terlalu besar (Maks 2MB)'); window.history.back();</script>";
            exit;
        }
    } else {
        echo "<script>alert('Gagal! Ekstensi file tidak didukung'); window.history.back();</script>";
        exit;
    }
}

$query = "INSERT INTO lkh (user_id, tanggal, kegiatan, hasil_kegiatan, link_bukti_dukung, lampiran, status) 
          VALUES ('$user_id', '$tanggal', '$kegiatan', '$hasil_kegiatan', '$link_bukti_dukung', '$nama_file_db', 'proses')";

if (mysqli_query($conn, $query)) {
    header("Location: dashboard.php?pesan=berhasil");
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
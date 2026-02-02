<?php
session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['id']) || $_SESSION['level'] == 'pegawai') {
    exit("Akses ditolak");
}

$id = $_GET['id'];

// Ubah status kembali ke proses
$update = mysqli_query($conn, "UPDATE lkh SET status = 'proses' WHERE id = '$id'");

if ($update) {
    echo "<script>
        window.location.href='riwayat_persetujuan.php';
    </script>";
} else {
    echo "Gagal membatalkan: " . mysqli_error($conn);
}
?>
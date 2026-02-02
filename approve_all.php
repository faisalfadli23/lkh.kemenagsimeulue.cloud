<?php
session_start();
include 'config/koneksi.php';

// Proteksi: Hanya boleh diakses oleh Admin/Atasan
if (!isset($_SESSION['id']) || $_SESSION['level'] == 'pegawai') {
    header("Location: dashboard.php");
    exit;
}

// Ambil bulan dan tahun saat ini agar tidak salah menyetujui laporan lama
$bulan_ini = date('m');
$tahun_ini = date('Y');

// Query: Ubah semua status 'proses' menjadi 'disetujui' untuk bulan ini
$query = "UPDATE lkh 
          SET status = 'disetujui' 
          WHERE status = 'proses' 
          AND MONTH(tanggal) = '$bulan_ini' 
          AND YEAR(tanggal) = '$tahun_ini'";

if (mysqli_query($conn, $query)) {
    // Berikan pesan sukses lewat URL
    header("Location: dashboard_admin.php?pesan=approve_semua_berhasil");
} else {
    echo "Gagal melakukan verifikasi massal: " . mysqli_error($conn);
}
?>
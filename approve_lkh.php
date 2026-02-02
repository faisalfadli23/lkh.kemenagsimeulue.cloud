<?php
session_start();
include 'config/koneksi.php';

// Pastikan yang mengakses adalah Admin atau Atasan
if (!isset($_SESSION['id']) || $_SESSION['level'] == 'pegawai') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = $_GET['id'];
    $status = $_GET['status']; // 'disetujui' atau 'ditolak'

    // Update status di database
    $query = "UPDATE lkh SET status = '$status' WHERE id = '$id'";

    if (mysqli_query($conn, $query)) {
        // Kembali ke dashboard admin dengan pesan sukses
        header("Location: dashboard_admin.php?pesan=update_berhasil");
    } else {
        echo "Gagal memperbarui status: " . mysqli_error($conn);
    }
} else {
    header("Location: dashboard_admin.php");
}
?>
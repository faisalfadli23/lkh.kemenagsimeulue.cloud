<?php
session_start();
include 'config/koneksi.php';

// Pastikan user sudah login
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);
$user_id = $_SESSION['id'];

/** * Melakukan penghapusan data secara permanen dari tabel lkh di database
 * Hanya menghapus jika ID cocok, milik user yang login, dan statusnya 'proses' atau 'ditolak'
 */
$query = "DELETE FROM lkh WHERE id = '$id' AND user_id = '$user_id' AND (status = 'proses' OR status = 'ditolak')";

if (mysqli_query($conn, $query)) {
    // Jika query berhasil dieksekusi, data di database terhapus permanen
    header("Location: dashboard.php?pesan=hapus_berhasil");
    exit;
} else {
    // Jika gagal karena kesalahan koneksi atau database
    echo "Gagal menghapus: " . mysqli_error($conn);
}
?>
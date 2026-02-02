<?php
session_start();
include 'config/koneksi.php';

$user = mysqli_real_escape_string($conn, trim($_POST['username']));
$pass = mysqli_real_escape_string($conn, trim($_POST['password']));

// Melakukan JOIN untuk mendapatkan nama_satker dari tabel satuan_kerja
$sql = "SELECT u.*, s.nama_satker 
        FROM users u 
        LEFT JOIN satuan_kerja s ON u.satker_id = s.id 
        WHERE TRIM(u.username)='$user' AND TRIM(u.password)='$pass'";

$query = mysqli_query($conn, $sql);
$cek = mysqli_num_rows($query);

if ($cek > 0) {
    $data = mysqli_fetch_assoc($query);
    
    $_SESSION['id']         = $data['id'];
    $_SESSION['nama']       = $data['nama_lengkap'];
    $_SESSION['level']      = $data['level'];
    $_SESSION['satker_id']  = $data['satker_id'];
    $_SESSION['nama_satker']= $data['nama_satker']; // Menyimpan nama satker untuk folder
    
    if ($data['level'] == 'admin' || $data['level'] == 'atasan') {
        header("Location: dashboard_admin.php");
    } else {
        header("Location: dashboard.php");
    }
} else {
    header("Location: login.php?pesan=gagal");
}
?>
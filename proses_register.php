<?php
include 'config/koneksi.php';

$nip          = mysqli_real_escape_string($conn, $_POST['nip']);
$nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
$username     = mysqli_real_escape_string($conn, trim($_POST['username']));
$password     = mysqli_real_escape_string($conn, trim($_POST['password']));
$jabatan      = mysqli_real_escape_string($conn, $_POST['jabatan']);
$satker_id    = mysqli_real_escape_string($conn, $_POST['satker_id']);

// Cek apakah username sudah ada
$cek_user = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
if (mysqli_num_rows($cek_user) > 0) {
    echo "<script>alert('Username sudah digunakan, cari yang lain!'); window.location.href='register.php';</script>";
} else {
    // LOGIKA PENYESUAIAN ATASAN:
    // Mencari user dengan level 'atasan' pada satker yang dipilih
    $query_atasan = mysqli_query($conn, "SELECT id FROM users WHERE satker_id='$satker_id' AND level='atasan' LIMIT 1");
    $data_atasan  = mysqli_fetch_assoc($query_atasan);
    
    // Jika atasan di satker tersebut ada, gunakan ID-nya. 
    // Jika tidak ada (NULL), otomatis ke Nashrullah (ID 1) sebagai pucuk pimpinan.
    $atasan_id = ($data_atasan) ? $data_atasan['id'] : 1;

    // Masukkan ke database (level default: pegawai)
    $query = "INSERT INTO users (nip, nama_lengkap, username, password, jabatan, level, satker_id, atasan_id) 
              VALUES ('$nip', '$nama_lengkap', '$username', '$password', '$jabatan', 'pegawai', '$satker_id', '$atasan_id')";
    
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Pendaftaran Berhasil! Silakan Login'); window.location.href='login.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>
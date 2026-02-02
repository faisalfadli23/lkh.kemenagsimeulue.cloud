<?php
include 'config/koneksi.php';

// Menghindari SQL Injection
$username = mysqli_real_escape_string($conn, $_POST['username']);
$nip      = mysqli_real_escape_string($conn, $_POST['nip']);
$password = mysqli_real_escape_string($conn, $_POST['password_baru']);

// 1. Validasi apakah Username dan NIP cocok di database
$query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND nip='$nip'");
$cek   = mysqli_num_rows($query);

if($cek > 0) {
    // 2. Update password tanpa enkripsi (sesuai dengan sistem login Anda yang plain text)
    $update = mysqli_query($conn, "UPDATE users SET password='$password' WHERE username='$username'");
    
    if($update) {
        header("location: lupa_password.php?pesan=success");
    } else {
        echo "Error: " . mysqli_error($conn);
    }
} else {
    // 3. Jika data tidak ditemukan
    header("location: lupa_password.php?pesan=notfound");
}
?>
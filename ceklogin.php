<?php
session_start();
include 'config.php';

$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = mysqli_real_escape_string($conn, $_POST['password']);

// 1. Cek di Tabel ADMIN
$sql_admin = "SELECT * FROM admin WHERE username='$username' AND password='$password'";
$login_admin = mysqli_query($conn, $sql_admin);
$cek_admin = mysqli_num_rows($login_admin);

if($cek_admin > 0){
    $data = mysqli_fetch_assoc($login_admin);
    // Buat session Admin
    $_SESSION['username'] = $username;
    $_SESSION['nama_lengkap'] = $data['nama_lengkap'];
    $_SESSION['role'] = "admin";
    $_SESSION['login'] = "aktif";
    header("location: manajemen_simas.php"); // Arahkan ke dashboard Admin
    exit;
}

// 2. Cek di Tabel PEMINJAM (jika tidak ditemukan sebagai Admin)
$sql_peminjam = "SELECT * FROM peminjam WHERE username='$username' AND password='$password'";
$login_peminjam = mysqli_query($conn, $sql_peminjam);
$cek_peminjam = mysqli_num_rows($login_peminjam);

if($cek_peminjam > 0){
    $data = mysqli_fetch_assoc($login_peminjam);
    // Buat session User
    $_SESSION['id_peminjam'] = $data['id_peminjam'];
    $_SESSION['username'] = $username;
    $_SESSION['nama_lengkap'] = $data['nama_lengkap'];
    $_SESSION['role'] = "user";
    $_SESSION['login'] = "aktif";
    header("location: dashboard_user.php"); // Arahkan ke dashboard User
    exit;
}

// 3. Login Gagal (Modul 5: Error Handling)
header("location: login.php?pesan=gagal");

mysqli_close($conn);
?>
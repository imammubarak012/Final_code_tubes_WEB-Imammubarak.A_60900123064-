<?php
session_start();
include 'config.php';

// Ambil data dari form
$username = mysqli_real_escape_string($conn, $_POST['username']);
$password = mysqli_real_escape_string($conn, $_POST['password']);

// ================================
// 1. CARI DI TABEL ADMIN
// ================================
$sql_admin = "SELECT * FROM admin WHERE username='$username' AND password='$password'";
$login_admin = mysqli_query($conn, $sql_admin);
$cek_admin = mysqli_num_rows($login_admin);

if($cek_admin > 0){
    $data = mysqli_fetch_assoc($login_admin);

    // SET SESSION ADMIN
    $_SESSION['username'] = $data['username'];
    $_SESSION['nama_lengkap'] = $data['nama_lengkap'];
    $_SESSION['role'] = "admin";
    $_SESSION['login'] = "aktif";

    // ✔ SET COOKIE REMEMBER USERNAME 7 HARI
    setcookie(
        "ingat_username",
        $data['username'],
        time() + (7 * 24 * 60 * 60), // 7 hari
        "/"
    );

    header("location: manajemen_simas.php");
    exit;
}

// ================================
// 2. CARI DI TABEL PEMINJAM
// ================================
$sql_peminjam = "SELECT * FROM peminjam WHERE username='$username' AND password='$password'";
$login_peminjam = mysqli_query($conn, $sql_peminjam);
$cek_peminjam = mysqli_num_rows($login_peminjam);

if($cek_peminjam > 0){
    $data = mysqli_fetch_assoc($login_peminjam);

    // SET SESSION USER
    $_SESSION['id_peminjam'] = $data['id_peminjam'];
    $_SESSION['username'] = $data['username'];
    $_SESSION['nama_lengkap'] = $data['nama_lengkap'];
    $_SESSION['role'] = "user";
    $_SESSION['login'] = "aktif";

    // ✔ SET COOKIE REMEMBER USERNAME 7 HARI
    setcookie(
        "ingat_username",
        $data['username'],
        time() + (7 * 24 * 60 * 60), // 7 hari
        "/"
    );

    header("location: dashboard_user.php");
    exit;
}

// ================================
// 3. LOGIN GAGAL
// ================================
header("location: login.php?pesan=gagal");
exit;

mysqli_close($conn);
?>

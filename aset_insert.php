<?php
// File aset_insert.php: Proses CREATE Aset (Modul 7)
session_start();
include 'config.php';

if(!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("location: login.php");
    exit;
}

if(isset($_POST['simpan'])){
    $nama_aset = mysqli_real_escape_string($conn, $_POST['nama_aset']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $stok      = (int)$_POST['stok'];
    $stok_tersedia = $stok; 

    // Query SQL INSERT INTO (Modul 7: Menambah Data)
    $sql = "INSERT INTO aset (nama_aset, deskripsi, stok, stok_tersedia) 
            VALUES ('$nama_aset', '$deskripsi', $stok, $stok_tersedia)";
    
    $query_insert = mysqli_query($conn, $sql);

    // Error Handling (Modul 7)
    if($query_insert) {
        echo "<script>alert('Data aset berhasil disimpan')</script>";
        echo "<meta http-equiv='refresh' content='0; url=manajemen_simas.php'>";
    } else {
        echo "<script>alert('Tidak dapat menyimpan data aset. Error: " . mysqli_error($conn) . "')</script>";
        echo "<meta http-equiv='refresh' content='0; url=manajemen_simas.php'>";
    }
} else {
    header("location: manajemen_simas.php");
}

mysqli_close($conn);
?>
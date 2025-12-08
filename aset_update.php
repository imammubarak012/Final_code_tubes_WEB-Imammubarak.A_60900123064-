<?php
// File aset_update.php: Proses UPDATE Aset (Modul 8)
session_start();
include 'config.php';

if(!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("location: login.php");
    exit;
}

if(isset($_POST['update'])){
    $id_aset = $_POST['id_aset'];
    $nama_aset = mysqli_real_escape_string($conn, $_POST['nama_aset']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $stok      = (int)$_POST['stok'];
    $stok_tersedia = (int)$_POST['stok_tersedia'];

    // 1. Validasi: Stok tersedia tidak boleh lebih besar dari stok total
    if ($stok_tersedia > $stok) {
        echo "<script>alert('Gagal! Stok tersedia ($stok_tersedia) tidak boleh melebihi Stok Total ($stok).')</script>";
        echo "<meta http-equiv='refresh' content='0; url=manajemen_simas.php'>";
        mysqli_close($conn);
        exit;
    }

    // 2. Query SQL UPDATE (Modul 8: Mengubah Data)
    $sql = "UPDATE aset SET 
            nama_aset = '$nama_aset', 
            deskripsi = '$deskripsi', 
            stok = $stok, 
            stok_tersedia = $stok_tersedia
            WHERE id_aset = $id_aset";
    
    $query_update = mysqli_query($conn, $sql);

    // 3. Error Handling (Modul 8)
    if($query_update) {
        echo "<script>alert('Data aset berhasil diperbarui')</script>";
        echo "<meta http-equiv='refresh' content='0; url=manajemen_simas.php'>";
    } else {
        echo "<script>alert('Tidak dapat memperbarui data aset. Error: " . mysqli_error($conn) . "')</script>";
        echo "<meta http-equiv='refresh' content='0; url=manajemen_simas.php'>";
    }
} else {
    header("location: manajemen_simas.php");
}

mysqli_close($conn);
?>
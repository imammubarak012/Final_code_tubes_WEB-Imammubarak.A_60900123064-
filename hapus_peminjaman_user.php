<?php
session_start();
include 'config.php';

if(!isset($_SESSION['login']) || $_SESSION['role'] !== 'user'){
    header("location: login.php");
    exit;
}

$id_peminjaman = (int)$_GET['id'];

// Pastikan hanya peminjam terkait yang bisa menghapus
$nama_user = $_SESSION['nama_lengkap'];

$q = mysqli_query($conn, 
    "SELECT * FROM peminjaman 
     WHERE id_peminjaman=$id_peminjaman 
     AND nama_peminjam='$nama_user'");

if(mysqli_num_rows($q) == 0){
    echo "<script>alert('Data tidak ditemukan atau bukan milik Anda.')</script>";
    echo "<meta http-equiv=\"refresh\" content=\"0; url=dashboard_user.php\">";
    exit;
}

$data = mysqli_fetch_assoc($q);
$status = $data['status'];

// Hanya bisa menghapus Diajukan / Ditolak
if($status != 'Diajukan' && $status != 'Ditolak'){
    echo "<script>alert('Hanya pengajuan Diajukan / Ditolak yang boleh dihapus.')</script>";
    echo "<meta http-equiv=\"refresh\" content=\"0; url=dashboard_user.php\">";
    exit;
}

// Jika status Diajukan → kembalikan stok
if($status == 'Diajukan'){
    $id_aset = $data['id_aset'];
    $jumlah = $data['jumlah_pinjam'];
    mysqli_query($conn, "UPDATE aset SET stok_tersedia = stok_tersedia + $jumlah WHERE id_aset=$id_aset");
}

// Hapus data
mysqli_query($conn, "DELETE FROM peminjaman WHERE id_peminjaman=$id_peminjaman");

echo "<script>alert('Pengajuan peminjaman berhasil dihapus.')</script>";
echo "<meta http-equiv=\"refresh\" content=\"0; url=dashboard_user.php\">";

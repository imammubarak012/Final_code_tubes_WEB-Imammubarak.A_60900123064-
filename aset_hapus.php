<?php
// File aset_hapus.php: Proses DELETE Aset (Modul 8)
session_start();
include 'config.php';

if(!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("location: login.php");
    exit;
}

if(isset($_GET['id'])){
    $id_aset = $_GET['id'];

    // Cek apakah aset sedang dipinjam (untuk mencegah penghapusan data yang berelasi)
    $cek_pinjam = mysqli_query($conn, "SELECT COUNT(*) FROM peminjaman WHERE id_aset='$id_aset' AND status IN ('Dipinjam', 'Disetujui')");
    $count = mysqli_fetch_row($cek_pinjam)[0];

    if ($count > 0) {
        echo "<script>alert('Gagal menghapus aset! Aset ini masih terkait dengan transaksi pinjaman yang aktif.')</script>";
        echo "<meta http-equiv='refresh' content='0; url=manajemen_simas.php'>";
    } else {
        // Query SQL DELETE (Modul 8: Menghapus Data)
        $sql = "DELETE FROM aset WHERE id_aset='$id_aset'";
        $query_delete = mysqli_query($conn, $sql);

        // Error Handling (Modul 8)
        if($query_delete) {
            echo "<script>alert('Data aset berhasil dihapus')</script>";
            echo "<meta http-equiv='refresh' content='0; url=manajemen_simas.php'>";
        } else {
            echo "<script>alert('Tidak dapat menghapus data aset. Error: " . mysqli_error($conn) . "')</script>";
            echo "<meta http-equiv='refresh' content='0; url=manajemen_simas.php'>";
        }
    }
} else {
    header("location: manajemen_simas.php");
}

mysqli_close($conn);
?>
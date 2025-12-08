<?php 
// Memulai session dan memanggil koneksi (Modul 5 & 6)
session_start();
include 'config.php'; 

// Cek apakah pengguna sudah login (Modul 5: Session untuk mengamankan halaman)
if(!isset($_SESSION['login'])) {
    // Jika belum login, kembalikan ke halaman login
    header("location: login.php");
    exit;
}

$nama_admin = $_SESSION['nama_lengkap'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SIMAS</title>
    <style>
        /* Gaya tambahan (Anda bisa kembangkan sendiri) */
        body { font-family: Arial, sans-serif; }
        .header { background-color: #333; color: white; padding: 15px; display: flex; justify-content: space-between; align-items: center; }
        .header a { color: white; text-decoration: none; margin-left: 20px; }
        .container { padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #007bff; color: white; }
        .tambah-btn { background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 4px; text-decoration: none; display: inline-block; margin-bottom: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>SIMAS - Sistem Peminjaman Aset</h1>
        <div>
            Selamat Datang, **<?php echo htmlspecialchars($nama_admin); ?>**
            <a href="logout.php">Keluar</a> 
        </div>
    </div>

    <div class="container">
        <h2>Daftar Aset (CRUD Read)</h2>
        
        <a href="aset_tambah.php" class="tambah-btn">Tambah Aset Baru</a>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Aset</th>
                    <th>Deskripsi</th>
                    <th>Stok Total</th>
                    <th>Stok Tersedia</th>
                    <th>Aksi (Edit/Hapus)</th>
                </tr>
            </thead>
            <tbody>
            <?php
            // Query untuk menampilkan data aset (Modul 6: Mengirim Query SELECT)
            $query_aset = mysqli_query($conn, "SELECT * FROM aset ORDER BY id_aset DESC");
            $no = 1;

            // Error Handling (Modul 6: Mengecek Query)
            if (!$query_aset) {
                echo "<tr><td colspan='6'>Kesalahan saat mengambil data: " . mysqli_error($conn) . "</td></tr>";
            } elseif (mysqli_num_rows($query_aset) == 0) {
                 echo "<tr><td colspan='6'>Tidak ada data aset.</td></tr>";
            } else {
                // Loop untuk menampilkan data (Modul 6: mysqli_fetch_array)
                while($data_aset = mysqli_fetch_array($query_aset)){
            ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td><?php echo htmlspecialchars($data_aset['nama_aset']); ?></td>
                    <td><?php echo htmlspecialchars($data_aset['deskripsi']); ?></td>
                    <td><?php echo $data_aset['stok']; ?></td>
                    <td><?php echo $data_aset['stok_tersedia']; ?></td>
                    <td>
                        <a href="aset_edit.php?id=<?php echo $data_aset['id_aset']; ?>">Edit</a> | 
                        <a href="aset_hapus.php?id=<?php echo $data_aset['id_aset']; ?>" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                    </td>
                </tr>
            <?php 
                }
            }
            ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php
// Tutup koneksi setelah selesai (Modul 6)
mysqli_close($conn);
?>
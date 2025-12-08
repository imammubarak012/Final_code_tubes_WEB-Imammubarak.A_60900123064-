<?php
// Konfigurasi Database (Modul 6: Koneksi PHP dan MySQL)
$dbhost = "localhost";
$dbuser = "root"; // Sesuaikan dengan username MySQL Anda
$dbpass = "";     // Sesuaikan dengan password MySQL Anda (biasanya kosong jika menggunakan XAMPP/Localhost)
$dbname = "db_simas";

// Membuat Koneksi
$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);

// Error Handling (Modul 6: Menangani Koneksi Gagal)
if (mysqli_connect_errno()) {
    echo "Koneksi database gagal: " . mysqli_connect_error();
    exit(); // Hentikan eksekusi skrip jika koneksi gagal
}
?>
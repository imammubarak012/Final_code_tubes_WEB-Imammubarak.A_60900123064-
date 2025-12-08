<?php 
// File logout.php: Menghapus Session (Modul 5)
session_start();

// Menghapus semua variabel session
$_SESSION = array();

// Menghancurkan session
session_destroy();

// Redirect ke halaman login
header("location: login.php");
exit;
?>
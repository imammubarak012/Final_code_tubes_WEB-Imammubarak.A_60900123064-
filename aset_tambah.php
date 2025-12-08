<?php 
// File: aset_tambah.php
// Deskripsi: Form Tambah Aset dengan Fitur Upload Gambar

session_start();
include 'config.php'; 

// 1. Cek Login
if(!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("location: login.php");
    exit;
}

// 2. Logika Simpan Data (Diproses di halaman yang sama)
if(isset($_POST['simpan'])){
    $nama_aset = mysqli_real_escape_string($conn, $_POST['nama_aset']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $stok      = (int) $_POST['stok'];
    
    // LOGIKA UPLOAD GAMBAR
    $nama_gambar_baru = null; // Default null jika tidak ada gambar

    if(!empty($_FILES['gambar_aset']['name'])){
        $nama_file = $_FILES['gambar_aset']['name'];
        $ukuran_file = $_FILES['gambar_aset']['size'];
        $tmp_file = $_FILES['gambar_aset']['tmp_name'];
        $error = $_FILES['gambar_aset']['error'];

        // Cek ekstensi file
        $ext_valid = ['jpg', 'jpeg', 'png', 'gif'];
        $ext_file = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));

        if(!in_array($ext_file, $ext_valid)){
            echo "<script>alert('Format gambar tidak valid! Gunakan JPG, JPEG, atau PNG.');</script>";
        } else {
            // Generate nama unik agar tidak bentrok
            $nama_gambar_baru = uniqid() . '.' . $ext_file;
            $tujuan_upload = "uploads/" . $nama_gambar_baru;

            // Pindahkan file
            move_uploaded_file($tmp_file, $tujuan_upload);
        }
    }

    // Query Insert Data (stok_tersedia awal = stok total)
    $query = "INSERT INTO aset (nama_aset, deskripsi, stok, stok_tersedia, gambar) 
              VALUES ('$nama_aset', '$deskripsi', '$stok', '$stok', '$nama_gambar_baru')";

    if(mysqli_query($conn, $query)){
        echo "<script>
                alert('Aset berhasil ditambahkan!');
                window.location.href = 'manajemen_simas.php';
              </script>";
    } else {
        echo "<script>alert('Gagal menyimpan data: " . mysqli_error($conn) . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Aset - SIMAS</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* CSS INTERNAL UNTUK FORM YANG LEBIH BAGUS */
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; padding-top: 50px; }
        
        .card {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 600px;
        }

        h2 { margin-top: 0; color: #333; border-bottom: 2px solid #3f51b5; padding-bottom: 10px; margin-bottom: 20px; }

        .form-group { margin-bottom: 20px; }
        
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; }
        
        input[type="text"], 
        input[type="number"], 
        textarea, 
        input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 1rem;
            box-sizing: border-box; /* Agar padding tidak merusak width */
            transition: 0.3s;
        }

        input:focus, textarea:focus { border-color: #3f51b5; outline: none; }

        .preview-box {
            margin-top: 10px;
            padding: 10px;
            background: #f9f9f9;
            border: 1px dashed #ccc;
            text-align: center;
            border-radius: 6px;
        }

        .btn-group { display: flex; gap: 10px; margin-top: 30px; }
        
        .btn { flex: 1; padding: 12px; border: none; border-radius: 6px; font-size: 1rem; font-weight: bold; cursor: pointer; transition: 0.3s; }
        
        .btn-primary { background: #3f51b5; color: white; }
        .btn-primary:hover { background: #303f9f; }
        
        .btn-secondary { background: #e0e0e0; color: #333; }
        .btn-secondary:hover { background: #ccc; }

    </style>
</head>
<body>

    <div class="card">
        <h2>➕ Tambah Aset Baru</h2>
        
        <form action="" method="POST" enctype="multipart/form-data">
            
            <div class="form-group">
                <label for="nama_aset">Nama Barang</label>
                <input type="text" id="nama_aset" name="nama_aset" placeholder="Contoh: Laptop ASUS ROG" required>
            </div>
        
            <div class="form-group">
                <label for="deskripsi">Deskripsi & Spesifikasi</label>
                <textarea id="deskripsi" name="deskripsi" rows="4" placeholder="Jelaskan kondisi dan spesifikasi barang..." required></textarea>
            </div>
        
            <div class="form-group">
                <label for="stok">Jumlah Stok Awal</label>
                <input type="number" id="stok" name="stok" placeholder="0" required min="1">
            </div>

            <div class="form-group">
                <label for="gambar_aset">Foto Barang (Opsional)</label>
                <input type="file" id="gambar_aset" name="gambar_aset" accept="image/*" onchange="previewImage(event)">
                <small style="color: #888;">Format: JPG, PNG. Maksimal 2MB.</small>
                
                <div class="preview-box" id="preview-container" style="display: none;">
                    <img id="preview-img" src="" alt="Preview" style="max-height: 150px; border-radius: 4px;">
                </div>
            </div>
            
            <div class="btn-group">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='manajemen_simas.php';">Batal</button>
                <button type="submit" name="simpan" class="btn btn-primary">Simpan Data</button>
            </div>
        </form>
    </div>

    <script>
        // Script sederhana untuk preview gambar sebelum upload
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function(){
                var output = document.getElementById('preview-img');
                output.src = reader.result;
                document.getElementById('preview-container').style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>

</body>
</html>
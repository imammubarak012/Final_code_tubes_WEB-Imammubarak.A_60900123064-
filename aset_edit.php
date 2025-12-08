<?php 
// File: aset_edit.php
// Deskripsi: Form Edit Aset dengan Fitur Update Gambar

session_start();
include 'config.php'; 

// 1. Cek Login
if(!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("location: login.php");
    exit;
}

// 2. Ambil ID dari URL
$id_aset = $_GET['id'];

// 3. PROSES UPDATE DATA (Jika Tombol Update Diklik)
if(isset($_POST['update'])){
    $id           = $_POST['id_aset'];
    $nama_aset    = mysqli_real_escape_string($conn, $_POST['nama_aset']);
    $deskripsi    = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $stok         = (int) $_POST['stok'];
    $stok_tersedia= (int) $_POST['stok_tersedia'];
    $gambar_lama  = $_POST['gambar_lama']; // Nama file gambar saat ini

    // Cek apakah user mengupload gambar baru?
    if(!empty($_FILES['gambar_aset']['name'])){
        // --- PROSES UPLOAD GAMBAR BARU ---
        $nama_file = $_FILES['gambar_aset']['name'];
        $tmp_file  = $_FILES['gambar_aset']['tmp_name'];
        $ext       = pathinfo($nama_file, PATHINFO_EXTENSION);
        $valid_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if(in_array(strtolower($ext), $valid_ext)){
            // Hapus gambar lama jika ada (Opsional, agar server tidak penuh)
            if($gambar_lama && file_exists("uploads/".$gambar_lama)){
                unlink("uploads/".$gambar_lama);
            }

            // Nama baru unik
            $nama_gambar_baru = uniqid() . "." . $ext;
            move_uploaded_file($tmp_file, "uploads/" . $nama_gambar_baru);
            
            // Set variabel gambar ke file baru
            $gambar_final = $nama_gambar_baru;
        } else {
            echo "<script>alert('Format gambar salah! Update dibatalkan.');</script>";
            // Stop proses jika format salah
            $gambar_final = $gambar_lama; 
        }
    } else {
        // --- JIKA TIDAK UPLOAD GAMBAR BARU ---
        // Gunakan gambar yang lama
        $gambar_final = $gambar_lama;
    }

    // Query Update
    $query_update = "UPDATE aset SET 
                        nama_aset = '$nama_aset',
                        deskripsi = '$deskripsi',
                        stok = '$stok',
                        stok_tersedia = '$stok_tersedia',
                        gambar = '$gambar_final'
                     WHERE id_aset = '$id'";

    if(mysqli_query($conn, $query_update)){
        echo "<script>alert('Data aset berhasil diperbarui!'); window.location.href='manajemen_simas.php';</script>";
    } else {
        echo "<script>alert('Gagal update: ".mysqli_error($conn)."');</script>";
    }
}

// 4. Ambil Data Aset Berdasarkan ID untuk ditampilkan di form
$sql_get = "SELECT * FROM aset WHERE id_aset='$id_aset'";
$query_edit = mysqli_query($conn, $sql_get);

if(mysqli_num_rows($query_edit) == 0){
    echo "<script>alert('Data aset tidak ditemukan!'); window.location.href='manajemen_simas.php';</script>";
    exit;
}

$data = mysqli_fetch_array($query_edit);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Aset - SIMAS</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* Styling Form Modern (Sama seperti aset_tambah.php) */
        body { background-color: #f0f2f5; font-family: 'Segoe UI', sans-serif; display: flex; justify-content: center; padding-top: 30px; padding-bottom: 30px; }
        
        .card {
            background: white; padding: 40px; border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1); width: 100%; max-width: 600px;
        }

        h2 { margin-top: 0; color: #333; border-bottom: 2px solid #3f51b5; padding-bottom: 10px; margin-bottom: 20px; }

        .form-group { margin-bottom: 20px; }
        
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; }
        
        input[type="text"], input[type="number"], textarea, input[type="file"] {
            width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px;
            font-size: 1rem; box-sizing: border-box; transition: 0.3s;
        }
        input:focus, textarea:focus { border-color: #3f51b5; outline: none; }

        /* Preview Gambar */
        .preview-container {
            display: flex; gap: 20px; align-items: flex-start; margin-top: 10px;
            background: #f9f9f9; padding: 15px; border-radius: 8px; border: 1px dashed #ccc;
        }
        .img-box { text-align: center; width: 120px; }
        .img-box img { width: 100%; height: 100px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
        .img-label { font-size: 0.75rem; color: #888; display: block; margin-bottom: 5px; }

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
        <h2>✏️ Edit Data Aset</h2>
        
        <form action="" method="POST" enctype="multipart/form-data">
            
            <input type="hidden" name="id_aset" value="<?php echo $data['id_aset']; ?>">
            <input type="hidden" name="gambar_lama" value="<?php echo $data['gambar']; ?>">

            <div class="form-group">
                <label for="nama_aset">Nama Aset</label>
                <input type="text" id="nama_aset" name="nama_aset" value="<?php echo htmlspecialchars($data['nama_aset']); ?>" required>
            </div>
        
            <div class="form-group">
                <label for="deskripsi">Deskripsi</label>
                <textarea id="deskripsi" name="deskripsi" rows="4" required><?php echo htmlspecialchars($data['deskripsi']); ?></textarea>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label for="stok">Total Stok</label>
                    <input type="number" id="stok" name="stok" value="<?php echo $data['stok']; ?>" required min="1">
                </div>
                <div class="form-group">
                    <label for="stok_tersedia">Stok Tersedia</label>
                    <input type="number" id="stok_tersedia" name="stok_tersedia" value="<?php echo $data['stok_tersedia']; ?>" required min="0">
                </div>
            </div>

            <div class="form-group">
                <label for="gambar_aset">Gambar Aset</label>
                <input type="file" id="gambar_aset" name="gambar_aset" accept="image/*" onchange="previewImage(event)">
                
                <div class="preview-container">
                    <div class="img-box">
                        <span class="img-label">Gambar Saat Ini</span>
                        <?php 
                            $path = "uploads/" . $data['gambar'];
                            if(!empty($data['gambar']) && file_exists($path)): 
                        ?>
                            <img src="<?php echo $path; ?>" alt="Foto Lama">
                        <?php else: ?>
                            <div style="height:100px; display:flex; align-items:center; justify-content:center; background:#eee; color:#aaa;">No Image</div>
                        <?php endif; ?>
                    </div>

                    <div style="align-self: center; font-size: 1.5rem; color: #999;">➝</div>

                    <div class="img-box">
                        <span class="img-label">Preview Baru</span>
                        <img id="preview-img" src="#" alt="Belum ada" style="display: none;">
                        <div id="no-preview" style="height:100px; display:flex; align-items:center; justify-content:center; background:#eee; color:#aaa; font-size:0.8rem; border-radius:4px;">Upload Baru</div>
                    </div>
                </div>
            </div>
            
            <div class="btn-group">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='manajemen_simas.php';">Batal</button>
                <button type="submit" name="update" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>

    <script>
        function previewImage(event) {
            var reader = new FileReader();
            reader.onload = function(){
                var output = document.getElementById('preview-img');
                var noPreview = document.getElementById('no-preview');
                
                output.src = reader.result;
                output.style.display = 'block';
                noPreview.style.display = 'none';
            };
            if(event.target.files[0]){
                reader.readAsDataURL(event.target.files[0]);
            }
        }
    </script>

</body>
</html>
<?php mysqli_close($conn); ?>
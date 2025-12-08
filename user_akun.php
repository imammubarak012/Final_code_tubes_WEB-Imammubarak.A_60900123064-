<?php 
// File: user_akun.php
// Deskripsi: Manajemen Akun User (Validasi NIK 16 Digit)

session_start();
include 'config.php'; 

// 1. Cek Login & Role
if(!isset($_SESSION['login']) || $_SESSION['role'] !== 'user') {
    header("location: login.php");
    exit;
}

$nama_user_session = $_SESSION['nama_lengkap'];

// --- BLOK LOGIKA UPDATE AKUN ---
if(isset($_POST['update_akun'])){
    $id_user      = $_POST['id_peminjam']; 
    $new_nama     = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $new_hp       = mysqli_real_escape_string($conn, $_POST['nomor_hp']);
    $new_nik      = mysqli_real_escape_string($conn, $_POST['nik']);
    $new_password = mysqli_real_escape_string($conn, $_POST['password']);

    // --- VALIDASI SERVER SIDE (PHP) ---
    // Pastikan NIK tepat 16 digit dan angka
    if (strlen($new_nik) !== 16 || !is_numeric($new_nik)) {
        echo "<script>alert('Gagal! NIK harus terdiri dari tepat 16 digit angka.'); window.history.back();</script>";
        exit; // Hentikan proses jika validasi gagal
    }

    // Mulai Query Update
    $sql_update = "UPDATE peminjam SET 
                   nama_lengkap='$new_nama', 
                   nomor_hp='$new_hp', 
                   nik='$new_nik'";
                    
    if (!empty($new_password)) {
        $sql_update .= ", password='$new_password'";
    }

    $sql_update .= " WHERE id_peminjam='$id_user'"; 

    // Cek error duplicate entry (misal NIK sudah dipakai orang lain)
    try {
        if(mysqli_query($conn, $sql_update)) {
            // Update session jika nama berubah
            if ($new_nama !== $nama_user_session) {
                 mysqli_query($conn, "UPDATE peminjaman SET nama_peminjam='$new_nama' WHERE nama_peminjam='$nama_user_session'");
                 $_SESSION['nama_lengkap'] = $new_nama;
            }
            echo "<script>alert('Informasi akun berhasil diperbarui!'); window.location='user_akun.php';</script>";
        } else {
            throw new Exception(mysqli_error($conn));
        }
    } catch (Exception $e) {
        echo "<script>alert('Gagal memperbarui akun. NIK mungkin sudah digunakan oleh pengguna lain.'); window.history.back();</script>";
    }
}

// --- AMBIL DATA USER TERBARU ---
$q_user = mysqli_query($conn, "SELECT * FROM peminjam WHERE nama_lengkap='$nama_user_session'");
if(mysqli_num_rows($q_user) > 0){
    $user_data = mysqli_fetch_assoc($q_user);
    $id_to_use = isset($user_data['id_peminjaman']) ? $user_data['id_peminjaman'] : (isset($user_data['id']) ? $user_data['id'] : 0);
} else {
    $user_data = ['nama_lengkap' => $nama_user_session, 'username' => '-', 'nomor_hp' => '', 'nik' => ''];
    $id_to_use = 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Saya - SIMAS</title>
    <link rel="stylesheet" href="assets/style.css">
    
    <script>
        // Validasi input hanya angka
        function hanyaAngka(evt) {
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                return false;
            }
            return true;
        }
    </script>

    <style>
        /* RESET & BASE */
        body { margin:0; padding:0; background:#f0f2f5; font-family: 'Segoe UI', Arial, sans-serif; color: #333; }
        * { box-sizing: border-box; }

        /* HEADER MODERN */
        .header { 
            background: linear-gradient(135deg, #2c3e50 0%, #3f51b5 100%);
            color:#fff; padding:1rem 2.5rem; display:flex; justify-content:space-between; align-items:center; position:sticky; top:0; z-index:9999; 
            box-shadow: 0 4px 25px rgba(63, 81, 181, 0.15); border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .brand-wrapper { display: flex; align-items: center; gap: 12px; }
        .brand-logo { width: 34px; height: 34px; color: #a5b4fc; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2)); }
        .brand-name { font-size: 1.4rem; font-weight: 900; letter-spacing: 1px; }
        .brand-subtitle { font-weight: 300; opacity: 0.9; font-size: 1.2rem; margin-left: 5px; }

        .dashboard-layout { display:grid; grid-template-columns:250px 1fr; min-height:100vh; }
        
        /* SIDEBAR MODERN */
        .sidebar { 
            background:#2c3e50; color:white; padding:0; position:sticky; top:60px; height:calc(100vh - 60px); 
            display: flex; flex-direction: column; justify-content: space-between;
        }
        .sidebar-content-wrapper { flex-grow: 1; overflow-y: auto; }
        .sidebar-profile { 
            padding:20px; background:#34495e; display:flex; gap:15px; align-items:center; 
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .sidebar-profile .user-icon { 
            width:45px; height:45px; background:#2ecc71; border-radius:50%; 
            display:flex; align-items:center; justify-content:center; font-size: 1.4rem; font-weight: bold;
            box-shadow: 0 4px 10px rgba(46, 204, 113, 0.3);
        }
        .sidebar-menu { list-style:none; padding:10px 0; margin:0; }
        .sidebar-menu a { 
            display:flex; align-items:center; color:rgba(255,255,255,0.8); padding:15px 25px; 
            text-decoration:none; transition: 0.3s; border-bottom: 1px solid rgba(255,255,255,0.05); font-weight: 500;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active { 
            background: rgba(63, 81, 181, 0.2); color: white; border-left:5px solid #2ecc71; padding-left:30px; 
        }
        
        .sidebar-footer { padding: 20px; border-top: 1px solid rgba(255,255,255,0.1); background: #233140; }
        .btn-logout { 
            display: block; width: 100%; padding: 12px; background: linear-gradient(to right, #e74c3c, #c0392b); 
            color: white; text-align: center; border-radius: 8px; text-decoration: none; font-weight: 700; 
            transition: 0.3s; box-shadow: 0 4px 10px rgba(231, 76, 60, 0.3); letter-spacing: 1px; 
        }
        .btn-logout:hover { transform: translateY(-3px); box-shadow: 0 6px 15px rgba(231, 76, 60, 0.4); }

        /* MAIN CONTENT */
        .main-content { padding:40px; max-width: 1000px; margin: 0 auto; width: 100%; }
        
        /* === CARD FORM DIPERKECIL === */
        .account-card {
            background: white; 
            border-radius: 12px; 
            padding: 30px; 
            box-shadow: 0 8px 25px rgba(0,0,0,0.05); 
            border: 1px solid #eef2f6;
            max-width: 550px; 
            margin: 0 auto; 
        }
        
        .form-header {
            border-bottom: 2px solid #f0f2f5; 
            padding-bottom: 15px; 
            margin-bottom: 25px;
            text-align: center; 
        }
        .form-header h2 { margin: 0; font-size: 1.5rem; color: #1e293b; font-weight: 800; }
        .form-header p { color: #64748b; margin: 5px 0 0 0; font-size: 0.9rem; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 6px; font-weight: 700; color: #334155; font-size: 0.9rem; }
        .form-group input { 
            width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; 
            font-size: 0.95rem; transition: 0.3s; color: #333;
        }
        .form-group input:focus { border-color: #3f51b5; outline: none; box-shadow: 0 0 0 3px rgba(63, 81, 181, 0.1); }
        .form-group input:disabled { background: #f8fafc; border-style: dashed; color: #94a3b8; cursor: not-allowed; }
        
        /* Validasi Visual */
        input:invalid:not(:placeholder-shown) { border-color: #ef4444; background: #fef2f2; }

        .password-section {
            background: #fffbeb; border: 1px solid #fcd34d; padding: 15px; border-radius: 8px; margin-top: 10px;
        }
        .password-section h4 { margin: 0 0 8px 0; color: #b45309; font-size: 0.95rem; }
        .password-section p { font-size: 0.8rem; color: #d97706; margin-bottom: 10px; }

        .btn-save {
            width: 100%; padding: 14px; background: linear-gradient(to right, #3f51b5, #303f9f); 
            color: white; font-size: 1rem; font-weight: 700; border: none; border-radius: 8px; 
            cursor: pointer; transition: 0.3s; box-shadow: 0 4px 15px rgba(63, 81, 181, 0.3); margin-top: 20px;
        }
        .btn-save:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(63, 81, 181, 0.4); }

        @media (max-width: 768px) {
            .dashboard-layout { grid-template-columns: 1fr; }
            .sidebar { position: relative; height: auto; display: block; }
            .sidebar-footer { margin-top: 0; }
            .account-card { padding: 20px; width: 100%; }
        }
    </style>
</head>
<body>

<div class="header">
    <div class="brand-wrapper">
        <svg class="brand-logo" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12.378 1.602a.75.75 0 00-.756 0L3 6.632l9 5.25 9-5.25-8.622-5.03zM21.75 7.93l-9 5.25v9l8.628-5.032a.75.75 0 00.372-.648V7.93zM11.25 22.18v-9l-9-5.25v8.57a.75.75 0 00.372.648l8.628 5.033z" />
        </svg>
        <div class="brand-name">
            SIMAS <span class="brand-subtitle">USER DASHBOARD</span>
        </div>
    </div>
</div>

<div class="dashboard-layout">
    
    <div class="sidebar">
        <div class="sidebar-content-wrapper">
            <div class="sidebar-profile">
                <div class="user-icon">👤</div>
                <div>
                    <strong><?php echo htmlspecialchars($user_data['nama_lengkap'] ?? $nama_user_session); ?></strong><br>
                    <small>User Area</small>
                </div>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard_user.php#katalog">📚 Katalog Aset</a></li>
                <li><a href="dashboard_user.php#status">📋 Status Pinjaman</a></li>
                <li><a href="user_akun.php" class="active">⚙️ Akun Saya</a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php" class="btn-logout">🚪 LOGOUT</a>
        </div>
    </div>

    <div class="main-content">
        
        <div class="account-card">
            <div class="form-header">
                <h2>Manajemen Akun</h2>
                <p>Perbarui informasi profil Anda di bawah ini.</p>
            </div>
            
            <form action="" method="POST">
                <input type="hidden" name="id_peminjam" value="<?php echo $id_to_use; ?>">

                <div class="form-group">
                    <label>Username</label>
                    <input type="text" value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>" disabled>
                </div>
                
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" value="<?php echo htmlspecialchars($user_data['nama_lengkap'] ?? ''); ?>" required>
                </div>

                <div class="form-group">
                    <label>Nomor HP / WhatsApp</label>
                    <input type="text" name="nomor_hp" value="<?php echo htmlspecialchars($user_data['nomor_hp'] ?? ''); ?>" 
                           onkeypress="return hanyaAngka(event)" required>
                </div>

                <div class="form-group">
                    <label>NIK / Identitas</label>
                    <input type="text" name="nik" value="<?php echo htmlspecialchars($user_data['nik'] ?? ''); ?>" 
                           minlength="16" maxlength="16" pattern="\d{16}" 
                           title="NIK harus terdiri dari 16 digit angka"
                           onkeypress="return hanyaAngka(event)" required>
                    <small style="color:#64748b; font-size:0.8rem; margin-top:2px; display:block;">*Wajib 16 digit angka.</small>
                </div>
                
                <div class="password-section">
                    <h4>🔒 Ganti Password</h4>
                    <p>Kosongkan jika tidak ingin mengganti password.</p>
                    <div class="form-group" style="margin-bottom:0;">
                        <input type="password" name="password" placeholder="Password baru (Min. 6 Karakter)">
                    </div>
                </div>

                <button type="submit" name="update_akun" class="btn-save">💾 Simpan Perubahan</button>
            </form>
        </div>

    </div>
</div>

</body>
</html>
<?php mysqli_close($conn); ?>
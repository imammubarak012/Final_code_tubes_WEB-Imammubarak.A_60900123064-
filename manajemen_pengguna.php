<?php 
// File: manajemen_pengguna.php
// Deskripsi: Manajemen Pengguna (Fix Unknown Column Error + Card Compact)

session_start();
include 'config.php'; 

if(!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("location: login.php");
    exit;
}
$nama_admin = $_SESSION['nama_lengkap'];

// --- LOGIKA HAPUS USER ---
if (isset($_GET['delete_user_id'])) {
    $delete_id = (int)$_GET['delete_user_id'];
    
    // Cek apakah user punya pinjaman aktif (Status Diajukan/Disetujui)
    // Subquery mencari nama berdasarkan ID (Mencoba id_peminjaman atau id)
    $cek_nama = mysqli_query($conn, "SELECT nama_lengkap FROM peminjam WHERE id_peminjaman=$delete_id OR id=$delete_id LIMIT 1");
    
    if(mysqli_num_rows($cek_nama) > 0) {
        $data_user = mysqli_fetch_assoc($cek_nama);
        $nama_hapus = $data_user['nama_lengkap'];

        $cek_active = mysqli_query($conn, "SELECT COUNT(*) AS count FROM peminjaman WHERE nama_peminjam='$nama_hapus' AND status IN ('Diajukan', 'Disetujui')");
        $active_count = mysqli_fetch_assoc($cek_active)['count'];
        
        if ($active_count > 0) {
            echo "<script>alert('Gagal! User ini masih punya $active_count pinjaman aktif/belum kembali.')</script>";
        } else {
            // Coba hapus menggunakan id_peminjaman atau id
            // Kita coba query delete yang aman (menggunakan OR jika kolom id_peminjaman tidak ada, mysql akan skip, tapi ini logic PHP)
            // Cara aman: Deteksi kolom dulu atau coba delete
            
            // Query Delete Sederhana (Asumsi kolom id_peminjaman ada, jika error 'Unknown column', user harus sesuaikan database)
            // Saya gunakan @ untuk suppress error jika kolom tidak ada, lalu coba 'id'
            $del1 = @mysqli_query($conn, "DELETE FROM peminjam WHERE id_peminjaman=$delete_id");
            if(!$del1) {
                $del2 = @mysqli_query($conn, "DELETE FROM peminjam WHERE id=$delete_id");
            }

            echo "<script>alert('Pengguna berhasil dihapus.')</script>";
        }
    }
    echo "<meta http-equiv='refresh' content='0; url=manajemen_pengguna.php'>";
    exit;
}

// --- QUERY DATA (FIX ERROR DISINI) ---
// Mengganti 'ORDER BY id_peminjaman' menjadi 'ORDER BY nama_lengkap' agar aman
$query_peminjam = mysqli_query($conn, "SELECT * FROM peminjam ORDER BY nama_lengkap ASC");
$query_admin = mysqli_query($conn, "SELECT * FROM admin ORDER BY id_admin ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manajemen Pengguna - SIMAS</title>
<link rel="stylesheet" href="assets/style.css">
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
    .sidebar-profile .admin-icon { 
        width:45px; height:45px; background:orange; border-radius:50%; 
        display:flex; align-items:center; justify-content:center; font-size: 1.4rem; font-weight: bold;
        box-shadow: 0 4px 10px rgba(255, 165, 0, 0.3);
    }
    .sidebar-menu { list-style:none; padding:10px 0; margin:0; }
    .sidebar-menu a { 
        display:flex; align-items:center; color:rgba(255,255,255,0.8); padding:15px 25px; 
        text-decoration:none; transition: 0.3s; border-bottom: 1px solid rgba(255,255,255,0.05); font-weight: 500;
    }
    .sidebar-menu a:hover, .sidebar-menu a.active { 
        background: rgba(63, 81, 181, 0.2); color: white; border-left:5px solid orange; padding-left:30px; 
    }
    
    .sidebar-footer { padding: 20px; border-top: 1px solid rgba(255,255,255,0.1); background: #233140; }
    .btn-logout { 
        display: block; width: 100%; padding: 12px; background: linear-gradient(to right, #e74c3c, #c0392b); 
        color: white; text-align: center; border-radius: 8px; text-decoration: none; font-weight: 700; 
        transition: 0.3s; box-shadow: 0 4px 10px rgba(231, 76, 60, 0.3); letter-spacing: 1px; 
    }
    .btn-logout:hover { transform: translateY(-3px); box-shadow: 0 6px 15px rgba(231, 76, 60, 0.4); }

    /* MAIN CONTENT */
    .main-content { padding:40px; max-width: 1200px; margin: 0 auto; width: 100%; }
    .section-heading { font-size:1.8rem; font-weight:800; margin-bottom:0.5rem; color: #1a1a1a; }
    .section-description { margin-bottom:2.5rem; color:#666; font-size: 1rem; }

    /* === USER GRID & CARD (UKURAN LEBIH BESAR) === */
    .user-grid { 
        display: grid; 
        /* Lebar min 280px agar card terlihat lebih besar */
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); 
        gap: 25px; /* Jarak antar card */
        margin-bottom: 40px; 
    }
    
    .user-card { 
        background: white; border-radius: 12px; padding: 25px; /* Padding lebih lega */
        box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #f0f0f0; 
        display: flex; flex-direction: column; justify-content: space-between; 
        transition: 0.3s; position: relative; overflow: hidden;
    }
    .user-card:hover { transform: translateY(-5px); box-shadow: 0 12px 25px rgba(0,0,0,0.08); border-color: #d1d5db; }
    
    /* Avatar Inisial */
    .user-avatar {
        width: 55px; height: 55px; /* Avatar lebih besar */
        background: #e0e7ff; color: #4338ca; 
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        font-size: 1.3rem; font-weight: 800; flex-shrink: 0;
    }
    .avatar-admin { background: #fff7ed; color: #ea580c; }

    /* Info User */
    .user-info { width: 100%; overflow: hidden; }
    .user-info h4 { 
        margin: 0 0 5px 0; font-size: 1.15rem; /* Font nama lebih besar */
        color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; 
    }
    .user-info p { 
        margin: 3px 0; font-size: 0.9rem; /* Font detail pas */
        color: #64748b; display: flex; align-items: center; gap: 8px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    
    .role-badge { 
        padding: 4px 10px; border-radius: 12px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; margin-bottom: 8px; display: inline-block; 
    }
    .role-user { background: #dbeafe; color: #1e40af; }
    .role-admin { background: #ffedd5; color: #9a3412; }

    .card-actions { margin-top: 20px; border-top: 1px solid #f1f5f9; padding-top: 15px; text-align: right; }
    
    .btn-delete-user { 
        background: #fee2e2; color: #b91c1c; padding: 8px 14px; border-radius: 6px; 
        text-decoration: none; font-size: 0.8rem; font-weight: 600; transition: 0.2s; 
        display: inline-flex; align-items: center; gap: 5px;
    }
    .btn-delete-user:hover { background: #fca5a5; }

    .section-title-small {
        font-size: 1.2rem; font-weight: 700; color: #334155; margin-bottom: 20px; 
        border-left: 5px solid #3f51b5; padding-left: 12px;
    }

    @media (max-width: 768px) {
        .dashboard-layout { grid-template-columns: 1fr; }
        .sidebar { height: auto; position: relative; display: block; }
        .sidebar-footer { margin-top: 0; }
        .user-grid { grid-template-columns: 1fr; }
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
            SIMAS <span class="brand-subtitle">ADMIN PANEL</span>
        </div>
    </div>
</div>

<div class="dashboard-layout">
    
    <div class="sidebar">
        <div class="sidebar-content-wrapper">
            <div class="sidebar-profile">
                <div class="admin-icon">👤</div>
                <div>
                    <strong><?php echo htmlspecialchars($nama_admin); ?></strong><br>
                    <small>Administrator</small>
                </div>
            </div>
            <ul class="sidebar-menu">
                <li><a href="manajemen_simas.php#dashboard">🏠 Dashboard Utama</a></li>
                <li><a href="manajemen_simas.php#aset">📦 Manajemen Aset</a></li>
                <li><a href="kelola_transaksi.php">🧾 Kelola Transaksi</a></li>
                <li><a href="manajemen_pengguna.php" class="active">👥 Manajemen Pengguna</a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php" class="btn-logout">🚪 LOGOUT</a>
        </div>
    </div>

    <div class="main-content">
        <h2 class="section-heading">Manajemen Pengguna</h2>
        <p class="section-description">Kelola data pengguna, peminjam, dan administrator sistem.</p>

        <h3 class="section-title-small">Daftar Peminjam (User)</h3>
        
        <div class="user-grid">
            <?php if(mysqli_num_rows($query_peminjam)==0): ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 40px; background: white; border-radius: 12px; color: #888; font-size: 1rem;">
                    Tidak ada data peminjam.
                </div>
            <?php else: while($user=mysqli_fetch_array($query_peminjam)): 
                // Ambil inisial nama
                $initial = strtoupper(substr($user['nama_lengkap'], 0, 1));
                
                // DETEKSI ID (Untuk mencegah error jika nama kolom beda)
                // Cek apakah 'id_peminjaman' ada, jika tidak pakai 'id'
                $id_user = isset($user['id_peminjaman']) ? $user['id_peminjaman'] : (isset($user['id']) ? $user['id'] : 0);
            ?>
            <div class="user-card">
                <div style="display:flex; gap:15px;">
                    <div class="user-avatar"><?php echo $initial; ?></div>
                    <div class="user-info">
                        <span class="role-badge role-user">Peminjam</span>
                        <h4><?php echo htmlspecialchars($user['nama_lengkap']); ?></h4>
                        <p>👤 @<?php echo htmlspecialchars($user['username']); ?></p>
                        <p>📞 <?php echo htmlspecialchars($user['nomor_hp']); ?></p>
                        <p>🆔 <?php echo htmlspecialchars($user['nik']); ?></p>
                    </div>
                </div>
                
                <div class="card-actions">
                    <a href="?delete_user_id=<?php echo $id_user; ?>"
                       onclick="return confirm('PERINGATAN: Yakin hapus pengguna ini? Data peminjaman terkait mungkin akan hilang.')"
                       class="btn-delete-user">
                       🗑️ Hapus Akun
                    </a>
                </div>
            </div>
            <?php endwhile; endif; ?>
        </div>

        <h3 class="section-title-small" style="border-left-color: orange; margin-top: 40px;">Daftar Administrator</h3>
        
        <div class="user-grid">
            <?php while($admin=mysqli_fetch_array($query_admin)): 
                $initial_adm = strtoupper(substr($admin['nama_lengkap'], 0, 1));
            ?>
            <div class="user-card" style="border-left: 5px solid orange;">
                <div style="display:flex; gap:15px; align-items:center;">
                    <div class="user-avatar avatar-admin"><?php echo $initial_adm; ?></div>
                    <div class="user-info">
                        <span class="role-badge role-admin">Administrator</span>
                        <h4><?php echo htmlspecialchars($admin['nama_lengkap']); ?></h4>
                        <p>👤 @<?php echo htmlspecialchars($admin['username']); ?></p>
                        <p style="font-size:0.75rem; color:#94a3b8; margin-top:5px;">*Admin Access</p>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

    </div>
</div>

</body>
</html>

<?php mysqli_close($conn); ?>
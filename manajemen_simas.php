<?php 
// File: manajemen_simas.php
// Deskripsi: Dashboard Admin Modern (Search Button Square Blue Style)

session_start();
include 'config.php'; 

// 1. Cek Login
if(!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("location: login.php");
    exit;
}
$nama_admin = $_SESSION['nama_lengkap'];

// 2. Metrics
$total_aset = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM aset"))['count'];
$total_peminjam = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM peminjam"))['count'];
$transaksi_diajukan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM peminjaman WHERE status='Diajukan' OR status='' OR status IS NULL"))['count'];
$transaksi_disetujui = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS count FROM peminjaman WHERE status='Disetujui'"))['count'];

// 3. LOGIKA PENCARIAN ASET
$keyword = "";
if(isset($_GET['cari'])) {
    $keyword = mysqli_real_escape_string($conn, $_GET['cari']);
    $query_str = "SELECT * FROM aset WHERE nama_aset LIKE '%$keyword%' OR deskripsi LIKE '%$keyword%' ORDER BY id_aset DESC";
} else {
    $query_str = "SELECT * FROM aset ORDER BY id_aset DESC";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SIMAS</title>
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
        
        /* METRICS */
        .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .metric-card { background: white; padding: 25px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); position: relative; overflow: hidden; }
        .metric-card h3 { font-size: 2.5rem; margin: 10px 0 0 0; font-weight: 800; color: #333; }
        .metric-card span { font-size: 0.9rem; font-weight: 600; color: #888; text-transform: uppercase; }
        .metric-card::before { content:''; position:absolute; top:0; left:0; width:6px; height:100%; }
        .metric-green::before { background: #22c55e; } .metric-blue::before { background: #3b82f6; }
        .metric-orange::before { background: #f97316; } .metric-red::before { background: #ef4444; }

        /* SECTION HEADER & SEARCH (UPDATED STYLE) */
        .section-header { 
            display: flex; justify-content: space-between; align-items: center; 
            margin-bottom: 25px; border-bottom: 2px solid #e5e7eb; padding-bottom: 15px; 
            flex-wrap: wrap; gap: 15px;
        }
        .section-title { font-size: 1.5rem; font-weight: 800; color: #1a1a1a; margin: 0; }
        .section-desc { color: #666; margin-bottom: 0; font-size: 0.95rem;}

        /* Wrapper Search + Tombol Tambah */
        .header-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        /* --- SEARCH BAR STYLE SEPERTI GAMBAR --- */
        .search-form {
            display: flex;
            align-items: center;
            gap: 8px; /* Jarak antara input dan tombol biru */
        }
        
        .search-input-wrapper {
            position: relative;
        }

        .search-input {
            padding: 10px 15px;
            border: 1px solid #cbd5e1;
            border-radius: 8px; /* Rounded corners */
            width: 250px;
            font-size: 0.95rem;
            color: #333;
            outline: none;
            transition: all 0.3s ease;
            background: white;
        }

        .search-input:focus {
            border-color: #3f51b5;
            box-shadow: 0 0 0 3px rgba(63, 81, 181, 0.1);
        }

        /* Tombol Cari Biru Kotak Rounded */
        .btn-search {
            background: #3b82f6; /* Warna Biru Utama */
            border: none;
            width: 42px; /* Lebar Kotak */
            height: 42px; /* Tinggi Kotak */
            border-radius: 8px; /* Rounded */
            cursor: pointer;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.2s;
            box-shadow: 0 4px 6px rgba(59, 130, 246, 0.2);
        }

        .btn-search:hover {
            background: #2563eb; /* Biru lebih gelap saat hover */
            transform: translateY(-1px);
            box-shadow: 0 6px 10px rgba(59, 130, 246, 0.3);
        }

        /* Tombol Reset 'X' */
        .btn-reset {
            text-decoration: none;
            color: #ef4444;
            font-weight: bold;
            font-size: 1.2rem;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            background: none;
            border: none;
        }

        /* Tombol Tambah */
        .btn-add { 
            background: linear-gradient(to right, #3f51b5, #303f9f); 
            color: white; 
            padding: 0 20px; 
            height: 42px; /* Sama dengan tinggi search bar */
            border-radius: 8px; 
            text-decoration: none; 
            font-weight: 700; 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            box-shadow: 0 4px 10px rgba(63, 81, 181, 0.3); 
            transition: 0.3s; 
            font-size: 0.9rem;
            white-space: nowrap;
        }
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(63, 81, 181, 0.5); }

        /* === ASSET CARD MODERN === */
        .asset-list { display: flex; flex-direction: column; gap: 20px; }

        .asset-card {
            background: white; border-radius: 16px; border: 1px solid #f0f0f0;
            display: flex; justify-content: space-between; overflow: hidden;
            transition: 0.3s; position: relative; box-shadow: 0 4px 10px rgba(0,0,0,0.03);
        }
        .asset-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.08); border-color: #e0e0e0; }
        
        .asset-body { flex-grow: 1; display: flex; padding: 25px; gap: 25px; }
        
        .asset-img-box {
            width: 130px; height: 130px; flex-shrink: 0;
            background: #f8fafc; border-radius: 12px; overflow: hidden;
            border: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: center;
        }
        .asset-img { width: 100%; height: 100%; object-fit: cover; transition: 0.3s;}
        .asset-card:hover .asset-img { transform: scale(1.05); }
        .no-img { font-size: 2.5rem; color: #cbd5e1; }

        .asset-info { flex-grow: 1; width: 100%; }
        
        .asset-header { 
            display: flex; justify-content: space-between; align-items: center; 
            margin-bottom: 10px; width: 100%;
        }
        .asset-name { font-size: 1.3rem; font-weight: 800; color: #1e293b; margin: 0; }
        
        .badge-stock { padding: 6px 12px; border-radius: 30px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
        .stock-ok { background: #dcfce7; color: #166534; }
        .stock-low { background: #fee2e2; color: #991b1b; }

        .asset-meta { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 20px; padding-top: 20px; border-top: 1px dashed #e5e7eb; }
        .meta-label { font-size: 0.75rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .meta-val { font-size: 1rem; font-weight: 700; color: #334155; margin-top: 5px; display: block; }

        .asset-actions { background: #f8fafc; padding: 25px; display: flex; flex-direction: column; justify-content: center; gap: 10px; border-left: 1px solid #f0f0f0; min-width: 150px; }
        
        .btn-action { 
            display: block; text-align: center; padding: 8px 12px; border-radius: 6px; 
            font-size: 0.85rem; font-weight: 700; text-decoration: none; transition: 0.3s; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid transparent;
        }
        .btn-edit { background: #e2e8f0; color: #475569; border: 1px solid #cbd5e1; } 
        .btn-edit:hover { background: #cbd5e1; color: #1e293b; transform: translateY(-2px); }
        .btn-delete { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; } 
        .btn-delete:hover { background: #fecaca; transform: translateY(-2px); }

        @media (max-width: 768px) {
            .header { padding: 1rem; }
            .brand-name { font-size: 1.2rem; } .brand-subtitle { display: none; }
            .dashboard-layout { grid-template-columns: 1fr; }
            .sidebar { height: auto; position: relative; display: block; }
            .sidebar-footer { margin-top: 0; }
            .main-content { padding: 20px; }
            .asset-body { flex-direction: column; padding: 20px; }
            .asset-img-box { width: 100%; height: 200px; }
            .asset-card { flex-direction: column; }
            .asset-actions { border-left: none; border-top: 1px solid #eee; flex-direction: row; padding: 20px;}
            .btn-action { flex: 1; }
            .asset-meta { grid-template-columns: 1fr; gap: 10px; }
            
            /* Responsive Search */
            .section-header { flex-direction: column; align-items: flex-start; gap: 15px;}
            .header-actions { width: 100%; justify-content: space-between; flex-wrap: wrap; }
            .search-form { width: 100%; max-width: none; flex-grow: 1; }
            .search-input { width: 100%; }
            .btn-add { width: auto; }
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
                <li><a href="manajemen_simas.php" class="active">🏠 Dashboard Utama</a></li>
                <li><a href="#aset-section">📦 Manajemen Aset</a></li>
                <li><a href="kelola_transaksi.php">🧾 Kelola Transaksi</a></li>
                <li><a href="manajemen_pengguna.php">👥 Manajemen Pengguna</a></li>
            </ul>
        </div>
        
        <div class="sidebar-footer">
            <a href="logout.php" class="btn-logout">🚪 LOGOUT</a>
        </div>
    </div>

    <div class="main-content">
        
        <h2 class="section-title" style="border:none; margin-bottom:10px;">Dashboard Overview</h2>
        <p style="color:#64748b; margin-bottom:30px;">Ringkasan data inventaris dan aktivitas peminjaman.</p>

        <div class="metrics-grid">
            <div class="metric-card metric-green"><span>Total Jenis Aset</span><h3><?php echo $total_aset; ?></h3></div>
            <div class="metric-card metric-blue"><span>Total Pengguna</span><h3><?php echo $total_peminjam; ?></h3></div>
            <div class="metric-card metric-orange"><span>Pengajuan Baru</span><h3><?php echo $transaksi_diajukan; ?></h3></div>
            <div class="metric-card metric-red"><span>Sedang Dipinjam</span><h3><?php echo $transaksi_disetujui; ?></h3></div>
        </div>

        <div id="aset-section" style="padding-top: 20px;">
            <div class="section-header">
                <div>
                    <h2 class="section-title" style="margin-bottom:5px; border:none; padding:0;">Manajemen Aset</h2>
                    <p class="section-desc">Kelola data barang inventaris yang tersedia.</p>
                </div>
                
                <div class="header-actions">
                    <form action="" method="GET" class="search-form">
                        <div class="search-input-wrapper">
                            <input type="text" name="cari" class="search-input" placeholder="Cari nama aset..." value="<?php echo htmlspecialchars($keyword); ?>" autocomplete="off">
                            <?php if(!empty($keyword)): ?>
                                <a href="manajemen_simas.php" class="btn-reset" title="Hapus Pencarian">&times;</a>
                            <?php endif; ?>
                        </div>

                        <button type="submit" class="btn-search">
                            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </button>
                    </form>

                    <a href="aset_tambah.php" class="btn-add"><span>➕</span> Tambah</a>
                </div>
            </div>

            <div class="asset-list">
                <?php
                // Query Aset
                $query_aset = mysqli_query($conn, $query_str);
                
                if(mysqli_num_rows($query_aset) == 0):
                ?>
                    <div style="text-align:center; padding:50px; background:white; border-radius:16px; color:#888; box-shadow: 0 4px 10px rgba(0,0,0,0.03);">
                        <div style="font-size:3rem; margin-bottom:10px;">🔍</div>
                        <h3>Tidak ditemukan data aset.</h3>
                        <p>Coba kata kunci lain atau tambah aset baru.</p>
                        <?php if(!empty($keyword)): ?>
                            <a href="manajemen_simas.php" style="color:#3f51b5; font-weight:bold; text-decoration:none; display:inline-block; margin-top:10px; padding:8px 15px; background:#e0e7ff; border-radius:6px;">Reset Pencarian</a>
                        <?php endif; ?>
                    </div>
                <?php else: 
                    while($data = mysqli_fetch_array($query_aset)):
                        $stok = $data['stok_tersedia'];
                        $badge_class = ($stok > 0) ? 'stock-ok' : 'stock-low';
                        $status_text = ($stok > 0) ? 'TERSEDIA' : 'HABIS';
                        
                        $file_gambar = 'uploads/' . $data['gambar'];
                        $gambar_tampil = ($data['gambar'] && file_exists($file_gambar)) ? $file_gambar : null;
                ?>
                <div class="asset-card">
                    <div class="asset-body">
                        <div class="asset-img-box">
                            <?php if($gambar_tampil): ?>
                                <img src="<?php echo $gambar_tampil; ?>" alt="Gambar Aset" class="asset-img">
                            <?php else: ?>
                                <span class="no-img">📷</span>
                            <?php endif; ?>
                        </div>

                        <div class="asset-info">
                            <div class="asset-header">
                                <h3 class="asset-name"><?php echo htmlspecialchars($data['nama_aset']); ?></h3>
                                <div style="font-size:0.9rem; font-weight:600; color:#64748b;">
                                    Status: <span class="badge-stock <?php echo $badge_class; ?>"><?php echo $status_text; ?></span>
                                </div>
                            </div>
                            <div style="color:#64748b; font-size:0.95rem; line-height:1.5; margin-bottom:15px;">
                                <?php echo htmlspecialchars($data['deskripsi']); ?>
                            </div>
                            
                            <div class="asset-meta">
                                <div><span class="meta-label">Total Stok</span><span class="meta-val"><?php echo $data['stok']; ?> Unit</span></div>
                                <div><span class="meta-label">Tersedia</span><span class="meta-val" style="color:<?php echo ($stok>0)?'#16a34a':'#dc2626'; ?>"><?php echo $stok; ?> Unit</span></div>
                                <div><span class="meta-label">ID Aset</span><span class="meta-val">#<?php echo $data['id_aset']; ?></span></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="asset-actions">
                        <a href="aset_edit.php?id=<?php echo $data['id_aset']; ?>" class="btn-action btn-edit">✏️ Edit Data</a>
                        <a href="aset_hapus.php?id=<?php echo $data['id_aset']; ?>" onclick="return confirm('Yakin hapus aset ini? Data tidak bisa dikembalikan.')" class="btn-action btn-delete">🗑️ Hapus</a>
                    </div>
                </div>
                <?php endwhile; endif; ?>
            </div>
        </div>

    </div>
</div>

</body>
</html>
<?php mysqli_close($conn); ?>
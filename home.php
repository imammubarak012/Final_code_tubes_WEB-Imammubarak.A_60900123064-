<?php 
// File: home.php
// Deskripsi: Landing Page Publik Premium (Hero + Search Feature + Katalog)

session_start();
include 'config.php'; 

// Redirect jika sudah login
if(isset($_SESSION['login']) && $_SESSION['login'] === 'aktif') {
    if ($_SESSION['role'] === 'admin') {
        header("location: manajemen_simas.php"); 
        exit;
    } elseif ($_SESSION['role'] === 'user') {
        header("location: dashboard_user.php");
        exit;
    }
}

// --- LOGIKA PENCARIAN ---
$keyword = "";
if(isset($_GET['keyword'])) {
    $keyword = mysqli_real_escape_string($conn, $_GET['keyword']);
    // Cari berdasarkan Nama Aset atau Deskripsi
    $query_str = "SELECT * FROM aset WHERE nama_aset LIKE '%$keyword%' OR deskripsi LIKE '%$keyword%' ORDER BY nama_aset ASC";
} else {
    $query_str = "SELECT * FROM aset ORDER BY nama_aset ASC";
}

$query_aset = mysqli_query($conn, $query_str);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMAS - Sistem Manajemen Aset</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        /* === RESET & BASE VARIABLES === */
        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
            --dark-bg: #0f172a;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --bg-page: #f8fafc;
        }

        body { 
            margin: 0; padding: 0; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            background-color: var(--bg-page); 
            color: var(--text-main);
            overflow-x: hidden;
        }
        * { box-sizing: border-box; }

        /* === NAVBAR === */
        .header { 
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
            padding: 0.8rem 5%; display: flex; justify-content: space-between; align-items: center; 
            position: sticky; top: 0; z-index: 1000; border-bottom: 1px solid rgba(226, 232, 240, 0.6);
        }
        .brand-wrapper { display: flex; align-items: center; gap: 12px; }
        .brand-logo { 
            width: 36px; height: 36px; background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white; border-radius: 8px; padding: 6px; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);
        }
        .brand-name { font-size: 1.3rem; font-weight: 800; color: var(--dark-bg); letter-spacing: -0.5px; }
        
        .nav-buttons { display: flex; gap: 12px; }
        .btn-nav { padding: 10px 20px; border-radius: 8px; font-weight: 600; text-decoration: none; font-size: 0.9rem; transition: 0.2s; }
        .btn-outline { color: var(--text-main); border: 1px solid var(--border); background: white; }
        .btn-outline:hover { border-color: var(--primary); color: var(--primary); background: #eff6ff; }
        .btn-solid { background: var(--primary); color: white; border: 1px solid var(--primary); box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25); }
        .btn-solid:hover { background: var(--primary-dark); transform: translateY(-1px); }

        /* === HERO SECTION === */
        .hero {
            position: relative; background-color: var(--dark-bg);
            background-image: linear-gradient(rgba(255, 255, 255, 0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 30px 30px; color: white; padding: 100px 5% 140px 5%; text-align: center;
            overflow: hidden; display: flex; flex-direction: column; align-items: center; justify-content: center;
        }
        .hero::after {
            content: ''; position: absolute; top: -50%; left: 50%; transform: translateX(-50%); width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(59,130,246,0.4) 0%, transparent 70%); filter: blur(60px); z-index: 0; pointer-events: none;
        }
        .hero-content { position: relative; z-index: 1; max-width: 750px; animation: fadeInUp 0.8s ease-out; }
        .hero h1 {
            font-size: 3.5rem; margin: 0 0 20px 0; font-weight: 800; letter-spacing: -1.5px; line-height: 1.1;
            background: linear-gradient(to right, #ffffff, #93c5fd); -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .hero p { font-size: 1.15rem; color: #cbd5e1; margin: 0 auto 35px auto; line-height: 1.6; font-weight: 400; max-width: 600px; }
        .btn-hero {
            display: inline-flex; align-items: center; gap: 8px; background: white; color: var(--primary); padding: 14px 32px; border-radius: 50px;
            font-weight: 700; font-size: 1rem; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
        }
        .btn-hero:hover { transform: translateY(-3px) scale(1.05); box-shadow: 0 0 30px rgba(255, 255, 255, 0.4); }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

        /* === SEARCH BAR MODERN === */
        .search-container {
            margin-top: -35px; /* Naik ke atas hero */
            position: relative; z-index: 10; width: 100%; max-width: 700px; margin-left: auto; margin-right: auto;
            padding: 0 20px;
        }
        .search-form {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            padding: 10px; border-radius: 16px; display: flex; gap: 10px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1); border: 1px solid rgba(255,255,255,0.5);
            align-items: center;
        }
        .search-input {
            flex-grow: 1; border: none; background: transparent; padding: 12px 20px;
            font-size: 1.1rem; color: var(--text-main); font-family: 'Plus Jakarta Sans', sans-serif;
            outline: none;
        }
        .search-btn {
            background: var(--primary); color: white; border: none; padding: 12px 24px;
            border-radius: 10px; font-weight: 700; font-family: 'Plus Jakarta Sans', sans-serif;
            cursor: pointer; transition: 0.2s; font-size: 1rem;
        }
        .search-btn:hover { background: var(--primary-dark); transform: scale(1.02); }

        /* === CONTAINER & LIST === */
        .container { max-width: 1280px; margin: 0 auto; padding: 60px 20px; }
        .section-header { text-align: center; margin-bottom: 50px; }
        .section-badge { background: #eff6ff; color: var(--primary); padding: 6px 16px; border-radius: 20px; font-weight: 700; font-size: 0.85rem; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 1px; display: inline-block; }
        .section-title { font-size: 2.2rem; font-weight: 800; color: var(--dark-bg); margin: 0; letter-spacing: -0.5px; }

        .asset-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px; }

        /* === CARD DESIGN === */
        .asset-card {
            background: white; border-radius: 20px; border: 1px solid rgba(226, 232, 240, 0.8);
            overflow: hidden; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: flex; flex-direction: column; position: relative;
        }
        .asset-card:hover { transform: translateY(-8px); box-shadow: 0 20px 40px -5px rgba(0,0,0,0.1); border-color: var(--primary); }
        .asset-body { padding: 25px 25px 0 25px; display: flex; gap: 20px; }
        
        .asset-img-container {
            position: relative; width: 90px; height: 90px; flex-shrink: 0;
            background: #f1f5f9; border-radius: 16px; padding: 5px; border: 1px solid #e2e8f0;
        }
        .asset-img { width: 100%; height: 100%; object-fit: cover; border-radius: 12px; mix-blend-mode: multiply; }
        .no-img { font-size: 2.5rem; display:flex; justify-content:center; align-items:center; height:100%; width:100%; color:#cbd5e1; }

        .status-pill {
            position: absolute; bottom: -10px; left: 50%; transform: translateX(-50%);
            padding: 4px 10px; border-radius: 20px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase;
            white-space: nowrap; box-shadow: 0 4px 6px rgba(0,0,0,0.05); z-index: 5; border: 2px solid white;
        }
        .status-ok { background: #dcfce7; color: #15803d; }
        .status-low { background: #fee2e2; color: #b91c1c; }

        .asset-info { flex-grow: 1; min-width: 0; display: flex; flex-direction: column; justify-content: center; }
        .asset-name { font-size: 1.1rem; font-weight: 700; color: var(--dark-bg); margin: 0 0 5px 0; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .asset-desc { color: var(--text-muted); font-size: 0.85rem; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

        .asset-meta { 
            display: flex; justify-content: space-between; background: #f8fafc;
            margin: 20px 25px 0 25px; padding: 12px 20px; border-radius: 12px; border: 1px dashed #e2e8f0;
        }
        .meta-item { display: flex; flex-direction: column; align-items: center; }
        .meta-label { font-size: 0.7rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; margin-bottom: 2px; }
        .meta-val { font-size: 1rem; font-weight: 800; color: var(--text-main); }

        .card-footer { padding: 20px 25px; }
        .btn-action {
            display: block; width: 100%; padding: 12px; text-align: center;
            background: var(--dark-bg); color: white; border-radius: 12px; 
            text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: 0.3s; box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-action:hover { background: var(--primary); transform: translateY(-2px); box-shadow: 0 8px 15px rgba(59, 130, 246, 0.3); }
        .btn-disabled { background: #e2e8f0; color: #94a3b8; cursor: not-allowed; pointer-events: none; box-shadow: none; }

        .footer { text-align: center; padding: 60px 20px; color: var(--text-muted); font-size: 0.9rem; background: white; border-top: 1px solid #e2e8f0; margin-top: 80px; }

        @media (max-width: 768px) {
            .hero h1 { font-size: 2.5rem; }
            .asset-list { grid-template-columns: 1fr; }
            .asset-body { flex-direction: column; align-items: center; text-align: center; }
            .asset-img-container { width: 120px; height: 120px; }
            .status-pill { bottom: -12px; font-size: 0.75rem; padding: 5px 12px; }
            .asset-meta { justify-content: center; gap: 30px; }
        }
    </style>
</head>
<body>

    <div class="header">
        <div class="brand-wrapper">
            <div class="brand-logo">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" width="24" height="24">
                  <path d="M12.378 1.602a.75.75 0 00-.756 0L3 6.632l9 5.25 9-5.25-8.622-5.03zM21.75 7.93l-9 5.25v9l8.628-5.032a.75.75 0 00.372-.648V7.93zM11.25 22.18v-9l-9-5.25v8.57a.75.75 0 00.372.648l8.628 5.033z" />
                </svg>
            </div>
            <div class="brand-name">SIMAS</div>
        </div>
        <div class="nav-buttons">
            <a href="login.php" class="btn-nav btn-outline">Masuk</a>
            <a href="register.php" class="btn-nav btn-solid">Daftar</a>
        </div>
    </div>

    <div class="hero">
        <div class="hero-content">
            <h1>Kelola Aset dengan Cerdas, <br> Cepat, & Transparan.</h1>
            <p>Platform terpusat untuk meminjam, melacak, dan mengelola inventaris aset organisasi Anda dalam satu dashboard yang modern.</p>
        </div>
    </div>

    <div class="search-container">
        <form class="search-form" action="" method="GET">
            <input type="text" name="keyword" class="search-input" placeholder="Cari nama aset atau deskripsi..." value="<?php echo htmlspecialchars($keyword); ?>" autocomplete="off">
            <button type="submit" class="search-btn">
                🔍 Cari
            </button>
        </form>
    </div>

    <div class="container" id="katalog">
        <div class="section-header">
            <span class="section-badge">Inventaris</span>
            <h2 class="section-title">Katalog Aset Tersedia</h2>
        </div>

        <div class="asset-list">
            <?php
            if(mysqli_num_rows($query_aset) == 0):
            ?>
                <div style="grid-column: 1/-1; text-align:center; padding:80px; background:white; border-radius:20px; color:#94a3b8; border:1px dashed #cbd5e1;">
                    <h3 style="font-size:1.5rem; margin-bottom:10px;">🔍 Tidak Ditemukan</h3>
                    <p>Maaf, aset dengan kata kunci <strong>"<?php echo htmlspecialchars($keyword); ?>"</strong> tidak ditemukan.</p>
                    <a href="home.php" style="display:inline-block; margin-top:15px; color:var(--primary); text-decoration:none; font-weight:700;">Refresh Katalog</a>
                </div>
            <?php else: 
                while($data = mysqli_fetch_array($query_aset)):
                    $stok = $data['stok_tersedia'];
                    $badge_class = ($stok > 0) ? 'status-ok' : 'status-low';
                    $status_text = ($stok > 0) ? 'TERSEDIA' : 'KOSONG';
                    $btn_class = ($stok > 0) ? '' : 'btn-disabled';
                    $btn_text = ($stok > 0) ? 'Ajukan Pinjaman' : 'Stok Tidak Tersedia';
                    
                    // Gambar
                    $path_gambar = "uploads/" . $data['gambar'];
                    $gambar_ada = ($data['gambar'] && file_exists($path_gambar));
            ?>
            
            <div class="asset-card">
                <div class="asset-body">
                    <div class="asset-img-container">
                        <div class="asset-img-box">
                            <?php if($gambar_ada): ?>
                                <img src="<?php echo $path_gambar; ?>" alt="Foto Aset" class="asset-img">
                            <?php else: ?>
                                <div class="no-img">📦</div>
                            <?php endif; ?>
                        </div>
                        <div class="status-pill <?php echo $badge_class; ?>">
                            <?php echo $status_text; ?>
                        </div>
                    </div>

                    <div class="asset-info">
                        <h3 class="asset-name"><?php echo htmlspecialchars($data['nama_aset']); ?></h3>
                        <div class="asset-desc">
                            <?php echo htmlspecialchars($data['deskripsi']); ?>
                        </div>
                    </div>
                </div>

                <div class="asset-meta">
                    <div class="meta-item">
                        <span class="meta-label">Total Stok</span>
                        <span class="meta-val"><?php echo $data['stok']; ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Tersedia</span>
                        <span class="meta-val" style="color:<?php echo ($stok>0)?'#16a34a':'#dc2626'; ?>">
                            <?php echo $stok; ?>
                        </span>
                    </div>
                </div>
                
                <div class="card-footer">
                    <a href="login.php" class="btn-action <?php echo $btn_class; ?>">
                        <?php echo $btn_text; ?>
                    </a>
                </div>
            </div>

            <?php endwhile; endif; ?>
        </div>
    </div>

    <div class="footer">
        <p>&copy; <?php echo date('Y'); ?> <strong>SIMAS</strong> - Sistem Manajemen Aset. Built for Efficiency.</p>
    </div>

</body>
</html>
<?php mysqli_close($conn); ?>
<?php 
// File: dashboard_user.php
// Deskripsi: Dashboard User Modern (Konsisten dengan Admin)

session_start();
include 'config.php'; 

// 1. Cek Login & Role
if(!isset($_SESSION['login']) || $_SESSION['role'] !== 'user') {
    header("location: login.php");
    exit;
}

$nama_user = $_SESSION['nama_lengkap'];

// --- GET DATA USER ---
$q_user_data = mysqli_query($conn, "SELECT * FROM peminjam WHERE nama_lengkap='$nama_user'");
if(mysqli_num_rows($q_user_data) > 0){
    $user_data = mysqli_fetch_assoc($q_user_data);
} else {
    $user_data = ['nama_lengkap' => $nama_user];
}

// --- METRICS ---
$q_aktif = mysqli_query($conn, "SELECT COUNT(*) as c FROM peminjaman WHERE nama_peminjam='$nama_user' AND status IN ('Diajukan', 'Disetujui')");
$count_aktif = mysqli_fetch_assoc($q_aktif)['c'];

$q_selesai = mysqli_query($conn, "SELECT COUNT(*) as c FROM peminjaman WHERE nama_peminjam='$nama_user' AND status IN ('Selesai', 'Ditolak')");
$count_selesai = mysqli_fetch_assoc($q_selesai)['c'];


// --- PROSES BATALKAN / HAPUS PINJAMAN ---
if(isset($_POST['batalkan_pinjam'])){
    $id_batal = (int)$_POST['id_peminjaman'];
    
    $cek_batal = mysqli_query($conn, "SELECT * FROM peminjaman WHERE id_peminjaman=$id_batal AND nama_peminjam='$nama_user'");
    
    if(mysqli_num_rows($cek_batal) > 0){
        $data_batal = mysqli_fetch_assoc($cek_batal);
        $status_batal = $data_batal['status'];
        $jml_batal = $data_batal['jumlah_pinjam'];
        $aset_batal = $data_batal['id_aset'];

        if($status_batal == 'Diajukan'){
            mysqli_query($conn, "UPDATE aset SET stok_tersedia = stok_tersedia + $jml_batal WHERE id_aset=$aset_batal");
            mysqli_query($conn, "DELETE FROM peminjaman WHERE id_peminjaman=$id_batal");
            echo "<script>alert('Pengajuan berhasil dibatalkan.');</script>";
        } elseif($status_batal == 'Ditolak' || $status_batal == 'Selesai'){
            mysqli_query($conn, "DELETE FROM peminjaman WHERE id_peminjaman=$id_batal");
            echo "<script>alert('Riwayat berhasil dihapus.');</script>";
        } else {
            echo "<script>alert('Gagal: Pinjaman sedang berjalan.');</script>";
        }
    }
    echo "<meta http-equiv='refresh' content='0; url=dashboard_user.php'>";
    exit;
}


// --- PROSES PENGAJUAN PINJAMAN ---
if(isset($_POST['ajukan_pinjam'])){
    $id_aset = (int)$_POST['id_aset'];
    $jumlah_pinjam = (int)$_POST['jumlah_pinjam'];
    $tgl_pinjam = mysqli_real_escape_string($conn, $_POST['tanggal_pinjam']);
    $tgl_kembali = mysqli_real_escape_string($conn, $_POST['tanggal_kembali']);
    
    $today = date('Y-m-d');
    if($tgl_pinjam < $today) { echo "<script>alert('Gagal! Tanggal tidak valid.'); echo '<meta http-equiv=\"refresh\" content=\"0; url=dashboard_user.php\">'; exit; }</script>"; }
    if($tgl_kembali < $tgl_pinjam) { echo "<script>alert('Gagal! Tanggal kembali salah.'); echo '<meta http-equiv=\"refresh\" content=\"0; url=dashboard_user.php\">'; exit; }</script>"; }

    $res_stok = mysqli_query($conn, "SELECT stok_tersedia FROM aset WHERE id_aset='$id_aset'");
    $data_stok = mysqli_fetch_assoc($res_stok);
    $stok_tersedia = $data_stok['stok_tersedia'];

    if($jumlah_pinjam > $stok_tersedia || $jumlah_pinjam <= 0){
        echo "<script>alert('Gagal! Stok tidak cukup.')</script>";
    } else {
        $sql_pinjam = "INSERT INTO peminjaman (id_aset, nama_peminjam, jumlah_pinjam, tanggal_pinjam, tanggal_kembali, status) 
                       VALUES ('$id_aset', '$nama_user', '$jumlah_pinjam', '$tgl_pinjam', '$tgl_kembali', 'Diajukan')";
        $sql_update_stok = "UPDATE aset SET stok_tersedia = stok_tersedia - $jumlah_pinjam WHERE id_aset = $id_aset";

        if(mysqli_query($conn, $sql_pinjam) && mysqli_query($conn, $sql_update_stok)) {
            echo "<script>alert('Berhasil diajukan!')</script>";
        } else {
            echo "<script>alert('Gagal mengajukan.')</script>";
        }
    }
    echo "<meta http-equiv='refresh' content='0; url=dashboard_user.php'>";
    exit;
}

// --- LOGIKA PENGELOMPOKAN DATA STATUS ---
$query_pinjam = mysqli_query($conn, 
    "SELECT p.*, a.nama_aset, a.gambar 
     FROM peminjaman p
     JOIN aset a ON p.id_aset = a.id_aset
     WHERE p.nama_peminjam = '$nama_user'
     ORDER BY p.tanggal_pinjam DESC");

$grouped_loans = [
    'Diajukan' => [],
    'Disetujui' => [],
    'Selesai' => [],
    'Ditolak' => []
];

$has_loans = false;
while($row = mysqli_fetch_array($query_pinjam)) {
    $has_loans = true;
    $st = $row['status'] ?: 'Diajukan';
    if(isset($grouped_loans[$st])) {
        $grouped_loans[$st][] = $row;
    } else {
        $grouped_loans['Diajukan'][] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - SIMAS</title>
    <link rel="stylesheet" href="assets/style.css"> 
    
    <script>
        function openPinjamForm(id, nama, stok) {
            document.getElementById('pinjam_id_aset').value = id;
            document.getElementById('pinjam_nama_aset').innerText = nama;
            document.getElementById('pinjam_stok_tersedia').innerText = stok;
            document.getElementById('jumlah_pinjam').setAttribute('max', stok); 
            document.getElementById('jumlah_pinjam').value = 1;

            const tglPinjamInput = document.getElementById('tgl_pinjam');
            const tglKembaliInput = document.getElementById('tgl_kembali');
            const today = new Date().toISOString().split('T')[0];
            
            tglPinjamInput.setAttribute('min', today);
            tglPinjamInput.value = today;
            tglKembaliInput.setAttribute('min', today);
            tglKembaliInput.value = "";

            tglPinjamInput.addEventListener('change', function() {
                tglKembaliInput.setAttribute('min', this.value);
                if(tglKembaliInput.value && tglKembaliInput.value < this.value){
                    tglKembaliInput.value = this.value;
                }
            });

            document.getElementById('modal-pinjam').style.display = 'flex';
        }

        function closePinjamForm() {
            document.getElementById('modal-pinjam').style.display = 'none';
        }
    </script>

    <style>
        /* MENGGUNAKAN CSS YANG SAMA PERSIS DENGAN MANAJEMEN_SIMAS.PHP */
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
        .main-content { padding:40px; max-width: 1200px; margin: 0 auto; width: 100%; }
        
        /* METRICS */
        .metrics-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .metric-card { background: white; padding: 25px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); position: relative; overflow: hidden; }
        .metric-card h3 { font-size: 2.5rem; margin: 10px 0 0 0; font-weight: 800; color: #333; }
        .metric-card span { font-size: 0.9rem; font-weight: 600; color: #888; text-transform: uppercase; }
        .metric-card::before { content:''; position:absolute; top:0; left:0; width:6px; height:100%; }
        .metric-blue::before { background: #3b82f6; }
        .metric-green::before { background: #22c55e; }

        /* SECTION HEADER */
        .section-title { font-size: 1.5rem; font-weight: 800; color: #1a1a1a; margin-bottom: 20px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }

        /* === GROUP STATUS STYLES (Sama dgn Admin) === */
        .group-container {
            margin-bottom: 30px;
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.02);
        }
        .group-header {
            padding: 12px 20px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-diajukan { background: #fff7ed; color: #c2410c; border-left: 5px solid #f97316; }
        .header-disetujui { background: #f0fdf4; color: #15803d; border-left: 5px solid #22c55e; }
        .header-selesai { background: #f8fafc; color: #475569; border-left: 5px solid #94a3b8; }
        .header-ditolak { background: #fef2f2; color: #b91c1c; border-left: 5px solid #ef4444; }
        .group-body { padding: 20px; display: flex; flex-direction: column; gap: 15px; }

        /* CARD LIST (Sama dgn Admin) */
        .asset-list { display: flex; flex-direction: column; gap: 15px; }

        .asset-card {
            background: white; border-radius: 12px; border: 1px solid #e5e7eb;
            display: flex; justify-content: space-between; overflow: hidden;
            transition: 0.3s; position: relative; box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .asset-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.08); border-color: #e0e0e0;}
        
        .asset-body { flex-grow: 1; display: flex; padding: 20px; gap: 20px; }
        
        /* Gambar */
        .asset-img-box {
            width: 120px; height: 120px; flex-shrink: 0;
            background: #f3f4f6; border-radius: 8px; overflow: hidden;
            border: 1px solid #e5e7eb; display: flex; align-items: center; justify-content: center;
        }
        .asset-img { width: 100%; height: 100%; object-fit: cover; transition: 0.3s;}
        .asset-card:hover .asset-img { transform: scale(1.05); }
        .no-img { font-size: 2rem; color: #ccc; }

        /* Info */
        .asset-info { flex-grow: 1; width: 100%; }
        
        /* HEADER KARTU SAMA DENGAN ADMIN */
        .card-content-main {
             display: flex; justify-content: space-between; align-items: center;
             margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #cfd8dc; 
             width: 100%;
        }
        
        .card-title { font-weight: 800; color: #1e293b; font-size: 1.3rem; margin: 0; }
        .status-info { flex-shrink: 0; margin-left: 15px; }
        
        .badge-stock { padding: 6px 12px; border-radius: 30px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
        .stock-ok { background: #dcfce7; color: #166534; }
        .stock-low { background: #fee2e2; color: #991b1b; }

        .asset-meta { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 20px; padding-top: 20px; border-top: 1px dashed #e5e7eb; }
        .meta-label { font-size: 0.75rem; color: #9ca3af; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .meta-val { font-size: 1rem; font-weight: 700; color: #334155; margin-top: 5px; display: block; }

        /* Tombol Aksi */
        .asset-actions { background: #f8fafc; padding: 20px; display: flex; flex-direction: column; justify-content: center; gap: 10px; border-left: 1px solid #f0f0f0; min-width: 150px; }
        
        .btn { display: block; text-align: center; padding: 10px 15px; border-radius: 8px; font-size: 0.9rem; font-weight: 700; text-decoration: none; cursor: pointer; transition: 0.2s; width: 100%; }
        .btn-success { background: linear-gradient(to right, #3f51b5, #303f9f); color: white; border:none; } 
        .btn-success:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(63, 81, 181, 0.3); }
        .btn-secondary { background: #e2e8f0; color: #555; cursor: not-allowed; border:none; }
        .btn-danger { background: linear-gradient(to right, #ef4444, #dc2626); color: white; border:none; }
        .btn-danger:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(239, 68, 68, 0.3); }

        /* STATUS BADGES */
        .status-badge { padding: 6px 12px; border-radius: 30px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; display: inline-block; letter-spacing: 0.5px; }
        .bg-yellow { background: #fffbeb; color: #b45309; border: 1px solid #fcd34d;}
        .bg-green { background: #f0fdf4; color: #15803d; border: 1px solid #86efac;}
        .bg-red { background: #fef2f2; color: #b91c1c; border: 1px solid #fca5a5;}
        .bg-grey { background: #f8fafc; color: #475569; border: 1px solid #cbd5e1;}

        /* MODAL */
        .modal { display: none; position: fixed; z-index: 10000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); justify-content: center; align-items: center; backdrop-filter: blur(5px); }
        .modal-content { background-color: #fff; padding: 30px; border-radius: 16px; width: 90%; max-width: 450px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); animation: slideIn 0.3s; }
        @keyframes slideIn { from {transform: translateY(-20px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 700; color: #475569; font-size: 0.9rem;}
        .form-group input { width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 1rem; transition: 0.3s;}
        .form-group input:focus { border-color: #3f51b5; outline: none; }

        @media (max-width: 768px) {
            .dashboard-layout { grid-template-columns: 1fr; }
            .sidebar { position: relative; height: auto; display: block; }
            .sidebar-footer { margin-top: 0; }
            .asset-body { flex-direction: column; }
            .asset-img-box { width: 100%; height: 200px; }
            .asset-card { flex-direction: column; }
            .asset-actions { border-left: none; border-top: 1px solid #eee; flex-direction: row; }
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
                    <strong><?php echo htmlspecialchars($user_data['nama_lengkap'] ?? $nama_user); ?></strong><br>
                    <small>User Area</small>
                </div>
            </div>
            <ul class="sidebar-menu">
                <li><a href="#katalog" class="active">📚 Katalog Aset</a></li>
                <li><a href="#status">📋 Status Pinjaman</a></li>
                <li><a href="user_akun.php">⚙️ Akun Saya</a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php" class="btn-logout">🚪 LOGOUT</a>
        </div>
    </div>

    <div class="main-content">
        
        <h2 class="section-title">Dashboard Overview</h2>
        
        <div class="metrics-grid">
            <div class="metric-card metric-blue">
                <span>Pinjaman Aktif</span>
                <h3><?php echo $count_aktif; ?></h3>
            </div>
            <div class="metric-card metric-green">
                <span>Riwayat Selesai</span>
                <h3><?php echo $count_selesai; ?></h3>
            </div>
        </div>

        <div id="katalog" style="padding-top: 20px;">
            <h2 class="section-title">📚 Katalog Aset Tersedia</h2>
            
            <div class="asset-list">
                <?php
                $query_aset = mysqli_query($conn, "SELECT * FROM aset ORDER BY nama_aset ASC");
                
                if(mysqli_num_rows($query_aset) == 0):
                ?>
                    <div style="text-align:center; padding:50px; background:white; border-radius:16px; color:#888; box-shadow: 0 4px 10px rgba(0,0,0,0.03);">
                        <h3>Belum ada aset tersedia.</h3>
                        <p>Silahkan hubungi admin untuk informasi lebih lanjut.</p>
                    </div>
                <?php else: 
                    while($data = mysqli_fetch_array($query_aset)):
                        $stok = $data['stok_tersedia'];
                        $badge_class = ($stok > 0) ? 'stock-ok' : 'stock-low';
                        $status_text = ($stok > 0) ? 'TERSEDIA' : 'HABIS';
                        $btn_class = ($stok > 0) ? 'btn-success' : 'btn-secondary';
                        $disabled = ($stok == 0) ? 'disabled' : '';
                        
                        $path_gambar = "uploads/" . $data['gambar'];
                        $gambar_ada = ($data['gambar'] && file_exists($path_gambar));
                ?>
                <div class="asset-card">
                    <div class="asset-body">
                        <div class="asset-img-box">
                            <?php if($gambar_ada): ?>
                                <img src="<?php echo $path_gambar; ?>" alt="Foto Aset" class="asset-img">
                            <?php else: ?>
                                <span class="no-img">📷</span>
                            <?php endif; ?>
                        </div>

                        <div class="asset-info">
                            <div class="card-content-main">
                                <div class="card-title"><?php echo htmlspecialchars($data['nama_aset']); ?></div>
                                <div class="status-info"><span class="badge-stock <?php echo $badge_class; ?>"><?php echo $status_text; ?></span></div>
                            </div>
                            <div style="color:#64748b; font-size:0.95rem; line-height:1.5; margin-bottom:15px;">
                                <?php echo htmlspecialchars($data['deskripsi']); ?>
                            </div>
                            <div class="asset-meta">
                                <div><span class="meta-label">Total Stok</span><strong class="meta-val"><?php echo $data['stok']; ?> Unit</strong></div>
                                <div><span class="meta-label">Tersedia</span><strong class="meta-val" style="color:<?php echo ($stok>0)?'#16a34a':'#dc2626'; ?>"><?php echo $stok; ?> Unit</strong></div>
                                <div><span class="meta-label">ID Aset</span><strong class="meta-val">#<?php echo $data['id_aset']; ?></strong></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="asset-actions">
                        <button class="btn <?php echo $btn_class; ?>" <?php echo $disabled; ?> 
                            onclick="openPinjamForm(<?php echo $data['id_aset']; ?>, '<?php echo htmlspecialchars($data['nama_aset']); ?>', <?php echo $stok; ?>)">
                            Ajukan Pinjaman
                        </button>
                    </div>
                </div>
                <?php endwhile; endif; ?>
            </div>
        </div>

        <div id="status" style="margin-top: 50px;">
            <h2 class="section-title">📋 Status Pinjaman Anda</h2>
            
            <?php 
                if(!$has_loans):
            ?>
                <div style="text-align:center; padding:30px; background:white; border-radius:12px; color:#888;">
                    Belum ada riwayat peminjaman.
                </div>
            <?php else: ?>

                <?php 
                // Grouping Status
                $status_order = ['Diajukan', 'Disetujui', 'Ditolak', 'Selesai'];
                
                foreach($status_order as $group_name):
                    $transactions = $grouped_loans[$group_name];
                    if(empty($transactions)) continue;

                    $header_class = ''; $icon_group = '';
                    if($group_name == 'Diajukan') { $header_class = 'header-diajukan'; $icon_group = '⏳'; }
                    elseif($group_name == 'Disetujui') { $header_class = 'header-disetujui'; $icon_group = '✅'; }
                    elseif($group_name == 'Ditolak') { $header_class = 'header-ditolak'; $icon_group = '❌'; }
                    elseif($group_name == 'Selesai') { $header_class = 'header-selesai'; $icon_group = '📦'; }
                ?>
                
                <div class="group-container">
                    <div class="group-header <?php echo $header_class; ?>">
                        <span><?php echo $icon_group . ' ' . $group_name; ?></span>
                        <span><?php echo count($transactions); ?> Item</span>
                    </div>

                    <div class="group-body">
                        <?php foreach($transactions as $row): 
                            $st = $row['status'];
                            $badge = 'bg-grey'; 
                            if($st == 'Diajukan') { $badge='bg-yellow'; }
                            elseif($st == 'Disetujui') { $badge='bg-green'; }
                            elseif($st == 'Ditolak') { $badge='bg-red'; }

                            $path_gambar = "uploads/" . $row['gambar'];
                            $gambar_ada = ($row['gambar'] && file_exists($path_gambar));
                            $tgl_kembali = $row['tanggal_kembali'] ? date('d M Y', strtotime($row['tanggal_kembali'])) : '-';
                        ?>
                        <div class="asset-card">
                            <div class="asset-body">
                                <div class="asset-img-box">
                                    <?php if($gambar_ada): ?>
                                        <img src="<?php echo $path_gambar; ?>" alt="Foto Aset" class="asset-img">
                                    <?php else: ?>
                                        <span class="no-img">📦</span>
                                    <?php endif; ?>
                                </div>

                                <div class="asset-info">
                                    <div class="card-content-main">
                                        <div class="card-title">
                                            <?php echo htmlspecialchars($row['nama_aset']); ?> 
                                            <span style="font-weight:400; color:#64748b; font-size:0.9rem; margin-left:5px;">(<?php echo $row['jumlah_pinjam']; ?> Unit)</span>
                                        </div>
                                        <div class="status-info"><span class="status-badge <?php echo $badge; ?>"><?php echo $st; ?></span></div>
                                    </div>
                                    
                                    <div class="asset-meta">
                                        <div><span class="meta-label">Tgl Pinjam</span><strong class="meta-val"><?php echo date('d M Y', strtotime($row['tanggal_pinjam'])); ?></strong></div>
                                        <div><span class="meta-label">Rencana Kembali</span><strong class="meta-val"><?php echo $tgl_kembali; ?></strong></div>
                                        <div><span class="meta-label">ID Transaksi</span><strong class="meta-val">TRX-<?php echo $row['id_peminjaman']; ?></strong></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="asset-actions">
                                <?php if($st == 'Diajukan'): ?>
                                    <form action="" method="POST" onsubmit="return confirm('Yakin ingin membatalkan pengajuan ini? Stok akan dikembalikan.')">
                                        <input type="hidden" name="id_peminjaman" value="<?php echo $row['id_peminjaman']; ?>">
                                        <button type="submit" name="batalkan_pinjam" class="btn btn-danger">🗑 Batalkan</button>
                                    </form>
                                <?php elseif($st == 'Ditolak' || $st == 'Selesai'): ?>
                                    <form action="" method="POST" onsubmit="return confirm('Hapus riwayat ini?')">
                                        <input type="hidden" name="id_peminjaman" value="<?php echo $row['id_peminjaman']; ?>">
                                        <button type="submit" name="batalkan_pinjam" class="btn btn-danger" style="background:#64748b; border-color:#64748b;">🗑 Hapus Riwayat</button>
                                    </form>
                                <?php else: ?>
                                    <small style="color:#94a3b8; text-align:center; font-weight:600;">Tidak ada aksi<br>untuk status ini</small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php endforeach; ?>

            <?php endif; ?>
        </div>

    </div>
</div>

<div id="modal-pinjam" class="modal">
    <div class="modal-content">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
            <h2 style="margin:0; color:#1e293b; font-weight:800;">📝 Form Peminjaman</h2>
            <span class="close-btn" onclick="closePinjamForm()" style="font-size:2rem; cursor:pointer;">&times;</span>
        </div>
        
        <div style="background:#f1f5f9; padding:15px; border-radius:12px; margin-bottom:25px; border:1px solid #e2e8f0;">
            <div style="font-size:0.9rem; color:#64748b; margin-bottom:5px;">Aset yang akan dipinjam:</div>
            <div style="font-size:1.2rem; font-weight:800; color:#1e293b;" id="pinjam_nama_aset"></div>
            <div style="margin-top:10px; font-weight:700; color:#16a34a;">✅ Tersedia: <span id="pinjam_stok_tersedia"></span> unit</div>
        </div>
        
        <form action="" method="POST">
            <input type="hidden" name="id_aset" id="pinjam_id_aset">
            
            <div class="form-group">
                <label>Jumlah Pinjam</label>
                <input type="number" name="jumlah_pinjam" id="jumlah_pinjam" required min="1" placeholder="Contoh: 1">
            </div>
            
            <div class="form-group">
                <label>Tanggal Mulai Pinjam</label>
                <input type="date" name="tanggal_pinjam" id="tgl_pinjam" required>
            </div>

            <div class="form-group">
                <label>Rencana Pengembalian</label>
                <input type="date" name="tanggal_kembali" id="tgl_kembali" required>
            </div>
            
            <button type="submit" name="ajukan_pinjam" class="btn btn-success" style="padding: 15px; font-size: 1.1rem; margin-top: 10px;">🚀 Kirim Pengajuan</button>
        </form>
    </div>
</div>

</body>
</html>
<?php mysqli_close($conn); ?>
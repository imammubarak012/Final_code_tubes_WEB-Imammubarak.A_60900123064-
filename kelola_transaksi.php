<?php 
// File: kelola_transaksi.php
// Deskripsi: Halaman Admin Modern (Fix Dropdown Z-Index)

session_start();
include 'config.php'; 

// 1. Cek Login & Role Admin
if(!isset($_SESSION['login']) || $_SESSION['role'] !== 'admin') {
    header("location: login.php");
    exit;
}
$nama_admin = $_SESSION['nama_lengkap'];

// 2. Logika Update / Delete / Return
if(isset($_GET['action']) && isset($_GET['id_pinjam'])){
    $id_pinjam = (int)$_GET['id_pinjam'];
    $action = $_GET['action'];
    
    $cek = mysqli_query($conn, "SELECT id_aset, jumlah_pinjam, status FROM peminjaman WHERE id_peminjaman=$id_pinjam");
    
    if(mysqli_num_rows($cek) == 0) {
        echo "<script>alert('Data tidak ditemukan');window.location='kelola_transaksi.php';</script>"; 
        exit;
    }
    
    $data_transaksi = mysqli_fetch_assoc($cek);
    $id_aset = $data_transaksi['id_aset'];
    $jml = $data_transaksi['jumlah_pinjam'];
    $status_db = $data_transaksi['status'] ?: 'Diajukan'; 

    $refresh = "<meta http-equiv='refresh' content='0; url=kelola_transaksi.php'>"; 

    if ($action == 'approve') {
        mysqli_query($conn, "UPDATE peminjaman SET status='Disetujui' WHERE id_peminjaman=$id_pinjam");
        echo "<script>alert('Berhasil Disetujui.');</script>";
        echo $refresh; exit;
    } elseif ($action == 'reject') {
        if ($status_db == 'Diajukan') { 
            mysqli_query($conn, "UPDATE aset SET stok_tersedia = stok_tersedia + $jml WHERE id_aset = $id_aset");
        }
        mysqli_query($conn, "UPDATE peminjaman SET status='Ditolak' WHERE id_peminjaman=$id_pinjam");
        echo "<script>alert('Berhasil Ditolak.');</script>";
        echo $refresh; exit;
    } elseif ($action == 'return') {
        if ($status_db === 'Disetujui') {
            $tgl = date('Y-m-d');
            mysqli_query($conn, "UPDATE aset SET stok_tersedia = stok_tersedia + $jml WHERE id_aset = $id_aset");
            mysqli_query($conn, "UPDATE peminjaman SET status='Selesai', tanggal_kembali='$tgl' WHERE id_peminjaman=$id_pinjam");
            echo "<script>alert('Aset dikembalikan (Selesai).');</script>";
        }
        echo $refresh; exit;
    } elseif ($action == 'delete') {
        if ($status_db == 'Diajukan' || $status_db == 'Disetujui') {
            mysqli_query($conn, "UPDATE aset SET stok_tersedia = stok_tersedia + $jml WHERE id_aset = $id_aset");
        }
        mysqli_query($conn, "DELETE FROM peminjaman WHERE id_peminjaman=$id_pinjam");
        echo "<script>alert('Data dihapus.');</script>";
        echo $refresh; exit;
    }
}

// 3. Ambil Data & Kelompokkan
$query_pinjam = mysqli_query($conn, 
    "SELECT p.*, a.nama_aset, pm.nama_lengkap AS peminjam_nama 
     FROM peminjaman p
     JOIN aset a ON p.id_aset = a.id_aset
     JOIN peminjam pm ON p.nama_peminjam = pm.nama_lengkap 
     ORDER BY p.tanggal_pinjam DESC");

$grouped_data = [
    'Diajukan' => [],
    'Disetujui' => [],
    'Selesai' => [],
    'Ditolak' => []
];

while($row = mysqli_fetch_array($query_pinjam)) {
    $st = trim($row['status']) ?: 'Diajukan';
    if(isset($grouped_data[$st])) {
        $grouped_data[$st][] = $row;
    } else {
        $grouped_data['Diajukan'][] = $row; 
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Transaksi - SIMAS</title>
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
        .section-heading { font-size:2rem; font-weight:800; margin-bottom:0.5rem; color: #1a1a1a; }
        .section-description { margin-bottom:2.5rem; color:#666; font-size: 1rem; }

        /* GROUP CONTAINER */
        .group-container {
            background: white; border-radius: 16px; margin-bottom: 40px; 
            /* REMOVED overflow: hidden to allow dropdown to show */
            box-shadow: 0 10px 30px rgba(0,0,0,0.04); border: 1px solid #eef0f2;
        }
        .group-header {
            padding: 15px 25px; font-size: 1.1rem; font-weight: 700; display: flex; justify-content: space-between; align-items: center; letter-spacing: 0.5px; text-transform: uppercase;
            border-top-left-radius: 16px; border-top-right-radius: 16px; /* Restore radius */
        }
        
        /* WARNA HEADER */
        .header-diajukan { background: #fff7ed; color: #ea580c; border-left: 6px solid #f97316; }
        .header-disetujui { background: #f0fdf4; color: #15803d; border-left: 6px solid #22c55e; }
        .header-selesai { background: #f8fafc; color: #475569; border-left: 6px solid #94a3b8; }
        .header-ditolak { background: #fef2f2; color: #b91c1c; border-left: 6px solid #ef4444; }

        .count-badge { background: rgba(0,0,0,0.08); padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 800; }
        .group-body { padding: 20px; display: flex; flex-direction: column; gap: 15px; }

        /* CARD ITEM */
        .card-item { 
            background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; 
            display: flex; justify-content: space-between; 
            position: relative; /* Context for dropdown */
            z-index: 1; /* Default lower z-index */
            transition: transform 0.2s ease, box-shadow 0.2s ease; 
            /* IMPORTANT: Allow dropdown to overflow */
            overflow: visible !important; 
        }
        .card-item:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(0,0,0,0.08); border-color: #d1d5db; }
        
        /* High Z-Index Class for Active Card */
        .card-item.z-active { 
            z-index: 1000 !important; 
            transform: none !important; /* Disable hover transform to keep menu stable */
            box-shadow: 0 15px 40px rgba(0,0,0,0.15) !important;
        }

        /* BORDER KIRI */
        .border-orange { border-left: 4px solid #f97316; }
        .border-green { border-left: 4px solid #22c55e; }
        .border-red { border-left: 4px solid #ef4444; }
        .border-gray { border-left: 4px solid #94a3b8; }

        .card-content { flex-grow:1; padding:20px; }
        .card-title { font-weight: 700; font-size: 1.15rem; color: #1f2937; display: flex; align-items: center; gap: 10px; margin-bottom: 8px;}
        
        .transaction-meta-grid { 
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 15px; 
            padding-top: 15px; border-top: 1px dashed #e5e7eb;
        }
        .meta-group { display: flex; flex-direction: column; }
        .meta-label { font-size: 0.7rem; font-weight: 700; color: #9ca3af; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.5px;}
        .meta-value { font-size: 0.95rem; font-weight: 600; color: #4b5563; }

        /* BADGE KECIL */
        .mini-badge { font-size: 0.75rem; padding: 4px 10px; border-radius: 30px; font-weight: 700; text-transform: uppercase; }
        .bg-orange { background: #fff7ed; color: #c2410c; }
        .bg-green { background: #f0fdf4; color: #15803d; }
        .bg-red { background: #fef2f2; color: #991b1b; }
        .bg-gray { background: #f8fafc; color: #475569; }

        /* DROPDOWN AKSI */
        .card-actions { 
            min-width: 100px; display: flex; align-items: center; justify-content: center; 
            padding: 15px; border-left: 1px solid #f3f4f6; background: #fcfcfc; border-radius: 0 12px 12px 0;
            position: relative; /* Context for dropdown positioning */
        }
        .dropdown { position:relative; width:100%; display: flex; justify-content: center;}
        
        .btn-icon-action { 
            background: white; color: #374151; border: 1px solid #d1d5db; 
            width: 36px; height: 36px; border-radius: 8px; cursor: pointer; 
            font-size: 1.2rem; display: flex; align-items: center; justify-content: center;
            transition: 0.2s;
        }
        .btn-icon-action:hover { background: #f3f4f6; border-color: #9ca3af; }
        
        .dropdown-content { 
            display:none; 
            position:absolute; 
            right:0; 
            top:100%; /* Position right below button */
            margin-top: 5px; 
            background:white; 
            flex-direction:column; 
            min-width:160px; 
            border-radius:8px; 
            box-shadow:0 10px 25px -5px rgba(0,0,0,0.15); 
            z-index: 9999; /* Ensure high z-index relative to card */
            border: 1px solid #e5e7eb; 
            overflow: hidden;
        }
        
        .dropdown-content.open { display:flex; animation: popIn 0.2s cubic-bezier(0.16, 1, 0.3, 1); }
        @keyframes popIn { from { opacity:0; transform:translateY(10px) scale(0.95); } to { opacity:1; transform:translateY(0) scale(1); } }

        .dropdown-content a { 
            padding:12px 15px; display:flex; align-items: center; gap: 10px;
            text-decoration:none; color:#4b5563; font-size: 0.9rem; 
            border-bottom: 1px solid #f9fafb; transition: 0.2s;
        }
        .dropdown-content a:hover { background:#f9fafb; color: #3f51b5; padding-left: 20px;}
        .dropdown-content a:last-child { border-bottom: none; }

        @media (max-width:768px){
            .dashboard-layout { grid-template-columns:1fr; }
            .transaction-meta-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
            .card-item { flex-direction:column; }
            .card-actions { min-width:100%; border-left:none; border-top:1px solid #eee; padding: 10px; border-radius: 0 0 12px 12px;}
            .dropdown { justify-content: flex-end; padding-right: 10px; }
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
                <li><a href="kelola_transaksi.php" class="active">🧾 Kelola Transaksi</a></li>
                <li><a href="manajemen_pengguna.php">👥 Manajemen Pengguna</a></li>
            </ul>
        </div>
        <div class="sidebar-footer">
            <a href="logout.php" class="btn-logout">🚪 LOGOUT</a>
        </div>
    </div>

    <div class="main-content">
        <h2 class="section-heading">Kelola Transaksi</h2>
        <p class="section-description">Kelola persetujuan dan pengembalian aset inventaris.</p>

        <?php 
        $has_data = false;
        foreach($grouped_data as $group_name => $transactions): 
            if(empty($transactions)) continue;
            $has_data = true;

            // Style Group Header Modern
            $header_class = '';
            $icon_group = '';
            if($group_name == 'Diajukan') { $header_class = 'header-diajukan'; $icon_group = '⏳'; }
            elseif($group_name == 'Disetujui') { $header_class = 'header-disetujui'; $icon_group = '✅'; }
            elseif($group_name == 'Selesai') { $header_class = 'header-selesai'; $icon_group = '🏁'; }
            elseif($group_name == 'Ditolak') { $header_class = 'header-ditolak'; $icon_group = '❌'; }
        ?>
        
        <div class="group-container">
            <div class="group-header <?php echo $header_class; ?>">
                <span><?php echo $icon_group . ' ' . $group_name; ?></span>
                <span class="count-badge"><?php echo count($transactions); ?> Item</span>
            </div>

            <div class="group-body">
                <?php foreach($transactions as $data_pinjam): 
                    $status = trim($data_pinjam['status']) ?: 'Diajukan';

                    // Style Card
                    $border_class = 'border-gray';
                    $badge_mini_class = 'bg-gray';

                    if($status == 'Diajukan') { $border_class = 'border-orange'; $badge_mini_class = 'bg-orange'; }
                    elseif($status == 'Disetujui') { $border_class = 'border-green'; $badge_mini_class = 'bg-green'; }
                    elseif($status == 'Ditolak') { $border_class = 'border-red'; $badge_mini_class = 'bg-red'; }

                    $tgl_kembali_tampil = $data_pinjam['tanggal_kembali'] ?: '-';
                ?>
                
                <div class="card-item <?php echo $border_class; ?>">
                    <div class="card-content">
                        <div class="card-title">
                            <?php echo htmlspecialchars($data_pinjam['nama_aset']); ?> 
                            <span style="font-weight:400; color:#666; font-size:0.9rem; margin-left:5px;">(<?php echo $data_pinjam['jumlah_pinjam']; ?> Unit)</span>
                            <span class="mini-badge <?php echo $badge_mini_class; ?>" style="margin-left:auto;"><?php echo $status; ?></span>
                        </div>
                        
                        <div class="transaction-meta-grid">
                            <div class="meta-group"><span class="meta-label">Peminjam</span><span class="meta-value"><?php echo htmlspecialchars($data_pinjam['peminjam_nama']); ?></span></div>
                            <div class="meta-group"><span class="meta-label">Tgl Pinjam</span><span class="meta-value"><?php echo date('d M Y', strtotime($data_pinjam['tanggal_pinjam'])); ?></span></div>
                            <div class="meta-group"><span class="meta-label">Rencana Kembali</span><span class="meta-value"><?php echo ($tgl_kembali_tampil != '-') ? date('d M Y', strtotime($tgl_kembali_tampil)) : '-'; ?></span></div>
                            <div class="meta-group"><span class="meta-label">ID Tiket</span><span class="meta-value">#<?php echo $data_pinjam['id_peminjaman']; ?></span></div>
                        </div>
                    </div>

                    <div class="card-actions">
                        <div class="dropdown">
                            <button class="btn-icon-action" onclick="toggleDropdown(this)">⋮</button>
                            
                            <div class="dropdown-content">
                                <?php if($status == 'Diajukan'): ?>
                                    <a href="?action=approve&id_pinjam=<?php echo $data_pinjam['id_peminjaman']; ?>">
                                        <span style="color:green">✔</span> Setujui
                                    </a>
                                    <a href="?action=reject&id_pinjam=<?php echo $data_pinjam['id_peminjaman']; ?>" onclick="return confirm('Tolak? Stok kembali.')">
                                        <span style="color:orange">✖</span> Tolak
                                    </a>
                                <?php endif; ?>

                                <?php if($status == 'Disetujui'): ?>
                                    <a href="?action=return&id_pinjam=<?php echo $data_pinjam['id_peminjaman']; ?>" onclick="return confirm('Konfirmasi kembali?')">
                                        <span style="color:blue">↩</span> Kembalikan
                                    </a>
                                <?php endif; ?>

                                <a href="?action=delete&id_pinjam=<?php echo $data_pinjam['id_peminjaman']; ?>" onclick="return confirm('Hapus history permanen?')" style="color:red;">
                                    <span>🗑</span> Hapus
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <?php endforeach; ?>
        
        <?php if(!$has_data): ?>
            <div style="text-align:center; padding:50px; background:white; border-radius:16px; color:#888;">
                <p>Tidak ada transaksi peminjaman yang tercatat saat ini.</p>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
function toggleDropdown(btn) {
    event.stopPropagation();
    const dropdown = btn.closest('.dropdown');
    const menuContent = dropdown.querySelector('.dropdown-content');
    const cardItem = btn.closest('.card-item');

    const isOpen = menuContent.classList.contains('open');

    // Reset semua dropdown
    document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('open'));
    // Reset z-index untuk semua card
    document.querySelectorAll('.card-item').forEach(c => c.classList.remove('z-active'));

    if (!isOpen) {
        menuContent.classList.add('open');
        if(cardItem) cardItem.classList.add('z-active');
    }
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('.dropdown')) {
        document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('open'));
        document.querySelectorAll('.card-item').forEach(c => c.classList.remove('z-active'));
    }
});
</script>

</body>
</html>

<?php mysqli_close($conn); ?>
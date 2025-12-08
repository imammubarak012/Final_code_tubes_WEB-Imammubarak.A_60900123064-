<?php 
// File login.php: Halaman Login Modern
session_start();

// Jika sudah login, redirect sesuai role
if(isset($_SESSION['login']) && $_SESSION['login'] === 'aktif') {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("location: manajemen_simas.php"); 
    } elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'user') {
        header("location: dashboard_user.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SIMAS</title>
    <style>
        /* RESET & BASE */
        body { 
            margin: 0; padding: 0; 
            font-family: 'Segoe UI', Arial, sans-serif; 
            /* Background Image dengan Overlay Modern */
            background: linear-gradient(rgba(30, 41, 59, 0.6), rgba(30, 41, 59, 0.6)), 
                        url('https://images.unsplash.com/photo-1510511459019-5beef1451c22?q=80&w=1974&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        * { box-sizing: border-box; }

        /* CARD LOGIN */
        .auth-box {
            background: rgba(255, 255, 255, 0.95); /* Sedikit transparan */
            padding: 40px;
            width: 100%;
            max-width: 420px;
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* LOGO BRANDING */
        .brand-logo {
            width: 50px;
            height: 50px;
            color: #3f51b5; /* Warna Utama */
            margin-bottom: 10px;
            filter: drop-shadow(0 4px 6px rgba(63, 81, 181, 0.3));
        }

        .auth-box h2 {
            color: #1e293b;
            font-size: 1.8rem;
            font-weight: 800;
            margin: 0 0 5px 0;
            letter-spacing: 1px;
        }
        
        .auth-subtitle {
            color: #64748b;
            font-size: 0.95rem;
            margin-bottom: 30px;
        }

        /* FORM STYLES (Sama dengan Dashboard) */
        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
            text-align: left;
        }
        
        label {
            font-weight: 700;
            color: #334155;
            font-size: 0.9rem;
            margin-bottom: 5px;
            display: block;
        }
        
        input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: 0.3s;
            background: #f8fafc;
            color: #333;
        }
        
        input:focus {
            border-color: #3f51b5;
            background: #fff;
            outline: none;
            box-shadow: 0 0 0 4px rgba(63, 81, 181, 0.1);
        }

        /* TOMBOL LOGIN */
        .btn-login {
            background: linear-gradient(to right, #3f51b5, #303f9f);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 4px 15px rgba(63, 81, 181, 0.3);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(63, 81, 181, 0.4);
        }

        /* LINK FOOTER */
        .auth-footer {
            margin-top: 25px;
            font-size: 0.9rem;
            color: #64748b;
        }
        
        .btn-link {
            color: #3f51b5;
            font-weight: 700;
            text-decoration: none;
            transition: 0.3s;
        }
        
        .btn-link:hover {
            color: #303f9f;
            text-decoration: underline;
        }

        /* TOMBOL KEMBALI KE HOME */
        .home-button {
            position: absolute;
            top: 30px;
            right: 30px;
            text-decoration: none;
            color: white;
            background: rgba(255, 255, 255, 0.1);
            padding: 10px 20px;
            border-radius: 30px;
            font-weight: 600;
            font-size: 0.9rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: 0.3s;
            backdrop-filter: blur(5px);
        }
        .home-button:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        /* ALERT ERROR */
        .alert-error {
            background: #fef2f2;
            color: #ef4444;
            padding: 12px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            border: 1px solid #fecaca;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        /* RESPONSIVE */
        @media (max-width: 480px) {
            .auth-box {
                padding: 30px 20px;
                width: 90%;
            }
            .home-button {
                top: 20px;
                right: 20px;
                padding: 8px 15px;
            }
        }
    </style>
</head>
<body>

    <a href="home.php" class="home-button">← Kembali ke Home</a>

    <div class="auth-box">
        
        <svg class="brand-logo" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12.378 1.602a.75.75 0 00-.756 0L3 6.632l9 5.25 9-5.25-8.622-5.03zM21.75 7.93l-9 5.25v9l8.628-5.032a.75.75 0 00.372-.648V7.93zM11.25 22.18v-9l-9-5.25v8.57a.75.75 0 00.372.648l8.628 5.033z" />
        </svg>

        <h2>LOGIN SIMAS</h2>
        <p class="auth-subtitle">Sistem Manajemen Aset</p>
        
        <?php if(isset($_GET['pesan']) && $_GET['pesan'] == "gagal"): ?>
            <div class="alert-error">
                <span>⚠️</span> Username atau Password salah!
            </div>
        <?php endif; ?>

        <form action="ceklogin.php" method="POST">
            <div>
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Masukkan username Anda" required>
            </div>
            
            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password Anda" required>
            </div>
            
            <button type="submit" class="btn-login">Masuk Sekarang</button>
        </form>

        <div class="auth-footer">
            Belum punya akun peminjam? 
            <a href="register.php" class="btn-link">Daftar di sini</a>
        </div>
    </div>

</body>
</html>
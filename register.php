<?php
// File register.php: Pendaftaran Akun User Modern
session_start();
include 'config.php';

// Jika user sudah login, arahkan ke dashboard yang sesuai
if(isset($_SESSION['login'])) {
    if ($_SESSION['role'] === 'admin') {
        header("location: manajemen_simas.php"); 
    } else {
        header("location: dashboard_user.php");
    }
    exit;
}

if(isset($_POST['register'])){
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $hp = mysqli_real_escape_string($conn, $_POST['nomor_hp']);
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);

    // Validasi Server-Side Tambahan (Keamanan Ganda)
    if (!is_numeric($hp)) {
        echo "<script>alert('Gagal! Nomor HP harus berupa angka.')</script>";
    } elseif (strlen($nik) !== 16 || !is_numeric($nik)) {
        echo "<script>alert('Gagal! NIK harus terdiri dari 16 digit angka.')</script>";
    } else {
        $sql = "INSERT INTO peminjam (username, password, nama_lengkap, nomor_hp, nik) 
                VALUES ('$username', '$password', '$nama', '$hp', '$nik')";
        
        // Cek error insert (misal username/nik duplikat)
        try {
            if(mysqli_query($conn, $sql)) {
                echo "<script>alert('Pendaftaran berhasil! Silahkan login.')</script>";
                echo "<meta http-equiv='refresh' content='0; url=login.php'>";
            } else {
                throw new Exception(mysqli_error($conn));
            }
        } catch (Exception $e) {
            echo "<script>alert('Pendaftaran gagal. Username atau NIK mungkin sudah terdaftar.')</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - SIMAS</title>
    <style>
        /* RESET & BASE */
        body { 
            margin: 0; padding: 0; 
            font-family: 'Segoe UI', Arial, sans-serif; 
            background: linear-gradient(rgba(30, 41, 59, 0.7), rgba(30, 41, 59, 0.7)), 
                        url('https://images.unsplash.com/photo-1510511459019-5beef1451c22?q=80&w=1974&auto=format&fit=crop');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        * { box-sizing: border-box; }

        /* CARD REGISTER */
        .auth-box {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            width: 100%;
            max-width: 480px; 
            border-radius: 16px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            text-align: center;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            animation: slideUp 0.5s ease-out;
            margin: 20px;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* LOGO BRANDING */
        .brand-logo {
            width: 45px; height: 45px; color: #3f51b5; margin-bottom: 10px;
            filter: drop-shadow(0 4px 6px rgba(63, 81, 181, 0.3));
        }

        .auth-box h2 {
            color: #1e293b; font-size: 1.8rem; font-weight: 800; margin: 0 0 5px 0; letter-spacing: 1px;
        }
        
        .auth-subtitle {
            color: #64748b; font-size: 0.95rem; margin-bottom: 25px;
        }

        /* FORM STYLES */
        form { display: flex; flex-direction: column; gap: 15px; text-align: left; }
        
        label {
            font-weight: 700; color: #334155; font-size: 0.85rem; margin-bottom: 4px; display: block;
        }
        
        input {
            width: 100%; padding: 12px 15px; border: 2px solid #e2e8f0; border-radius: 10px;
            font-size: 0.95rem; transition: 0.3s; background: #f8fafc; color: #333;
        }
        
        input:focus {
            border-color: #3f51b5; background: #fff; outline: none;
            box-shadow: 0 0 0 4px rgba(63, 81, 181, 0.1);
        }

        /* Validasi input error (merah jika invalid) */
        input:invalid:not(:placeholder-shown) {
            border-color: #ef4444;
            background-color: #fef2f2;
        }

        /* TOMBOL REGISTER */
        .btn-register {
            background: linear-gradient(to right, #3f51b5, #303f9f);
            color: white; padding: 14px; border: none; border-radius: 10px;
            font-size: 1rem; font-weight: 700; cursor: pointer; transition: 0.3s;
            box-shadow: 0 4px 15px rgba(63, 81, 181, 0.3); text-transform: uppercase;
            letter-spacing: 1px; margin-top: 10px;
        }
        
        .btn-register:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(63, 81, 181, 0.4); }

        /* LINK FOOTER */
        .auth-footer { margin-top: 25px; font-size: 0.9rem; color: #64748b; }
        
        .btn-link { color: #3f51b5; font-weight: 700; text-decoration: none; transition: 0.3s; }
        .btn-link:hover { color: #303f9f; text-decoration: underline; }

        /* TOMBOL KEMBALI KE HOME */
        .home-button {
            position: absolute; top: 30px; right: 30px; text-decoration: none;
            color: white; background: rgba(255, 255, 255, 0.1); padding: 10px 20px;
            border-radius: 30px; font-weight: 600; font-size: 0.9rem;
            border: 1px solid rgba(255, 255, 255, 0.2); transition: 0.3s;
            backdrop-filter: blur(5px);
        }
        .home-button:hover { background: rgba(255, 255, 255, 0.2); transform: translateY(-2px); }

        /* RESPONSIVE */
        @media (max-width: 480px) {
            .auth-box { padding: 30px 20px; width: 90%; margin-top: 60px;}
            .home-button { top: 20px; right: 20px; padding: 8px 15px; }
        }
    </style>
    
    <script>
        // Script Tambahan untuk Memastikan Input Angka Saja
        function hanyaAngka(evt) {
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

    <a href="home.php" class="home-button">← Kembali ke Home</a>

    <div class="auth-box">
        
        <svg class="brand-logo" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
          <path d="M12.378 1.602a.75.75 0 00-.756 0L3 6.632l9 5.25 9-5.25-8.622-5.03zM21.75 7.93l-9 5.25v9l8.628-5.032a.75.75 0 00.372-.648V7.93zM11.25 22.18v-9l-9-5.25v8.57a.75.75 0 00.372.648l8.628 5.033z" />
        </svg>

        <h2>DAFTAR AKUN</h2>
        <p class="auth-subtitle">Bergabung dengan SIMAS sekarang</p>
        
        <form action="register.php" method="POST">
            <div>
                <label>Username</label>
                <input type="text" name="username" placeholder="Buat username unik" required>
            </div>
            
            <div>
                <label>Password</label>
                <input type="password" name="password" placeholder="Buat password yang kuat" required>
            </div>

            <div>
                <label>Nama Lengkap</label>
                <input type="text" name="nama_lengkap" placeholder="Sesuai kartu identitas" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <label>Nomor HP/WA</label>
                    <input type="text" name="nomor_hp" placeholder="08xxxxxxxxxx" 
                           onkeypress="return hanyaAngka(event)" 
                           pattern="[0-9]+" title="Hanya boleh angka" required>
                </div>
                <div>
                    <label>NIK</label>
                    <input type="text" name="nik" placeholder="16 Digit NIK" 
                           onkeypress="return hanyaAngka(event)" 
                           minlength="16" maxlength="16" pattern="\d{16}" 
                           title="NIK harus tepat 16 digit angka" required>
                </div>
            </div>
            
            <button type="submit" name="register" class="btn-register">Daftar Sekarang</button>
        </form>

        <div class="auth-footer">
            Sudah punya akun? 
            <a href="login.php" class="btn-link">Login di sini</a>
        </div>
    </div>

</body>
</html>
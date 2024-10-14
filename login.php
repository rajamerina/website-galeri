<?php
session_start();
require_once("config/koneksi.php"); // Menghubungkan ke database

// Memeriksa apakah pengguna sudah login
if (isset($_SESSION['username']) && isset($_SESSION['userId'])) {
    // Jika sudah login, arahkan ke halaman sesuai role
    header('Location: index.php'); // Arahkan user ke homepage
    exit();
}

// Memproses form saat dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validasi input
    if (!empty($username) && !empty($password)) {
        // Mencari pengguna di database
        $sql = "SELECT * FROM user WHERE Username = :username"; // Mencari berdasarkan username
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        
        // Memeriksa apakah pengguna ditemukan
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC); // Dapatkan data pengguna

            // Memverifikasi password
            if (password_verify($password, $user['Password'])) {
                // Menyimpan informasi pengguna ke sesi
                $_SESSION['username'] = $user['Username'];
                $_SESSION['userId'] = $user['UserID']; // Simpan userId
                $_SESSION['role'] = $user['role']; // Simpan role pengguna

                // Arahkan ke halaman berdasarkan role
                if ($user['role'] === 'admin') {
                    header('Location: dashboard_admin.php'); // Arahkan admin ke dashboard admin
                } else {
                    header('Location: index.php'); // Arahkan user ke homepage
                }
                exit();
            } else {
                $error_message = "Password salah.";
            }
        } else {
            $error_message = "Pengguna tidak ditemukan.";
        }
    } else {
        $error_message = "Silakan masukkan username dan password.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        /* Gaya CSS untuk halaman login */
        body {
            font-family: Arial, sans-serif;
            background-color: #e0f7fa; /* Warna laut */
            color: #004d40; /* Warna teks */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0; /* Menghilangkan margin default */
        }

        .login-form {
            background-color: #ffffff;
            padding: 30px; /* Tambahkan padding untuk ruang lebih */
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 300px; /* Lebar form yang konsisten */
        }

        .login-form h2 {
            margin-bottom: 20px;
            color: #00695c;
            text-align: center; /* Pusatkan teks judul */
        }

        .login-form label {
            display: block; /* Tampilkan label sebagai blok untuk tata letak yang rapi */
            margin-bottom: 5px; /* Jarak bawah label */
            font-weight: bold; /* Menebalkan teks label */
        }

        .login-form input[type="text"],
        .login-form input[type="password"] {
            width: 90%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #00695c;
            border-radius: 4px;
            font-size: 14px; /* Ukuran font input yang lebih kecil */
        }

        .login-form button {
            width: 100%;
            padding: 10px;
            background-color: #00695c;
            border: none;
            border-radius: 4px;
            color: #ffffff;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s; /* Transisi halus saat hover */
        }

        .login-form button:hover {
            background-color: #004d40;
        }

        .error-message {
            color: red;
            margin-bottom: 15px;
            text-align: center; /* Pusatkan pesan error */
        }
    </style>
</head>
<body>
    <form class="login-form" action="" method="post">
        <h2>Login</h2>
        <?php if (isset($error_message)): ?>
            <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
        <p>Belum punya akun? <a href="register.php">Registrasi sekarang</a></p>
    </form>
</body>
</html>

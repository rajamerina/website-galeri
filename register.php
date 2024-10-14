<?php
// Koneksi ke database
include 'config/koneksi.php';

$message = ""; // Variabel untuk menyimpan pesan

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $username = $_POST['username'];
    $email = $_POST['email'];
    $namaLengkap = $_POST['namaLengkap'];
    $alamat = $_POST['alamat'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password
    $role = $_POST['role'];

    // Query untuk memeriksa apakah username atau email sudah ada
    $checkSql = "SELECT * FROM user WHERE Username = :username OR Email = :email";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindParam(':username', $username);
    $checkStmt->bindParam(':email', $email);
    $checkStmt->execute();

    if ($checkStmt->rowCount() > 0) {
        // Jika username atau email sudah ada
        $message = "<div class='error-message'>Username atau Email sudah terdaftar!</div>";
    } else {
        // Jika tidak ada duplikasi, lakukan insert
        $sql = "INSERT INTO user (Username, Email, NamaLengkap, Password, Alamat, role)
                VALUES (:username, :email, :namaLengkap, :password, :alamat, :role)";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':namaLengkap', $namaLengkap);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':alamat', $alamat);
        $stmt->bindParam(':role', $role);

        if ($stmt->execute()) {
            $message = "<div class='success-message'>Registrasi berhasil!</div>";
        } else {
            $message = "<div class='error-message'>Terjadi kesalahan saat registrasi.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Page</title>
    <style>
        /* Gaya CSS untuk halaman register */
        body {
            font-family: Arial, sans-serif;
            background-color: #e0f7fa;
            color: #004d40;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .register-form {
            background-color: #ffffff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 250px;
        }

        .register-form h2 {
            margin-bottom: 15px;
            color: #00695c;
            text-align: center;
            font-size: 18px;
        }

        .register-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 12px;
        }

        .register-form input[type="text"],
        .register-form input[type="email"],
        .register-form input[type="password"],
        .register-form select {
            width: 90%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #00695c;
            border-radius: 4px;
            font-size: 12px;
        }

        .register-form button {
            width: 100%;
            padding: 8px;
            background-color: #00695c;
            border: none;
            border-radius: 4px;
            color: #ffffff;
            font-size: 14px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .register-form button:hover {
            background-color: #004d40;
        }

        .error-message,
        .success-message {
            color: red;
            margin-bottom: 15px;
            text-align: center;
        }

        .success-message {
            color: green;
        }

        .role-selection {
            margin: 10px 0;
        }

        .login-link {
            text-align: center;
            margin-top: 10px;
            font-size: 12px;
        }

        .login-link a {
            color: #00695c;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="register-form">
    <h2>Registrasi</h2>
    <form action="" method="POST">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="namaLengkap">Nama Lengkap</label>
        <input type="text" id="namaLengkap" name="namaLengkap" required>

        <label for="alamat">Alamat</label>
        <input type="text" id="alamat" name="alamat" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <label for="role">Role</label>
        <select id="role" name="role" required>
            <option value="user">User</option>
            <option value="admin">Admin</option>
        </select>

        <button type="submit">Register</button>
    </form>

    <!-- Notifikasi ditampilkan di sini -->
    <?php echo $message; ?>

    <div class="login-link">
        Sudah punya akun? <a href="login.php">Login di sini</a>
    </div>
</div>

</body>
</html>

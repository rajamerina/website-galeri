<?php
session_start();
// Menghubungkan dengan database dan mengambil daftar album dan foto
require_once("config/koneksi.php");

// Fungsi untuk memeriksa apakah pengguna sudah login dan merupakan admin
function checkAdminLoggedIn() {
    // Periksa apakah user sudah login
    if (!isset($_SESSION["userId"])) {
        header("Location: login.php"); // Arahkan ke halaman login jika belum login
        exit;
    }

    // Pastikan hanya admin yang bisa mengakses
    if ($_SESSION['role'] !== 'admin') {
        header('Location: index.php'); // Redirect ke halaman index jika bukan admin
        exit;
    }
}

// Panggil fungsi untuk memeriksa login dan role admin
checkAdminLoggedIn();

// Inisialisasi variabel pencarian
$searchQuery = isset($_GET['search']) ? $_GET['search'] : '';

// Query untuk mengambil daftar album berdasarkan pencarian
$sqlAlbum = "SELECT AlbumID, NamaAlbum, Deskripsi, TanggalDibuat, UserID, CoverPhoto 
             FROM album 
             WHERE NamaAlbum LIKE :searchQuery OR AlbumID LIKE :searchQuery";
$stmtAlbum = $conn->prepare($sqlAlbum);
$searchParam = '%' . $searchQuery . '%';
$stmtAlbum->bindParam(':searchQuery', $searchParam);
$stmtAlbum->execute();
$albums = $stmtAlbum->fetchAll(PDO::FETCH_ASSOC);

// Query untuk mengambil daftar foto berdasarkan pencarian
$sqlFoto = "SELECT * FROM foto WHERE JudulFoto LIKE :searchQuery OR FotoID LIKE :searchQuery OR AlbumID LIKE :searchQuery";
$stmtFoto = $conn->prepare($sqlFoto);
$stmtFoto->bindParam(':searchQuery', $searchParam);
$stmtFoto->execute();
$fotos = $stmtFoto->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            padding: 20px;
        }

        .navbar {
            background-color: #00796b;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 30px;
            color: white;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin-right: 20px;
        }

        h1 {
            color: #00796b;
        }

        .section {
            margin-bottom: 50px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ccc;
            padding: 10px;
        }

        th {
            background-color: #00796b;
            color: white;
        }

        td {
            text-align: center;
        }

        a.action-btn {
            padding: 6px 12px;
            margin-right: 10px;
            background-color: #00796b;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        a.action-btn:hover {
            background-color: #004d40;
        }

        .search-form {
            margin-bottom: 20px;
        }

        .search-form input[type="text"] {
            width: 300px;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        .search-form button {
            padding: 8px 15px;
            background-color: #00796b;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .search-form button:hover {
            background-color: #004d40;
        }
    </style>
</head>
<body>

    <div class="navbar">
        <a href="index.php">Beranda</a>
        <a href="logout.php">Logout</a>
    </div>

    <h1>Dashboard Admin</h1>

    <!-- Form Pencarian -->
    <div class="search-form">
        <form action="" method="GET">
            <input type="text" name="search" placeholder="Cari berdasarkan ID Album, ID Foto, Judul Foto, atau Nama Album" value="<?php echo htmlspecialchars($searchQuery); ?>">
            <button type="submit">Cari</button>
        </form>
    </div>

    <!-- Bagian Kelola Album -->
    <div class="section">
        <h2>Kelola Album</h2>
        <a href="tambah_album.php" class="action-btn">Tambah Album</a>
       
        <table>
            <thead>
                <tr>
                    <th>ID Album</th>
                    <th>Nama Album</th>
                    <th>Deskripsi</th>
                    <th>Tanggal Dibuat</th>
                    <th>User ID</th>
                    <th>Foto Sampul</th> <!-- New column for Cover Photo -->
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($albums) > 0): ?>
                    <?php foreach ($albums as $album): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($album['AlbumID']); ?></td>
                            <td><?php echo htmlspecialchars($album['NamaAlbum']); ?></td>
                            <td><?php echo htmlspecialchars($album['Deskripsi']); ?></td>
                            <td><?php echo htmlspecialchars($album['TanggalDibuat']); ?></td>
                            <td><?php echo htmlspecialchars($album['UserID']); ?></td>
                            <td>
                                <?php if (!empty($album['CoverPhoto'])): ?>
                                    <img src="<?php echo htmlspecialchars($album['CoverPhoto']); ?>" alt="Cover Photo" style="width: 100px; height: auto;"> <!-- Display Cover Photo -->
                                <?php else: ?>
                                    Tidak ada foto
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="edit_album.php?id=<?php echo $album['AlbumID']; ?>" class="action-btn">Edit</a>
                                <a href="delete_album.php?id=<?php echo $album['AlbumID']; ?>" class="action-btn" onclick="return confirm('Apakah Anda yakin ingin menghapus album ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">Tidak ada album yang ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Bagian Kelola Foto -->
    <div class="section">
        <h2>Kelola Foto</h2>
        <a href="tambah_foto.php" class="action-btn">Tambah Foto</a>
        <table>
            <thead>
                <tr>
                    <th>ID Foto</th>
                    <th>Judul Foto</th>
                    <th>Deskripsi</th>
                    <th>Album ID</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($fotos) > 0): ?>
                    <?php foreach ($fotos as $foto): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($foto['FotoID']); ?></td>
                            <td><?php echo htmlspecialchars($foto['JudulFoto']); ?></td>
                            <td><?php echo htmlspecialchars($foto['DeskripsiFoto']); ?></td>
                            <td><?php echo htmlspecialchars($foto['AlbumID']); ?></td>
                            <td>
                                <a href="edit_foto.php?id=<?php echo $foto['FotoID']; ?>" class="action-btn">Edit</a>
                                <a href="delete_foto.php?id=<?php echo $foto['FotoID']; ?>" class="action-btn" onclick="return confirm('Apakah Anda yakin ingin menghapus foto ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">Tidak ada foto yang ditemukan.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>

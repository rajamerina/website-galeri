<?php
session_start(); // Mulai sesi

require_once("config/koneksi.php");

// Cek apakah pengguna login atau tidak
$is_logged_in = isset($_SESSION['userId']);
$userId = $is_logged_in ? $_SESSION['userId'] : null;

// Variabel untuk query pencarian
$searchQuery = '';
if (isset($_POST['search'])) {
    $searchQuery = trim($_POST['search']);
}

// SQL untuk mengambil semua album, termasuk foto sampul
$sql = "SELECT AlbumID, NamaAlbum, Deskripsi, TanggalDibuat, CoverPhoto FROM album WHERE NamaAlbum LIKE :searchQuery";
$stmt = $conn->prepare($sql);
$stmt->bindValue(':searchQuery', '%' . $searchQuery . '%', PDO::PARAM_STR);
$stmt->execute();
$albums = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Menyiapkan foto untuk setiap album
$photosByAlbum = [];
foreach ($albums as $album) {
    $sqlPhotos = "SELECT FotoID, JudulFoto FROM foto WHERE AlbumID = :albumId LIMIT 1"; // Mengambil 1 foto dari setiap album
    $stmtPhotos = $conn->prepare($sqlPhotos);
    $stmtPhotos->bindValue(':albumId', $album['AlbumID'], PDO::PARAM_INT);
    $stmtPhotos->execute();
    $photosByAlbum[$album['AlbumID']] = $stmtPhotos->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Add Font Awesome -->
    <style>
        /* Styling sama seperti sebelumnya */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; background-color: #e0f7fa; color: #00695c; display: flex; flex-direction: column; align-items: center; padding: 20px; }
        nav { width: 100%; border-radius: 40px; background-color: #00796b; padding: 10px 0; display: flex; justify-content: space-between; align-items: center; }
        .logo { color: #ffffff; font-size: 24px; font-weight: bold; margin-left: 20px; text-decoration: none; }
        .nav-links { list-style-type: none; display: flex; margin-right: 20px; }
        .nav-links li { margin-left: 20px; }
        .nav-links a { color: #ffffff; text-decoration: none; padding: 10px 15px; border-radius: 4px; }
        .nav-links a:hover { background-color: #004d40; }
        h1 { margin-top: 20px; color: #00796b; }
        h2 { margin: 20px 0; color: #00796b; }
        .search-form { margin-bottom: 20px; display: flex; justify-content: center; }
        .search-form input[type="text"] { padding: 10px; width: 300px; border: 1px solid #c8e6c9; border-radius: 4px; margin-right: 10px; }
        .search-form input[type="submit"] { padding: 10px 15px; background-color: #009688; color: #ffffff; border: none; border-radius: 4px; cursor: pointer; }
        .search-form input[type="submit"]:hover { background-color: #00796b; }
        .container { width: 100%; max-width: 1000px; margin-top: 20px; }
        .album-list { display: flex; flex-wrap: wrap; gap: 20px; justify-content: center; }

        /* Modern album card styles */
        .album { 
            flex: 1 0 calc(25% - 20px); 
            cursor: pointer; 
            padding: 20px; 
            border-radius: 8px; 
            background-color: #ffffff; 
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); 
            transition: transform 0.3s ease, box-shadow 0.3s ease; 
            position: relative; 
            overflow: hidden; 
        }

        .album:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2); 
        }

        .album-icon { 
            margin-right: 8px; 
            font-size: 24px; 
            color: #00796b; 
        }

        .album h3 { 
            font-size: 1.2em; 
            margin: 10px 0; 
        }

        .album p { 
            margin: 5px 0; 
            font-size: 0.9em; 
            color: #555; 
        }

        .album .date {
            font-size: 0.8em; 
            color: #999; 
            position: absolute; 
            bottom: 10px; 
            right: 10px; 
        }

        /* Styling untuk tampilan foto */
        .photos { 
            display: flex; 
            flex-wrap: wrap; 
            justify-content: space-around; 
            margin-top: 20px; 
        }

        .photo { 
            width: calc(30% - 20px); 
            margin: 10px; 
            border-radius: 8px; 
            overflow: hidden; 
            position: relative; 
            background-color: #fff; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); 
        }

        .photo img {
            width: 100%; 
            height: auto; 
            display: block; 
        }

        .photo-title { 
            padding: 10px; 
            font-weight: bold; 
            text-align: center; 
        }
    </style>
</head>
<body>
    <nav>
        <a href="#" class="logo">Galeri Foto</a>
        <ul class="nav-links">
            <?php if ($is_logged_in): ?>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li><a href="dashboard_admin.php">Dashboard</a></li> <!-- Tambahkan tautan dashboard untuk admin -->
                <?php endif; ?>
                <li><a href="tambah_album.php">Tambah Album</a></li>
            <?php endif; ?>
            <li><a href="<?php echo $is_logged_in ? 'logout.php' : 'login.php'; ?>">
                <?php echo $is_logged_in ? 'Logout' : 'Login'; ?>
            </a></li>
        </ul>
    </nav>

    <h1>Selamat Datang di Galeri Foto</h1>
    <?php if ($is_logged_in): ?>
        <p>Halo, <?php echo htmlspecialchars($_SESSION['username']); ?>! Anda telah login.</p>
    <?php else: ?>
        <p>Anda belum login. <a href="login.php">Login</a> untuk menambah album.</p>
    <?php endif; ?>

    <h2>Daftar Album</h2>
    <form action="" method="post" class="search-form">
        <input type="text" name="search" placeholder="Cari album..." value="<?php echo htmlspecialchars($searchQuery); ?>">
        <input type="submit" value="Cari">
    </form>

    <div class="container">
        <?php if (count($albums) > 0): ?>
            <div class="album-list">
                <?php foreach ($albums as $album): ?>
                    <div class="album" onclick="window.location.href='daftar_foto.php?album_id=<?php echo $album['AlbumID']; ?>'">
                        <i class="fas fa-camera album-icon"></i>
                        <h3><?php echo htmlspecialchars($album['NamaAlbum']); ?></h3>
                        <p>Deskripsi: <?php echo htmlspecialchars($album['Deskripsi']); ?></p>
                        <p class="date">Tanggal Dibuat: <?php echo htmlspecialchars($album['TanggalDibuat']); ?></p>
                        <?php if (!empty($album['CoverPhoto'])): ?>
                            <img src="<?php echo htmlspecialchars($album['CoverPhoto']); ?>" alt="Cover Photo" style="width: 100%; height: auto; border-radius: 8px;">
                        <?php else: ?>
                            <p>Tidak ada foto sampul</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Tidak ada album yang ditemukan.</p>
        <?php endif; ?>
    </div>
</body>
</html>

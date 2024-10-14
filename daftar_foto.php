<?php
session_start(); // Mulai sesi

// Memeriksa apakah pengguna telah login
if (!isset($_SESSION['username']) || !isset($_SESSION['userId'])) {
    header('Location: login.php');
    exit();
}

require_once("config/koneksi.php"); // Pastikan file ini mengandung koneksi yang benar

// Memeriksa apakah album_id dikirim melalui URL
if (!isset($_GET['album_id'])) {
    header('Location: index.php');
    exit();
}

$albumId = intval($_GET['album_id']); // Mengambil album_id dari URL

// Inisialisasi variabel fotos sebagai array kosong
$fotos = [];

// Mengambil foto dari database berdasarkan album_id
$sql = "SELECT * FROM foto WHERE AlbumID = :albumId";
$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bindValue(':albumId', $albumId, PDO::PARAM_INT);
    $stmt->execute();
    $fotos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    // Jika query gagal, tampilkan pesan error
    echo "Terjadi kesalahan dalam mengambil data foto.";
    exit();
}

// Proses untuk menambahkan like
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like'])) {
    $fotoId = intval($_POST['foto_id']);
    $userId = $_SESSION['userId']; // Ambil UserID dari sesi

    // Memeriksa apakah pengguna sudah menyukai foto ini
    $sqlCheck = "SELECT * FROM likefoto WHERE FotoID = :fotoId AND UserID = :userId";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bindValue(':fotoId', $fotoId, PDO::PARAM_INT);
    $stmtCheck->bindValue(':userId', $userId, PDO::PARAM_INT);
    $stmtCheck->execute();

    if ($stmtCheck->rowCount() > 0) {
        // Jika sudah like, hapus like
        $sqlDelete = "DELETE FROM likefoto WHERE FotoID = :fotoId AND UserID = :userId";
        $stmtDelete = $conn->prepare($sqlDelete);
        $stmtDelete->bindValue(':fotoId', $fotoId, PDO::PARAM_INT);
        $stmtDelete->bindValue(':userId', $userId, PDO::PARAM_INT);
        $stmtDelete->execute();
    } else {
        // Jika belum like, tambahkan like
        $sqlInsert = "INSERT INTO likefoto (FotoID, UserID, TanggalLike) VALUES (:fotoId, :userId, NOW())";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bindValue(':fotoId', $fotoId, PDO::PARAM_INT);
        $stmtInsert->bindValue(':userId', $userId, PDO::PARAM_INT);
        $stmtInsert->execute();
    }

    // Kembali ke halaman daftar foto
    header("Location: daftar_foto.php?album_id=" . $albumId);
    exit();
}

// Proses untuk menambahkan komentar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['komentar'])) {
    $fotoId = intval($_POST['foto_id']);
    $userId = $_SESSION['userId']; // Ambil UserID dari sesi
    $isiKomentar = trim($_POST['komentar']);

    // Menyimpan komentar ke database
    $sql = "INSERT INTO komentarfoto (FotoID, UserID, IsiKomentar, TanggalKomentar) VALUES (:fotoId, :userId, :isiKomentar, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':fotoId', $fotoId, PDO::PARAM_INT);
    $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':isiKomentar', $isiKomentar, PDO::PARAM_STR);
    
    if ($stmt->execute()) {
        // Setelah komentar berhasil ditambahkan, kembali ke halaman daftar foto
        header("Location: daftar_foto.php?album_id=" . $albumId);
        exit();
    } else {
        echo "Gagal menambahkan komentar.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Foto Album</title>
    <!-- Menambahkan Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e0f7fa;
            color: #004d40;
            padding: 20px;
        }

        .navbar {
            background-color: #00796b;
            padding: 20px;
            border-radius: 40px;
            margin-bottom: 30px;
        }

        .navbar a {
            color: #ffffff;
            text-decoration: none;
            margin-right: 20px;
        }

        .foto-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 10px;
        }

        .foto {
            border: 1px solid #c8e6c9;
            border-radius: 4px;
            background-color: #f1f8e9;
            text-align: center;
            padding-bottom: 10px;
        }

        .foto img {
            width: 50%;
            max-height: 150px;
            object-fit: cover;
            border-radius: 4px;
        }

        .foto-actions {
            margin-top: 10px;
        }

        .foto-actions i {
            font-size: 24px;
            cursor: pointer;
            margin-right: 10px;
            color: #00796b;
            transition: color 0.3s ease;
        }

        .foto-actions i:hover {
            color: #004d40;
        }

        .komentar-form {
            display: none;
        }

        .komentar-form.active {
            display: block;
        }

        @media print {
            body * {
                visibility: hidden;
            }
            .foto img {
                visibility: visible;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php">Beranda</a>
        <a href="tambah_foto.php?album_id=<?php echo $albumId; ?>">Tambah Foto</a>
    </div>

    <h1>Daftar Foto di Album</h1>
    <div class="foto-container">
        <!-- Periksa jika foto ditemukan di album -->
        <?php if (count($fotos) > 0): ?>
            <?php foreach ($fotos as $foto): ?>
                <div class="foto" id="foto-<?php echo $foto['FotoID']; ?>">
                    <strong><?php echo htmlspecialchars($foto['JudulFoto']); ?></strong><br>
                    <img src="<?php echo htmlspecialchars($foto['LokasiFile']); ?>" alt="<?php echo htmlspecialchars($foto['JudulFoto']); ?>" id="img-<?php echo $foto['FotoID']; ?>">
                    <p><?php echo htmlspecialchars($foto['DeskripsiFoto']); ?></p>

                    <!-- Ikon Aksi: Like, Edit, Komentar, dan Print -->
                    <div class="foto-actions">
                        <!-- Like icon -->
                        <form action="daftar_foto.php?album_id=<?php echo $albumId; ?>" method="post" style="display: inline;">
                            <input type="hidden" name="foto_id" value="<?php echo $foto['FotoID']; ?>">
                            <button type="submit" name="like" style="background: none; border: none; padding: 0;">
                                <i class="fas fa-heart"></i>
                            </button>
                        </form>

                        <!-- Edit icon -->
                        <a href="edit_foto.php?id=<?php echo $foto['FotoID']; ?>">
                            <i class="fas fa-edit"></i>
                        </a>

                        <!-- Komentar icon -->
                        <button onclick="toggleKomentarForm('<?php echo $foto['FotoID']; ?>')" style="background: none; border: none; padding: 0;">
                            <i class="fas fa-comment"></i>
                        </button>

                        <!-- Print icon -->
                        <button onclick="printFoto('img-<?php echo $foto['FotoID']; ?>')" style="background: none; border: none; padding: 0;">
                            <i class="fas fa-print"></i>
                        </button>
                    </div>

                    <!-- Tampilkan jumlah like -->
                    <?php
                    $sqlCountLikes = "SELECT COUNT(*) AS totalLikes FROM likefoto WHERE FotoID = :fotoId";
                    $stmt = $conn->prepare($sqlCountLikes);
                    $stmt->bindValue(':fotoId', $foto['FotoID'], PDO::PARAM_INT);
                    $stmt->execute();
                    $likeData = $stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <p><?php echo $likeData['totalLikes']; ?> Likes</p>

                    <!-- Form komentar -->
                    <div id="komentar-form-<?php echo $foto['FotoID']; ?>" class="komentar-form">
                        <form action="daftar_foto.php?album_id=<?php echo $albumId; ?>" method="post">
                            <input type="hidden" name="foto_id" value="<?php echo $foto['FotoID']; ?>">
                            <textarea name="komentar" placeholder="Tulis komentar Anda di sini..." rows="3" required></textarea>
                            <button type="submit">Kirim Komentar</button>
                        </form>
                    </div>

                    <!-- Menampilkan komentar untuk setiap foto -->
                    <h4>Komentar:</h4>
                    <?php
                    $sqlGetComments = "SELECT k.IsiKomentar, u.Username, k.TanggalKomentar 
                                       FROM komentarfoto k
                                       JOIN user u ON k.UserID = u.UserID
                                       WHERE k.FotoID = :fotoId
                                       ORDER BY k.TanggalKomentar DESC";
                    $stmt = $conn->prepare($sqlGetComments);
                    $stmt->bindValue(':fotoId', $foto['FotoID'], PDO::PARAM_INT);
                    $stmt->execute();
                    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Tampilkan komentar jika ada
                    if (count($comments) > 0):
                        foreach ($comments as $comment):
                    ?>
                            <p><strong><?php echo htmlspecialchars($comment['Username']); ?>:</strong> <?php echo htmlspecialchars($comment['IsiKomentar']); ?> <em>(<?php echo $comment['TanggalKomentar']; ?>)</em></p>
                    <?php
                        endforeach;
                    else:
                    ?>
                        <p>Tidak ada komentar.</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Tidak ada foto di album ini.</p>
        <?php endif; ?>
    </div>

    <script>
        // Fungsi untuk menampilkan atau menyembunyikan form komentar
        function toggleKomentarForm(fotoId) {
            var form = document.getElementById('komentar-form-' + fotoId);
            if (form.classList.contains('active')) {
                form.classList.remove('active');
            } else {
                form.classList.add('active');
            }
        }

        // Fungsi untuk mencetak foto
        function printFoto(imgId) {
            var imgElement = document.getElementById(imgId);
            var newWindow = window.open("");
            newWindow.document.write("<img src='" + imgElement.src + "' width='100%'>");
            newWindow.print();
            newWindow.close();
        }
    </script>
</body>
</html>

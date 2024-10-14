<?php
session_start(); // Start session

// Include the database connection
require_once('config/koneksi.php'); // Make sure this path is correct

$error_message = ""; // Initialize error message variable
$success_message = ""; // Initialize success message variable
$albums = []; // Array to store albums

// Fetch album list from the database (without filtering by user)
try {
    $stmt = $conn->prepare("SELECT AlbumID, NamaAlbum FROM album");
    $stmt->execute();
    $albums = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error fetching albums: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve input data
    $judul_foto = trim($_POST['judul_foto']);
    $deskripsi_foto = trim($_POST['deskripsi_foto']);
    $album_id = $_POST['album_id']; // Get the selected album ID
    $tanggal_unggah = date('Y-m-d H:i:s'); // Current date and time
    $lokasi_file = ''; // Assuming you will set this with file upload handling

    // Handle file upload (implement your logic here)
    if (isset($_FILES['file_foto']) && $_FILES['file_foto']['error'] === UPLOAD_ERR_OK) {
        $file_tmp = $_FILES['file_foto']['tmp_name'];
        $file_name = $_FILES['file_foto']['name'];
        $upload_dir = 'uploads/'; // Make sure this directory exists
        $lokasi_file = $upload_dir . basename($file_name);

        if (move_uploaded_file($file_tmp, $lokasi_file)) {
            try {
                // Prepare an SQL statement
                $sql = "INSERT INTO foto (JudulFoto, DeskripsiFoto, TanggalUnggah, LokasiFile, AlbumID, UserID) 
                        VALUES (:judul_foto, :deskripsi_foto, :tanggal_unggah, :lokasi_file, :album_id, :user_id)";
                $stmt = $conn->prepare($sql); // Using $conn to prepare the statement

                // Bind parameters
                $stmt->bindParam(':judul_foto', $judul_foto);
                $stmt->bindParam(':deskripsi_foto', $deskripsi_foto);
                $stmt->bindParam(':tanggal_unggah', $tanggal_unggah);
                $stmt->bindParam(':lokasi_file', $lokasi_file);
                $stmt->bindParam(':album_id', $album_id); // Bind the selected AlbumID
                $stmt->bindParam(':user_id', $_SESSION['userId']); // Bind the UserID from session

                // Execute the statement
                if ($stmt->execute()) {
                    $success_message = "Foto berhasil ditambahkan."; // Set success message
                } else {
                    $error_message = "Gagal menambahkan foto.";
                }
            } catch (PDOException $e) {
                $error_message = "Error: " . $e->getMessage();
            }
        } else {
            $error_message = "Gagal mengunggah file.";
        }
    } else {
        $error_message = "Tidak ada file yang diunggah.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Foto</title>
    <style>
        /* Reset default margin and padding */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #e0f7fa; /* Latar belakang biru laut muda */
            color: #00695c; /* Teks berwarna hijau tua */
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        /* Container styles */
        .container {
            max-width: 800px;
            background-color: #ffffff; /* Latar belakang putih untuk kontainer */
            border-radius: 8px; /* Sudut melengkung */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Bayangan halus */
            padding: 20px;
            margin-top: 20px; /* Jarak atas dari navbar */
        }

        h2 {
            margin-bottom: 15px;
            color: #00796b; /* Judul berwarna teal */
        }

        /* Error and success message styles */
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 4px;
        }
        .error-message {
            color: red; /* Warna merah untuk pesan error */
            background-color: #ffebee; /* Warna latar belakang untuk pesan error */
        }

        .success-message {
            color: green; /* Warna hijau untuk pesan sukses */
            background-color: #e8f5e9; /* Warna latar belakang untuk pesan sukses */
        }

        /* Form styles */
        label {
            display: block;
            margin: 10px 0 5px; /* Jarak atas dan bawah label */
        }

        input[type="text"],
        textarea,
        input[type="file"],
        select {
            width: 100%; /* Mengisi lebar kontainer */
            padding: 10px;
            border: 1px solid #c8e6c9; /* Garis batas input */
            border-radius: 4px; /* Sudut input melengkung */
            margin-bottom: 15px; /* Jarak bawah untuk elemen form */
        }

        button {
            padding: 10px 15px; /* Padding untuk tombol */
            background-color: #009688; /* Warna latar belakang tombol */
            color: #ffffff; /* Warna teks tombol */
            border: none; /* Tanpa garis batas */
            border-radius: 4px; /* Sudut tombol melengkung */
            cursor: pointer; /* Pointer saat mengarahkan mouse */
        }

        button:hover {
            background-color: #00796b; /* Warna tombol saat hover */
        }

        /* Link styles */
        .back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none; /* Tanpa garis bawah pada tautan */
            color: #009688; /* Warna teal untuk tautan */
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Tambah Foto</h2>
        <?php if ($error_message): ?>
            <div class="message error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <div class="message success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>
        <form action="" method="post" enctype="multipart/form-data">
            <label for="judul_foto">Judul Foto:</label>
            <input type="text" name="judul_foto" id="judul_foto" required>

            <label for="deskripsi_foto">Deskripsi Foto:</label>
            <textarea name="deskripsi_foto" id="deskripsi_foto" required></textarea>

            <label for="album_id">Pilih Album:</label>
            <select name="album_id" id="album_id" required>
                <option value="">Pilih Album</option>
                <?php foreach ($albums as $album): ?>
                    <option value="<?php echo $album['AlbumID']; ?>"><?php echo htmlspecialchars($album['NamaAlbum']); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="file_foto">Unggah Foto:</label>
            <input type="file" name="file_foto" id="file_foto" accept="image/*" required>

            <button type="submit">Tambah Foto</button>
        </form>
        <a href="index.php" class="back-link">Kembali ke Beranda</a> <!-- Link to return to homepage -->
    </div>
</body>
</html>

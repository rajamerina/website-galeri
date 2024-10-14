<?php
require_once("config/koneksi.php");

if (isset($_GET['id'])) {
    $albumId = $_GET['id'];

    // Ambil data album berdasarkan ID
    $sql = "SELECT * FROM album WHERE AlbumID = :albumId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':albumId', $albumId, PDO::PARAM_INT);
    $stmt->execute();
    $album = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$album) {
        echo "Album tidak ditemukan.";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaAlbum = $_POST['namaAlbum'];
    $deskripsi = $_POST['deskripsi'];

    // Initialize the update SQL statement without cover photo
    $sql = "UPDATE album SET NamaAlbum = :namaAlbum, Deskripsi = :deskripsi WHERE AlbumID = :albumId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':namaAlbum', $namaAlbum);
    $stmt->bindParam(':deskripsi', $deskripsi);
    $stmt->bindParam(':albumId', $albumId, PDO::PARAM_INT);
    
    $coverPhotoUpdated = false; // Flag to check if cover photo is updated
    $target_file = ''; // Initialize the file path variable

    // Handle file upload for cover photo
    if (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/"; // Directory where files will be uploaded
        $target_file = $target_dir . basename($_FILES["cover_photo"]["name"]); // File path
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is an actual image or fake image
        $check = getimagesize($_FILES["cover_photo"]["tmp_name"]);
        if ($check === false) {
            echo "File yang diunggah bukan gambar.";
            $uploadOk = false;
        }

        // Check file size (limit to 2MB)
        if ($_FILES["cover_photo"]["size"] > 2000000) {
            echo "File terlalu besar. Maksimum ukuran adalah 2MB.";
            $uploadOk = false;
        }

        // Allow certain file formats
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            echo "Hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
            $uploadOk = false;
        }

        // Check if $uploadOk is set to true by an error
        if ($uploadOk !== false) {
            // Move the uploaded file to the target directory
            if (move_uploaded_file($_FILES["cover_photo"]["tmp_name"], $target_file)) {
                $coverPhotoUpdated = true; // Set flag to true
            } else {
                echo "Gagal mengunggah file.";
            }
        }
    }

    // If cover photo is updated, include it in the SQL statement
    if ($coverPhotoUpdated) {
        $sql = "UPDATE album SET NamaAlbum = :namaAlbum, Deskripsi = :deskripsi, CoverPhoto = :cover_photo WHERE AlbumID = :albumId";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':cover_photo', $target_file); // Store the file path in the database
        $stmt->bindParam(':namaAlbum', $namaAlbum);
        $stmt->bindParam(':deskripsi', $deskripsi);
        $stmt->bindParam(':albumId', $albumId, PDO::PARAM_INT);
    }

    // Execute the statement
    if ($stmt->execute()) {
        // Redirect kembali ke dashboard jika sukses
        header("Location: dashboard_admin.php");
        exit();
    } else {
        echo "Terjadi kesalahan saat memperbarui album.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Album</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            padding: 20px;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .edit-form-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
        }

        h1 {
            color: #00796b;
            margin-bottom: 20px;
            text-align: center;
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: #00796b;
        }

        input[type="text"],
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        textarea {
            resize: vertical;
            height: 100px;
        }

        input[type="file"] {
            margin-bottom: 20px; /* Margin below file input */
        }

        input[type="submit"] {
            padding: 10px 20px;
            background-color: #00796b;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            display: block;
            margin: 0 auto;
        }

        input[type="submit"]:hover {
            background-color: #004d40;
        }
    </style>
</head>
<body>

    <div class="edit-form-container">
        <h1>Edit Album</h1>

        <form action="" method="post" enctype="multipart/form-data"> <!-- Added enctype for file upload -->
            <label for="namaAlbum">Nama Album:</label>
            <input type="text" name="namaAlbum" value="<?php echo htmlspecialchars($album['NamaAlbum']); ?>" required>

            <label for="deskripsi">Deskripsi:</label>
            <textarea name="deskripsi" required><?php echo htmlspecialchars($album['Deskripsi']); ?></textarea>

            <label for="cover_photo">Foto Sampul (optional):</label>
            <input type="file" name="cover_photo" id="cover_photo" accept="image/*"> <!-- Added file input -->

            <input type="submit" value="Perbarui">
        </form>
    </div>

</body>
</html>

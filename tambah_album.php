<?php
session_start(); // Start session

// Include the database connection
require_once('config/koneksi.php'); // Make sure this path is correct

$error_message = ""; // Initialize error message variable

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve input data
    $nama_album = trim($_POST['nama_album']);
    $deskripsi = trim($_POST['deskripsi']);
    $tanggal_dibuat = date('Y-m-d H:i:s'); // Current date and time
    $user_id = $_SESSION['userId']; // Assuming you store UserID in session

    // Handle file upload
    if (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/"; // Directory where files will be uploaded
        $target_file = $target_dir . basename($_FILES["cover_photo"]["name"]); // File path
        $uploadOk = 1;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check if image file is an actual image or fake image
        $check = getimagesize($_FILES["cover_photo"]["tmp_name"]);
        if ($check === false) {
            $error_message = "File yang diunggah bukan gambar.";
            $uploadOk = 0;
        }

        // Check file size (limit to 2MB)
        if ($_FILES["cover_photo"]["size"] > 2000000) {
            $error_message = "File terlalu besar. Maksimum ukuran adalah 2MB.";
            $uploadOk = 0;
        }

        // Allow certain file formats
        if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif'])) {
            $error_message = "Hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
            $uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk === 1) {
            // Move the uploaded file to the target directory
            if (move_uploaded_file($_FILES["cover_photo"]["tmp_name"], $target_file)) {
                // Prepare an SQL statement
                $sql = "INSERT INTO album (NamaAlbum, Deskripsi, TanggalDibuat, UserID, CoverPhoto) VALUES (:nama_album, :deskripsi, :tanggal_dibuat, :user_id, :cover_photo)";
                $stmt = $conn->prepare($sql); // Using $conn to prepare the statement

                // Bind parameters
                $stmt->bindParam(':nama_album', $nama_album);
                $stmt->bindParam(':deskripsi', $deskripsi);
                $stmt->bindParam(':tanggal_dibuat', $tanggal_dibuat);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':cover_photo', $target_file); // Store the file path in the database

                // Execute the statement
                if ($stmt->execute()) {
                    header('Location: index.php'); // Redirect on success
                    exit();
                } else {
                    $error_message = "Gagal menambahkan album.";
                }
            } else {
                $error_message = "Gagal mengunggah file.";
            }
        }
    } else {
        $error_message = "Silakan unggah foto sampul.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Album</title>
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
            background-color: #e0f7fa; /* Light blue background */
            color: #00695c; /* Dark green text */
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        /* Container styles */
        .container {
            max-width: 600px; /* Set max width for the form */
            background-color: #ffffff; /* White background for container */
            border-radius: 8px; /* Rounded corners */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Soft shadow */
            padding: 20px; /* Padding inside the container */
            margin-top: 20px; /* Margin above the container */
        }

        h2 {
            color: #00796b; /* Teal color for the heading */
            margin-bottom: 15px; /* Margin below the heading */
        }

        .error-message {
            color: red; /* Red color for error messages */
            margin-bottom: 10px; /* Margin below the error message */
        }

        label {
            display: block; /* Make labels block elements */
            margin-bottom: 5px; /* Margin below labels */
            font-weight: bold; /* Bold font for labels */
        }

        input[type="text"],
        textarea {
            width: 100%; /* Full width inputs */
            padding: 10px; /* Padding inside inputs */
            border: 1px solid #c8e6c9; /* Light green border */
            border-radius: 4px; /* Rounded corners */
            margin-bottom: 15px; /* Margin below inputs */
        }

        input[type="file"] {
            margin-bottom: 15px; /* Margin below file input */
        }

        button {
            padding: 10px 15px; /* Padding inside the button */
            background-color: #009688; /* Button background color */
            color: #ffffff; /* Button text color */
            border: none; /* No border */
            border-radius: 4px; /* Rounded corners */
            cursor: pointer; /* Pointer cursor on hover */
            transition: background-color 0.3s; /* Transition effect on hover */
        }

        button:hover {
            background-color: #00796b; /* Darker color on hover */
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Tambah Album</h2>
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form action="" method="post" enctype="multipart/form-data"> <!-- Added enctype for file upload -->
            <label for="nama_album">Nama Album:</label>
            <input type="text" name="nama_album" id="nama_album" required>

            <label for="deskripsi">Deskripsi:</label>
            <textarea name="deskripsi" id="deskripsi" rows="4" required></textarea>

            <label for="cover_photo">Foto Sampul:</label>
            <input type="file" name="cover_photo" id="cover_photo" accept="image/*" required> <!-- Added file input -->

            <button type="submit">Tambah Album</button>
            <a href="index.php" class="back-link">Kembali ke Beranda</a> <!-- Link to return to homepage -->
        </form>
    </div>
</body>
</html>

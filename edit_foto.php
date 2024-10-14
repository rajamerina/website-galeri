<?php
require_once("config/koneksi.php");

if (isset($_GET['id'])) {
    $fotoId = $_GET['id'];

    // Ambil data foto berdasarkan ID
    $sql = "SELECT * FROM foto WHERE FotoID = :fotoId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':fotoId', $fotoId, PDO::PARAM_INT);
    $stmt->execute();
    $foto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$foto) {
        echo "Foto tidak ditemukan.";
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judulFoto = $_POST['judulFoto'];
    $deskripsiFoto = $_POST['deskripsiFoto'];

    // Update data foto
    $sql = "UPDATE foto SET JudulFoto = :judulFoto, DeskripsiFoto = :deskripsiFoto WHERE FotoID = :fotoId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':judulFoto', $judulFoto);
    $stmt->bindParam(':deskripsiFoto', $deskripsiFoto);
    $stmt->bindParam(':fotoId', $fotoId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Redirect kembali ke dashboard jika sukses
        header("Location: dashboard_admin.php");
        exit();
    } else {
        echo "Terjadi kesalahan saat memperbarui foto.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Foto</title>
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
        <h1>Edit Foto</h1>

        <form action="" method="post">
            <label for="judulFoto">Judul Foto:</label>
            <input type="text" name="judulFoto" value="<?php echo htmlspecialchars($foto['JudulFoto']); ?>" required>

            <label for="deskripsiFoto">Deskripsi Foto:</label>
            <textarea name="deskripsiFoto" required><?php echo htmlspecialchars($foto['DeskripsiFoto']); ?></textarea>

            <input type="submit" value="Perbarui">
             <a href="index.php" class="back-link">Kembali ke Beranda</a> <!-- Link to return to homepage -->
        </form>
    </div>

</body>
</html>

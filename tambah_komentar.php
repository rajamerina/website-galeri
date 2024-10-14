<?php
session_start();
require_once("config/koneksi.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fotoId = intval($_POST['foto_id']);
    $isiKomentar = trim($_POST['isi_komentar']);
    $userId = $_SESSION['userId']; // User yang sedang login

    try {
        // Insert komentar ke database
        $sql = "INSERT INTO komentar (FotoID, UserID, IsiKomentar) VALUES (:foto_id, :user_id, :isi_komentar)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':foto_id', $fotoId);
        $stmt->bindParam(':user_id', $userId);
        $stmt->bindParam(':isi_komentar', $isiKomentar);
        $stmt->execute();

        header("Location: daftar_foto.php?album_id=" . intval($_GET['album_id'])); // Redirect kembali
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

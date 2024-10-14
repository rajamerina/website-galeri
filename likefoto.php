<?php
session_start(); // Memulai sesi

// Memeriksa apakah pengguna telah login
if (!isset($_SESSION['username']) || !isset($_SESSION['userId'])) {
    header('Location: login.php');
    exit();
}

require_once("config/koneksi.php"); // Pastikan file ini mengandung koneksi yang benar

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['foto_id'])) {
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

    // Kembali ke halaman sebelumnya (daftar foto)
    header("Location: daftar_foto.php?album_id=" . $_GET['album_id']);
    exit();
} else {
    header('Location: index.php'); // Kembali ke halaman utama jika tidak ada POST
    exit();
}

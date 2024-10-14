<?php
session_start(); // Memulai sesi
require_once("config/koneksi.php");
// Pastikan pengguna sudah login
if (!isset($_SESSION['username'])) {
    echo "Anda harus login untuk memberikan komentar.";
    exit();
}

// Cek apakah foto ID dan isi komentar dikirim melalui POST
if (isset($_POST['foto_id']) && isset($_POST['isi_komentar'])) {
    $foto_id = $_POST['foto_id'];
    $isi_komentar = $_POST['isi_komentar'];
    $user_id = $_SESSION['username']; // Gunakan username atau ID pengguna sesuai kebutuhan

    // Periksa apakah pengguna sudah memberikan komentar untuk foto ini
    $sql_check = "SELECT * FROM komentarfoto WHERE FotoID = ? AND UserID = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("is", $foto_id, $user_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        echo "Anda sudah memberikan komentar untuk foto ini.";
    } else {
        // Tambahkan komentar ke database
        $sql_insert = "INSERT INTO komentarfoto (FotoID, UserID, IsiKomentar, TanggalKomentar) VALUES (?, ?, ?, NOW())";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("iss", $foto_id, $user_id, $isi_komentar);
        
        if ($stmt_insert->execute()) {
            echo "Komentar berhasil ditambahkan.";
        } else {
            echo "Terjadi kesalahan saat menambahkan komentar.";
        }

        // Menutup statement
        $stmt_insert->close();
    }

    // Menutup statement
    $stmt_check->close();
} else {
    echo "Data tidak lengkap.";
}

// Redirect kembali ke halaman album
header("Location: daftar_foto.php?id=" . $_POST['album_id']);
exit();
?>
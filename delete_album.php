<?php
require_once("config/koneksi.php");

if (isset($_GET['id'])) {
    $albumId = $_GET['id'];

    // Hapus album dari database
    $sql = "DELETE FROM album WHERE AlbumID = :albumId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':albumId', $albumId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Redirect kembali ke dashboard jika sukses
        header("Location: dashboard_admin.php");
        exit();
    } else {
        echo "Terjadi kesalahan saat menghapus album.";
    }
} else {
    echo "ID album tidak ditemukan.";
}
?>

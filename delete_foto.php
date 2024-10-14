<?php
require_once("config/koneksi.php");

if (isset($_GET['id'])) {
    $fotoId = $_GET['id'];

    // Hapus foto dari database
    $sql = "DELETE FROM foto WHERE FotoID = :fotoId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':fotoId', $fotoId, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Redirect kembali ke dashboard jika sukses
        header("Location: dashboard_admin.php");
        exit();
    } else {
        echo "Terjadi kesalahan saat menghapus foto.";
    }
} else {
    echo "ID foto tidak ditemukan.";
}
?>

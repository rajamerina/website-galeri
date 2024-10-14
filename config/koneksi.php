<?php
// Mengatur variabel koneksi
$host = 'localhost'; // Ganti dengan host database Anda jika berbeda
$db = 'gallery'; // Ganti dengan nama database Anda
$user = 'root'; // Ganti dengan username database Anda
$pass = ''; // Ganti dengan password database Anda

try {
    // Membuat koneksi menggunakan PDO
    $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    // Mengatur mode kesalahan PDO untuk exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Menampilkan pesan kesalahan jika koneksi gagal
    echo "Koneksi gagal: " . $e->getMessage();
    exit();
}

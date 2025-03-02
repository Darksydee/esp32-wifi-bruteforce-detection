<?php
$host = "localhost";
$user = "root"; // Ganti sesuai dengan username database
$password = ""; // Ganti jika ada password database
$database = "smarthome_wifi_security";

$conn = new mysqli($host, $user, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>
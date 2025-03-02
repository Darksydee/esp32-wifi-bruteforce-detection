<?php
session_start(); // Mulai sesi
// Jika user belum login, redirect ke login.php
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Wi-Fi Bruteforce Monitor</title>
    <link rel="stylesheet" href="style_dashboard/style_settings.css">
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <h3>Dashboard Wi-Fi Bruteforce Monitor</h3>
    <div class="nav-links">
        <button class="nav-btn" onclick="location.href='dashboard.php'"><span>🏠</span> Home</button>
        <button class="nav-btn" onclick="location.href='sort_by.php'"><span>↕️</span> Sort by</button>
        <button class="nav-btn active" onclick="location.href='settings.php'"><span>⚙️</span> Settings</button>
        <button class="nav-btn profile" onclick="location.href='account_manager.php'">👤</button>
    </div>
</div>

<!-- Settings Section -->
<div class="settings-container">
    <h3>Settings</h3>

    <div class="section">
        <h4>Pengaturan Sistem</h4>
        <p>Halaman ini akan digunakan untuk konfigurasi sistem di masa mendatang.</p>
    </div>

    <div class="section">
        <h4>Versi Dashboard</h4>
        <p>Versi: 1.0 (Beta)</p>
    </div>

</div>

</body>
</html>
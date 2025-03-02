<?php
session_start();
include "config.php";

// Redirect ke login jika user belum login
if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit();
}

// Ambil data user dari database
$user_id = $_SESSION["id"];
$stmt = $conn->prepare("SELECT nama, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($nama, $email);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Manager - Dashboard Wi-Fi Bruteforce Monitor</title>
    <link rel="stylesheet" href="style_dashboard/style_account_manage.css">
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <h2>Dashboard Wi-Fi Bruteforce Monitor</h2>
    <div class="nav-links">
        <button class="nav-btn" onclick="location.href='dashboard.php'"><span>🏠</span> Home</button>
        <button class="nav-btn" onclick="location.href='sort_by.php'"><span>↕️</span> Sort by</button>
        <button class="nav-btn" onclick="location.href='settings.php'"><span>⚙️</span> Settings</button>
        <button class="nav-btn profile active" onclick="location.href='account_manager.php'">👤</button>
    </div>
</div>

<!-- Profile Card -->
<div class="profile-card">
    <div class="profile-box">
        <div class="profile-image">👤</div>
        <h3><?php echo htmlspecialchars($nama); ?></h3>
        <p><?php echo htmlspecialchars($email); ?></p>
        <form action="logout.php" method="post">
            <button type="submit" class="btn logout">Log Out</button>
        </form>
    </div>
</div>

<!-- Account Management Section -->
<div class="account-container">
    <div class="account-details">
        <h3>Account Manager</h3>
        <p><strong>Name:</strong> <?php echo htmlspecialchars($nama); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
    </div>

    <!-- Tombol Edit -->
    <div class="button-group">
        <button class="btn name" onclick="location.href='change_name.php'">Change Name</button>
        <button class="btn email" onclick="location.href='change_email.php'">Change Email</button>
        <button class="btn password" onclick="location.href='change_password.php'">Change Password</button>
    </div>
</div>

</body>
</html>
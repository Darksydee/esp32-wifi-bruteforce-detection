<?php
session_start();
include "config.php";

// Redirect ke login jika user belum login
if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["id"];

// Jika form dikirimkan
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_name = trim($_POST["new_name"]);

    if (!empty($new_name)) {
        $stmt = $conn->prepare("UPDATE users SET nama = ? WHERE id = ?");
        $stmt->bind_param("si", $new_name, $user_id);
        
        if ($stmt->execute()) {
            $_SESSION["nama"] = $new_name; // Perbarui sesi
            echo "<script>alert('Nama berhasil diperbarui!'); window.location.href='account_manager.php';</script>";
        } else {
            echo "<script>alert('Terjadi kesalahan, coba lagi!');</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Nama tidak boleh kosong!');</script>";
    }
}

// Ambil nama user dari database
$stmt = $conn->prepare("SELECT nama FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($nama);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Name - Dashboard Wi-Fi Bruteforce Monitor</title>
    <link rel="stylesheet" href="style_dashboard/style_account_manage.css">
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <h2>Dashboard Wi-Fi Bruteforce Monitor</h2>
    <div class="nav-links">
        <button class="nav-btn" onclick="location.href='dashboard.html'"><span>🏠</span> Home</button>
        <button class="nav-btn" onclick="location.href='sort_by.html'"><span>↕️</span> Sort by</button>
        <button class="nav-btn" onclick="location.href='settings.html'"><span>⚙️</span> Settings</button>
        <button class="nav-btn profile active" onclick="location.href='account_manager.php'">👤</button>
    </div>
</div>

<!-- Change Name Section -->
<div class="account-container">
    <h3>Change Name</h3>

    <form method="POST">
        <div class="account-details">
            <div class="field">
                <label>Enter New Name</label>
                <input type="text" class="input-field" name="new_name" placeholder="Enter New Name" value="<?php echo htmlspecialchars($nama); ?>" required>
            </div>
        </div>

        <div class="button-group">
            <button type="submit" class="btn confirm">Change</button>
            <button type="button" class="btn cancel" onclick="location.href='account_manager.php'">Cancel</button>
        </div>
    </form>
</div>

</body>
</html>

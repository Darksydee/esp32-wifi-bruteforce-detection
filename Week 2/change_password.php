<?php
session_start();
include "config.php";

// Redirect ke login jika user belum login
if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["id"];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST["current_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    // Ambil password lama dari database
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($hashed_password);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($current_password, $hashed_password)) {
        $message = "Password lama salah!";
    } elseif ($new_password !== $confirm_password) {
        $message = "Konfirmasi password baru tidak cocok!";
    } else {
        // Hash password baru dan update di database
        $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $new_hashed_password, $user_id);
        if ($stmt->execute()) {
            echo "<script>alert('Password berhasil diperbarui!'); window.location.href='account_manager.php';</script>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <link rel="stylesheet" href="style_dashboard/style_account_manage.css">
</head>
<body>
<div class="account-container">
    <h3>Change Password</h3>
    <?php if ($message) echo "<p class='error-message'>$message</p>"; ?>
    <form method="POST">
        <div class="field">
            <label>Current Password</label>
            <input type="password" name="current_password" class="input-field" required>
        </div>
        <div class="field">
            <label>New Password</label>
            <input type="password" name="new_password" class="input-field" required>
        </div>
        <div class="field">
            <label>Confirm New Password</label>
            <input type="password" name="confirm_password" class="input-field" required>
        </div>
        <button type="submit" class="btn confirm">Change Password</button>
    </form>
    <button class="btn cancel" onclick="location.href='account_manager.php'">Cancel</button>
</div>
</body>
</html>

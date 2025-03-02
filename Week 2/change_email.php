<?php
session_start();
include "config.php";

// Redirect ke login jika user belum login
if (!isset($_SESSION["id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["id"];
$email = $_SESSION["email"];
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $old_email = $_POST["old_email"];
    $new_email = $_POST["new_email"];
    $confirm_email = $_POST["confirm_email"];
    $password = $_POST["password"];

    if ($old_email !== $email) {
        $message = "Email lama tidak sesuai!";
    } elseif ($new_email !== $confirm_email) {
        $message = "Konfirmasi email baru tidak cocok!";
    } else {
        // Verifikasi password sebelum update email
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($hashed_password);
        $stmt->fetch();
        $stmt->close();

        if (!password_verify($password, $hashed_password)) {
            $message = "Password salah!";
        } else {
            // Update email di database
            $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
            $stmt->bind_param("si", $new_email, $user_id);
            if ($stmt->execute()) {
                $_SESSION["email"] = $new_email;
                echo "<script>alert('Email berhasil diperbarui!'); window.location.href='account_manager.php';</script>";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Change Email</title>
    <link rel="stylesheet" href="style_dashboard/style_account_manage.css">
</head>
<body>
<div class="account-container">
    <h3>Change Email</h3>
    <?php if ($message) echo "<p class='error-message'>$message</p>"; ?>
    <form method="POST">
        <div class="field">
            <label>Old Email</label>
            <input type="email" name="old_email" class="input-field" value="<?php echo htmlspecialchars($email); ?>" required>
        </div>
        <div class="field">
            <label>New Email</label>
            <input type="email" name="new_email" class="input-field" required>
        </div>
        <div class="field">
            <label>Confirm New Email</label>
            <input type="email" name="confirm_email" class="input-field" required>
        </div>
        <div class="field">
            <label>Password (For Security)</label>
            <input type="password" name="password" class="input-field" required>
        </div>
        <button type="submit" class="btn confirm">Change Email</button>
    </form>
    <button class="btn cancel" onclick="location.href='account_manager.php'">Cancel</button>
</div>
</body>
</html>

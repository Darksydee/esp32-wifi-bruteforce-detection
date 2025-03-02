<?php
include "config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST["nama"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
    $confirm_password = $_POST["confirm-password"];

    // Cek apakah password dan konfirmasi password cocok
    if (!password_verify($confirm_password, $password)) {
        echo "<script>alert('Konfirmasi password tidak cocok!');</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (nama, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nama, $email, $password);

        if ($stmt->execute()) {
            echo "<script>alert('Registrasi berhasil! Silakan login.'); window.location='login.php';</script>";
        } else {
            echo "<script>alert('Email sudah digunakan!');</script>";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="style_login/style.css">
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <h2>Register Akun Baru</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="nama">Nama</label>
                    <input type="text" id="nama" name="nama" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm-password">Ulangi Password</label>
                    <input type="password" id="confirm-password" name="confirm-password" required>
                </div>
                <button type="submit" class="btn btn-register">Daftar</button>
                <p>Sudah punya akun? <a href="login.php">Login</a></p>
            </form>
        </div>
    </div>
</body>
</html>

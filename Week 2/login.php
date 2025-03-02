<?php
session_start();
include "config.php"; // Pastikan config.php memiliki koneksi database yang benar

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil input & lakukan sanitasi
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    if (empty($email) || empty($password)) {
        header("Location: login.php?error=empty_fields");
        exit();
    }

    // Siapkan query dengan prepared statement
    $stmt = $conn->prepare("SELECT id, nama, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    // Jika akun ditemukan
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $nama, $hashed_password);
        $stmt->fetch();

        // Verifikasi password
        if (password_verify($password, $hashed_password)) {
            // Regenerasi session ID untuk keamanan
            session_regenerate_id(true);
            
            // Simpan data user ke dalam session
            $_SESSION["id"] = $id;
            $_SESSION["nama"] = $nama;
            $_SESSION["email"] = $email;

            // Redirect ke dashboard
            header("Location: dashboard.php");
            exit();
        } else {
            header("Location: login.php?error=wrong_password");
            exit();
        }
    } else {
        header("Location: login.php?error=user_not_found");
        exit();
    }

    $stmt->close();
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style_login/style.css">
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <h2>Login</h2>
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="text" id="email" name="email">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password">
                </div>
                <button type="submit" class="btn btn-login">Login</button>
                <p>Belum punya akun? <a href="register.php">Daftar</a></p>
            </form>
        </div>
    </div>
</body>
</html>

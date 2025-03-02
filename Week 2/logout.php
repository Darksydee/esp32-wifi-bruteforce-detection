<?php
session_start();
session_unset(); // Hapus semua sesi
session_destroy(); // Hancurkan sesi
header("Location: login.php"); // Redirect ke halaman login
exit();
?>

<?php
session_start(); // Mulai sesi

// Jika user belum login, redirect ke login.php
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}
include "config.php"; // Koneksi ke database

// Query jumlah serangan per hari
$sql_harian = "SELECT COUNT(*) AS total FROM bruteforce_attacking_data WHERE DATE(timestamp) = CURDATE()";
$result_harian = $conn->query($sql_harian);
$total_harian = $result_harian->fetch_assoc()['total'];

// Query jumlah serangan per minggu
$sql_mingguan = "SELECT COUNT(*) AS total FROM bruteforce_attacking_data WHERE YEARWEEK(timestamp, 1) = YEARWEEK(CURDATE(), 1)";
$result_mingguan = $conn->query($sql_mingguan);
$total_mingguan = $result_mingguan->fetch_assoc()['total'];

// Query jumlah serangan per bulan
$sql_bulanan = "SELECT COUNT(*) AS total FROM bruteforce_attacking_data WHERE MONTH(timestamp) = MONTH(CURDATE()) AND YEAR(timestamp) = YEAR(CURDATE())";
$result_bulanan = $conn->query($sql_bulanan);
$total_bulanan = $result_bulanan->fetch_assoc()['total'];

// Query jumlah serangan per hari dalam seminggu terakhir untuk grafik
$sql_chart = "SELECT DATE(timestamp) AS tanggal, COUNT(*) AS total 
              FROM bruteforce_attacking_data 
              WHERE timestamp >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
              GROUP BY DATE(timestamp) 
              ORDER BY tanggal ASC";

$result_chart = $conn->query($sql_chart);

// Simpan hasil ke array
$tanggal_array = [];
$total_array = [];

while ($row = $result_chart->fetch_assoc()) {
    $tanggal_array[] = $row['tanggal'];
    $total_array[] = $row['total'];
}

// Konversi array ke format JSON agar bisa digunakan di JavaScript
$tanggal_json = json_encode($tanggal_array);
$total_json = json_encode($total_array);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Wi-Fi Bruteforce Monitor</title>
    <link rel="stylesheet" href="style_dashboard/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <header>
            <h3>Dashboard Wi-Fi Bruteforce Monitor</h3>
            <nav>
                <ul>
                    <li><a href="dashboard.php" class="active">🏠 Home</a></li>
                    <li><a href="sort_by.php">🔃 Sort by</a></li>
                    <li><a href="settings.php">⚙️ Settings</a></li>
                    <li><a href="account_manager.php">👤</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <h2>Home</h2>

            <!-- Menampilkan Status Koneksi Database -->
            <div class="status-koneksi">
                <?php if ($conn) {
                    echo "<p style='color: green; font-weight: bold;'>Koneksi ke database berhasil!</p>";
                } else {
                    echo "<p style='color: red; font-weight: bold;'>Gagal terhubung ke database!</p>";
                } ?>
            </div>

            <!-- Statistik Serangan -->
            <div class="stats-container">
                <div class="stats-box">
                    <h3><?php echo $total_harian; ?></h3>
                    <p>Jumlah Serangan Per Hari</p>
                </div>
                <div class="stats-box">
                    <h3><?php echo $total_mingguan; ?></h3>
                    <p>Jumlah Serangan Per Minggu</p>
                </div>
                <div class="stats-box">
                    <h3><?php echo $total_bulanan; ?></h3>
                    <p>Jumlah Serangan Per Bulan</p>
                </div>
            </div>

            <!-- Grafik Serangan Brute Force -->
            <h3>Grafik Serangan Brute Force (7 Hari Terakhir)</h3>
            <canvas id="attackChart"></canvas>
        </main>
    </div>

    <script>
        // Ambil data dari PHP
        var labels = <?php echo $tanggal_json; ?>;
        var data = <?php echo $total_json; ?>;

        // Konfigurasi Chart.js
        var ctx = document.getElementById('attackChart').getContext('2d');
        var attackChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Serangan Harian',
                    data: data,
                    backgroundColor: 'rgba(255, 99, 132, 0.5)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>

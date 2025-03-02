<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

include "config.php";

// Ambil data serangan terbaru
$sql = "SELECT * FROM bruteforce_attacking_data ORDER BY timestamp DESC";
$result = $conn->query($sql);

// Data untuk grafik & tabel
$attack_data = [];
$timestamps = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $attack_data[] = $row;
        $timestamps[] = $row['timestamp'];
    }
}

// Ambil tahun-tahun unik dari data serangan
$sql_years = "SELECT DISTINCT YEAR(timestamp) as year FROM bruteforce_attacking_data ORDER BY year DESC";
$result_years = $conn->query($sql_years);
$years = [];

if ($result_years->num_rows > 0) {
    while ($row = $result_years->fetch_assoc()) {
        $years[] = $row['year'];
    }
}

// Ambil bulan-bulan unik dari data serangan
$sql_months = "SELECT DISTINCT DATE_FORMAT(timestamp, '%Y-%m') as month FROM bruteforce_attacking_data ORDER BY month DESC";
$result_months = $conn->query($sql_months);
$months = [];

if ($result_months->num_rows > 0) {
    while ($row = $result_months->fetch_assoc()) {
        $months[] = $row['month'];
    }
}

// Ambil minggu-minggu unik dari data serangan (dalam format ISO)
$sql_weeks = "SELECT DISTINCT YEAR(timestamp) as year, WEEK(timestamp, 3) as week FROM bruteforce_attacking_data ORDER BY year DESC, week DESC";
$result_weeks = $conn->query($sql_weeks);
$weeks = [];

if ($result_weeks->num_rows > 0) {
    while ($row = $result_weeks->fetch_assoc()) {
        $weeks[] = $row['year'] . '-W' . sprintf('%02d', $row['week']);
    }
}

// Ambil hari-hari unik dari data serangan
$sql_days = "SELECT DISTINCT DATE(timestamp) as day FROM bruteforce_attacking_data ORDER BY day DESC";
$result_days = $conn->query($sql_days);
$days = [];

if ($result_days->num_rows > 0) {
    while ($row = $result_days->fetch_assoc()) {
        $days[] = $row['day'];
    }
}

$attack_data_json = json_encode($attack_data);
$years_json = json_encode($years);
$months_json = json_encode($months);
$weeks_json = json_encode($weeks);
$days_json = json_encode($days);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Wi-Fi Bruteforce Monitor - Sort By</title>
    <link rel="stylesheet" href="style_dashboard/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid black; text-align: center; padding: 8px; }
        th { background-color: #f2f2f2; }
        .hidden { display: none; }
        button:disabled { background-color: #ccc; cursor: not-allowed; }
        #time-description { font-weight: bold; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h3>Dashboard Wi-Fi Bruteforce Monitor</h3>
            <nav>
                <ul>
                    <li><a href="dashboard.php">🏠 Home</a></li>
                    <li><a href="sort_by.php" class="active">🔃 Sort by</a></li>
                    <li><a href="settings.php">⚙️ Settings</a></li>
                    <li><a href="account_manager.php">👤</a></li>
                </ul>
            </nav>
        </header>

        <main>
            <h2>Sort Data</h2>

            <!-- Tombol Sort -->
            <div class="sort-buttons">
                <p>Sort By:</p>
                <button onclick="changeView('bar')">Bar</button>
                <button onclick="changeView('line')">Line</button>
                <button onclick="changeView('pie')">Pie</button>
                <button onclick="changeView('table')">Table</button>
            </div>
            <div class="sort-buttons">
                <p>Sort By Time:</p>
                <button id="time-all" onclick="filterByTime('all')">All</button>
                <button id="time-year" onclick="showDropdown('year')">Year</button>
                <button id="time-month" onclick="showDropdown('month')">Month</button>
                <button id="time-week" onclick="showDropdown('week')">Week</button>
                <button id="time-day" onclick="showDropdown('day')">Day</button>
                <select id="yearDropdown" class="hidden" onchange="filterByTime('year', this.value)">
                    <option value="">Pilih Tahun</option>
                </select>
                <select id="monthDropdown" class="hidden" onchange="filterByTime('month', this.value)">
                    <option value="">Pilih Bulan</option>
                </select>
                <select id="weekDropdown" class="hidden" onchange="filterByTime('week', this.value)">
                    <option value="">Pilih Minggu</option>
                </select>
                <select id="dayDropdown" class="hidden" onchange="filterByTime('day', this.value)">
                    <option value="">Pilih Hari</option>
                </select>
            </div>

            <!-- Chart -->
            <div id="chartContainer">
                <h2>Visualisasi Serangan</h2>
                <p id="time-description">Menampilkan semua data serangan</p> <!-- Keterangan waktu -->
                <canvas id="attackChart"></canvas>
            </div>

            <!-- Tabel -->
            <div id="tableContainer" class="hidden">
                <h2>Data Serangan</h2>
                <table>
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Jenis Serangan</th>
                            <th>Jumlah Serangan</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody"></tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        var attackData = <?php echo $attack_data_json; ?>;
        var years = <?php echo $years_json; ?>;
        var months = <?php echo $months_json; ?>;
        var weeks = <?php echo $weeks_json; ?>;
        var days = <?php echo $days_json; ?>;
        var chart;
        var currentChartType = 'bar';

        function renderChart(filteredData) {
            let attackTypes = filteredData.map(a => a.attack_type);
            let attackCounts = filteredData.map(a => a.attack_count);

            let ctx = document.getElementById('attackChart').getContext('2d');
            if (chart) chart.destroy();

            chart = new Chart(ctx, {
                type: currentChartType,
                data: {
                    labels: attackTypes,
                    datasets: [{
                        label: 'Jumlah Serangan',
                        data: attackCounts,
                        backgroundColor: ['rgba(255, 99, 132, 0.5)', 'rgba(54, 162, 235, 0.5)', 'rgba(255, 206, 86, 0.5)'],
                        borderColor: ['rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: currentChartType === 'pie' ? {} : { y: { beginAtZero: true } }
                }
            });
        }

        function changeView(view) {
            // Sembunyikan semua dropdown
            document.getElementById('yearDropdown').classList.add('hidden');
            document.getElementById('monthDropdown').classList.add('hidden');
            document.getElementById('weekDropdown').classList.add('hidden');
            document.getElementById('dayDropdown').classList.add('hidden');

            // Sembunyikan atau tampilkan chart dan tabel
            document.getElementById('chartContainer').classList.add('hidden');
            document.getElementById('tableContainer').classList.add('hidden');

            // Ambil semua tombol "Sort By Time" dan dropdown
            let timeButtons = document.querySelectorAll('.sort-buttons button[id^="time-"]');
            let dropdowns = document.querySelectorAll('.sort-buttons select');

            if (view === 'table') {
                // Tampilkan tabel
                document.getElementById('tableContainer').classList.remove('hidden');
                populateTable(attackData);

                // Nonaktifkan tombol "Sort By Time" dan dropdown
                timeButtons.forEach(btn => btn.disabled = true);
                dropdowns.forEach(dropdown => dropdown.disabled = true);
            } else {
                // Tampilkan chart
                currentChartType = view;
                document.getElementById('chartContainer').classList.remove('hidden');
                renderChart(attackData);

                // Aktifkan tombol "Sort By Time" dan dropdown
                timeButtons.forEach(btn => btn.disabled = false);
                dropdowns.forEach(dropdown => dropdown.disabled = false);
            }
        }

        function populateTable(data) {
            let tableBody = document.getElementById("tableBody");
            tableBody.innerHTML = "";

            data.forEach((attack, index) => {
                let row = `<tr>
                    <td>${index + 1}</td>
                    <td>${attack.attack_type}</td>
                    <td>${attack.attack_count}</td>
                    <td>${attack.timestamp}</td>
                </tr>`;
                tableBody.innerHTML += row;
            });
        }

        function showDropdown(type) {
            document.getElementById('yearDropdown').classList.add('hidden');
            document.getElementById('monthDropdown').classList.add('hidden');
            document.getElementById('weekDropdown').classList.add('hidden');
            document.getElementById('dayDropdown').classList.add('hidden');

            let dropdown = document.getElementById(`${type}Dropdown`);
            dropdown.classList.remove('hidden');
            dropdown.innerHTML = `<option value="">Pilih ${type.charAt(0).toUpperCase() + type.slice(1)}</option>`;

            let data = [];
            switch (type) {
                case 'year': data = years; break;
                case 'month': data = months; break;
                case 'week': data = weeks; break;
                case 'day': data = days; break;
            }

            data.forEach(item => {
                dropdown.innerHTML += `<option value="${item}">${item}</option>`;
            });
        }

        function filterByTime(type, value) {
            let filteredData = attackData.filter(attack => {
                let attackDate = new Date(attack.timestamp);

                switch (type) {
                    case 'year': return attackDate.getFullYear() === parseInt(value);
                    case 'month': return attackDate.toISOString().slice(0, 7) === value;
                    case 'week':
                        let selectedYear = value.split('-W')[0];
                        let selectedWeek = value.split('-W')[1];
                        let attackYear = attackDate.getFullYear();
                        let attackWeek = getWeekNumber(attackDate);
                        return attackYear === parseInt(selectedYear) && attackWeek === parseInt(selectedWeek);
                    case 'day': return attackDate.toISOString().slice(0, 10) === value;
                    default: return true;
                }
            });

            let descriptionText = {
                'year': `Menampilkan data serangan untuk tahun ${value}`,
                'month': `Menampilkan data serangan untuk bulan ${value}`,
                'week': `Menampilkan data serangan untuk minggu ${value}`,
                'day': `Menampilkan data serangan untuk hari ${value}`,
                'all': "Menampilkan semua data serangan"
            };

            document.getElementById('time-description').innerText = descriptionText[type];
            renderChart(filteredData);
        }

        function getWeekNumber(date) {
            const startOfYear = new Date(date.getFullYear(), 0, 1);
            const pastDaysOfYear = (date - startOfYear) / 86400000;
            return Math.ceil((pastDaysOfYear + startOfYear.getDay() + 1) / 7);
        }

        renderChart(attackData);
    </script>
</body>
</html>
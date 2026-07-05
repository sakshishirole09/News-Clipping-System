
<?php
$mysqli = new mysqli("localhost", "root", "", "news_clipping");
if ($mysqli->connect_errno) {
    die("Failed to connect: " . $mysqli->connect_error);
}

function getCount($mysqli, $table, $condition = "1=1") {
    $result = $mysqli->query("SELECT COUNT(*) AS total FROM $table WHERE $condition");
    return $result->fetch_assoc()['total'];
}

$imagesCount = getCount($mysqli, "uploaded_images");
$tagsCount = getCount($mysqli, "tags", "deleted = 0");
$departmentsCount = getCount($mysqli, "departments", "deleted = 0");
$newspapersCount = getCount($mysqli, "newspapers", "deleted = 0");

$catData = $mysqli->query("SELECT category, COUNT(*) as count FROM uploaded_images GROUP BY category");
$uploadsPerDay = $mysqli->query("SELECT date, COUNT(*) as count FROM uploaded_images GROUP BY date ORDER BY date");

$imgDept = $mysqli->query("
    SELECT d.name, COUNT(ui.id) as count
    FROM departments d
    LEFT JOIN uploaded_images ui ON ui.department = d.id
    WHERE d.deleted = 0
    GROUP BY d.name
");

$imgNews = $mysqli->query("SELECT n.name, COUNT(*) as count FROM uploaded_images ui JOIN newspapers n ON ui.newspaper = n.id WHERE n.deleted = 0 GROUP BY n.name");

$tagsData = $mysqli->query("SELECT t.name, 0 as count FROM tags t WHERE t.deleted = 0");
$tagCounts = [];
while ($row = $tagsData->fetch_assoc()) {
    $tagCounts[$row['name']] = 0;
}
$images = $mysqli->query("SELECT tags FROM uploaded_images");
while ($row = $images->fetch_assoc()) {
    $ids = explode(",", $row['tags']);
    foreach ($ids as $id) {
        $tag = $mysqli->query("SELECT name FROM tags WHERE id = $id AND deleted = 0")->fetch_assoc();
        if ($tag) $tagCounts[$tag['name']]++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>News Clipping Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body{background:#f4f9ff;font-family:sans-serif}
        .navbar-custom{background:#343a40}
        .navbar-custom .navbar-brand,.navbar-custom .nav-link{color:#fff}
        .navbar-custom .nav-link:hover{color:#ffc107}
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-custom mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Admin Panel</a>
    <div class="collapse navbar-collapse justify-content-end">
      <ul class="navbar-nav">
        <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="masters.php">Menu</a></li>
        <li class="nav-item"><a class="nav-link" href="uploadimg.php">Upload</a></li>
        <li class="nav-item"><a class="nav-link" href="admin.php">Search</a></li>
        <li class="nav-item"><a class="nav-link" href="uploaded-images.php">Todays Images</a></li>
        <li class="nav-item"><button class="btn btn-outline-light ms-2" onclick="handleLogout()">Logout</button></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container mt-4">
    <h2 class="mb-4 text-center">📊 News Clipping Dashboard</h2>

    <!-- Stat Summary Cards -->
    <div class="row text-white mb-4">
        <div class="col-md-3">
            <div class="card bg-primary shadow-sm rounded-3">
                <div class="card-body text-center">
                    <h5>Images</h5>
                    <h2><?= $imagesCount ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success shadow-sm rounded-3">
                <div class="card-body text-center">
                    <h5>Tags</h5>
                    <h2><?= $tagsCount ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning shadow-sm rounded-3">
                <div class="card-body text-center">
                    <h5>Departments</h5>
                    <h2><?= $departmentsCount ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger shadow-sm rounded-3">
                <div class="card-body text-center">
                    <h5>Newspapers</h5>
                    <h2><?= $newspapersCount ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Sections in Cards -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white text-center">Category Breakdown</div>
                <div class="card-body">
                    <canvas id="catChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white text-center">Daily Uploads</div>
                <div class="card-body">
                    <canvas id="dailyChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-warning text-white text-center">Images per Department</div>
                <div class="card-body">
                    <canvas id="deptChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-danger text-white text-center">Images per Newspaper</div>
                <div class="card-body">
                    <canvas id="newsChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white text-center">Images per Tag</div>
                <div class="card-body">
                    <canvas id="tagChart" style="max-height:320px"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const catCtx = document.getElementById('catChart').getContext('2d');
    new Chart(catCtx, {
        type: 'pie',
        data: {
            labels: [<?php while($row = $catData->fetch_assoc()) echo "'{$row['category']}',"; ?>],
            datasets: [{
                data: [<?php $catData->data_seek(0); while($row = $catData->fetch_assoc()) echo "{$row['count']},"; ?>],
                backgroundColor: ['#28a745', '#dc3545']
            }]
        },
        options: { plugins: { title: { display: true, text: 'Positive vs Negative' } } }
    });

    const dailyCtx = document.getElementById('dailyChart').getContext('2d');
    new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: [<?php while($row = $uploadsPerDay->fetch_assoc()) echo "'{$row['date']}',"; ?>],
            datasets: [{
                label: 'Uploads',
                data: [<?php $uploadsPerDay->data_seek(0); while($row = $uploadsPerDay->fetch_assoc()) echo "{$row['count']},"; ?>],
                borderColor: '#007bff',
                fill: false,
                tension: 0.3
            }]
        },
        options: { plugins: { title: { display: true, text: 'Uploads Per Day' } } }
    });

    const deptCtx = document.getElementById('deptChart').getContext('2d');
    new Chart(deptCtx, {
        type: 'bar',
        data: {
            labels: [<?php while($row = $imgDept->fetch_assoc()) echo "'{$row['name']}',"; ?>],
            datasets: [{
                label: 'Images',
                data: [<?php $imgDept->data_seek(0); while($row = $imgDept->fetch_assoc()) echo "{$row['count']},"; ?>],
                backgroundColor: '#17a2b8'
            }]
        },
        options: {
            indexAxis: 'y',
            plugins: { title: { display: true, text: 'Images Per Department' } }
        }
    });

    const newsCtx = document.getElementById('newsChart').getContext('2d');
    new Chart(newsCtx, {
        type: 'polarArea',
        data: {
            labels: [<?php while($row = $imgNews->fetch_assoc()) echo "'{$row['name']}',"; ?>],
            datasets: [{
                data: [<?php $imgNews->data_seek(0); while($row = $imgNews->fetch_assoc()) echo "{$row['count']},"; ?>],
                backgroundColor: ['#ff6384', '#36a2eb', '#ffcd56', '#4bc0c0']
            }]
        },
        options: { plugins: { title: { display: true, text: 'Images Per Newspaper' } } }
    });

    const tagCtx = document.getElementById('tagChart').getContext('2d');
    new Chart(tagCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($tagCounts)) ?>,
            datasets: [{
                data: <?= json_encode(array_values($tagCounts)) ?>,
                backgroundColor: ['#007bff', '#ffc107', '#dc3545', '#20c997', '#6610f2', '#fd7e14']
            }]
        },
        options: { plugins: { title: { display: true, text: 'Images Per Tag' } } }
    });
</script>
</body>
</html>
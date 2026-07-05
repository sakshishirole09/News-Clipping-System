<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "news_clipping";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$categoriesArr = [["name" => "Positive"], ["name" => "Negative"]];

$departments = $conn->query("SELECT id, name FROM departments ORDER BY name");
$newspapers = $conn->query("SELECT id, name FROM newspapers ORDER BY name");
$tagsList = $conn->query("SELECT id, name FROM tags ORDER BY name");

$departmentsArr = $departments ? $departments->fetch_all(MYSQLI_ASSOC) : [];
$newspapersArr = $newspapers ? $newspapers->fetch_all(MYSQLI_ASSOC) : [];
$tagsArr = $tagsList ? $tagsList->fetch_all(MYSQLI_ASSOC) : [];

$departmentsById = [];
foreach ($departmentsArr as $d) {
    $departmentsById[$d['id']] = $d['name'];
}
$newspapersById = [];
foreach ($newspapersArr as $n) {
    $newspapersById[$n['id']] = $n['name'];
}
$tagsById = [];
foreach ($tagsArr as $t) {
    $tagsById[$t['id']] = $t['name'];
}

if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    $conn->query("DELETE FROM uploaded_images WHERE id = $deleteId");

    $filter_date_for_redirect = $_GET['filter_date'] ?? date('Y-m-d');
    header("Location: uploaded-images.php?filter_date=" . urlencode($filter_date_for_redirect));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
    $id = intval($_POST['update_id']);
    $department = $_POST['department'];
    $category = $_POST['category'];
    $tags = isset($_POST['tags']) ? implode(',', $_POST['tags']) : '';

    $imagePathUpdate = "";
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = "uploads/";
        $newImageName = time() . "_" . basename($_FILES['image']['name']);
        $targetFilePath = $uploadDir . $newImageName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFilePath)) {
            $imagePathUpdate = ", image_path = '" . $conn->real_escape_string($targetFilePath) . "'";
        }
    }

    $stmt = $conn->prepare("UPDATE uploaded_images SET department = ?, category = ?, tags = ? $imagePathUpdate WHERE id = ?");
    $query = "UPDATE uploaded_images SET department = ?, category = ?, tags = ? $imagePathUpdate WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sssi", $department, $category, $tags, $id);
    $stmt->execute();

    $filter_date_for_redirect = $_GET['filter_date'] ?? date('Y-m-d');
    header("Location: uploaded-images.php?filter_date=" . urlencode($filter_date_for_redirect));
    exit;
}

$filterDate = $_GET['filter_date'] ?? date('Y-m-d');

$sql = "SELECT * FROM uploaded_images WHERE date = ? ORDER BY id DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $filterDate);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Uploaded Images</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: #f4f8fb;
    }
    .image-card {
      margin-bottom: 20px;
    }
    .card {
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .card-img-top {
      width: 100%;
      height: 220px;
      object-fit: contain;
      background-color: #fff;
      padding: 10px;
    }
    .card-description {
      padding: 20px;
      font-size: 14px;
    }
    .navbar-custom {
      background-color: #343a40;
    }
    .navbar-custom .navbar-brand,
    .navbar-custom .nav-link {
      color: #ffffff;
    }
    .navbar-custom .nav-link:hover {
      color: #ffc107;
    }
  </style>
</head>
<body>

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

<nav class="navbar navbar-light bg-light px-3">
  <div class="container-fluid">
    <div class="mx-auto">
    <span class="navbar-brand mb-0 h1 fs-3">Uploaded Images</span>
  </div>
    <a href="uploadimg.php" class="btn btn-warning">Back to Upload</a>
  </div>
</nav>

<div class="container mt-4">
  <form method="GET" class="row mb-4">
    <div class="col-md-4">
      <input type="date" name="filter_date" class="form-control" value="<?= htmlspecialchars($filterDate) ?>">
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-primary ">Filter</button>
    </div>
    <div class="col-md-2">
      <a href="uploaded-images.php" class="btn btn-secondary px-4">Reset</a>
    </div>
  </form>

  <div class="row">
    <?php
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $isEditing = isset($_GET['edit']) && intval($_GET['edit']) === intval($row['id']);
            echo '<div class="col-md-4 image-card">';
            echo '  <div class="card">';
            echo '    <img src="' . htmlspecialchars($row['image_path']) . '" class="card-img-top" alt="Image">';
            echo '    <div class="card-description">';
            $dateObj = DateTime::createFromFormat('Y-m-d', $row['date']);
            $formattedDate = $dateObj ? $dateObj->format('d/m/Y') : htmlspecialchars($row['date']);
            $newspaperName = $newspapersById[$row['newspaper']] ?? htmlspecialchars($row['newspaper']);
            echo '      <h5>' . htmlspecialchars($newspaperName) . ' (' . $formattedDate . ')</h5>';

            if ($isEditing) {
                $selectedTags = explode(',', $row['tags']);
                ?>
                <form method="POST" enctype="multipart/form-data">
                  <input type="hidden" name="update_id" value="<?= $row['id'] ?>">
                  <div class="mb-2">
                    <label>Department:</label>
                    <select name="department" class="form-control">
                      <?php foreach ($departmentsArr as $dept): ?>
                        <option value="<?= $dept['id'] ?>" <?= ($dept['id'] == $row['department']) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($dept['name']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="mb-2">
                    <label>Category:</label>
                    <select name="category" class="form-control">
                      <?php foreach ($categoriesArr as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['name']) ?>" <?= ($cat['name'] == $row['category']) ? 'selected' : '' ?>>
                          <?= htmlspecialchars($cat['name']) ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>
                  <div class="mb-2">
                    <label>Tags:</label><br>
                    <?php foreach ($tagsArr as $tag): ?>
                      <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="tags[]" value="<?= $tag['id'] ?>" <?= in_array($tag['id'], $selectedTags) ? 'checked' : '' ?>>
                        <label class="form-check-label"><?= htmlspecialchars($tag['name']) ?></label>
                      </div>
                    <?php endforeach; ?>
                  </div>
                  <div class="mb-2">
                    <label>Replace Image (optional):</label>
                    <input type="file" name="image" class="form-control">
                  </div>
                  <button type="submit" class="btn btn-sm btn-success">Save</button>
                  <a href="uploaded-images.php?filter_date=<?= urlencode($filterDate) ?>" class="btn btn-sm btn-light">Cancel</a>
                </form>
                <?php
            } else {
                $deptName = $departmentsById[$row['department']] ?? htmlspecialchars($row['department']);
                echo '<div><strong>Department:</strong> ' . htmlspecialchars($deptName) . '</div>';
                echo '<div><strong>Category:</strong> ' . htmlspecialchars($row['category']) . '</div>';
                $tagIds = explode(',', $row['tags']);
                $tagNames = array_map(fn($id) => $tagsById[$id] ?? $id, $tagIds);
                echo '<div><strong>Tags:</strong> ' . htmlspecialchars(implode(', ', $tagNames)) . '</div>';
                echo '<br><div class="d-flex justify-content-between">';
                echo '<a href="?edit=' . $row['id'] . '&filter_date=' . urlencode($filterDate) . '" class="btn btn-sm btn-info">Edit</a>';
                echo '<a href="?delete=' . $row['id'] . '&filter_date=' . urlencode($filterDate) . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this image?\')">Delete</a>';
                echo '</div>';
            }

            echo '    </div>';
            echo '  </div>';
            echo '</div>';
        }
    } else {
        echo '<p class="text-center">No images found.</p>';
    }
    ?>
  </div>
</div>

<script>
function handleLogout() {
  if (confirm("Are you sure you want to logout?")) {
    window.location.href = "index.php";
  }
}
</script>
</body>
</html>

<?php $conn->close(); ?>
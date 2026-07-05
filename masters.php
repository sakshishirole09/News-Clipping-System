<?php
$host = 'localhost';
$user = 'root';
$password = ''; // Change if needed
$database = 'news_clipping';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Flags for edit mode
$editMode = false;
$editType = '';
$editId = 0;
$editName = '';

// Check if editing
if (isset($_GET['edit_department'])) {
    $editMode = true;
    $editType = 'department';
    $editId = (int)$_GET['edit_department'];
    $result = $conn->query("SELECT name FROM departments WHERE id = $editId");
    $editName = $result->fetch_assoc()['name'];
}
if (isset($_GET['edit_newspaper'])) {
    $editMode = true;
    $editType = 'newspaper';
    $editId = (int)$_GET['edit_newspaper'];
    $result = $conn->query("SELECT name FROM newspapers WHERE id = $editId");
    $editName = $result->fetch_assoc()['name'];
}
if (isset($_GET['edit_tag'])) {
    $editMode = true;
    $editType = 'tag';
    $editId = (int)$_GET['edit_tag'];
    $result = $conn->query("SELECT name FROM tags WHERE id = $editId");
    $editName = $result->fetch_assoc()['name'];
}

// Add or update department
if (isset($_POST['add_department'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $check = $conn->query("SELECT * FROM departments WHERE name = '$name'");
    if ($check->num_rows > 0) {
        $row = $check->fetch_assoc();
        if ($row['deleted'] == 1) {
            $conn->query("UPDATE departments SET deleted = 0 WHERE name = '$name'");
        } else {
            echo "<script>alert('Department already exists.'); window.location.href='masters.php';</script>";
            exit;
        }
    } else {
        $conn->query("INSERT INTO departments (name) VALUES ('$name')");
    }
    header("Location: masters.php");
}
if (isset($_POST['update_department'])) {
    $id = (int)$_POST['id'];
    $name = $conn->real_escape_string($_POST['name']);
    $conn->query("UPDATE departments SET name = '$name' WHERE id = $id");
    header("Location: masters.php");
}

// Add or update newspaper
if (isset($_POST['add_newspaper'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $check = $conn->query("SELECT * FROM newspapers WHERE name = '$name'");
    if ($check->num_rows > 0) {
        $row = $check->fetch_assoc();
        if ($row['deleted'] == 1) {
            $conn->query("UPDATE newspapers SET deleted = 0 WHERE name = '$name'");
        } else {
            echo "<script>alert('Newspaper already exists.'); window.location.href='masters.php?tab=newspaper';</script>";
            exit;
        }
    } else {
        $conn->query("INSERT INTO newspapers (name) VALUES ('$name')");
    }
    header("Location: masters.php?tab=newspaper");
}
if (isset($_POST['update_newspaper'])) {
    $id = (int)$_POST['id'];
    $name = $conn->real_escape_string($_POST['name']);
    $conn->query("UPDATE newspapers SET name = '$name' WHERE id = $id");
    header("Location: masters.php?tab=newspaper");
}

// Add or update tag
if (isset($_POST['add_tag'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $check = $conn->query("SELECT * FROM tags WHERE name = '$name'");
    if ($check->num_rows > 0) {
        $row = $check->fetch_assoc();
        if ($row['deleted'] == 1) {
            $conn->query("UPDATE tags SET deleted = 0 WHERE name = '$name'");
        } else {
            echo "<script>alert('Tag already exists.'); window.location.href='masters.php?tab=tag';</script>";
            exit;
        }
    } else {
        $conn->query("INSERT INTO tags (name) VALUES ('$name')");
    }
    header("Location: masters.php?tab=tag");
}
if (isset($_POST['update_tag'])) {
    $id = (int)$_POST['id'];
    $name = $conn->real_escape_string($_POST['name']);
    $conn->query("UPDATE tags SET name = '$name' WHERE id = $id");
    header("Location: masters.php?tab=tag");
}

// Soft delete
if (isset($_GET['delete_department'])) {
    $id = (int)$_GET['delete_department'];
    $conn->query("UPDATE departments SET deleted = 1 WHERE id = $id");
    header("Location: masters.php");
}
if (isset($_GET['delete_newspaper'])) {
    $id = (int)$_GET['delete_newspaper'];
    $conn->query("UPDATE newspapers SET deleted = 1 WHERE id = $id");
    header("Location: masters.php?tab=newspaper");
}
if (isset($_GET['delete_tag'])) {
    $id = (int)$_GET['delete_tag'];
    $conn->query("UPDATE tags SET deleted = 1 WHERE id = $id");
    header("Location: masters.php?tab=tag");
}

// Fetch data
$departments = $conn->query("SELECT * FROM departments WHERE deleted = 0");
$newspapers = $conn->query("SELECT * FROM newspapers WHERE deleted = 0");
$tags = $conn->query("SELECT * FROM tags WHERE deleted = 0");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f4f9ff; font-family:sans-serif; }
        .navbar-custom { background:#343a40; }
        .navbar-custom .navbar-brand, .navbar-custom .nav-link { color:#fff; }
        .navbar-custom .nav-link:hover { color:#ffc107; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-custom mb-4">
    <div class="container-fluid">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
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

<div class="container mt-5">
    <ul class="nav nav-tabs mb-3">
        <li class="nav-item">
            <a class="nav-link <?= !isset($_GET['tab']) || $_GET['tab'] == 'department' ? 'active' : '' ?>" href="masters.php">Department</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= isset($_GET['tab']) && $_GET['tab'] == 'newspaper' ? 'active' : '' ?>" href="masters.php?tab=newspaper">Newspaper</a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= isset($_GET['tab']) && $_GET['tab'] == 'tag' ? 'active' : '' ?>" href="masters.php?tab=tag">Tag</a>
        </li>
    </ul>

    <!-- Department Tab -->
    <?php if (!isset($_GET['tab']) || $_GET['tab'] == 'department'): ?>
        <h3>Department</h3>
        <form method="POST" class="mb-3 d-flex">
            <input type="text" name="name" class="form-control me-2" placeholder="Department name" required value="<?= ($editMode && $editType == 'department') ? htmlspecialchars($editName) : '' ?>">
            <?php if ($editMode && $editType == 'department'): ?>
                <input type="hidden" name="id" value="<?= $editId ?>">
                <button type="submit" name="update_department" class="btn btn-primary">Update</button>
                <a href="masters.php" class="btn btn-secondary ms-2">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add_department" class="btn btn-success">Add</button>
            <?php endif; ?>
        </form>
        <table class="table table-bordered">
            <thead><tr><th>Department</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                <?php while ($row = $departments->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td class="text-end">
                            <a href="?edit_department=<?= $row['id'] ?>" class="btn btn-warning btn-sm me-2">Edit</a>
                            <a href="?delete_department=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this department?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Newspaper Tab -->
    <?php if (isset($_GET['tab']) && $_GET['tab'] == 'newspaper'): ?>
        <h3>Newspaper</h3>
        <form method="POST" class="mb-3 d-flex">
            <input type="text" name="name" class="form-control me-2" placeholder="Newspaper name" required value="<?= ($editMode && $editType == 'newspaper') ? htmlspecialchars($editName) : '' ?>">
            <?php if ($editMode && $editType == 'newspaper'): ?>
                <input type="hidden" name="id" value="<?= $editId ?>">
                <button type="submit" name="update_newspaper" class="btn btn-primary">Update</button>
                <a href="masters.php?tab=newspaper" class="btn btn-secondary ms-2">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add_newspaper" class="btn btn-success">Add</button>
            <?php endif; ?>
        </form>
        <table class="table table-bordered">
            <thead><tr><th>Newspaper</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                <?php while ($row = $newspapers->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td class="text-end">
                            <a href="?tab=newspaper&edit_newspaper=<?= $row['id'] ?>" class="btn btn-warning btn-sm me-2">Edit</a>
                            <a href="?tab=newspaper&delete_newspaper=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this newspaper?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- Tag Tab -->
    <?php if (isset($_GET['tab']) && $_GET['tab'] == 'tag'): ?>
        <h3>Tag</h3>
        <form method="POST" class="mb-3 d-flex">
            <input type="text" name="name" class="form-control me-2" placeholder="Tag name" required value="<?= ($editMode && $editType == 'tag') ? htmlspecialchars($editName) : '' ?>">
            <?php if ($editMode && $editType == 'tag'): ?>
                <input type="hidden" name="id" value="<?= $editId ?>">
                <button type="submit" name="update_tag" class="btn btn-primary">Update</button>
                <a href="masters.php?tab=tag" class="btn btn-secondary ms-2">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add_tag" class="btn btn-success">Add</button>
            <?php endif; ?>
        </form>
        <table class="table table-bordered">
            <thead><tr><th>Tag</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
                <?php while ($row = $tags->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td class="text-end">
                            <a href="?tab=tag&edit_tag=<?= $row['id'] ?>" class="btn btn-warning btn-sm me-2">Edit</a>
                            <a href="?tab=tag&delete_tag=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this tag?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
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
<?php
// DB Connection
$conn = new mysqli("localhost", "root", "", "news_clipping");
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$departments = $conn->query("SELECT * FROM departments");
$newspapers  = $conn->query("SELECT * FROM newspapers");
$tags        = $conn->query("SELECT * FROM tags");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $department = $_POST['department'] ?? '';
  $newspaper  = $_POST['newspaper'] ?? '';
  $date       = $_POST['date'] ?? '';
  $category   = $_POST['category'] ?? '';
  $tagsStr    = isset($_POST['tags']) ? implode(',', $_POST['tags']) : '';
  $file       = $_FILES['image'] ?? null;

  if ($file && $file['error'] === 0 && $file['size'] <= 5_000_000) {
    $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = uniqid('', true) . ".$ext";
    $path = "uploads/$name";
    if (!is_dir('uploads')) mkdir('uploads', 0777, true);
    move_uploaded_file($file['tmp_name'], $path);

    $stmt = $conn->prepare("INSERT INTO uploaded_images (department, newspaper, date, category, tags, image_path) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param("ssssss", $department, $newspaper, $date, $category, $tagsStr, $path);
    $stmt->execute(); $stmt->close();
    $message = "Image uploaded successfully!";
  } else {
    $message = $file ? "Image too large (max 5 MB)." : "Please choose an image.";
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"><title>Upload News</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { background:#f4f9ff; font-family:sans-serif; }
        .navbar-custom { background:#343a40; }
        .navbar-custom .navbar-brand, .navbar-custom .nav-link { color:#fff; }
        .navbar-custom .nav-link:hover { color:#ffc107; }
    #thumbPreview{display:none;cursor:pointer;margin-left:8px;height:50px;width:60px;border-radius:5px}
    #fullPreviewOverlay{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.9);justify-content:center;align-items:center;flex-direction:column;z-index:1050;opacity:0;transition:.3s}
    #fullPreviewOverlay.show{display:flex;opacity:1} #fullPreviewOverlay img{width:100vw;height:100vh;object-fit:contain}
    .btn-group-overlay{position:absolute;bottom:40px;display:flex;gap:10px}
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-custom mb-4">
  <div class="container-fluid">
   
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

<div class="container">
  <h2 class="text-center upload-title"><i class="bi bi-upload"></i> Upload News Image</h2>
  <?php if (isset($message)): ?>
    <div class="alert alert-info text-center"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <div class="mb-3 row">
      <label class="col-md-3 col-form-label">Select Image</label>
      <div class="col-md-9 d-flex align-items-center">
        <input class="form-control" type="file" id="image" name="image" accept="image/*" required>
        <img id="thumbPreview" alt="thumb">
      </div>
    </div>

    <div class="mb-3 row">
      <label class="col-md-3 col-form-label">Department</label>
      <div class="col-md-9">
        <select class="form-select" name="department" required>
          <option value="" disabled selected>Select department</option>
          <?php while($d=$departments->fetch_assoc()): ?>
            <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>

    <div class="mb-3 row">
      <label class="col-md-3 col-form-label">Newspaper</label>
      <div class="col-md-9">
        <select class="form-select" name="newspaper" required>
          <option value="" disabled selected>Select newspaper</option>
          <?php while($n=$newspapers->fetch_assoc()): ?>
            <option value="<?= $n['id'] ?>"><?= htmlspecialchars($n['name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
    </div>

    <div class="mb-3 row">
      <label class="col-md-3 col-form-label">Date</label>
      <div class="col-md-9"><input class="form-control" type="date" name="date" required></div>
    </div>

    <div class="mb-3 row">
      <label class="col-md-3 col-form-label">Category</label>
      <div class="col-md-9">
        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="category" value="Positive" required> Positive</div>
        <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="category" value="Negative"> Negative</div>
      </div>
    </div>

    <div class="mb-3 row">
      <label class="col-md-3 col-form-label">Tags</label>
      <div class="col-md-9 border rounded p-2" style="max-height:120px;overflow-y:auto">
        <?php while($t=$tags->fetch_assoc()): ?>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="tags[]" value="<?= $t['id'] ?>">
            <label class="form-check-label"><?= htmlspecialchars($t['name']) ?></label>
          </div>
        <?php endwhile; ?>
      </div>
    </div>

    <div class="text-center mb-4">
      <button class="btn btn-primary px-4" type="submit"><i class="bi bi-upload"></i> Upload</button>
    </div>
  </form>
</div>

<div id="fullPreviewOverlay">
  <button type="button" class="btn-close position-absolute top-0 end-0 m-3" aria-label="Close preview"></button>
  <img id="fullPreviewImage" alt="Full preview">
  <div class="btn-group-overlay">
    <button class="btn btn-success" id="editBtn"><i class="bi bi-pencil"></i> Edit</button>
    <button class="btn btn-danger" id="cancelBtn"><i class="bi bi-x-circle"></i> Cancel</button>
    <button class="btn btn-primary" id="saveBtn"><i class="bi bi-check-circle"></i> Save</button>
  </div>
  <input type="file" id="editInput" accept="image/*" hidden>
</div>

<script>
const imageInput = document.getElementById('image'), thumb = document.getElementById('thumbPreview');
const overlay = document.getElementById('fullPreviewOverlay'), full = document.getElementById('fullPreviewImage');
const editInput = document.getElementById('editInput');

imageInput.onchange = e => { if(e.target.files[0]) preview(e.target.files[0]); };
thumb.onclick = () => { if(imageInput.files.length) showOverlay(); };
document.querySelector('.btn-close').onclick = hideOverlay;
document.getElementById('cancelBtn').onclick = hideOverlay;
document.getElementById('saveBtn').onclick = () => { hideOverlay(); alert('Image updated locally. Click Upload to save.'); };
document.getElementById('editBtn').onclick = () => editInput.click();
editInput.onchange = () => { if(editInput.files[0]) preview(editInput.files[0]); };

function preview(file) {
  const url = URL.createObjectURL(file);
  thumb.src = url; full.src = url;
  thumb.style.display = 'inline-block';
  const dt = new DataTransfer(); dt.items.add(file); imageInput.files = dt.files;
}
function showOverlay() { overlay.style.display = 'flex'; requestAnimationFrame(() => overlay.classList.add('show')); }
function hideOverlay() { overlay.classList.remove('show'); setTimeout(() => overlay.style.display = 'none', 300); }
function logout() { if (confirm('Logout?')) location.href = 'index.php'; }
</script>
</body>
</html>

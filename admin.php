<?php
/* ── DB config ───────────────────────────────────────── */
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "news_clipping";

$conn = new mysqli($DB_HOST,$DB_USER,$DB_PASS,$DB_NAME);
if($conn->connect_error) die("DB error ".$conn->connect_error);

/* helper: id-to-name maps */
function table_map(mysqli $c,string $table){
  $out=[];$q=$c->query("SELECT id,name FROM $table ORDER BY name");
  while($r=$q->fetch_assoc()) $out[$r['id']]=$r['name'];
  return $out;
}
$deptMap = table_map($conn,'departments');
$newsMap = table_map($conn,'newspapers');
$tagMap  = table_map($conn,'tags');
$catList = array_unique(array_column(
            $conn->query("SELECT category FROM uploaded_images")->fetch_all(MYSQLI_ASSOC),'category'));
/* current filter values */
$f_dept = $_GET['department'] ?? '';
$f_news = $_GET['newspaper']  ?? '';
$f_tag  = $_GET['tag']        ?? '';
$f_cat  = $_GET['category']   ?? '';

/* was the Filter button pressed? */
$filterSubmitted = isset($_GET['filterBtn']);

/* build WHERE */
$where="1=1";
if($f_dept && $f_dept!=='All')      $where.=" AND department=".(int)$f_dept;
if($f_news && $f_news!=='All')      $where.=" AND newspaper=".(int)$f_news;
if($f_tag  && $f_tag!=='All')       $where.=" AND FIND_IN_SET(".(int)$f_tag.",tags)";
if($f_cat  && $f_cat!=='All')       $where.=" AND category='".$conn->real_escape_string($f_cat)."'";

$sql = "SELECT * FROM uploaded_images WHERE $where ORDER BY uploaded_at DESC";
$res = $filterSubmitted ? $conn->query($sql) : null;
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><title>Uploaded Images</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{background:#f4f9ff;font-family:sans-serif}
.navbar-custom{background:#343a40}.navbar-custom .navbar-brand,.navbar-custom .nav-link{color:#fff}
.navbar-custom .nav-link:hover{color:#ffc107}
.filter-container{margin:30px auto;max-width:95%}
.image-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;padding:0 30px 30px;margin:0 auto}
.image-card{width:320px;height:370px;position:relative;background:#fff;border-radius:10px;
            box-shadow:0 2px 8px rgba(0,0,0,.1);padding:10px;text-align:center;display:flex;flex-direction:column}
.image-card img{width:300px;height:200px;object-fit:contain;background:#f8f9fa;border-radius:6px;margin:0 auto 5px}
.image-description{font-size:.9rem;text-align:left;color:#333;line-height:1.35}
.icon-expand{position:absolute;top:8px;right:8px;background:rgba(255,255,255,.8);border-radius:50%;
             padding:6px;cursor:pointer;transition:background.3s}
.icon-expand:hover{background:rgba(255,193,7,.9)}
</style></head><body>

<nav class="navbar navbar-expand-lg navbar-custom mb-4">
 <div class="container-fluid">
  <a class="navbar-brand" href="#">Admin Panel</a>
  <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
  <div class="collapse navbar-collapse justify-content-end" id="nav">
    <ul class="navbar-nav">
      <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
      <li class="nav-item"><a class="nav-link" href="masters.php">Menu</a></li>
      <li class="nav-item"><a class="nav-link" href="uploadimg.php">Upload</a></li>
      <li class="nav-item"><a class="nav-link" href="admin.php">Search</a></li>
      <li class="nav-item"><a class="nav-link" href="uploaded-images.php">Todays Images</a></li>
      <li class="nav-item"><button class="btn btn-outline-light ms-2" onclick="if(confirm('Logout?'))location.href='index.php'">Logout</button></li>
    </ul>
  </div>
 </div>
</nav>

<div class="container filter-container">
 <h4>Filter Images</h4>
 <form class="row g-3 align-items-end" method="get">
  <div class="col-md-2">
   <select name="department" class="form-select">
    <option value="" <?= $f_dept===''?'selected':''?>
      >Select Department</option>
    <option value="All" <?= $f_dept==='All'?'selected':''?>>All</option>
    <?php foreach($deptMap as $id=>$name):?>
      <option value="<?= $id ?>" <?= $f_dept==$id?'selected':''?>><?= htmlspecialchars($name) ?></option>
    <?php endforeach;?>
   </select>
  </div>
  <div class="col-md-2">
   <select name="newspaper" class="form-select">
    <option value="" <?= $f_news===''?'selected':''?>>Select Newspaper</option>
    <option value="All" <?= $f_news==='All'?'selected':''?>>All</option>
    <?php foreach($newsMap as $id=>$name):?>
      <option value="<?= $id ?>" <?= $f_news==$id?'selected':''?>><?= htmlspecialchars($name) ?></option>
    <?php endforeach;?>
   </select>
  </div>
  <div class="col-md-2">
   <select name="tag" class="form-select">
    <option value="" <?= $f_tag===''?'selected':''?>>Select Tag</option>
    <option value="All" <?= $f_tag==='All'?'selected':''?>>All</option>
    <?php foreach($tagMap as $id=>$name):?>
      <option value="<?= $id ?>" <?= $f_tag==$id?'selected':''?>><?= htmlspecialchars($name) ?></option>
    <?php endforeach;?>
   </select>
  </div>
  <div class="col-md-2">
   <select name="category" class="form-select">
    <option value="" <?= $f_cat===''?'selected':''?>>Select Category</option>
    <option value="All" <?= $f_cat==='All'?'selected':''?>>All</option>
    <?php foreach($catList as $c):?>
      <option value="<?= htmlspecialchars($c) ?>" <?= $f_cat==$c?'selected':''?>><?= htmlspecialchars($c) ?></option>
    <?php endforeach;?>
   </select>
  </div>
  <div class="col-md-4 d-flex gap-2">
    <button type="submit" name="filterBtn" class="btn btn-primary w-50">Filter</button>
    <a href="admin.php" class="btn btn-secondary w-50">Reset</a>
  </div>
 </form>
</div>

<div class="container">
 <div class="image-grid">
<?php if(!$filterSubmitted): ?>
   <p class="text-muted">No filters selected</p>
<?php elseif($res && $res->num_rows): 
      while($row=$res->fetch_assoc()):
        $deptName = $deptMap[$row['department']] ?? 'Unknown';
        $newsName = $newsMap[$row['newspaper']] ?? 'Unknown';
        $tagNames=[];
        foreach(explode(',',$row['tags']) as $tid) if(isset($tagMap[$tid])) $tagNames[]=$tagMap[$tid];
        $tagStr = $tagNames ? implode(', ',$tagNames) : '—';
        $addedOn = date('d/m/Y', strtotime($row['date']));
?>
   <div class="image-card">
     <img src="<?= htmlspecialchars($row['image_path']) ?>" alt="">
     <div class="icon-expand" data-img="<?= htmlspecialchars($row['image_path']) ?>">
       <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#ffc107" class="bi bi-arrows-fullscreen" viewBox="0 0 16 16">
         <path fill-rule="evenodd" d="M1 1h5v1H2v4H1V1zm14 0v5h-1V2h-4V1h5zm0 14h-5v-1h4v-4h1v5zm-14 0v-5h1v4h4v1H1z"/>
       </svg>
     </div>
     <div class="image-description">
       <strong>Date:</strong> <?= htmlspecialchars($addedOn) ?><br>
       <strong>Department:</strong> <?= htmlspecialchars($deptName) ?><br>
       <strong>Newspaper:</strong> <?= htmlspecialchars($newsName) ?><br>
       <strong>Tags:</strong> <?= htmlspecialchars($tagStr) ?><br>
       <strong>Category:</strong> <?= htmlspecialchars($row['category']) ?>
     </div>
   </div>
<?php endwhile; else: ?>
   <p>No images found for selected filters.</p>
<?php endif; ?>
 </div>
</div>

<!-- Modal -->
<div class="modal fade" id="imgModal" tabindex="-1">
 <div class="modal-dialog modal-dialog-centered modal-lg">
  <div class="modal-content">
   <div class="modal-header"><h5 class="modal-title">Full-size Image</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
   <div class="modal-body text-center"><img id="modalImg" style="width:100%;height:auto;border-radius:8px"></div>
  </div>
 </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.querySelectorAll('.icon-expand').forEach(btn=>{
  btn.onclick=()=>{
    document.getElementById('modalImg').src=btn.dataset.img;
    new bootstrap.Modal(document.getElementById('imgModal')).show();
  };
});
</script>
</body></html>
<?php $conn->close(); ?>
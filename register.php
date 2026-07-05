<?php
session_start();

/* --------------------------------------------------------------------
   FIRST: capture the ajax request that stores the captcha in session
   ------------------------------------------------------------------*/
if (isset($_GET['action']) && $_GET['action'] === 'captcha' && isset($_GET['code'])) {
    $_SESSION['captcha'] = strtoupper($_GET['code']);   // store the captcha
    exit;                                               // nothing else to send
}

/* --------------------------------------------------------------------
   Database connection settings – CHANGE as per your setup
   ------------------------------------------------------------------*/
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";   // change this as needed
$DB_NAME = "news_clipping";

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_error) die("DB connection failed: " . $mysqli->connect_error);

/* --------------------------------------------------------------------
   Fetch departments from database for the select dropdown
   ------------------------------------------------------------------*/
$departments = $mysqli->query("SELECT id, name FROM departments ORDER BY name");
$departmentsArr = $departments ? $departments->fetch_all(MYSQLI_ASSOC) : [];

$errors = [];
$success = false;

/* --------------------------- form handler --------------------------*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // get POST safely
    $fullName   = trim($_POST['fullName'] ?? '');
    $gender     = trim($_POST['gender'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $mobile     = trim($_POST['mobile'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $captchaIn  = trim($_POST['captchaInput'] ?? '');

    // simple validation
    if (!$fullName)   $errors[] = "Full Name is required";
    if (!$gender)     $errors[] = "Gender is required";
    if (!$department) $errors[] = "Department is required";
    if (!$mobile)     $errors[] = "Mobile Number is required";
    if (!$email)      $errors[] = "Email is required";
    if (!$password)   $errors[] = "Password is required";
    if (!$captchaIn)  $errors[] = "Captcha is required";

    if (empty($_SESSION['captcha']) || strtoupper($captchaIn) !== $_SESSION['captcha']) {
        $errors[] = "Captcha does not match";
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $stmt = $mysqli->prepare(
            "INSERT INTO users (full_name, gender, department, mobile, email, password) VALUES (?, ?, ?, ?, ?, ?)"
        );
        if (!$stmt) {
            $errors[] = "Database prepare error: " . $mysqli->error;
        } else {
            $stmt->bind_param("ssssss", $fullName, $gender, $department, $mobile, $email, $hash);

            if ($stmt->execute()) {
                $success = true;
                unset($_SESSION['captcha']);
            } else {
                $errors[] = "Database error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Bootstrap Registration Form</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
html, body {
    height: 100%;
    margin: 0;
    overflow: hidden;
    background: linear-gradient(to right, #e0f7fa, #ffffff);
    font-family: Arial, sans-serif;
}
body {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    height: 100vh;
    box-sizing: border-box;
}
form {
    background: #fff;
    padding: 30px 40px;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0, 0, 0, .1);
    width: 100%;
    max-width: 900px;
    height: 90vh;
    overflow-y: auto;
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
}
.form-title {
    text-align: center;
    margin-bottom: 25px;
    font-weight: 600;
}
.row-custom {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
}
.col-custom {
    flex: 1;
}
.captcha-box {
    background: #ddd;
    font-weight: bold;
    text-align: center;
    letter-spacing: 3px;
    font-size: 20px;
    padding: 10px;
    cursor: pointer;
    border-radius: 5px;
    user-select: none;
    width: 150px;
    margin-top: 5px;
}
.btn-submit {
    margin-top: 20px;
}
.success-wrap {
    height: 90vh;
    width: 100%;
    max-width: 900px;
    margin: auto;
    background: #fff;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,.1);
}
</style>
</head>
<body>

<?php if ($success): ?>
<div class="d-flex flex-column align-items-center justify-content-center success-wrap">
  <h3 class="text-success">Registration Successful!</h3>
  <p>Thank you for registering.</p>
  <div>
    <a href="index.php" class="btn btn-link">Login here</a>
  </div>
</div>
<?php else: ?>

<form method="POST" novalidate>
  <h3 class="form-title">Registration Form</h3>

  <?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0">
    <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
  </ul></div>
  <?php endif; ?>

  <div class="mb-3">
    <label class="form-label" for="fullName">Full Name</label>
    <input type="text" id="fullName" name="fullName" class="form-control" required
           value="<?= htmlspecialchars($_POST['fullName'] ?? '') ?>" />
  </div>

  <div class="row-custom">
    <div class="col-custom">
      <label class="form-label" for="gender">Gender</label>
      <select id="gender" name="gender" class="form-select" required>
        <option value="">Select</option>
        <option <?= (($_POST['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
        <option <?= (($_POST['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
        <option <?= (($_POST['gender'] ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
      </select>
    </div>
    <div class="col-custom">
      <label class="form-label" for="department">Department</label>
      <select id="department" name="department" class="form-select" required>
        <option value="">Select Department</option>
        <?php
          foreach ($departmentsArr as $d) {
              $sel = (($_POST['department'] ?? '') == $d['id']) ? 'selected' : '';
              echo '<option value="' . htmlspecialchars($d['id']) . '" ' . $sel . '>' . htmlspecialchars($d['name']) . '</option>';
          }
        ?>
      </select>
    </div>
  </div>

  <div class="row-custom">
    <div class="col-custom">
      <label class="form-label" for="mobile">Mobile Number</label>
      <input type="text" id="mobile" name="mobile" class="form-control" required
             value="<?= htmlspecialchars($_POST['mobile'] ?? '') ?>" />
    </div>
    <div class="col-custom">
      <label class="form-label" for="email">Email</label>
      <input type="email" id="email" name="email" class="form-control" required
             value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" />
    </div>
  </div>

  <div class="mb-3">
    <label class="form-label" for="password">Password</label>
    <input type="password" id="password" name="password" class="form-control" required />
  </div>

  <div class="row-custom align-items-center">
    <div class="col-custom" style="max-width:300px">
      <label class="form-label" for="captchaInput">Captcha</label>
      <input type="text" id="captchaInput" name="captchaInput" class="form-control" placeholder="Enter shown text" required />
    </div>
    <div>
      <div id="captchaBox" class="captcha-box" title="Click to regenerate">-----</div>
    </div>
  </div>

  <button type="submit" class="btn btn-primary btn-submit w-100">Register</button>
</form>

<script>
// Captcha generator
function generateCaptcha(length = 5) {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
  let code = '';
  for (let i = 0; i < length; i++) {
    code += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  return code;
}

function refreshCaptcha() {
  const code = generateCaptcha();
  document.getElementById('captchaBox').textContent = code;
  // Send the code to the server to store in session
  fetch('?action=captcha&code=' + code)
    .catch(() => {});  // ignore errors
}

document.getElementById('captchaBox').addEventListener('click', refreshCaptcha);

window.onload = refreshCaptcha;
</script>

<?php endif; ?>

</body>
</html>

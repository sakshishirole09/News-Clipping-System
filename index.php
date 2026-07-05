<?php
/* ----------  CONFIG ---------- */
$DB_HOST = 'localhost';
$DB_NAME = 'news_clipping';
$DB_USER = 'root';
$DB_PASS = '';
$UPLOAD_PAGE = 'dashboard.php';     // default landing page after login

session_start();
$error = '';

/* ----------  HANDLE POST ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* 1.  Trim & quick validate */
    $mobile   = trim($_POST['mobile']   ?? '');
    $password =        $_POST['password'] ?? '';

    if (!preg_match('/^\d{10}$/', $mobile) || $password === '') {
        $error = 'Please enter a valid 10-digit mobile number and password.';
    } else {

        /* 2.  DB connect */
        $mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
        if ($mysqli->connect_errno) {
            $error = 'Database connection failed.';
        } else {

            /* 3.  Look up user  */
            $stmt = $mysqli->prepare('SELECT password /* , role */ FROM users WHERE mobile = ?');
            $stmt->bind_param('s', $mobile);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result($hash/* , $role */);
                $stmt->fetch();

                if (password_verify($password, $hash)) {
                    /* 4.  Success – set session & redirect */
                    $_SESSION['mobile'] = $mobile;
                    // if you have roles:
                    // $_SESSION['role'] = $role;
                    header("Location: $UPLOAD_PAGE");
                    exit;
                }
            }
            $error = 'Invalid mobile number or password.';
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
<title>Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
body{background:linear-gradient(to right,#e0f7fa,#fff);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:30px}
.form-container{background:#fff;padding:30px;border-radius:10px;box-shadow:0 0 20px rgba(0,0,0,.1);width:100%;max-width:400px}
.form-title{text-align:center;font-weight:600;margin-bottom:20px}
</style>
</head>
<body>
<div class="form-container">
  <h3 class="form-title">Login</h3>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="post" autocomplete="off">
    <div class="mb-3">
      <label class="form-label">Mobile Number</label>
      <input type="text"
             name="mobile"
             value="<?= htmlspecialchars($_POST['mobile'] ?? '') ?>"
             class="form-control"
             maxlength="10"
             required />
    </div>

    <div class="mb-3">
      <label class="form-label">Password</label>
      <input type="password" name="password" class="form-control" required />
    </div>

    <button type="submit" class="btn btn-primary w-100">Login</button>
  </form>

  <div class="text-center mt-3">
    <small>Don't have an account? <a href="register.php">Register here</a></small>
  </div>
</div>
</body>
</html>

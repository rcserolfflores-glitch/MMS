<?php
session_start();
require_once 'db_connect.php';

// Ensure `is_verified` column exists to avoid fatal errors on older databases.
// This is a best-effort runtime migration: if the column is missing we add it with a default of 1 (verified).
@ $colCheck = $conn->prepare("SELECT COUNT(*) AS cnt FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'is_verified'");
if ($colCheck) {
  try {
    $colCheck->execute();
    $cres = $colCheck->get_result();
    if ($cres && ($crow = $cres->fetch_assoc())) {
      if (intval($crow['cnt']) === 0) {
        @mysqli_query($conn, "ALTER TABLE users ADD COLUMN is_verified TINYINT(1) NOT NULL DEFAULT 1;");
      }
    }
  } catch (Exception $e) {
    // non-fatal: if the DB doesn't allow this, we'll gracefully proceed and handle missing column later
  }
  $colCheck->close();
}

$error = '';
$show_verify_button = false;
$verify_url = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Please enter both username and password.";
    } else {
        // Prepare the statement (include email so we can locate pending registration)
        $stmt = $conn->prepare("SELECT id, username, password, user_type, email FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user exists
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                // Check verification status (best-effort): if users.is_verified exists and is 0, block login
                $is_verified = 1;
                $vstmt = $conn->prepare("SELECT is_verified FROM users WHERE id = ? LIMIT 1");
                if ($vstmt) {
                  $vstmt->bind_param('i', $user['id']);
                  $vstmt->execute();
                  $vres = $vstmt->get_result();
                  if ($vres && $vres->num_rows === 1) {
                    $vrow = $vres->fetch_assoc();
                    $is_verified = intval($vrow['is_verified']);
                  }
                  $vstmt->close();
                }

                if ($is_verified === 0) {
                  // find a pending registration for this user so we can offer a verify link
                  $pendingId = 0;
                  $pstmt = $conn->prepare("SELECT id FROM pending_registrations WHERE username = ? OR email = ? ORDER BY id DESC LIMIT 1");
                  if ($pstmt) {
                    $emailVal = isset($user['email']) ? $user['email'] : '';
                    $pstmt->bind_param('ss', $user['username'], $emailVal);
                    $pstmt->execute();
                    $pres = $pstmt->get_result();
                    if ($pres && $pres->num_rows) {
                      $prow = $pres->fetch_assoc();
                      $pendingId = intval($prow['id']);
                    }
                    $pstmt->close();
                  }
                  // show an explanatory error and a Verify Account button on the login page
                  $error = "Your account is not verified. Please verify it.";
                  $show_verify_button = true;
                  if ($pendingId) {
                    $verify_url = 'id_verification.php?pid=' . $pendingId;
                  } else {
                    $verify_url = 'id_verification.php';
                  }
                } else {
                // Start session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_type'] = $user['user_type'];

                // Redirect based on user type
                // If doctor, attempt to load saved profile info (avatar, full name) from doctor_info table
                if ($user['user_type'] === 'doctor') {
                  try {
                    $dstmt = $conn->prepare("SELECT name, avatar_url FROM doctor_info WHERE user_id = ? LIMIT 1");
                    if ($dstmt) {
                      $dstmt->bind_param('i', $user['id']);
                      $dstmt->execute();
                      $dres = $dstmt->get_result();
                      if ($dres && $dres->num_rows === 1) {
                        $drow = $dres->fetch_assoc();
                        if (!empty($drow['avatar_url'])) {
                          $_SESSION['user_avatar'] = $drow['avatar_url'];
                        }
                        if (!empty($drow['name'])) {
                          // prefer stored full name for display
                          $_SESSION['username'] = $drow['name'];
                          $_SESSION['user_fullname'] = $drow['name'];
                        }
                      }
                    }
                  } catch (Exception $e) {
                    // non-fatal — continue login even if profile lookup fails
                  }
                }
                // also populate midwife profile info when midwife logs in
                if ($user['user_type'] === 'midwife') {
                  try {
                    $mstmt = $conn->prepare("SELECT name, avatar_url FROM midwife_info WHERE user_id = ? LIMIT 1");
                    if ($mstmt) {
                      $mstmt->bind_param('i', $user['id']);
                      $mstmt->execute();
                      $mres = $mstmt->get_result();
                      if ($mres && $mres->num_rows === 1) {
                        $mrow = $mres->fetch_assoc();
                        if (!empty($mrow['avatar_url'])) {
                          $_SESSION['user_avatar'] = $mrow['avatar_url'];
                        }
                        if (!empty($mrow['name'])) {
                          $_SESSION['username'] = $mrow['name'];
                          $_SESSION['user_fullname'] = $mrow['name'];
                        }
                      }
                    }
                  } catch (Exception $e) {
                    // continue even if lookup fails
                  }
                }

                // also populate admin profile info when admin logs in (avatar/name stored in admin_info)
                if ($user['user_type'] === 'admin') {
                  try {
                    $astmt = $conn->prepare("SELECT name, avatar_url FROM admin_info WHERE user_id = ? LIMIT 1");
                    if ($astmt) {
                      $astmt->bind_param('i', $user['id']);
                      $astmt->execute();
                      $ares = $astmt->get_result();
                      if ($ares && $ares->num_rows === 1) {
                        $arow = $ares->fetch_assoc();
                        if (!empty($arow['avatar_url'])) {
                          $_SESSION['user_avatar'] = $arow['avatar_url'];
                        }
                        if (!empty($arow['name'])) {
                          $_SESSION['username'] = $arow['name'];
                          $_SESSION['user_fullname'] = $arow['name'];
                        }
                      }
                    }
                  } catch (Exception $e) {
                    // non-fatal — continue login even if profile lookup fails
                  }
                }

                // also populate clerk profile info when clerk logs in
                if ($user['user_type'] === 'clerk') {
                  try {
                    $cstmt = $conn->prepare("SELECT name, avatar_url FROM clerk_info WHERE user_id = ? LIMIT 1");
                    if ($cstmt) {
                      $cstmt->bind_param('i', $user['id']);
                      $cstmt->execute();
                      $cres = $cstmt->get_result();
                      if ($cres && $cres->num_rows === 1) {
                        $crow = $cres->fetch_assoc();
                        if (!empty($crow['avatar_url'])) {
                          $_SESSION['user_avatar'] = $crow['avatar_url'];
                        }
                        if (!empty($crow['name'])) {
                          $_SESSION['username'] = $crow['name'];
                          $_SESSION['user_fullname'] = $crow['name'];
                        }
                      }
                    }
                  } catch (Exception $e) {
                    // continue even if lookup fails
                  }
                }

                switch ($user['user_type']) {
                    case 'patient':
                        header("Location: patient_portal.php");
                        break;
                    case 'doctor':
                        header("Location: doctor_portal.php");
                        break;
                    case 'midwife':
                      header("Location: midwife_portal.php");
                      break;
                  case 'clerk':
                    header("Location: clerk_portal.php");
                    break;
                    case 'admin':
                        header("Location: admin_dashboard.php");
                        break;
                    default:
                        header("Location: homepage.php");
                }
                exit();
                }
              } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "Invalid username.";
        }

        $stmt->close();
    }
}

$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0" />
<title>Login - Drea Lying-In Clinic</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Poppins:wght@600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
*{box-sizing:border-box;margin:0;padding:0}
body{
  font-family:'Inter',sans-serif;
  background:#f6f2fc;
  min-height:100vh;
  display:flex;
  flex-direction:column;
  justify-content:space-between;
}
.container{
  display:flex;
  flex:1;
  width:100%;
  min-height:calc(100vh - 80px);
}
.left-panel{
  flex:1;
  background: linear-gradient(90deg,#2b1b4f,#3b2c65); /* match header gradient */
  color: #fff;
  display:flex;
  flex-direction:column;
  justify-content:center;
  align-items:center;
  text-align:center;
  padding:40px;
  position:relative;
}
.left-panel img{
  width:180px;
  height:180px;
  border-radius:50%;
  object-fit:cover;
  margin-bottom:30px;
  border:4px solid #fff;
  box-shadow:0 8px 20px rgba(0,0,0,0.15);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.left-panel img:hover{
  transform: scale(1.05);
  box-shadow:0 12px 25px rgba(0,0,0,0.25);
}
.left-panel h1{
  font-family:'Poppins',sans-serif;
  font-size:34px;
  font-weight:700;
  margin-bottom:20px;
  color:#ffffff;
  text-shadow:0 2px 10px rgba(0,0,0,0.25);
}
.left-panel p{
  font-size:17px;
  line-height:1.7;
  max-width:560px;
  margin-bottom:20px;
  color: rgba(255,255,255,0.9);
  text-shadow:0 1px 0 rgba(0,0,0,0.05);
}
.left-panel .verse{
  font-size:15px;
  font-style:italic;
  opacity:0.95;
  max-width:500px;
  color: rgba(255,255,255,0.9);
}
.right-panel{
  flex:1;
  background:#fff;
  display:flex;
  flex-direction:column;
  justify-content:center;
  align-items:center;
  padding:50px;
  position:relative;
  border-radius:0;
  box-shadow:0 10px 30px rgba(0,0,0,0.08);
}
.home-btn{
  position:absolute;
  top:30px;
  left:30px;
  background:#a48de7;
  color:#fff;
  padding:12px 22px;
  border:none;
  border-radius:25px;
  cursor:pointer;
  font-size:15px;
  font-weight:500;
  font-family:'Inter',sans-serif;
  box-shadow:0 2px 6px rgba(0,0,0,0.15);
  transition:.2s;
}
.home-btn:hover{background:#9077d1;}
.form-box{
  width:100%;
  max-width:420px;
}
h2{
  text-align:center;
  margin-bottom:10px;
  font-size:30px;
  color:#8a70d6;
  font-family:'Poppins',sans-serif;
  font-weight:700;
}
p.subtext{
  text-align:center;
  margin-bottom:25px;
  color:#555;
  font-size:16px;
  line-height:1.5;
}
input[type=text],
input[type=password]{
  width:100%;
  padding:15px;
  margin-bottom:18px;
  border:1px solid #ccc;
  border-radius:8px;
  font-size:16px;
  transition:border-color .2s, box-shadow .2s;
}
input:focus{
  border-color:#a48de7;
  outline:none;
  box-shadow:0 0 10px rgba(164,141,231,0.3);
}
button.login-btn{
  width:100%;
  padding:16px;
  background:#a48de7;
  color:#fff;
  font-size:17px;
  font-weight:600;
  border:none;
  border-radius:25px;
  cursor:pointer;
  transition:.3s;
  font-family:'Inter',sans-serif;
}
button.login-btn:hover{
  background:#9077d1;
}
.error{
  text-align:center;
  margin-bottom:15px;
  font-size:15px;
  color:red;
}
.signup-link{
  text-align:center;
  margin-top:18px;
  font-size:16px;
  color:#555;
}
.signup-link a{
  color:#a48de7;
  font-weight:600;
  text-decoration:none;
}
.signup-link a:hover{
  text-decoration:underline;
}

/* Footer */
footer{
  background: linear-gradient(90deg,#2b1b4f,#3b2c65);
  text-align:center;
  padding:20px 10px;
  font-size:14px;
  color:#fff;
  font-family:'Inter',sans-serif;
  box-shadow:0 -6px 20px rgba(0,0,0,0.25);
}
footer i{
  color:#fff;
  margin-right:5px;
}

@media(max-width:900px){
  .container{flex-direction:column}
  .left-panel,.right-panel{width:100%;height:auto;padding:40px 20px}
  .left-panel h1{font-size:28px}
  .left-panel p,.left-panel .verse{font-size:15px}
  .form-box{max-width:100%}
  .home-btn{position:relative;top:auto;left:auto;margin-bottom:20px}
  .left-panel img{width:140px;height:140px;}
}

/* Keep left panel visible on larger screens while the user scrolls the page */
@media(min-width:901px){
  .left-panel{ position:sticky; top:0; height:100vh; overflow:auto; }
}
</style>
</head>
<body>
<div class="container">
  <!-- LEFT SIDE -->
  <div class="left-panel">
    <img src="assets/images/logodrea.jpg" alt="Clinic Logo" />
    <h1>Welcome to<br>Drea Lying-In Clinic</h1>
    <p>
      But because of his great love for us, God, who is rich in MERCY,
      made us alive with Christ even when we were dead in transgressions— 
      it is by grace you have been saved.
    </p>
    <div class="verse">– Ephesians 2:4-5</div>
  </div>

  <!-- RIGHT SIDE -->
  <div class="right-panel">
    <button class="home-btn" onclick="window.location.href='homepage.php'">Home</button>
    <div class="form-box">
      <h2>Log In</h2>
      <p class="subtext">Demonstrating God's love and compassion for women and their babies.</p>

      <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>
      <?php if (!empty($_SESSION['fp_message'])): ?>
        <div class="error"><?php echo htmlspecialchars($_SESSION['fp_message']); unset($_SESSION['fp_message']); ?></div>
      <?php endif; ?>
      <?php if ($error === "Invalid username."): ?>
        <div class="error">Not Verified Account!</div>
      <?php endif; ?>
      <?php if (!empty($show_verify_button) && !empty($verify_url)): ?>
        <div style="text-align:center;margin-top:12px">
          <a href="<?php echo htmlspecialchars($verify_url); ?>" class="login-btn" style="display:inline-block;text-decoration:none">Verify Account</a>
        </div>
      <?php endif; ?>

      <form method="POST">
        <input type="text" name="username" placeholder="Username" required
          value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" class="login-btn">Log In</button>
      </form>
      <div style="text-align:center;margin-top:12px">
        <a href="forgot_password.php" style="color:#a48de7;display:inline-block;margin-top:12px">Forgot password?</a>
      </div>
      <div class="signup-link">
        Don’t have an account? <a href="sign-up_process.php">Sign up here</a>
      </div>
    </div>
  </div>
</div>

</body>
</html>

<?php
session_start();
require 'computer.php';

// SECURITY: only admin
if(empty($_SESSION['user_id']) || empty($_SESSION['role']) || $_SESSION['role']!=='admin'){
    header("Location: index.php");
    exit;
}

// FETCH STAFF DATA
if(empty($_GET['id'])){
    header("Location: admin_dashboard.php");
    exit;
}
$id = (int)$_GET['id'];
$staff_result = $conn->query("SELECT * FROM staff WHERE id=$id");
if($staff_result->num_rows === 0){
    header("Location: admin_dashboard.php");
    exit;
}
$staff = $staff_result->fetch_assoc();

// HANDLE UPDATE
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_staff'])){
    $sname = $conn->real_escape_string($_POST['staff_name']);
    $srole = $conn->real_escape_string($_POST['staff_role']);
    $semail = $conn->real_escape_string($_POST['staff_email']);
    $sphone = $conn->real_escape_string($_POST['staff_phone']);
    $saddress = $conn->real_escape_string($_POST['staff_address']);
    $sarea = $conn->real_escape_string($_POST['staff_area']);

    // Update password only if entered
    $password_sql = '';
    if(!empty($_POST['staff_password'])){
        $password_hash = password_hash($_POST['staff_password'], PASSWORD_DEFAULT);
        $password_sql = ", password_hash='$password_hash'";
    }

    $conn->query("UPDATE staff SET name='$sname', role='$srole', email='$semail', phone='$sphone', address='$saddress', delivery_area='$sarea' $password_sql WHERE id=$id");
    header("Location: admin_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Staff - CREATECH</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
body{background:transparent;color:#f8fafc;padding-bottom:50px;}

/* VIDEO BACKGROUND */
#bg-video{position:fixed;top:0;left:0;width:100%;height:100%;object-fit:cover;z-index:-2;pointer-events:none;}
.video-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:-1;}

/* HEADER */
header{background:rgba(15,23,42,0.85);color:white;padding:20px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:999;backdrop-filter:blur(10px);}
header a{color:#22c55e;text-decoration:none;font-weight:bold;margin-left:15px;}
header a:hover{opacity:0.8;}
header button{background:#3b82f6;color:white;padding:8px 14px;border:none;border-radius:5px;cursor:pointer;margin-left:10px;}
header button.active{opacity:1;background:#22c55e;}

/* LOGO */
.logo{
    font-size:26px;
    font-weight:900;
    color: #ffffff;
    letter-spacing:2px;
}
.logo {
    font-size: 24px;
    font-weight: 900;
    letter-spacing: 2px;
    background: linear-gradient(90deg, #2e7738, #2cbb43,  #00ff73);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* FORM CONTAINER */
.form-container {
  max-width: 800px;
  margin: 50px auto;
  background: linear-gradient(#212121, #212121) padding-box,
              linear-gradient(145deg, transparent 35%,#e81cff, #40c9ff) border-box;
  border: 2px solid transparent;
  padding: 32px 24px;
  font-size: 14px;
  font-family: inherit;
  color: white;
  display: flex;
  flex-direction: column;
  gap: 20px;
  border-radius: 16px;
}

.form-container button:active { scale: 0.95; }

.form-container .form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-container .form-group {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.form-container .form-group label {
  display: block;
  margin-bottom: 5px;
  color: #717171;
  font-weight: 600;
  font-size: 12px;
}

.form-container .form-group input,
.form-container .form-group select,
.form-container .form-group textarea {
  width: 100%;
  padding: 12px 16px;
  border-radius: 8px;
  color: #fff;
  font-family: inherit;
  background-color: transparent;
  border: 1px solid #414141;
}

.form-container .form-group textarea { height: 96px; resize: none; }

.form-container .form-group input::placeholder { opacity: 0.5; }

.form-container .form-group input:focus,
.form-container .form-group textarea:focus,
.form-container .form-group select:focus {
  outline: none;
  border-color: #e81cff;
}

.form-container .form-submit-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: inherit;
  color: #717171;
  font-weight: 600;
  width: 50%;
  background: #313131;
  border: 1px solid #414141;
  padding: 12px 16px;
  font-size: inherit;
  gap: 8px;
  margin-top: 8px;
  cursor: pointer;
  border-radius: 6px;
}

.form-container .form-submit-btn:hover {
  background-color: #fff;
  border-color: #fff;
  color: #000;
}
</style>
</head>
<body>

<video autoplay muted loop playsinline id="bg-video">
    <source src="assets/adbg.mp4" type="video/mp4">
</video>
<div class="video-overlay"></div>

<header>
    <h1 class="logo">CREATECH</h1>
    <nav>
        <span>Hello, <?= htmlspecialchars($_SESSION['username']); ?></span>
        <button class="tab-btn active">Edit Staff</button>
        <a href="admin_dashboard.php">Back to Dashboard</a>
        <a href="store.php">Visit Store</a>
        <a href="logout.php" style="color:#ef4444;">Logout</a>
    </nav>
</header>

<div class="form-container">
  <form class="form" method="POST">
    
    <div class="form-group">
      <label for="staff_name">Full Name</label>
      <input type="text" id="staff_name" name="staff_name" placeholder="Full Name" value="<?= htmlspecialchars($staff['name']); ?>" required>
    </div>

    <div class="form-group">
      <label for="staff_role">Role</label>
      <select id="staff_role" name="staff_role" required>
        <option value="">-- Select Role --</option>
        <option value="Delivery" <?= ($staff['role']=='Delivery')?'selected':'' ?>>Delivery</option>
        <option value="Support" <?= ($staff['role']=='Support')?'selected':'' ?>>Support</option>
      </select>
    </div>

    <div class="form-group">
      <label for="staff_email">Email</label>
      <input type="email" id="staff_email" name="staff_email" placeholder="Email" value="<?= htmlspecialchars($staff['email']); ?>">
    </div>

    <div class="form-group">
      <label for="staff_phone">Phone</label>
      <input type="text" id="staff_phone" name="staff_phone" placeholder="Phone" value="<?= htmlspecialchars($staff['phone']); ?>">
    </div>

    <div class="form-group">
      <label for="staff_address">Address</label>
      <input type="text" id="staff_address" name="staff_address" placeholder="Address" value="<?= htmlspecialchars($staff['address']); ?>">
    </div>

    <div class="form-group">
      <label for="staff_area">Delivery Area</label>
      <input type="text" id="staff_area" name="staff_area" placeholder="Delivery Area" value="<?= htmlspecialchars($staff['delivery_area']); ?>">
    </div>

    <div class="form-group">
      <label for="staff_password">Password (leave blank to keep)</label>
      <input type="password" id="staff_password" name="staff_password" placeholder="Password">
    </div>

    <button type="submit" name="update_staff" class="form-submit-btn">Update Staff</button>

  </form>
</div>

</body>
</html>
<?php
session_start();
// Force browser not to cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect if not logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}
require 'computer.php'; // your DB connection

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user info
$stmt = $conn->prepare("SELECT username, email, street, city, province, zip, birthday, age, distance_km, delivery_minutes FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "User not found.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>User Profile - PC Parts Hub</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body {
    font-family: 'Segoe UI', sans-serif;
    margin:0;
    padding:0;
    background: linear-gradient(135deg, #0f172a, #1e293b);
    color:#f1f5f9;
}

.container {
    max-width: 700px;
    margin: 60px auto;
    background: rgba(15,23,42,0.85);
    padding: 40px 30px;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    border: 1px solid rgba(255,255,255,0.1);
}

h1 {
    text-align:center;
    margin-bottom:40px;
    font-size:32px;
    background: linear-gradient(90deg,#22c55e,#3b82f6,#a855f7,#ec4899,#f59e0b);
    background-clip: text;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    letter-spacing:2px;
}

.profile-info {
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:20px 40px;
}

.profile-info div {
    display:flex;
    flex-direction:column;
    background: rgba(255,255,255,0.05);
    padding: 15px 20px;
    border-radius: 12px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.2);
}

.profile-info label {
    font-weight:bold;
    color:#94a3b8;
    margin-bottom:5px;
    font-size:14px;
}

.profile-info span {
    font-size:16px;
    color:#f8fafc;
}

.actions {
    text-align:center;
    margin-top:30px;
}

.actions a {
    display:inline-block;
    margin:0 10px;
    padding:12px 24px;
    border-radius:8px;
    font-weight:bold;
    text-decoration:none;
    transition:0.3s;
}

.actions a.store {
    background: rgb(0, 0, 0);
    color:#fff;
}

.actions a.store:hover {
    background:rgba(34,197,94,1);
}

.actions a.logout {
    background: rgb(0, 0, 0);
    color:#fff;
}

.actions a.logout:hover {
    background:rgba(34,197,94,1)
}

/* Responsive */
@media(max-width:600px){
    .profile-info{
        grid-template-columns:1fr;
    }
}
</style>
</head>
<body>

<div class="container">
    <h1>User Profile</h1>

    <div class="profile-info">
        <div><label>Username</label><span><?= htmlspecialchars($user['username']); ?></span></div>
        <div><label>Email</label><span><?= htmlspecialchars($user['email']); ?></span></div>
        <div><label>Street / Barangay</label><span><?= htmlspecialchars($user['street']); ?></span></div>
        <div><label>City</label><span><?= htmlspecialchars($user['city']); ?></span></div>
        <div><label>Province</label><span><?= htmlspecialchars($user['province']); ?></span></div>
        <div><label>ZIP Code</label><span><?= htmlspecialchars($user['zip']); ?></span></div>
        <div><label>Birthday</label><span><?= htmlspecialchars($user['birthday']); ?></span></div>
        <div><label>Age</label><span><?= $user['age']; ?></span></div>
        <div><label>Distance (km)</label><span><?= $user['distance_km']; ?> km</span></div>
        <div><label>Estimated Delivery</label><span><?= $user['delivery_minutes']; ?> min</span></div>
    </div>

    <div class="actions">
        <a href="store.php" class="store">Back to Store</a>
        <a href="logout.php" class="logout">Logout</a>
    </div>
</div>
<script>
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
    window.history.forward();
}
</script>
</body>
</html>

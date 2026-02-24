<?php
session_start();
require_once __DIR__ . "/computer.php";

// Redirect if already logged in
if(isset($_SESSION['staff_id'])){
    header("Location: staff_dashboard.php");
    exit;
}

$error = "";
$login_success = false;

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM staff WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $staff = $result->fetch_assoc();

    if($staff){
        if(password_verify($password, $staff['password_hash'])){
            $_SESSION['staff_id'] = $staff['id'];
            $_SESSION['staff_name'] = $staff['name'];
            $_SESSION['staff_role'] = $staff['role'];
            $login_success = true;
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "Staff not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo $login_success ? 'System Initialized' : 'Staff Login'; ?> - CREATECH</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
<?php if($login_success): ?>
:root { --neon-green: #22c55e; --dark-bg: #020617; }
body { background: var(--dark-bg); color: var(--neon-green); font-family: 'Courier New', monospace; display:flex; justify-content:center; align-items:center; height:100vh; margin:0; }
#bg-video { position:fixed; top:0; left:0; width:100%; height:100%; object-fit:cover; z-index:-1; filter: brightness(0.3);}
.system-hub { border:2px solid #1e293b; background: rgba(15,23,42,0.85); padding:40px; border-radius:4px; width:90%; max-width:500px; position:relative; text-align:center; backdrop-filter: blur(10px);}
.system-hub::after { content:""; position:absolute; top:0; left:0; right:0; height:2px; background: rgba(34,197,94,0.2); animation: scan 4s linear infinite; }
@keyframes scan { 0%{top:0;} 100%{top:100%;} }
.status-line { margin:10px 0; font-size:14px; white-space: nowrap; overflow:hidden; width:0; animation: typing 0.5s steps(30,end) forwards; text-align:left;}
@keyframes typing { from{width:0;} to{width:100%;} }
.power-ring { width:80px; height:80px; margin:0 auto 20px; border:5px solid #1e293b; border-top:5px solid var(--neon-green); border-radius:50%; animation:spin 1.2s linear forwards; display:flex; align-items:center; justify-content:center;}
@keyframes spin { 100% { transform:rotate(360deg); border-color: var(--neon-green); } }
.power-ring::after { content:"OK"; font-weight:bold; opacity:0; animation: fadeIn 0.2s forwards 1.2s; }
.btn-enter { display:inline-block; margin-top:30px; padding:12px 24px; border:1px solid var(--neon-green); color: var(--neon-green); text-decoration:none; text-transform:uppercase; letter-spacing:2px; opacity:0; animation: fadeIn 0.5s forwards 2s;}
.btn-enter:hover { background: var(--neon-green); color: var(--dark-bg); box-shadow: 0 0 20px var(--neon-green); }
@keyframes fadeIn { to { opacity:1; } }
.glitch { animation: glitch 1s linear infinite; color:#fff;}
@keyframes glitch { 2%,64%{transform:translate(2px,0);} 4%,60%{transform:translate(-2px,0);} }
<?php else: ?>
body { margin:0; font-family: Arial, sans-serif; color: #fff; min-height:100vh; overflow-x:hidden; }
#bg-video { position:fixed; top:0; left:0; width:100%; height:100%; object-fit:cover; z-index:-1; pointer-events:none; }
header { background: rgba(0,0,0,0.4); color:white; padding:20px 40px; display:flex; justify-content:space-between; align-items:center; backdrop-filter: blur(5px);}
header a { color:white; text-decoration:none; margin-left:20px; font-weight:bold; }
.main-content { display:flex; justify-content:center; align-items:center; padding:60px 20px; min-height: calc(100vh - 120px); }
.login-card { background: rgba(0,0,0,0.5); padding:40px; border-radius:12px; box-shadow: 0 4px 30px rgba(0,0,0,0.5); width:100%; max-width:400px; text-align:center; color:#fff; backdrop-filter: blur(10px);}
form { display:flex; flex-direction:column; gap:15px; }
input { padding:12px; border:1px solid rgba(255,255,255,0.4); border-radius:6px; font-size:16px; background: rgba(255,255,255,0.1); color:#fff; }
input::placeholder { color: rgba(255,255,255,0.7); }
.btn { padding:14px; background: rgba(34,197,94,0.7); color:#fff; border:none; border-radius:6px; cursor:pointer; font-weight:bold; transition: all 0.3s;}
.btn:hover { background: rgba(34,197,94,1); transform:translateY(-2px); box-shadow:0 5px 15px rgba(34,197,94,0.5); }
.error-msg { color:#f87171; margin-bottom:15px; }
footer { background: rgba(0,0,0,0.4); color:white; text-align:center; padding:20px; position:fixed; bottom:0; width:100%; backdrop-filter: blur(5px); }
.logo { font-size:26px; font-weight:900; background:linear-gradient(270deg,#22c55e,#3b82f6,#a855f7,#ec4899,#f59e0b); background-size:400% 400%; animation: gradientMove 6s ease infinite; -webkit-background-clip:text; -webkit-text-fill-color:transparent; letter-spacing:2px; }
@keyframes gradientMove {0%{background-position:0% 50%;}50%{background-position:100% 50%;}100%{background-position:0% 50%;}}
<?php endif; ?>
</style>
</head>
<body>

<?php if($login_success): ?>
<video autoplay muted loop playsinline id="bg-video">
    <source src="assets/background.mp4" type="video/mp4">
</video>

<div class="system-hub">
    <div class="power-ring"></div>
    <h1 class="glitch">SYSTEM INITIALIZED</h1>
    <div class="status-line" style="animation-delay:1.2s;">> Handshake: <span style="color:white">SECURE</span></div>
    <div class="status-line" style="animation-delay:1.4s;">> Staff: <span style="color:white"><?= htmlspecialchars($_SESSION['staff_name']); ?></span></div>
    <div class="status-line" style="animation-delay:1.6s;">> Role: <span style="color:white"><?= strtoupper($_SESSION['staff_role']); ?></span></div>
    <div class="status-line" style="animation-delay:1.8s;">> Booting Staff Dashboard...</div>
    <a href="staff_dashboard.php" class="btn-enter">Enter Mainframe</a>
</div>

<?php else: ?>
<video autoplay muted loop playsinline id="bg-video">
    <source src="assets/login.mp4" type="video/mp4">
</video>

<header>
    <h1 class="logo">CREATECH</h1>
</header>

<div class="main-content">
    <div class="login-card">
        <h3>Staff Login</h3>
        <?php if($error): ?><p class="error-msg"><?= $error ?></p><?php endif; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="btn">Authorize</button>
        </form>
    </div>
</div>

<footer>&copy; <?= date("Y"); ?> CREATECH</footer>
<?php endif; ?>

</body>
</html>

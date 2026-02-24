<?php
session_start();
require_once __DIR__ . "/computer.php";


// Redirect if already logged in (Skip the login form)
if(isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit;
}

$error = "";
$login_success = false;

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username_email = trim($_POST['username_email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare(
        "SELECT id, username, password_hash, role
         FROM users
         WHERE username = ? OR email = ?
         LIMIT 1"
    );
    $stmt->bind_param("ss", $username_email, $username_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($user = $result->fetch_assoc()){
        if(password_verify($password, $user['password_hash'])){
            // SET SESSION DATA
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = strtolower(trim($user['role']));
            
            $login_success = true; 
        } else {
            $error = "Incorrect password!";
        }
    } else {
        $error = "User not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo $login_success ? 'System Initialized' : 'Login'; ?> - PC Parts Hub</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
<?php if($login_success): ?>
/* --- LOGIN SUCCESS ANIMATION --- */
:root { --neon-green: #22c55e; --dark-bg: #020617; }
body {
    background: var(--dark-bg);
    color: var(--neon-green);
    font-family: 'Courier New', monospace;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin:0;
}
#bg-video {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    object-fit: cover;
    z-index: -1;
    filter: brightness(0.3);
}
.system-hub {
    border: 2px solid #1e293b;
    background: rgba(15, 23, 42, 0.85);
    backdrop-filter: blur(10px);
    padding: 40px;
    border-radius: 4px;
    width: 90%;
    max-width: 500px;
    position: relative;
    text-align: center;
    box-shadow: 0 0 50px rgba(34, 197, 94, 0.1);
}
.system-hub::after {
    content: "";
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 2px;
    background: rgba(34,197,94,0.2);
    animation: scan 4s linear infinite;
}
@keyframes scan { 0% { top: 0; } 100% { top: 100%; } }
.status-line {
    margin: 10px 0;
    font-size: 14px;
    white-space: nowrap;
    overflow: hidden;
    width: 0;
    animation: typing 0.5s steps(30,end) forwards;
    text-align: left;
}
@keyframes typing { from { width: 0 } to { width: 100% } }
.power-ring {
    width: 80px; height: 80px; margin: 0 auto 20px;
    border: 5px solid #1e293b;
    border-top: 5px solid var(--neon-green);
    border-radius: 50%;
    animation: spin 1.2s linear forwards;
    display: flex;
    align-items: center;
    justify-content: center;
}
@keyframes spin { 100% { transform: rotate(360deg); border-color: var(--neon-green); } }
.power-ring::after { content: "OK"; font-weight: bold; opacity: 0; animation: fadeIn 0.2s forwards 1.2s; }
.btn-enter {
    display: inline-block; margin-top: 30px; padding: 12px 24px;
    border: 1px solid var(--neon-green);
    color: var(--neon-green);
    text-decoration: none;
    text-transform: uppercase;
    letter-spacing: 2px;
    opacity: 0;
    animation: fadeIn 0.5s forwards 2s;
}
.btn-enter:hover { background: var(--neon-green); color: var(--dark-bg); box-shadow: 0 0 20px var(--neon-green); }
@keyframes fadeIn { to { opacity: 1; } }
.glitch { animation: glitch 1s linear infinite; color: #fff; }
@keyframes glitch { 2%,64% { transform: translate(2px,0); } 4%,60% { transform: translate(-2px,0); } }

<?php else: ?>
/* --- LOGIN FORM STYLES --- */
body {
    margin:0;
    font-family: Arial, sans-serif;
    color: #fff;
    min-height: 100vh;
    overflow-x: hidden;
}
#bg-video {
    position: fixed;
    top:0; left:0;
    width: 100%; height: 100%;
    object-fit: cover;
    z-index: -1;
    pointer-events: none;
}

/* Transparent header */
header {
    background: rgba(0,0,0,0.4);
    color: white;
    padding: 20px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    z-index: 10;
    backdrop-filter: blur(5px);
}
header a { color: white; text-decoration: none; margin-left: 20px; font-weight: bold; }

/* Main content login card */
.main-content { display: flex; justify-content: center; align-items: center; padding: 60px 20px; min-height: calc(100vh - 120px); }
.login-card {
    width: 100%;
    max-width: 400px;

  background: linear-gradient(#212121, #212121) padding-box,
              linear-gradient(145deg, transparent 35%, #e81cff, #40c9ff) border-box;
  border: 2px solid transparent;

  padding: 32px 24px;
  font-size: 14px;
  color: white;

  display: flex;
  flex-direction: column;
  gap: 20px;

  box-sizing: border-box;
  border-radius: 16px;

  transition: all 0.4s ease;
}
.login-card:hover {

  border: 2px solid #13b61b;
  box-shadow: 0 0 20px rgba(255,255,255,0.2);
}

.login-card h3, .login-card p, .login-card input, .login-card button { color: #fff; }

form { display: flex; flex-direction: column; gap: 15px; }

input {
    padding: 12px;
    border: 1px solid rgba(255,255,255,0.4);
    border-radius: 6px;
    font-size: 16px;
    background: rgba(255,255,255,0.1);
    color: #fff;
}
input::placeholder { color: rgba(255,255,255,0.7); }
textarea{
  width: 100%;
  padding: 12px 16px;
  border-radius: 8px;
  resize: vertical;
  color: #fff;
  min-height: 96px;
  border: 1px solid #414141;
  background-color: transparent;
  font-family: inherit;
}

textarea:focus{
  outline: none;
  border-color: #13b61b;
}
.shadow__btn {
  padding: 10px 20px;
  border: none;
  font-size: 17px;
  color: #fff;
  border-radius: 7px;
  letter-spacing: 4px;
  font-weight: 700;
  text-transform: uppercase;
  transition: 0.5s;
  transition-property: box-shadow;
}

.shadow__btn {
  background: rgb(22, 131, 40);
  box-shadow: 0 0 25px rgb(9, 197, 18);
}

.shadow__btn:hover {
  box-shadow: 0 0 5px rgb(8, 153, 57),
              0 0 25px rgb(8, 153, 57),
              0 0 50px rgb(8, 153, 57),
              0 0 100px rgb(8, 153, 57);
}
.error-msg { color: #f87171; margin-bottom: 15px; }

/* Transparent footer */
footer {
    background: rgba(0,0,0,0.4);
    color: white;
    text-align: center;
    padding: 20px;
    position: fixed;
    bottom:0;
    width: 100%;
    backdrop-filter: blur(5px);
    z-index: 10;
}
.logo {
    font-size: 24px;
    font-weight: 900;
    letter-spacing: 2px;
    background: linear-gradient(90deg, #2e7738, #2cbb43,  #00ff73);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}


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
    <div class="status-line" style="animation-delay: 1.2s;">> Handshake: <span style="color:white">SECURE</span></div>
    <div class="status-line" style="animation-delay: 1.4s;">> User: <span style="color:white"><?php echo htmlspecialchars($_SESSION['username']); ?></span></div>
    <div class="status-line" style="animation-delay: 1.6s;">> Role: <span style="color:white"><?php echo strtoupper($_SESSION['role']); ?></span></div>
    <div class="status-line" style="animation-delay: 1.8s;">> Booting PC Parts Hub v2.0.26...</div>

    <?php $target = ($_SESSION['role'] === 'admin') ? 'admin_dashboard.php' : 'store.php'; ?>
    <a href="<?php echo $target; ?>" class="btn-enter">Enter Mainframe</a>
</div>

<?php else: ?>
<!-- LOGIN VIDEO BACKGROUND -->
<video autoplay muted loop playsinline id="bg-video">
    <source src="assets/login.mp4" type="video/mp4">
</video>

<header>
    <h1 class ="logo">CREATECH</h1>
    <nav><a href="index.php"class ="logo">Home</a>
</header>

<div class="main-content">
    <div class="login-card">
        <h3>Login</h3>
        <?php if($error): ?><p class="error-msg"><?php echo $error; ?></p><?php endif; ?>
        <form method="POST">
            <input type="text" name="username_email" placeholder="Username or Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" class="shadow__btn">Authorize</button>
        </form>
        <p style="margin-top:20px; font-size:14px;">Don't have an account? <a href="register.php" style="color:#22c55e; font-weight:bold;">Register</a></p>
    </div>
</div>

<footer><p>&copy; <?php echo date("Y"); ?> PC Parts Hub</p></footer>
<?php endif; ?>

</body>
</html>

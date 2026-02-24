<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>PC Parts Hub | Landing</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
/* --- THEME VARIABLES --- */
:root {
    --bg-body: #ffffff;
    --header-bg: rgba(15,23,42,0.7);
    --text-main: #000000;
    --video-src: 'assets/lightbody.mp4';
}

body.dark-mode {
    --bg-body: #020617;
    --header-bg: rgba(0,0,0,0.7);
    --text-main: #f1f5f9;
    --video-src: 'assets/darkbody.mp4';
}

body {
    margin:0;
    font-family:'Segoe UI', system-ui, sans-serif;
    background-color: var(--bg-body);
    color: var(--text-main);
}

/* BACKGROUND VIDEO */
#bg-video {
    position: fixed;
    top:0; left:0;
    width:100%; height:100%;
    object-fit: cover;
    z-index:-1;
    pointer-events:none;
}

/* HEADER */
header {
    background-color: var(--header-bg);
    color: white;
    padding: 15px 40px;
    display:flex;
    justify-content: space-between;
    align-items:center;
    position: sticky;
    top:0;
    z-index:1000;
    backdrop-filter: blur(8px);
}
nav {
    display:flex;
    align-items:center;
    gap:20px;
}
nav a, nav button {
    color:#94a3b8;
    text-decoration:none;
    font-weight:500;
    background:none;
    border:none;
    cursor:pointer;
}
nav a:hover, nav button:hover { color:white; }

.logo {
    font-size: 24px;
    font-weight: 900;
    letter-spacing: 2px;
    background: linear-gradient(90deg, #2e7738, #2cbb43,  #00ff73);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}


/* HERO */
.hero {
    position: relative;
    height: 100vh;
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    text-align:center;
    padding:0 20px;
    color:white;
}
.hero h1 { font-size:50px; margin-bottom:20px; }
.hero p { font-size:22px; margin-bottom:40px; }
.btn {
    padding:18px 40px;
    background:#22c55e;
    color:white;
    font-weight:bold;
    font-size:16px;
    text-transform:uppercase;
    text-decoration:none;
    border:none;
    cursor:pointer;
    border-radius:8px;
}
.btn:hover { opacity:0.8; }
</style>
</head>
<body>

<video autoplay muted loop playsinline id="bg-video">
    <source src="assets/lightbody.mp4" type="video/mp4">
    Your browser does not support the video tag.
</video>

<header>
    <h1 class="logo">CREATECH</h1>
    <nav>
        <button id="theme-toggle">🌙 Dark Mode</button>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login</a>
        <?php endif; ?>
        <a href="store.php" class="btn">Browse Store</a>
    </nav>
</header>

<section class="hero">
    <h1 class = "logo">Computer Hardware</h1>
    <p class="logo">High-performance components for the modern builder.</p>
    <a href="admin_dashboard.php" class="btn">Create Product</a>
</section>

<script>
// THEME TOGGLE
const themeToggle = document.getElementById('theme-toggle');
const body = document.body;
const bgVideo = document.getElementById('bg-video');

if(localStorage.getItem('theme')==='dark'){
    body.classList.add('dark-mode');
    themeToggle.innerHTML='☀️ Light Mode';
    bgVideo.src = 'assets/darkbody.mp4';
    bgVideo.load();
    bgVideo.play();
}

themeToggle.addEventListener('click',()=>{
    body.classList.toggle('dark-mode');
    if(body.classList.contains('dark-mode')){
        themeToggle.innerHTML='☀️ Light Mode';
        localStorage.setItem('theme','dark');
        bgVideo.src = 'assets/darkbody.mp4';
    } else {
        themeToggle.innerHTML='🌙 Dark Mode';
        localStorage.setItem('theme','light');
        bgVideo.src = 'assets/lightbody.mp4';
    }
    bgVideo.load();
    bgVideo.play();
});
</script>

</body>
</html>

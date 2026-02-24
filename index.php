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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>PC Parts Hub | Welcome</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
/* --- BASE STYLES --- */
body, html {
    margin:0;
    padding:0;
    font-family: 'Segoe UI', system-ui, sans-serif;
    color: white;
    height: 100%;
    overflow-x: hidden;
}

/* --- VIDEO BACKGROUND --- */
#bg-video {
    position: fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    object-fit:cover;
    z-index: -1; /* behind everything */
}

/* --- HEADER --- */
header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 40px;
    background: rgba(0, 0, 0, 0.35);
    backdrop-filter: blur(5px);
    position: fixed;
    top: 0;
    left: 0; /* Explicitly anchor to left */
    width: 100%;
    z-index: 10;
    box-sizing: border-box; /* THIS IS THE KEY FIX */
}

header nav {
    display: flex;
    gap: 15px;
}

header nav a {
    display: inline-block;
    padding: 10px 20px;
    background: rgba(34,197,94,0.85);
    color: white;
    font-weight: bold;
    text-decoration: none;
    border-radius: 8px;
    transition: background 0.3s, transform 0.3s;
    box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    font-size: 14px;
}

header nav a:hover {
    background: rgba(34,197,94,1);
    transform: translateY(-2px);
}
.logo {
    font-size: 24px;
    font-weight: 900;
    letter-spacing: 2px;
    background: linear-gradient(90deg, #2e7738, #2cbb43,  #00ff73);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}



nav a {
    display: inline-block;
    padding: 12px 24px;
    background: rgba(34,197,94,0.6); /* semi-transparent green */
    color: white;
    font-weight: bold;
    text-decoration: none;
    border-radius: 8px;
    transition: background 0.3s, transform 0.3s;
}
nav a:hover {
    background: rgba(34,197,94,0.9);
    transform: translateY(-2px);
}
/* --- HERO SECTION --- */
.hero {
    height:100vh;
    display:flex;
    flex-direction:column;
    justify-content:center;
    align-items:center;
    text-align:center;
    padding:0 20px;
}

.hero h1, .hero p {
    color: white;
    text-shadow: 0 0 15px rgba(0,0,0,0.6);
    margin: 10px 0;
}

.hero a.btn {
    margin-top:20px;
    padding: 18px 40px;
    background: rgba(34,197,94,0.6);
    border-radius:8px;
    text-decoration:none;
    color:white;
    font-weight:bold;
    text-transform:uppercase;
    transition:0.3s;
}
.hero a.btn:hover {
    background: rgba(34,197,94,0.9);
}
</style>
</head>
<body>

<!-- VIDEO BACKGROUND -->
<video autoplay muted loop playsinline id="bg-video">
    <source src="assets/landing.mp4" type="video/mp4">
    Your browser does not support the video tag.
</video>

<!-- HEADER -->
<header>
    <h1 class="logo">CREATECH</h1>
    <nav>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="store.php">Browse Store</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="login.php">Login to Browse Store</a>
        <?php endif; ?>
    </nav>
</header>

<!-- HERO -->
<section class="hero">
    <h1 class="logo">Premium Hardware</h1>
    <p class="logo">High-performance components for the modern builder.</p>
    <?php if(isset($_SESSION['user_id'])): ?>
        <a href="store.php" class="btn">Browse Store</a>
    <?php else: ?>
        <a href="login.php" class="btn">Login to Browse Store</a>
    <?php endif; ?>
</section>
<script>
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
    window.history.forward();
}
</script>
</body>
</html>

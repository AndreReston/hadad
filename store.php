<?php
session_start();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Redirect to login if not logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

// User info
$user_role = $_SESSION['role'] ?? 'user';
$username = $_SESSION['username'] ?? 'User';

include("computer.php");

// Fetch products
$products_result = $conn->query("SELECT * FROM products ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CREATECH Store</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
/* BODY & BACKGROUND */
body {margin:0;font-family:'Segoe UI',sans-serif;color:#fff;background:#0f172a;}
#bg-video {position:fixed;top:0;left:0;width:100%;height:100%;object-fit:cover;z-index:-2;filter:brightness(0.35);}
.video-overlay {position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.55);z-index:-1;}

/* HEADER */
header {
    background:rgba(15,23,42,0.75);
    padding:15px 30px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    position:sticky;
    top:0;
    backdrop-filter:blur(10px);
    z-index:999;
}
header a {color:#22c55e;text-decoration:none;margin-left:15px;font-weight:bold;}
header a:hover {opacity:0.8;}
.logo {
    font-size: 24px;
    font-weight: 900;
    letter-spacing: 2px;
    background: linear-gradient(90deg, #2e7738, #2cbb43,  #00ff73);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* SECTION WRAPPER */
.section{
    max-width:1200px;
    margin:0 auto;
    padding:40px 20px 80px;
    display:flex;
    justify-content:center;
    flex-wrap:wrap;
    gap:25px;
}

/* PRODUCT CARD USING FORM-CONTAINER STYLE */
.product.form-container {
    width: 300px; /* keep the size */
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 32px 24px;
    background: linear-gradient(#212121, #212121) padding-box,
                linear-gradient(145deg, transparent 35%,#e81cff, #1ba82e) border-box;
    border: 2px solid transparent;
    border-radius: 16px;
    gap: 20px;
    box-sizing: border-box;
    color: #fff;
    cursor: pointer;
    transition: transform 0.25s, box-shadow 0.25s, border 0.25s;
    overflow:hidden;
    backdrop-filter:blur(10px);
    text-decoration: none;
}
.product.form-container:hover{
    transform: translateY(-6px);
    border: 2px solid #13b61b;
    box-shadow:0 8px 25px rgba(0,0,0,0.35);
}

.product.form-container img {
    width:100%;
    height:190px;
    object-fit:contain;
    margin-bottom:15px;
    border-radius:8px;
}

.product.form-container h4 {
    margin:10px 0 8px;
    font-size:18px;
    color:#22c55e;
    font-weight:800;
}

.product.form-container p {
    color:#e5e7eb;
    font-size:13px;
    margin-bottom:12px;
    line-height:1.4;
    min-height:38px;
}

.product.form-container strong {
    font-size:20px;
    color:#ffcc00;
    font-weight:900;
}

/* SMALL RESPONSIVE FIX */
@media(max-width:600px){
    header{padding:12px 15px;}
    .logo{font-size:22px;}
    .product.form-container{width:90%;}
}
</style>
</head>
<body>

<video autoplay muted loop id="bg-video">
    <source src="assets/redbg.mp4" type="video/mp4">
</video>
<div class="video-overlay"></div>

<header>
    <div class="logo">CREATECH</div>
    <nav>
        <span class="logo" style="font-size:18px;">Hello, <?= htmlspecialchars($username) ?></span>
        <?php if($user_role !== 'admin'): ?>
            <a href="cart.php" class="logo" style="font-size:18px;">Cart 🛒</a>
            <a href="create_ticket.php" class="logo" style="font-size:18px;">Support</a>
            <a href="purchases.php" class="logo" style="font-size:18px;">Purchases</a>
            <a href="logout.php" class="logo" style="font-size:18px;">Logout</a>
        <?php endif; ?>
    </nav>
</header>

<div class="section">
    <?php while($product = $products_result->fetch_assoc()): ?>
        <a href="product.php?id=<?= $product['id'] ?>" class="product form-container">
            <img src="<?= htmlspecialchars($product['image_url'] ?: 'assets/placeholder.jpg') ?>" alt="Product">
            <h4><?= htmlspecialchars($product['name']) ?></h4>
            <p><?= htmlspecialchars(substr($product['description'],0,70)) ?>...</p>
            <strong>₱<?= number_format($product['price'],2) ?></strong>
        </a>
    <?php endwhile; ?>
</div>

<script>
// Force reload if page is loaded from cache
window.onpageshow = function(event) {
    if (event.persisted || window.performance && window.performance.navigation.type === 2) {
        window.location.reload();
    }
};
</script>
</body>
</html>
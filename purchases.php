<?php
session_start();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Redirect if not logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

// User info
$user_role = $_SESSION['role'] ?? 'user';
$username = $_SESSION['username'] ?? 'User';

include("computer.php");

// Fetch purchased products for this user
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT p.id AS product_id, p.name, p.image_url, p.price, p.description, o.quantity, o.id AS order_id, o.created_at
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.user_id = ?
    ORDER BY o.created_at DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$purchases = [];
while($row = $result->fetch_assoc()){
    $purchases[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Purchases - CREATECH</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body {margin:0;font-family:'Segoe UI',sans-serif;color:#fff;background:#0f172a;}
header {background:rgba(15,23,42,0.75);padding:15px 30px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;backdrop-filter:blur(10px);z-index:999;}
header a{color:#22c55e;text-decoration:none;margin-left:15px;font-weight:bold;}
header a:hover{opacity:0.8;}
.logo {
    font-size: 24px;
    font-weight: 900;
    letter-spacing: 2px;
    background: linear-gradient(90deg, #2e7738, #2cbb43,  #00ff73);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.section {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px 80px;
}

.products {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 25px;
}

/* --- PRODUCT CARD STYLING LIKE LOGIN CARD --- */
.product {
    background: linear-gradient(#212121, #212121) padding-box,
                linear-gradient(145deg, transparent 35%, var(--accent), #40c9ff) border-box;
    border: 2px solid transparent;
    padding: 20px;
    border-radius: 16px;
    text-align: center;
    cursor: pointer;
    transition: 0.25s;
    overflow: hidden;
    backdrop-filter: blur(10px);
}

.product:hover {
    transform: translateY(-6px);
    border: 2px solid var(--accent);
    box-shadow: 0 8px 25px rgba(2, 255, 78, 0.35);
}

.product img {
    width: 100%;
    height: 190px;
    object-fit: contain;
    margin-bottom: 15px;
    border-radius: 12px;
}

.product h4 {
    margin: 10px 0 8px;
    font-size: 18px;
    color: var(--neon-green);
    font-weight: 800;
}

.product p {
    color: #e5e7eb;
    font-size: 13px;
    margin-bottom: 12px;
    line-height: 1.4;
    min-height: 38px;
}

.product strong {
    display: block;
    font-size: 20px;
    color: #ffcc00;
    font-weight: 900;
}

.product small {
    color: #94a3b8;
    font-size: 12px;
}

@media(max-width:600px){
    header {padding:12px 15px;}
    .logo {font-size:22px;}
}
#bg-video {position:fixed;top:0;left:0;width:100%;height:100%;object-fit:cover;z-index:-2;}
.video-overlay {position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:-1;}

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
            <a href="store.php" class="logo" style="font-size:18px;">Store</a>
            <a href="logout.php" class="logo" style="font-size:18px;">Logout</a>
        <?php endif; ?>
    </nav>
</header>

<div class="section">
    <h2 style="color:#22c55e;margin-bottom:20px;">🛒 My Purchases</h2>
    <div class="products">
        <?php if(empty($purchases)): ?>
            <p>You haven't purchased any products yet.</p>
        <?php else: ?>
            <?php foreach($purchases as $p): ?>
                <div class="product">
                    <img src="<?= htmlspecialchars($p['image_url'] ?: 'assets/placeholder.jpg') ?>" alt="Product">
                    <h4><?= htmlspecialchars($p['name']) ?></h4>
                    <p><?= htmlspecialchars(substr($p['description'],0,70)) ?>...</p>
                    <strong>₱<?= number_format($p['price'],2) ?> × <?= (int)$p['quantity'] ?></strong>
                    <br><small style="color:#94a3b8;">Ordered on <?= htmlspecialchars($p['created_at']) ?></small>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
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

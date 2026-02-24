<?php
session_start();
include("computer.php"); // Database connection

// 🔒 Protect page
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['cart'] ?? [];
$selected = $_SESSION['checkout_selected'] ?? [];

if(!$selected){
    header("Location: cart.php");
    exit;
}

// Build selected cart items
$checkout_items = [];
$total = 0;
foreach($selected as $sid){
    if(isset($cart[$sid])){
        $item = $cart[$sid];
        $checkout_items[] = $item;
        $total += $item['price'] * $item['quantity'];
    }
}

// Fetch user info (using username column)
$stmt = $conn->prepare("SELECT username, email, birthday, address, city, province FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Birthday discount
$birthday_discount = $_SESSION['birthday_discount'] ?? 0;
$discount_amount = ($birthday_discount / 100) * $total;
$total_after_discount = $total - $discount_amount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Online Payment | PC Parts Hub</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body, html {margin:0;padding:0;font-family:'Segoe UI',sans-serif;color:#fff;min-height:100vh;background:#0f172a;}
#bg-video {position:fixed;top:0;left:0;width:100%;height:100%;object-fit:cover;z-index:-2;}
.video-overlay {position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.55);z-index:-1;}
header {background-color: rgba(15,23,42,0.7);color:white;padding:15px 40px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;backdrop-filter:blur(8px);z-index:2;}
nav a {color:#94a3b8;text-decoration:none;margin-left:20px;transition:color 0.3s;}
nav a:hover {color:white;}
.logo {
    font-size: 24px;
    font-weight: 900;
    letter-spacing: 2px;
    background: linear-gradient(90deg, #2e7738, #2cbb43,  #00ff73);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.payment-box {background: rgba(255,255,255,0.1);backdrop-filter: blur(15px);-webkit-backdrop-filter: blur(15px);border-radius:15px;padding:40px;max-width:500px;margin:80px auto;text-align:center;box-shadow:0 10px 30px rgba(0,0,0,0.3);}
.payment-box h2 {font-size:28px;margin-bottom:20px;background:linear-gradient(270deg,#22c55e,#3b82f6,#a855f7,#ec4899,#f59e0b);background-size:400% 400%;-webkit-background-clip:text;-webkit-text-fill-color:transparent;animation: gradientMove 6s ease infinite;}
.item {display:flex;justify-content:space-between;align-items:center;gap:15px;padding:14px;border-radius:14px;background: rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.10);margin-bottom:12px;}
.item-left {display:flex;gap:12px;align-items:center;}
.item img {width:60px;height:60px;object-fit:contain;background:white;border-radius:12px;padding:8px;}
button {width:100%;padding:14px 0;margin:10px 0;font-size:16px;font-weight:bold;text-transform:uppercase;border:none;border-radius:8px;cursor:pointer;transition: all 0.3s ease;color:#fff;}
button[value="gcash"] {background: rgba(34,197,94,0.7);}
button[value="gcash"]:hover {background: rgba(34,197,94,1);transform:translateY(-2px);}
button[value="paypal"] {background: rgba(59,130,246,0.7);}
button[value="paypal"]:hover {background: rgba(59,130,246,1);transform:translateY(-2px);}
.summary {margin-top:20px;background: rgba(0,0,0,0.35);padding:18px;border-radius:14px;border:1px solid rgba(255,255,255,0.10);}
.summary .row {display:flex;justify-content:space-between;margin-bottom:10px;}
.summary .row strong {font-size:16px;}
.cancel-btn{display:block;text-align:center;width:100%;padding:14px;margin-top:12px;border-radius:12px;font-size:15px;font-weight:bold;text-transform:uppercase;text-decoration:none;background:rgba(239,68,68,0.75);color:white;transition:0.2s ease;}
.cancel-btn:hover{background:rgba(239,68,68,1);transform:translateY(-2px);}
</style>
</head>
<body>


<div class="video-overlay"></div>

<header>
    <h1 class="logo">CREATECH</h1>
    <nav>
        <a href="cart.php"class="logo">Back to Cart</a>
        <a href="store.php"class="logo">Store</a>
        <a href="logout.php"class="logo">Logout</a>
    </nav>
</header>

<div class="payment-box">
    <h2>Hello, <?= htmlspecialchars($user['username']); ?>! Select Payment Method</h2>

    <?php foreach($checkout_items as $item): 
        $img = $item['image'] ?? "assets/placeholder.jpg";
        if(!str_contains($img,'/')) $img = "uploads/".$img;
        $item_total = $item['price'] * $item['quantity'];
    ?>
    <div class="item">
        <div class="item-left">
            <img src="<?= htmlspecialchars($img); ?>" alt="Product">
            <div>
                <strong><?= htmlspecialchars($item['name']); ?></strong><br>
                <span>₱<?= number_format($item['price'],2); ?> × <?= $item['quantity']; ?></span>
            </div>
        </div>
        <div><strong>₱<?= number_format($item_total,2); ?></strong></div>
    </div>
    <?php endforeach; ?>

    <div class="summary">
        <div class="row"><span>Subtotal</span><span>₱<?= number_format($total,2); ?></span></div>
        <?php if($birthday_discount>0): ?>
        <div class="row"><span>🎉 Birthday Discount</span><span>-₱<?= number_format($discount_amount,2); ?></span></div>
        <?php endif; ?>
        <div class="row"><strong>Total</strong><strong>₱<?= number_format($total_after_discount,2); ?></strong></div>
    </div>

    <form method="POST" action="final_payment.php">
        <input type="hidden" name="total_price" value="<?= $total_after_discount; ?>">
        <button type="submit" name="method" value="gcash">Pay with GCash</button>
        <button type="submit" name="method" value="paypal">Pay with PayPal</button>
    </form>

    <a href="cart.php" class="cancel-btn" onclick="return confirm('Cancel checkout and go back to cart?')">Cancel Order</a>
</div>

</body>
</html>

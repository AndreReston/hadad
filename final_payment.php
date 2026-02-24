<?php
session_start();
include("computer.php");

// 🔒 must be logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Must come from online_payment.php
$method = $_POST['method'] ?? null;
if(!$method){
    die("Error: No payment method selected.");
}

// Payment name
$payment_name = ($method === "gcash") ? "GCash" : "PayPal";

// Get cart + selected
$cart = $_SESSION['cart'] ?? [];
$selected = $_SESSION['checkout_selected'] ?? [];

if(!$selected){
    die("Error: No product selected for payment.");
}

// Build selected items
$checkout_items = [];
$total = 0;

foreach($selected as $sid){
    if(isset($cart[$sid])){
        $item = $cart[$sid];
        $checkout_items[] = $item;
        $total += ($item['price'] * $item['quantity']);
    }
}

if(!$checkout_items){
    die("Error: Selected items not found in cart.");
}

// Birthday discount
$birthday_discount = $_SESSION['birthday_discount'] ?? 0;
$discount_amount = ($birthday_discount / 100) * $total;
$total_after_discount = $total - $discount_amount;

// 🔥 DELIVERY DETAILS
$stmt = $conn->prepare("
    SELECT street, city, province, zip, distance_km, delivery_minutes
    FROM users
    WHERE id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$user){
    die("Error: User delivery details not found.");
}

$delivery_street = $user['street'];
$delivery_city = $user['city'];
$delivery_province = $user['province'];
$delivery_zip = $user['zip'];
$delivery_minutes = (int)$user['delivery_minutes'];
$distance_km = (int)$user['distance_km'];

// Convert delivery minutes to days
$packing_days = 1;
$delivery_days = max(1, ceil($delivery_minutes / 1440));
$total_days = $packing_days + $delivery_days;

$expected_delivery = date("Y-m-d H:i:s", strtotime("+$total_days days"));

// ✅ INSERT EACH ITEM INTO ORDERS & REDUCE STOCK
$success = true;

$stmt = $conn->prepare("
    INSERT INTO orders 
    (user_id, product_id, price, quantity, payment_method, status,
     delivery_street, delivery_city, delivery_province, delivery_zip,
     distance_km, delivery_minutes, expected_delivery_datetime)
    VALUES (?, ?, ?, ?, ?, 'Paid',
            ?, ?, ?, ?,
            ?, ?, ?)
");

foreach($checkout_items as $item){
    $product_id = (int)$item['id'];
    $price = (float)$item['price'];
    $qty = (int)$item['quantity'];

    // --- REDUCE STOCK ---
    $check = $conn->prepare("SELECT stock_quantity FROM products WHERE id=?");
    $check->bind_param("i", $product_id);
    $check->execute();
    $res = $check->get_result();
    $prod = $res->fetch_assoc();
    $check->close();

    if(!$prod || (int)$prod['stock_quantity'] < $qty){
        $success = false;
        break; // not enough stock
    }

    $update = $conn->prepare("
        UPDATE products 
        SET stock_quantity = stock_quantity - ? 
        WHERE id = ? AND stock_quantity >= ?
    ");
    $update->bind_param("iii", $qty, $product_id, $qty);
    $update->execute();
    if($update->affected_rows === 0){
        $success = false;
        $update->close();
        break;
    }
    $update->close();
    // --- END REDUCE STOCK ---

    // Insert order
    $stmt->bind_param(
        "iidissssiiis",
        $user_id,
        $product_id,
        $price,
        $qty,
        $payment_name,
        $delivery_street,
        $delivery_city,
        $delivery_province,
        $delivery_zip,
        $distance_km,
        $delivery_minutes,
        $expected_delivery
    );

    if(!$stmt->execute()){
        $success = false;
        break;
    }
}

$stmt->close();

// Remove selected items from cart if successful
if($success){
    foreach($selected as $sid){
        unset($_SESSION['cart'][$sid]);
    }
    unset($_SESSION['checkout_selected']);
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Payment Confirmation</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
:root { --neon-green: #22c55e; --dark-bg: #020617; }
body {
    margin:0;
    font-family: 'Courier New', monospace;
    background: var(--dark-bg);
    color: var(--neon-green);
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    overflow: hidden;
}
#bg-video {
    position: fixed;
    top:0; left:0;
    width:100%; height:100%;
    object-fit: cover;
    z-index:-1;
    filter: brightness(0.3);
}

/* --- SYSTEM HUB --- */
.system-hub {
    max-width: 700px;
    width: 100%;
    max-height: 85vh;
    overflow-y: auto;
    background: rgba(15,23,42,0.85);
    border-radius: 16px;
    padding: 30px 20px;
    border: 2px solid rgba(34,197,94,0.4);
    box-shadow: 0 0 60px rgba(34,197,94,0.2);
    position: relative;
}
.system-hub::after {
    content:"";
    position:absolute;
    top:0; left:0; right:0;
    height:2px;
    background: rgba(34,197,94,0.2);
    animation: scan 4s linear infinite;
}
@keyframes scan {0%{top:0}100%{top:100%}}

.power-ring {
    width: 80px; height: 80px;
    margin:0 auto 20px;
    border: 5px solid #1e293b;
    border-top:5px solid var(--neon-green);
    border-radius: 50%;
    animation: spin 1.2s linear infinite;
    display:flex; align-items:center; justify-content:center;
}
.power-ring::after { content:"OK"; font-weight:bold; opacity:0; animation: fadeIn 0.2s forwards 1.2s; }
@keyframes spin {100%{transform:rotate(360deg); border-color: var(--neon-green)}}
@keyframes fadeIn {to{opacity:1}}

h1 {
    text-align:center;
    margin-bottom: 12px;
}

.status-line {
    font-size:14px;
    white-space: nowrap;
    overflow:hidden;
    width:0;
    animation: typing 0.5s steps(30,end) forwards;
    margin:6px 0;
}
@keyframes typing { from { width:0 } to { width:100% } }

.item-line {
    display:flex;
    justify-content:space-between;
    margin:4px 0;
}

.summary {
    margin-top:15px;
    padding:16px;
    border-radius:14px;
    background: rgba(0,0,0,0.35);
    border:1px solid rgba(34,197,94,0.3);
}
.summary .row {
    display:flex;
    justify-content:space-between;
    margin-bottom:8px;
}
.btn {
    display:block;
    margin:20px auto 0;
    padding:12px 20px;
    background:#3b82f6;
    color:white;
    text-decoration:none;
    border-radius:12px;
    font-weight:bold;
    text-align:center;
    transition:0.2s;
}
.btn:hover { background:#2563eb; }

.success { color: #22c55e; text-align:center; margin-bottom:10px; }
.fail { color: #ef4444; text-align:center; margin-bottom:10px; }

</style>
</head>
<body>

<video autoplay muted loop playsinline id="bg-video">
    <source src="assets/background.mp4" type="video/mp4">
</video>

<div class="system-hub">

    <div class="power-ring"></div>

<?php if($success): ?>
    <h1 class="success">SYSTEM PAYMENT INITIALIZED ✅</h1>

    <div class="status-line" style="animation-delay: 0.5s;">> User: <?= htmlspecialchars($_SESSION['username']) ?></div>
    <div class="status-line" style="animation-delay: 0.7s;">> Role: <?= strtoupper($_SESSION['role']) ?></div>
    <div class="status-line" style="animation-delay: 0.9s;">> Payment Method: <?= htmlspecialchars($payment_name) ?></div>
    <div class="status-line" style="animation-delay: 1.1s;">> Expected Delivery: <?= htmlspecialchars($expected_delivery) ?></div>

    <h3 style="margin-top:15px;">Purchased Items:</h3>
    <?php foreach($checkout_items as $item): ?>
        <div class="item-line">
            <span><?= htmlspecialchars($item['name']) ?> × <?= (int)$item['quantity'] ?></span>
            <span>₱<?= number_format($item['price'] * $item['quantity'],2) ?></span>
        </div>
    <?php endforeach; ?>

    <div class="summary">
        <div class="row"><span>Subtotal</span><span>₱<?= number_format($total,2) ?></span></div>
        <?php if($birthday_discount > 0): ?>
        <div class="row"><span>🎉 Birthday Discount (<?= $birthday_discount ?>%)</span><span>-₱<?= number_format($discount_amount,2) ?></span></div>
        <?php endif; ?>
        <div class="row"><b>Total Paid</b><b>₱<?= number_format($total_after_discount,2) ?></b></div>
    </div>

    <a class="btn" href="store.php" style="background-color: #22c55e;">Back to Store</a>

<?php else: ?>
    <h1 class="fail">❌ PAYMENT FAILED</h1>
    <div class="status-line">> Error processing your order.</div>
    <a class="btn" href="cart.php">Back to Cart</a>
<?php endif; ?>

</div>
</body>
</html>

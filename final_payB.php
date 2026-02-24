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

// ✅ INSERT EACH ITEM INTO ORDERS
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

// If successful, remove selected items from cart
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
    body {
        font-family: Arial, sans-serif;
        background: #0f172a;
        color: white;
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        margin: 0;
        padding: 20px;
    }
    .container {
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.10);
        padding: 30px 30px;
        border-radius: 18px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        width: 100%;
        max-width: 650px;
    }
    h1 {
        margin-bottom: 18px;
        font-size: 26px;
    }
    .success { color: #22c55e; }
    .fail { color: #ef4444; }

    .item {
        display: flex;
        justify-content: space-between;
        padding: 12px;
        border-radius: 12px;
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.08);
        margin-bottom: 10px;
    }
    .summary {
        margin-top: 18px;
        padding: 16px;
        border-radius: 14px;
        background: rgba(0,0,0,0.35);
        border: 1px solid rgba(255,255,255,0.08);
    }
    .row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 15px;
    }
    .btn {
        display: inline-block;
        margin-top: 18px;
        padding: 12px 20px;
        background: #3b82f6;
        color: white;
        text-decoration: none;
        border-radius: 12px;
        font-weight: bold;
        transition: 0.2s ease;
    }
    .btn:hover { background: #2563eb; }
</style>
</head>
<body>

<div class="container">

<?php if($success): ?>
    <h1 class="success">✅ Payment Successful!</h1>

    <p><b>Payment Method:</b> <?= htmlspecialchars($payment_name) ?></p>

    <h3 style="margin-top:18px;">Purchased Items:</h3>

    <?php foreach($checkout_items as $item): ?>
        <div class="item">
            <div>
                <b><?= htmlspecialchars($item['name']) ?></b><br>
                ₱<?= number_format($item['price'],2) ?> × <?= (int)$item['quantity'] ?>
            </div>
            <div>
                <b>₱<?= number_format($item['price'] * $item['quantity'],2) ?></b>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="summary">
        <div class="row">
            <span>Subtotal</span>
            <span>₱<?= number_format($total,2) ?></span>
        </div>

        <?php if($birthday_discount > 0): ?>
        <div class="row">
            <span>🎉 Birthday Discount (<?= $birthday_discount ?>%)</span>
            <span>-₱<?= number_format($discount_amount,2) ?></span>
        </div>
        <?php endif; ?>

        <div class="row">
            <b>Total Paid</b>
            <b>₱<?= number_format($total_after_discount,2) ?></b>
        </div>

        <div class="row">
            <span>Expected Delivery</span>
            <span><b><?= htmlspecialchars($expected_delivery) ?></b></span>
        </div>
    </div>

    <a class="btn" href="store.php">Back to Store</a>

<?php else: ?>
    <h1 class="fail">❌ Payment Failed</h1>
    <p>There was an error processing your order.</p>
    <a class="btn" href="cart.php">Back to Cart</a>
<?php endif; ?>

</div>

</body>
</html>

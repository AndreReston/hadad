<?php
session_start();
include("computer.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['cart'] ?? [];

if(empty($cart)){
    header("Location: cart.php");
    exit;
}

/* =========================================================
   GET POST VALUES
   ========================================================= */

$base_price = $_POST['final_price'] ?? 0;
$payment_method = $_POST['payment_method'] ?? "";
$delivery_type = $_POST['delivery_type'] ?? "standard";

// Validate
if($base_price <= 0 || empty($payment_method)){
    header("Location: order.php");
    exit;
}

/* =========================================================
   DELIVERY TYPE LOGIC
   ========================================================= */

if($delivery_type === "express"){
    $shipping_fee = 250;
    $extra_minutes = 60;     // Faster processing
} else {
    $shipping_fee = 100;
    $extra_minutes = 180;    // Normal processing
}

// Final price including shipping
$final_price = $base_price + $shipping_fee;

/* =========================================================
   GET USER DELIVERY INFO
   ========================================================= */

$stmt = $conn->prepare("
    SELECT street, city, province, zip, distance_km, delivery_minutes 
    FROM users 
    WHERE id=?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if(!$user){
    die("User not found.");
}

$street = $user['street'];
$city = $user['city'];
$province = $user['province'];
$zip = $user['zip'];
$distance_km = (int)$user['distance_km'];
$delivery_minutes = (int)$user['delivery_minutes'];

/* =========================================================
   CALCULATE EXPECTED DELIVERY
   ========================================================= */

$packing_minutes = 60;
$total_minutes = $packing_minutes + $delivery_minutes + $extra_minutes;

$expected_delivery_datetime = date(
    "Y-m-d H:i:s",
    strtotime("+$total_minutes minutes")
);

/* =========================================================
   ONLINE PAYMENT REDIRECT
   ========================================================= */

if($payment_method === "online"){
    $_SESSION['online_cart'] = $cart;
    $_SESSION['online_price'] = $final_price;
    $_SESSION['shipping_fee'] = $shipping_fee;
    $_SESSION['expected_delivery_datetime'] = $expected_delivery_datetime;
    header("Location: online_payment.php");
    exit;
}

/* =========================================================
   CASH ON DELIVERY PROCESS
   ========================================================= */

$conn->begin_transaction();

try {

    foreach($cart as $item){

        $product_id = (int)$item['id'];
        $qty = (int)$item['quantity'];
        $price = (float)$item['price'];

        if($qty <= 0){
            throw new Exception("Invalid quantity.");
        }

        /* ==============================
           1) CHECK STOCK
        ============================== */

        $check = $conn->prepare(
            "SELECT stock_quantity FROM products WHERE id=?"
        );
        $check->bind_param("i", $product_id);
        $check->execute();
        $res = $check->get_result();
        $prod = $res->fetch_assoc();
        $check->close();

        if(!$prod){
            throw new Exception("Product not found (ID: $product_id)");
        }

        if((int)$prod['stock_quantity'] < $qty){
            throw new Exception("Not enough stock for product ID: $product_id");
        }

        /* ==============================
           2) REDUCE STOCK
        ============================== */

        $update = $conn->prepare("
            UPDATE products
            SET stock_quantity = stock_quantity - ?
            WHERE id = ? AND stock_quantity >= ?
        ");
        $update->bind_param("iii", $qty, $product_id, $qty);
        $update->execute();

        if($update->affected_rows === 0){
            $update->close();
            throw new Exception("Stock update failed for product ID: $product_id");
        }

        $update->close();

        /* ==============================
           3) INSERT ORDER
        ============================== */

        $item_total = $price * $qty;

       $stmt = $conn->prepare("
    INSERT INTO orders 
    (user_id, product_id, quantity, price, shipping_fee,
     payment_method, status,
     delivery_street, delivery_city, delivery_province, delivery_zip,
     distance_km, delivery_minutes, expected_delivery_datetime)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

        $stmt->bind_param(
    "iiiddssssiiiss",
    $user_id,
    $product_id,
    $qty,
    $item_total,
    $shipping_fee,
    $payment_method,
    $status,
    $street,
    $city,
    $province,
    $zip,
    $distance_km,
    $delivery_minutes,
    $expected_delivery_datetime
);

        $stmt->execute();
        $stmt->close();
    }

    // Commit if everything successful
    $conn->commit();

    // Clear cart
    unset($_SESSION['cart']);

    header("Location: success.php");
    exit;

} catch(Exception $e){

    // Rollback everything if error
    $conn->rollback();

    die("Checkout failed: " . $e->getMessage());
}
?>
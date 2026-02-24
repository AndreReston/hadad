<?php
session_start();
include("computer.php");

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$product_id = $_POST['product_id'];
$final_price = $_POST['final_price'];
$payment_method = $_POST['payment_method'];
$user_id = $_SESSION['user_id'];

// 1) Get user delivery details
$stmt = $conn->prepare("SELECT street, city, province, zip, distance_km, delivery_minutes 
                        FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$delivery_street = $user['street'];
$delivery_city = $user['city'];
$delivery_province = $user['province'];
$delivery_zip = $user['zip'];
$distance_km = (int)$user['distance_km'];
$delivery_minutes = (int)$user['delivery_minutes'];

// Add packing time (optional)
$packing_minutes = 60;

// 2) Compute expected delivery datetime
$total_minutes = $packing_minutes + $delivery_minutes;
$expected_delivery = date("Y-m-d H:i:s", strtotime("+$total_minutes minutes"));

if($payment_method == "cod"){

    // 3) Insert order with delivery details
    $stmt = $conn->prepare("INSERT INTO orders 
        (user_id, product_id, price, payment_method, status, 
         delivery_street, delivery_city, delivery_province, delivery_zip,
         distance_km, delivery_minutes, expected_delivery_datetime)
        VALUES (?, ?, ?, 'Cash on Delivery', 'Pending',
                ?, ?, ?, ?,
                ?, ?, ?)");

    $stmt->bind_param("iidssssiiis",
        $user_id, $product_id, $final_price,
        $delivery_street, $delivery_city, $delivery_province, $delivery_zip,
        $distance_km, $delivery_minutes, $expected_delivery
    );

    $stmt->execute();
    $stmt->close();

    echo "Order placed successfully! Expected delivery: $expected_delivery";

} elseif($payment_method == "online") {

    $_SESSION['online_product_id'] = $product_id;
    $_SESSION['online_price'] = $final_price;

    header("Location: online_payment.php");
    exit;
}
?>

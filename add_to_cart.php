<?php
session_start();
include("computer.php");

// 🔒 must be logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

// Admin cannot add to cart
if(($_SESSION['role'] ?? 'user') === "admin"){
    header("Location: store.php");
    exit;
}

$product_id = (int)($_POST['product_id'] ?? 0);

if($product_id <= 0){
    header("Location: store.php");
    exit;
}

// Fetch product from DB
$stmt = $conn->prepare("SELECT id, name, price, image_url, stock_quantity FROM products WHERE id=?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$product){
    header("Location: store.php");
    exit;
}

// Init cart
if(!isset($_SESSION['cart'])){
    $_SESSION['cart'] = [];
}

// If already in cart -> increase quantity
if(isset($_SESSION['cart'][$product_id])){
    $_SESSION['cart'][$product_id]['quantity'] += 1;
} else {
    $_SESSION['cart'][$product_id] = [
        "id" => $product['id'],
        "name" => $product['name'],
        "price" => (float)$product['price'],
        "image" => $product['image_url'],
        "quantity" => 1,
        "stock" => (int)$product['stock_quantity']
    ];
}

// Optional: prevent exceeding stock
if($_SESSION['cart'][$product_id]['quantity'] > $product['stock_quantity']){
    $_SESSION['cart'][$product_id]['quantity'] = $product['stock_quantity'];
}

// Redirect back
header("Location: cart.php");
exit;

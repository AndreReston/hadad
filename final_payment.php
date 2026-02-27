<?php
session_start();
include("computer.php");

if(!isset($_SESSION['user_id']) || !isset($_SESSION['checkout_selected'])){
    header("Location: cart.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$cart = $_SESSION['cart'] ?? [];
$selected = $_SESSION['checkout_selected'];
$method = $_POST['method'] ?? '';
$total_price = (float)($_POST['total_price'] ?? 0);

if(empty($method) || $total_price <= 0){
    die("Invalid payment.");
}

$conn->begin_transaction();

try {

    foreach($selected as $sid){

        if(!isset($cart[$sid])) continue;

        $item = $cart[$sid];

        $product_id = (int)$item['id'];
        $qty = (int)$item['quantity'];
        $price = (float)$item['price'];

        /* ==========================
           1️⃣ CHECK STOCK
        ========================== */
        $check = $conn->prepare("SELECT stock_quantity FROM products WHERE id=?");
        $check->bind_param("i", $product_id);
        $check->execute();
        $res = $check->get_result();
        $prod = $res->fetch_assoc();
        $check->close();

        if(!$prod || $prod['stock_quantity'] < $qty){
            throw new Exception("Not enough stock for product ID: $product_id");
        }

        /* ==========================
           2️⃣ REDUCE STOCK
        ========================== */
        $update = $conn->prepare("
            UPDATE products
            SET stock_quantity = stock_quantity - ?
            WHERE id=? AND stock_quantity >= ?
        ");
        $update->bind_param("iii", $qty, $product_id, $qty);
        $update->execute();

        if($update->affected_rows === 0){
            throw new Exception("Stock update failed.");
        }

        $update->close();

        /* ==========================
           3️⃣ INSERT ORDER
        ========================== */

        $item_total = $price * $qty;
        $status = "Paid";

        $stmt = $conn->prepare("
            INSERT INTO orders
            (user_id, product_id, quantity, price, payment_method, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "iiidss",
            $user_id,
            $product_id,
            $qty,
            $item_total,
            $method,
            $status
        );

        $stmt->execute();
        $stmt->close();

        // Remove item from cart
        unset($_SESSION['cart'][$sid]);
    }

    $conn->commit();

    unset($_SESSION['checkout_selected']);

    header("Location: success.php");
    exit;

} catch(Exception $e){

    $conn->rollback();
    die("Payment failed: " . $e->getMessage());
}
?>
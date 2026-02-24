<?php
session_start();
include("computer.php");

// 🔒 Protect page
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

// Get POST data safely
$product_id = $_POST['product_id'] ?? null;
$final_price = $_POST['final_price'] ?? null;
$payment_method = $_POST['payment_method'] ?? null;
$user_id = $_SESSION['user_id'];

// Validate required fields
if(!$product_id || !$final_price || !$payment_method){
    die("Error: Missing order information.");
}

// 1) Get user delivery details
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

// Assign variables
$street = $user['street'];
$city = $user['city'];
$province = $user['province'];
$zip = $user['zip'];
$distance_km = (int)$user['distance_km'];
$delivery_minutes = (int)$user['delivery_minutes'];

// Calculate expected delivery in days
$packing_days = 1;
$delivery_days = ceil($delivery_minutes / 1440); // minutes → days
$total_days = $packing_days + $delivery_days;
$expected_delivery = date("Y-m-d H:i:s", strtotime("+$total_days days"));

// Insert order (COD)
$success = false;
if($payment_method === "cod"){
    $stmt = $conn->prepare("
        INSERT INTO orders 
        (user_id, product_id, price, payment_method, status,
         delivery_street, delivery_city, delivery_province, delivery_zip,
         distance_km, delivery_minutes, expected_delivery_datetime)
        VALUES (?, ?, ?, 'Cash on Delivery', 'Pending',
                ?, ?, ?, ?,
                ?, ?, ?)
    ");
    $stmt->bind_param(
        "iidssssiis",
        $user_id,
        $product_id,
        $final_price,
        $street,
        $city,
        $province,
        $zip,
        $distance_km,
        $delivery_minutes,
        $expected_delivery
    );
    $success = $stmt->execute();
    $stmt->close();
} elseif($payment_method === "online"){
    $_SESSION['online_product_id'] = $product_id;
    $_SESSION['onlineprice'] = $final_price;
    header("Location: online_payment.php");
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Order Confirmation | PC Parts Hub</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
/* --- BASE --- */
body, html {
    margin:0;
    padding:0;
    font-family: 'Segoe UI', sans-serif;
    background:#0f172a;
    color:#fff;
    min-height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
}

/* --- BACKGROUND VIDEO --- */
#bg-video {
    position: fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    object-fit: cover;
    z-index: -2;
}
body::before {
    content:"";
    position: fixed;
    top:0;
    left:0;
    width:100%;
    height:100%;
    background: rgba(0,0,0,0.4);
    z-index:-1;
}

/* --- HEADER --- */
header{
    position: fixed;
    top:0;
    left:0;
    width:100%;
    padding:15px 40px;
    display:flex;
    justify-content: space-between;
    align-items:center;
    background: rgba(15,23,42,0.7);
    backdrop-filter: blur(8px);
    z-index: 2;
}
.logo{
    font-size:26px;
    font-weight:900;
    background: linear-gradient(270deg,#22c55e,#3b82f6,#a855f7,#ec4899,#f59e0b);
    background-size:400% 400%;
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    animation: gradientMove 6s ease infinite;
    letter-spacing:2px;
}
header nav a{
    color:#94a3b8;
    text-decoration:none;
    margin-left:20px;
    transition: color 0.3s;
}
header nav a:hover {color:white;}
@keyframes gradientMove{
    0%{background-position:0% 50%;}
    50%{background-position:100% 50%;}
    100%{background-position:0% 50%;}
}

/* --- CONFIRMATION CARD --- */
.confirm-box{
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(15px);
    -webkit-backdrop-filter: blur(15px);
    border-radius:15px;
    padding:40px;
    max-width:450px;
    width:100%;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    text-align:center;
    animation: fadeIn 0.7s ease;
}
.confirm-box h1{
    font-size:28px;
    margin-bottom:20px;
    background: linear-gradient(270deg,#22c55e,#3b82f6,#a855f7,#ec4899,#f59e0b);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    animation: gradientMove 6s ease infinite;
}
.confirm-box p{
    font-size:16px;
    margin:10px 0;
    color:#fff;
}
.delivery{
    font-weight:bold;
    color:#facc15; /* yellow highlight */
}
button.btn{
    padding:12px 25px;
    margin-top:25px;
    background:#3b82f6;
    color:#fff;
    border:none;
    border-radius:8px;
    cursor:pointer;
    transition:0.3s;
}
button.btn:hover{
    background:#2563eb;
}

/* ANIMATIONS */
@keyframes fadeIn{
    0%{opacity:0; transform: translateY(20px);}
    100%{opacity:1; transform: translateY(0);}
}
</style>
</head>
<body>



<header>
    <h1 class="logo">CREATECH</h1>
    <nav>
        <a href="store.php">Back to Store</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="confirm-box">
<?php if($success): ?>
    <h1>Order Placed Successfully!</h1>
    <p>Payment Method: <b>Cash on Delivery</b></p>
    <p>Amount: <b>₱<?= number_format($final_price,2); ?></b></p>
    <p>Expected Delivery: <span class="delivery"><?= $expected_delivery; ?></span></p>
    <form action="store.php">
        <button type="submit" class="btn">Back to Store</button>
    </form>
<?php else: ?>
    <h1 style="color:red;">Order Pending</h1>
    <p>If you chose online payment, please complete it on the next page.</p>
    <form action="store.php">
        <button type="submit" class="btn">Back to Store</button>
    </form>
<?php endif; ?>
</div>

</body>
</html>

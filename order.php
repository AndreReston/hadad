<?php
session_start();
include("computer.php");

// 🔒 Must be logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

// Fetch cart & selected items
$cart = $_SESSION['cart'] ?? [];
$birthday_discount = $_SESSION['birthday_discount'] ?? 0;
$selected_ids = $_SESSION['checkout_selected'] ?? [];

if(empty($selected_ids)){
    $_SESSION['cart_error'] = "Please select at least one item to checkout.";
    header("Location: cart.php");
    exit;
}

// Build array of selected items only
$selected_items = [];
foreach($selected_ids as $pid){
    if(isset($cart[$pid])){
        $selected_items[$pid] = $cart[$pid];
    }
}

// Calculate totals for selected items only
$total = 0;
foreach($selected_items as $item){
    $total += $item['price'] * $item['quantity'];
}

$discount_amount = ($birthday_discount / 100) * $total;
$total_after_discount = $total - $discount_amount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Confirm Order | CREATECH</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
/* ====== BODY & VIDEO ====== */
body, html {
    margin:0; padding:0;
    font-family:'Segoe UI',sans-serif;
    color:#fff;
    min-height:100vh;
    background: transparent;
}

#bg-video {
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    object-fit: cover;
    z-index: -2;
}

body::before {
    content: "";
    position: fixed;
    top:0; left:0; width:100%; height:100%;
    background: rgba(15,23,42,0.5); /* overlay for readability */
    z-index: -1;
}

/* ====== HEADER ====== */
header {
    position: relative; z-index: 1;
    background-color: rgba(15,23,42,0.7);
    padding: 15px 40px;
    display:flex; justify-content:space-between; align-items:center;
    position:sticky; top:0; backdrop-filter:blur(8px);
}
nav a { color:#94a3b8; text-decoration:none; margin-left:20px; }
nav a:hover { color:white; }
.logo {
    font-size: 24px;
    font-weight: 900;
    letter-spacing: 2px;
    background: linear-gradient(90deg, #2e7738, #2cbb43,  #00ff73);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* ====== BOX ====== */
.container{
  max-width: 900px;          /* KEEP your max width */
  margin: 30px auto;         /* KEEP center alignment */
  
  background: linear-gradient(#212121, #212121) padding-box,
              linear-gradient(145deg, transparent 35%, #e81cff, #40c9ff) border-box;
  border: 2px solid transparent;

  padding: 32px 24px;
  font-size: 14px;
  font-family: inherit;
  color: white;

  display: flex;
  flex-direction: column;
  gap: 20px;

  box-sizing: border-box;
  border-radius: 16px;
}
.container:hover{

    transform: translateY(-6px);
    border: 2px solid #13b61b;
    box-shadow:0 8px 25px rgba(0,0,0,0.35);
}




/* ====== CONTENT ====== */
h2 { margin-bottom:18px; font-size:28px;
    background: linear-gradient(270deg,#22c55e,#3b82f6,#a855f7,#ec4899,#f59e0b);
    background-size:400% 400%; -webkit-background-clip:text;
    -webkit-text-fill-color:transparent; animation: gradientMove 6s ease infinite; 
}
.plain-title { background: none !important; -webkit-background-clip: initial !important;
    -webkit-text-fill-color: white !important; color: white !important; animation: none !important; }

.item { display:flex; justify-content:space-between; align-items:center; gap:15px;
    padding:14px; border-radius:14px; background: rgba(255,255,255,0.06);
    border:1px solid rgba(255,255,255,0.10); margin-bottom:12px;
}
.item-left { display:flex; gap:12px; align-items:center; }
.item img { width:70px; height:70px; object-fit:contain; background:white; border-radius:12px; padding:8px; }
.item strong { font-size:16px; }
.small { color:#cbd5e1; font-size:13px; }

.summary { margin-top:20px; background: rgba(0,0,0,0.35);
    padding:18px; border-radius:14px; border:1px solid rgba(255,255,255,0.10);
}
.row { display:flex; justify-content:space-between; margin-bottom:10px; }
.row strong { font-size:18px; }
.discount { color:#fbbf24; font-weight:bold; }

input, select {
    width:100%;
    padding:10px;
    margin-bottom:12px;
    border-radius:8px;
    border:none;
    outline:none;
    background:rgba(255,255,255,0.15);
    color:white;
}

textarea{
    width: 100%;
    padding: 12px 16px;
    margin-bottom:12px;
    border-radius: 8px;
    resize: vertical;
    min-height: 96px;
    border: 1px solid #414141;
    background-color: transparent;
    color: #fff;
    font-family: inherit;
}

textarea:focus{
    outline: none;
    border-color: #13b61b;
}

button { width:100%; padding:14px; margin-top:12px; font-size:15px; font-weight:bold;
    text-transform:uppercase; border:none; border-radius:12px; cursor:pointer; transition:0.2s ease; }
.cod { background:rgba(34,197,94,0.75); color:#fff; }
.cod:hover { background:rgba(34,197,94,1); transform:translateY(-2px); }
.online { background:rgba(59,130,246,0.75); color:#fff; }
.online:hover { background:rgba(59,130,246,1); transform:translateY(-2px); }

.cancel-btn { display:block; text-align:center; width:100%; padding:14px; margin-top:12px;
    border-radius:12px; font-size:15px; font-weight:bold; text-transform:uppercase;
    text-decoration:none; background:rgba(239,68,68,0.75); color:white; transition:0.2s ease; }
.cancel-btn:hover { background:rgba(239,68,68,1); transform:translateY(-2px); }
select {
    width:100%;
    padding:10px;
    margin-bottom:12px;
    border-radius:8px;
    border:none;
    outline:none;
    background: rgba(255,255,255,0.15);
    color: white;          /* text in closed state */
    -webkit-appearance: none; /* removes default arrow on Safari */
    -moz-appearance: none;
    appearance: none;
}

select option {
    color: black;  /* make options readable when dropdown is open */
    background: white; /* optional: white background for readability */
}
.cancel-btn {
    display: inline-block;      /* makes it behave more like a button */
    width: 100%;                /* fill the same container width */
    max-width: 100%;            /* prevent it from stretching */
    box-sizing: border-box;     /* include padding in width */
    padding: 14px;
    margin-top: 12px;
    border-radius: 12px;
    font-size: 15px;
    font-weight: bold;
    text-transform: uppercase;
    text-align: center;
    text-decoration: none;
    background: rgba(239,68,68,0.75);
    color: #fff;
    transition: 0.2s ease;
}
</style>
</head>
<body>

<!-- BACKGROUND VIDEO -->
<video autoplay muted loop id="bg-video">
    <source src="assets/redbg.mp4" type="video/mp4">
    Your browser does not support the video tag.
</video>

<!-- HEADER -->
<header>
    <h1 class="logo">CREATECH</h1>
    <nav>
        <a href="cart.php" class="logo" style="font-size:18px;">Back to Cart</a>
        <a href="store.php" class="logo" style="font-size:18px;">Store</a>
        <a href="logout.php" class="logo" style="font-size:18px;">Logout</a>
    </nav>
</header>

<!-- MAIN BOX -->
<div class="container">
    <h2 style="color:green;">Confirm Your Order</h2>

    <?php foreach($selected_items as $item): 
        $image = $item['image'] ?? "";
        if(empty($image)) $image = "assets/placeholder.jpg";
        else if(!str_contains($image, "/")) $image = "uploads/" . $image;
        $item_total = $item['price'] * $item['quantity'];
    ?>
    <div class="item">
        <div class="item-left">
            <img src="<?= htmlspecialchars($image) ?>" alt="Product">
            <div>
                <strong><?= htmlspecialchars($item['name']); ?></strong><br>
                <span class="small">₱<?= number_format($item['price'],2); ?> × <?= $item['quantity']; ?></span>
            </div>
        </div>
        <div><strong>₱<?= number_format($item_total,2); ?></strong></div>
    </div>
    <?php endforeach; ?>

    <div class="summary">
        <div class="row">
            <span>Subtotal (Selected Items)</span>
            <span>₱<?= number_format($total,2); ?></span>
        </div>
        <?php if($birthday_discount > 0): ?>
        <div class="row discount">
            <span>🎉 Birthday Discount (<?= $birthday_discount ?>%)</span>
            <span>- ₱<?= number_format($discount_amount,2); ?></span>
        </div>
        <?php endif; ?>
        <hr style="border:none;border-top:1px solid rgba(255,255,255,0.15);margin:12px 0;">
        <div class="row">
            <strong>Total</strong>
            <strong>₱<?= number_format($total_after_discount,2); ?></strong>
        </div>
    </div>



    <form method="POST" action="checkout.php">
        <label>Full Name</label>
        <input type="text" name="fullname" required>

        <label>Phone Number</label>
        <input type="text" name="phone" required>

        <label>Complete Address</label>
        <textarea name="address" required></textarea>



        <input type="hidden" name="final_price" value="<?= $total_after_discount; ?>">

        <button type="submit" name="payment_method" value="cod" class="cod">
            Cash on Delivery
        </button>
    </form>

    <form method="POST" action="online_payment.php">
        <input type="hidden" name="final_price" value="<?= $total_after_discount; ?>">
        <button type="submit" name="payment_method" value="online" class="online">
            Online Payment
        </button>
    </form>

    <a href="cart.php" class="cancel-btn" onclick="return confirm('Cancel this checkout and go back to cart?')">
        Cancel Order
    </a>
</div>

</body>
</html>
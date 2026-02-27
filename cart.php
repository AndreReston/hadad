<?php
session_start();
include("computer.php");

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Redirect to login if not logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$birthday_discount = $_SESSION['birthday_discount'] ?? 0;
$cart = $_SESSION['cart'] ?? [];

/* ================================
   HANDLE AJAX QUANTITY & REMOVE
================================ */
if($_SERVER['REQUEST_METHOD']==="POST" && isset($_POST['ajax_action'])){

    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['ajax_action'];
    $selected = $_POST['selected'] ?? [];

    $cart = $_SESSION['cart'] ?? [];

    $response = [
        "error" => "",
        "quantity" => 0,
        "item_total" => 0,
        "subtotal" => 0,
        "discount" => 0,
        "total_after" => 0
    ];

    if(!isset($cart[$id])){
        echo json_encode($response);
        exit;
    }

    // IMPORTANT: Use correct column name
    $stmt = $conn->prepare("SELECT stock_quantity FROM products WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_assoc();
    $stock = $res['stock_quantity'] ?? 0;

    if($action==="increase"){
        if($cart[$id]['quantity'] < $stock){
            $cart[$id]['quantity']++;
        }
    }

    if($action==="decrease"){
        $cart[$id]['quantity']--;
        if($cart[$id]['quantity'] <= 0){
            unset($cart[$id]);
        }
    }

    if($action==="remove"){
        unset($cart[$id]);
    }

    $_SESSION['cart'] = $cart;

    if(isset($cart[$id])){
        $response['quantity'] = $cart[$id]['quantity'];
        $response['item_total'] = $cart[$id]['quantity'] * $cart[$id]['price'];
    }

    $subtotal = 0;
    foreach($selected as $sid){
        $sid=(int)$sid;
        if(isset($cart[$sid])){
            $subtotal += $cart[$sid]['quantity'] * $cart[$sid]['price'];
        }
    }

    $discount = ($birthday_discount/100) * $subtotal;
    $total_after = $subtotal - $discount;

    $response['subtotal'] = $subtotal;
    $response['discount'] = $discount;
    $response['total_after'] = $total_after;

    echo json_encode($response);
    exit;
}

/* ================================
   HANDLE POST FOR SELECTED ACTIONS
================================ */
if($_SERVER['REQUEST_METHOD'] === "POST" && !isset($_POST['ajax_action'])){
    $action = $_POST['action'] ?? "";
    $selected = $_POST['selected'] ?? [];

    if($action==="remove_selected"){
        foreach($selected as $sid){
            $sid=(int)$sid;
            unset($_SESSION['cart'][$sid]);
        }
        header("Location: cart.php");
        exit;
    }
    if($action==="checkout_selected"){
        if(empty($selected)){
            $_SESSION['cart_error']="Please select at least one item to checkout.";
            header("Location: cart.php");
            exit;
        }
        $_SESSION['checkout_selected']=array_map("intval",$selected);
        header("Location: order.php");
        exit;
    }
}

$cart_error = $_SESSION['cart_error'] ?? "";
unset($_SESSION['cart_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Your Cart</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: 'Segoe UI', sans-serif;
        min-height: 100vh;
        color: #e5e7eb;
        background: #0f172a;
        overflow-x: hidden;
    }

    #bg-video {
        position: fixed;
        top: 0; left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        z-index: -2;
    }

    .overlay {
        position: fixed;
        top: 0; left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.65);
        z-index: -1;
    }

    header {
        background-color: rgba(15,23,42,0.55);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(255,255,255,0.08);
        padding: 16px 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        position: sticky;
        top: 0;
        z-index: 10;
    }

.logo {
    font-size: 24px;
    font-weight: 900;
    letter-spacing: 2px;
    background: linear-gradient(90deg, #2e7738, #2cbb43,  #00ff73);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

    nav a {
        color: #94a3b8;
        text-decoration: none;
        margin-left: 18px;
        font-weight: 600;
        transition: 0.25s ease;
    }
    nav a:hover { color: white; }

   

.container{
  max-width: 1100px;          /* KEEP your max width */
  margin: auto;         /* KEEP center alignment */
  
  background: linear-gradient(#212121, #212121) padding-box,
              linear-gradient(145deg, transparent 35%, #1ccaff, #13b61b) border-box;
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

textarea{
  width: 100%;
  padding: 12px 16px;
  border-radius: 8px;
  resize: vertical;
  color: #fff;
  min-height: 96px;
  border: 1px solid #414141;
  background-color: transparent;
  font-family: inherit;
}

textarea:focus{
  outline: none;
  border-color: #13b61b;
}

    h1 {
        font-size: 28px;
        margin-bottom: 18px;
        font-weight: 800;
        color:  #13b61b;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .cart-box {
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.10);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        padding: 20px;
        border-radius: 18px;
        box-shadow: 0 18px 40px rgba(0,0,0,0.35);
    }

    .top-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        margin-bottom: 18px;
        flex-wrap: wrap;
    }

    .select-all {
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 800;
        color: white;
    }

    .btn-row {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btn-secondary {
        border: none;
        padding: 12px 16px;
        border-radius: 14px;
        cursor: pointer;
        font-weight: 900;
        background: rgba(255,255,255,0.10);
        color: white;
        transition: 0.2s ease;
        border: 1px solid rgba(255,255,255,0.12);
    }

    .btn-secondary:hover {
        background: rgba(255,255,255,0.18);
        transform: translateY(-2px);
    }

    .btn-danger {
        background: rgba(239,68,68,0.75);
    }
    .btn-danger:hover {
        background: rgba(239,68,68,1);
    }

    .cart-item {
        display: flex;
        gap: 16px;
        padding: 16px;
        border-radius: 16px;
        background: rgba(255,255,255,0.06);
        border: 1px solid rgba(255,255,255,0.08);
        margin-bottom: 14px;
        align-items: center;
        transition: 0.25s ease;
    }

    .cart-item:hover {
        transform: translateY(-3px);
        border-color: rgba(255,255,255,0.18);
        background: rgba(255,255,255,0.09);
    }

    .cart-check {
        width: 20px;
        height: 20px;
        cursor: pointer;
        accent-color: #3b82f6;
    }

    .cart-item img {
        width: 95px;
        height: 95px;
        object-fit: contain;
        border-radius: 14px;
        padding: 10px;
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.10);
    }

    .cart-details { flex: 1; }

    .cart-name {
        font-size: 16px;
        font-weight: 800;
        margin-bottom: 6px;
        color: white;
    }

    .cart-meta {
        font-size: 14px;
        color: #cbd5e1;
        margin-bottom: 8px;
    }

    .price {
        font-weight: 900;
        color: #60a5fa;
    }

    .item-total {
        font-weight: 800;
        color: #facc15;
        font-size: 14px;
        margin-bottom: 8px;
    }

    .qty-controls {
        display: flex;
        gap: 10px;
        align-items: center;
        margin-top: 8px;
    }

    .qty-btn {
        border: none;
        width: 36px;
        height: 36px;
        border-radius: 12px;
        cursor: pointer;
        font-weight: 900;
        font-size: 18px;
        color: white;
        background: rgba(255,255,255,0.12);
        border: 1px solid rgba(255,255,255,0.12);
        transition: 0.2s ease;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .qty-btn:hover {
        background: rgba(255,255,255,0.22);
        transform: translateY(-1px);
    }

    .qty-number {
        font-weight: 900;
        font-size: 15px;
        width: 34px;
        text-align: center;
        color: white;
    }

    .remove-btn {
        border: none;
        padding: 11px 16px;
        border-radius: 14px;
        cursor: pointer;
        font-weight: 900;
        background: rgba(239,68,68,0.75);
        color: white;
        transition: 0.2s ease;
        border: 1px solid rgba(255,255,255,0.10);
        text-decoration: none;
        display: inline-block;
    }

    .remove-btn:hover {
        background: rgba(239,68,68,1);
        transform: translateY(-2px);
    }

    .summary {
        margin-top: 20px;
        padding: 18px;
        border-radius: 18px;
        background: rgba(15, 23, 42, 0.85);
        border: 1px solid rgba(255,255,255,0.08);
        color: white;
        box-shadow: 0 12px 35px rgba(0,0,0,0.35);
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
        font-size: 15px;
        color: #e5e7eb;
    }

    .discount-msg {
        color: #fbbf24;
        font-weight: 900;
    }

    .checkout-btn {
        width: 100%;
        margin-top: 18px;
        padding: 14px;
        border: none;
        border-radius: 16px;
        background: rgba(59,130,246,0.80);
        color: white;
        font-size: 16px;
        font-weight: 900;
        cursor: pointer;
        transition: 0.2s ease;
        border: 1px solid rgba(255,255,255,0.12);
    }

    .checkout-btn:hover {
        background: rgba(59,130,246,1);
        transform: translateY(-2px);
    }

    .error-msg {
        background: rgba(239,68,68,0.20);
        border: 1px solid rgba(239,68,68,0.35);
        padding: 12px 14px;
        border-radius: 14px;
        color: #fecaca;
        font-weight: 800;
        margin-bottom: 16px;
    }
.shadow__btn {
    padding: 10px 20px;
    border: none;
    font-size: 15px;
    color: #fff;
    border-radius: 12px;
    letter-spacing: 2px;
    font-weight: 800;
    text-transform: uppercase;
    transition: 0.4s ease;
    cursor: pointer;
    background: rgb(22, 131, 40);
  box-shadow: 0 0 25px rgb(9, 197, 18);
}

.shadow__btn:hover {
     box-shadow: 0 0 5px rgb(8, 153, 57),
              0 0 25px rgb(8, 153, 57),
              0 0 50px rgb(8, 153, 57),
              0 0 100px rgb(8, 153, 57);
    transform: translateY(-2px);
}
    .empty-cart {
        text-align: center;
        padding: 60px 15px;
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.10);
        backdrop-filter: blur(14px);
        border-radius: 18px;
        box-shadow: 0 18px 40px rgba(0,0,0,0.35);
    }

    .empty-cart h2 {
        font-size: 22px;
        margin-bottom: 10px;
        color: white;
    }

    .empty-cart p {
        color: #cbd5e1;
    }

    @media(max-width: 650px){
        header{ padding: 14px 18px; }
        .cart-item{ flex-direction: column; align-items: flex-start; }
        .cart-item img{ width: 100%; height: 180px; }
        .remove-btn{ width: 100%; text-align:center; }
        .btn-row{ width: 100%; }
        .btn-secondary{ width: 100%; }
    }

    @keyframes fadeIn {
        0% { opacity:0; transform: translateY(18px); }
        100% { opacity:1; transform: translateY(0); }
    }
</style>
</head>
<body>

<video autoplay muted loop id="bg-video">
    <source src="assets/redbg.mp4" type="video/mp4">
</video>
<div class="overlay"></div>

<header>
    <div class="logo">CREATECH</div>
    <nav>
        <a href="store.php" class="logo" style="font-size:18px;">Store</a>
        <a href="index.php" class="logo" style="font-size:18px;">Home</a>
        <a href="logout.php" class="logo" style="font-size:18px;">Logout</a>
    </nav>
</header>

<div class="container">
    <h1>🛒 Your Cart</h1>
    <div id="cartError" class="error-msg"><?= htmlspecialchars($cart_error) ?></div>

    <?php if(!$cart): ?>
        <div class="empty-cart">
            <h2>Your cart is empty</h2>
            <p>Add products to your cart and come back here.</p>
        </div>
    <?php else: ?>
    <form method="POST" class="cart-box" id="cartForm">
        <div class="top-actions">
            <label class="select-all">
                <input type="checkbox" id="selectAll"> Select All
            </label>
            <div class="btn-row">
                <button type="submit" name="action" value="remove_selected" class="shadow__btn" style=   " background: rgb(0,140,255);
  box-shadow: 0 0 25px rgb(0,140,255);">
    Remove Selected
</button>
               <button type="submit" name="action" value="checkout_selected" class="shadow__btn">
    Checkout Selected
</button>
            </div>
        </div>

        <?php foreach($cart as $id=>$item):
            $img = $item['image'] ?? "assets/placeholder.jpg";
            if(!str_contains($img,'/')) $img="uploads/".$img;
        ?>
        <div class="cart-item" data-id="<?= $id ?>">
            <input type="checkbox" class="itemCheck" name="selected[]" value="<?= $id ?>">
            <img src="<?= htmlspecialchars($img) ?>" alt="Product Image">
            <div class="cart-details">
                <div class="cart-name"><?= htmlspecialchars($item['name']) ?></div>
                <div class="cart-meta">€<span class="price"><?= number_format($item['price'],2) ?></span> each</div>
                <div class="item-total">Total: €<span><?= number_format($item['price']*$item['quantity'],2) ?></span></div>
                <div class="qty-controls">
                    <button type="button" class="qty-btn" data-action="decrease" data-id="<?= $id ?>">-</button>
                    <div class="qty-number"><?= (int)$item['quantity'] ?></div>
                    <button type="button" class="qty-btn" data-action="increase" data-id="<?= $id ?>">+</button>
                </div>
            </div>
            <button type="button"
        class="shadow__btn"
        style="background: rgb(0,140,255);
  box-shadow: 0 0 25px rgb(0,140,255);"
        data-id="<?= $id ?>">
    Remove
</button>
        </div>
        <?php endforeach; ?>

        <div class="summary">
            <div class="summary-row">Subtotal: <span id="selectedSubtotal">€0.00</span></div>
            <?php if($birthday_discount>0): ?>
            <div class="summary-row discount-msg">Birthday Discount: <span id="selectedDiscount">- €0.00</span></div>
            <?php endif; ?>
            <div class="summary-row"><strong>Total After Discount:</strong> <strong id="selectedTotalAfter">€0.00</strong></div>
        </div>
    </form>
    <?php endif; ?>
</div>

<script>
const birthdayDiscount = <?= (float)$birthday_discount ?>;

// FORCE RELOAD ON BACK
window.addEventListener("pageshow", function(e){
    if(e.persisted || window.performance.getEntriesByType("navigation")[0]?.type==="back_forward"){
        window.location.href="cart.php";
    }
});

// SELECT ALL CHECKBOX
const selectAll = document.getElementById("selectAll");
selectAll?.addEventListener("change", ()=> {
    document.querySelectorAll(".itemCheck").forEach(c=>c.checked=selectAll.checked);
    recalcSelectedTotal();
});

// RECALC TOTALS
function recalcSelectedTotal(){
    let subtotal = 0;
    document.querySelectorAll(".itemCheck:checked").forEach(chk=>{
        const div = chk.closest(".cart-item");
        const price = parseFloat(div.querySelector(".price").innerText.replace(/[€,]/g,'')) || 0;
        const qty = parseInt(div.querySelector(".qty-number").innerText) || 1;
        subtotal += price*qty;
    });
    const discount = (birthdayDiscount/100)*subtotal;
    const total = subtotal - discount;
    document.getElementById("selectedSubtotal").innerText = '€' + subtotal.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});
    const discEl = document.getElementById("selectedDiscount");
    if(discEl) discEl.innerText = '- €' + discount.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});
    document.getElementById("selectedTotalAfter").innerText = '€' + total.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});
}
// INDIVIDUAL CHECKBOX CHANGE
document.addEventListener("change", function(e){
    if(e.target.classList.contains("itemCheck")){
        recalcSelectedTotal();

        // Auto update Select All state
        const all = document.querySelectorAll(".itemCheck");
        const checked = document.querySelectorAll(".itemCheck:checked");

        if(selectAll){
            selectAll.checked = all.length === checked.length;
        }
    }
});
// GLOBAL EVENT DELEGATION FOR + - REMOVE
document.addEventListener("click", async function(e){
    const btn = e.target.closest(".qty-btn, .remove-btn");
    if(!btn) return;

    const id = btn.dataset.id;
    const action = btn.dataset.action || "remove";

    const selected = [...document.querySelectorAll(".itemCheck:checked")].map(c=>c.value);
    const formData = new URLSearchParams();
    formData.append("id", id);
    formData.append("ajax_action", action);
    selected.forEach(s=>formData.append("selected[]", s));

    try {
        const res = await fetch("cart.php", {method:"POST", body: formData});
        const data = await res.json();

        const itemDiv = document.querySelector(`.cart-item[data-id='${id}']`);
        if(!itemDiv) return;

        if(action==="remove" || data.quantity===0){
            itemDiv.remove();
        } else {
            itemDiv.querySelector(".qty-number").innerText = data.quantity;
            itemDiv.querySelector(".item-total span").innerText = data.item_total.toFixed(2);
        }

        document.getElementById("selectedSubtotal").innerText='€'+data.subtotal.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});
        const discEl=document.getElementById("selectedDiscount");
        if(discEl) discEl.innerText='- €'+data.discount.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});
        document.getElementById("selectedTotalAfter").innerText='€'+data.total_after.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2});
    } catch(err){
        console.error("AJAX error:", err);
    }
});

// INITIAL CALC
recalcSelectedTotal();
</script>

</body>
</html>

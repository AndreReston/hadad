<?php
include("computer.php");
session_start();
// Force browser not to cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect if not logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}
// Only admin can manage products
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Access denied!");
}

// Add Product
if (isset($_POST['add_product'])) {
    $name = $_POST['name'];
    $category_id = $_POST['category_id'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock_quantity'];

    $stmt = $conn->prepare("INSERT INTO products (name, category_id, description, price, stock_quantity) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sisdi", $name, $category_id, $description, $price, $stock);
    if ($stmt->execute()) {
        echo "Product added successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
}

// List products
$result = $conn->query("SELECT products.*, categories.name AS category_name FROM products JOIN categories ON products.category_id = categories.id");
?>
<style>
/* BODY & BACKGROUND */
body {margin:0;font-family:'Segoe UI',sans-serif;color:#fff;background:#0f172a;}
#bg-video {position:fixed;top:0;left:0;width:100%;height:100%;object-fit:cover;z-index:-2;}
.video-overlay {position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:-1;}

/* HEADER */
header {background:rgba(15,23,42,0.7);padding:15px 40px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;backdrop-filter:blur(8px);z-index:2;}
header a {color:#22c55e;text-decoration:none;margin-left:15px;font-weight:bold;}
header a:hover {opacity:0.8;}
.logo {
    font-size: 24px;
    font-weight: 900;
    letter-spacing: 2px;
    background: linear-gradient(90deg, #2e7738, #2cbb43,  #00ff73);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}


/* PRODUCTS GRID */
.section {padding:60px 40px;max-width:1200px;margin:auto;}
.products {display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:30px;}
.product {background-color: rgba(255,255,255,0.2); border:1px solid rgba(255,255,255,0.2); padding:25px; text-align:center; border-radius:12px; cursor:pointer; transition: transform 0.3s; position: relative; overflow: hidden;}
.product:hover {transform: translateY(-5px);}
.product img {width:100%; height:200px; object-fit:contain; margin-bottom:20px;}
.product h4 {margin:10px 0; font-size:20px; color:#000;}
.product p {color:white; font-size:14px; margin-bottom:10px;}
.product strong {display:block; margin-bottom:20px; font-size:22px; color:#ff0000;}

/* MODAL */
.modal {display:none; position:fixed; z-index:999; left:0; top:0; width:100%; height:100%; overflow:auto; background: rgba(0,0,0,0.85);}
.modal-content {background: rgba(255,255,255,0.05); margin:50px auto; padding:20px; border-radius:12px; max-width:600px; backdrop-filter:blur(10px); position:relative;}
.close {position:absolute; top:10px; right:15px; color:white; font-size:28px; cursor:pointer; font-weight:bold;}
.modal img {width:100%; max-height:300px; object-fit:contain; margin-bottom:15px;}
.modal h3 {color:#22c55e; margin-bottom:10px;}
.modal p, .modal .stock, .modal .price {color:#fff; margin-bottom:8px;}
.modal .reviews {margin-top:15px; background: rgba(255,255,255,0.1); padding:10px; border-radius:8px; max-height:250px; overflow-y:auto;}
.modal .reviews div {margin-bottom:10px;}
.modal .reviews p {margin:2px 0; color:#e5e7eb;}
.modal textarea {width:100%; padding:8px; border-radius:6px; margin-top:5px; resize:none;}
.modal select {padding:6px; border-radius:6px; margin-top:5px;}
.modal button {margin-top:10px; padding:10px 16px; background:#22c55e; border:none; color:white; cursor:pointer; font-weight:bold; border-radius:6px;}
.modal button:hover {background:#16a34a;}
.birthday-msg {background:#fef3c7; color:#b45309; padding:10px; margin-bottom:20px; border-radius:5px; font-weight:bold; text-align:center;}
</style>
<h2>Products</h2>
<form method="POST">
    Name: <input type="text" name="name" required>
    Category: 
    <select name="category_id">
    <?php 
    $cat_result = $conn->query("SELECT * FROM categories");
    while($cat = $cat_result->fetch_assoc()) {
        echo "<option value='{$cat['id']}'>{$cat['name']}</option>";
    }
    ?>
    </select>
    Description: <input type="text" name="description">
    Price: <input type="number" step="0.01" name="price" required>
    Stock: <input type="number" name="stock_quantity" required>
    <button type="submit" name="add_product">Add Product</button>
</form>
<script>
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
    window.history.forward();
}
</script>
<ul>
<?php while($row = $result->fetch_assoc()): ?>
    <li><?php echo $row['name']; ?> (<?php echo $row['category_name']; ?>) - $<?php echo $row['price']; ?> - Stock: <?php echo $row['stock_quantity']; ?></li>
<?php endwhile; ?>
</ul>

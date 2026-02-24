<?php
session_start();
require 'computer.php';

// SECURITY: only admin
if(empty($_SESSION['user_id']) || empty($_SESSION['role']) || $_SESSION['role']!=='admin'){
    header("Location: index.php");
    exit;
}

// FETCH PRODUCT DATA
if(empty($_GET['id'])){
    header("Location: admin_dashboard.php");
    exit;
}
$id = (int)$_GET['id'];
$product_result = $conn->query("SELECT * FROM products WHERE id=$id");
if($product_result->num_rows === 0){
    header("Location: admin_dashboard.php");
    exit;
}
$product = $product_result->fetch_assoc();

// FETCH CATEGORIES
$categories_result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$categories = [];
while($row = $categories_result->fetch_assoc()) $categories[] = $row;

// HANDLE UPDATE
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])){
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock_quantity'];
    $category_id = (int)$_POST['category_id'];

    // IMAGE UPLOAD (optional)
    $file_name = $product['image_url'];
    if(!empty($_FILES['image']['name'])){
        $target_dir = "uploads/";
        if(!is_dir($target_dir)) mkdir($target_dir, 0777, true);
        $file_name = time() . "_" . basename($_FILES["image"]["name"]);
        $target_file = $target_dir . $file_name;
        if(move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)){
            $file_name = $target_file;
        }
    }

    $conn->query("UPDATE products SET name='$name', description='$description', price='$price', stock_quantity='$stock', category_id='$category_id', image_url='$file_name' WHERE id=$id");
    header("Location: admin_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Product - CREATECH</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
body{background:transparent;color:#f8fafc;padding-bottom:50px;}

/* VIDEO BACKGROUND */
#bg-video{position:fixed;top:0;left:0;width:100%;height:100%;object-fit:cover;z-index:-2;pointer-events:none;}
.video-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:-1;}

/* HEADER */
header{background:rgba(15,23,42,0.85);color:white;padding:20px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:999;backdrop-filter:blur(10px);}
header a{color:#22c55e;text-decoration:none;font-weight:bold;margin-left:15px;}
header a:hover{opacity:0.8;}
header button{background:#3b82f6;color:white;padding:8px 14px;border:none;border-radius:5px;cursor:pointer;margin-left:10px;}
header button.active{opacity:1;background:#22c55e;}

/* LOGO */
.logo{
    font-size:26px;
    font-weight:900;
    color: #ffffff;
    letter-spacing:2px;
}
.logo {
    font-size: 24px;
    font-weight: 900;
    letter-spacing: 2px;
    background: linear-gradient(90deg, #2e7738, #2cbb43,  #00ff73);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* FORM CONTAINER */
.form-container {
  max-width: 800px;
  margin: 50px auto;
  background: linear-gradient(#212121, #212121) padding-box,
              linear-gradient(145deg, transparent 35%,#e81cff, #40c9ff) border-box;
  border: 2px solid transparent;
  padding: 32px 24px;
  font-size: 14px;
  font-family: inherit;
  color: white;
  display: flex;
  flex-direction: column;
  gap: 20px;
  border-radius: 16px;
}

.form-container button:active { scale: 0.95; }

.form-container .form {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.form-container .form-group {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.form-container .form-group label {
  display: block;
  margin-bottom: 5px;
  color: #717171;
  font-weight: 600;
  font-size: 12px;
}

.form-container .form-group input,
.form-container .form-group select,
.form-container .form-group textarea {
  width: 100%;
  padding: 12px 16px;
  border-radius: 8px;
  color: #fff;
  font-family: inherit;
  background-color: transparent;
  border: 1px solid #414141;
}

.form-container .form-group textarea { height: 96px; resize: none; }

.form-container .form-group input::placeholder { opacity: 0.5; }

.form-container .form-group input:focus,
.form-container .form-group textarea:focus,
.form-container .form-group select:focus {
  outline: none;
  border-color: #e81cff;
}

.form-container .form-submit-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: inherit;
  color: #717171;
  font-weight: 600;
  width: 50%;
  background: #313131;
  border: 1px solid #414141;
  padding: 12px 16px;
  font-size: inherit;
  gap: 8px;
  margin-top: 8px;
  cursor: pointer;
  border-radius: 6px;
}

.form-container .form-submit-btn:hover {
  background-color: #fff;
  border-color: #fff;
  color: #000;
}

img#preview { width: 100px; height: 100px; object-fit: cover; border-radius: 6px; margin-bottom: 10px; }
</style>
</head>
<body>

<video autoplay muted loop playsinline id="bg-video">
    <source src="assets/adbg.mp4" type="video/mp4">
</video>
<div class="video-overlay"></div>

<header>
    <h1 class="logo">CREATECH</h1>
    <nav>
        <span>Hello, <?= htmlspecialchars($_SESSION['username']); ?></span>
        <button class="tab-btn active">Edit Product</button>
        <a href="admin_dashboard.php">Back to Dashboard</a>
        <a href="store.php">Visit Store</a>
        <a href="logout.php" style="color:#ef4444;">Logout</a>
    </nav>
</header>

<div class="form-container">
  <form class="form" method="POST" enctype="multipart/form-data">
    
    <div class="form-group">
      <label for="name">Product Name</label>
      <input type="text" id="name" name="name" placeholder="Product Name" value="<?= htmlspecialchars($product['name']); ?>" required>
    </div>

    <div class="form-group">
      <label for="category_id">Category</label>
      <select id="category_id" name="category_id" required>
        <option value="">-- Select Category --</option>
        <?php foreach($categories as $cat): ?>
        <option value="<?= $cat['id']; ?>" <?= ($product['category_id']==$cat['id'])?'selected':'' ?>><?= htmlspecialchars($cat['name']); ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="form-group">
      <label for="price">Price</label>
      <input type="number" step="0.01" id="price" name="price" placeholder="Price" value="<?= htmlspecialchars($product['price']); ?>" required>
    </div>

    <div class="form-group">
      <label for="stock_quantity">Stock Quantity</label>
      <input type="number" id="stock_quantity" name="stock_quantity" placeholder="Stock Quantity" value="<?= htmlspecialchars($product['stock_quantity']); ?>" required>
    </div>

    <div class="form-group">
      <label for="description">Description</label>
      <textarea id="description" name="description" placeholder="Product Description" required><?= htmlspecialchars($product['description']); ?></textarea>
    </div>

    <div class="form-group">
      <label for="image">Product Image</label>
      <?php if(!empty($product['image_url'])): ?>
      <img id="preview" src="<?= $product['image_url']; ?>" alt="Current Image">
      <?php endif; ?>
      <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(event)">
    </div>

    <button type="submit" name="update_product" class="form-submit-btn">Update Product</button>

  </form>
</div>

<script>
function previewImage(event){
    const reader = new FileReader();
    reader.onload = function(){
        let preview = document.getElementById('preview');
        if(!preview){
            preview = document.createElement('img');
            preview.id='preview';
            event.target.parentNode.insertBefore(preview, event.target);
        }
        preview.src = reader.result;
    }
    reader.readAsDataURL(event.target.files[0]);
}
</script>
</body>
</html>
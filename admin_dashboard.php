<?php
session_start();
require 'computer.php';

// SECURITY: only admin
if(empty($_SESSION['user_id']) || empty($_SESSION['role']) || $_SESSION['role']!=='admin'){
    header("Location: index.php");
    exit;
}

// FETCH CATEGORIES
$categories_result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
$categories = [];
while($row = $categories_result->fetch_assoc()) $categories[] = $row;

// HANDLE DELETE PRODUCT
if(isset($_GET['delete_product'])){
    $id=(int)$_GET['delete_product'];
    $conn->query("DELETE FROM products WHERE id=$id");
    header("Location: admin_dashboard.php");
    exit;
}

// HANDLE DELETE STAFF
if(isset($_GET['delete_staff'])){
    $id=(int)$_GET['delete_staff'];
    $conn->query("DELETE FROM staff WHERE id=$id");
    header("Location: admin_dashboard.php");
    exit;
}

// HANDLE POST REQUESTS
if($_SERVER['REQUEST_METHOD']==='POST'){

    // ADD PRODUCT
    if(isset($_POST['add_product'])){
        $name=$conn->real_escape_string($_POST['name']);
        $description=$conn->real_escape_string($_POST['description']);
        $price=(float)$_POST['price'];
        $stock=(int)$_POST['stock_quantity'];
        $category_id=(int)$_POST['category_id'];

        // IMAGE UPLOAD
        $file_name="";
        if(!empty($_FILES['image']['name'])){
            $target_dir="uploads/";
            if(!is_dir($target_dir)) mkdir($target_dir,0777,true);
            $file_name=time()."_".basename($_FILES["image"]["name"]);
            $target_file=$target_dir.$file_name;
            if(move_uploaded_file($_FILES["image"]["tmp_name"],$target_file)){
                $file_name=$target_file;
            }
        }

        $conn->query("INSERT INTO products (name,description,price,stock_quantity,category_id,image_url)
                      VALUES ('$name','$description','$price','$stock','$category_id','$file_name')");
        header("Location: admin_dashboard.php");
        exit;
    }

    // ADD STAFF
    if(isset($_POST['add_staff'])){
        $sname=$conn->real_escape_string($_POST['staff_name']);
        $srole=$conn->real_escape_string($_POST['staff_role']);
        $semail=$conn->real_escape_string($_POST['staff_email']);
        $sphone=$conn->real_escape_string($_POST['staff_phone']);
        $saddress=$conn->real_escape_string($_POST['staff_address']);
        $sarea=$conn->real_escape_string($_POST['staff_area']);
        $password_hash=password_hash($_POST['staff_password'],PASSWORD_DEFAULT);

        $conn->query("INSERT INTO staff (name,role,email,phone,address,delivery_area,password_hash)
                      VALUES ('$sname','$srole','$semail','$sphone','$saddress','$sarea','$password_hash')");
        header("Location: admin_dashboard.php");
        exit;
    }
}

// FETCH PRODUCTS
$products_query = "SELECT p.*, c.name as cat_name FROM products p LEFT JOIN categories c ON p.category_id=c.id";
if (!empty($_GET['product_search_input'])) {
    $search = $conn->real_escape_string($_GET['product_search_input']);
    $products_query .= " WHERE p.id='$search' OR p.name LIKE '%$search%'";
}
$products_query .= " ORDER BY p.id DESC";
$products_result = $conn->query($products_query);

// FETCH STAFF
$staff_query = "SELECT * FROM staff";
if (!empty($_GET['staff_search_input'])) {
    $search = $conn->real_escape_string($_GET['staff_search_input']);
    $staff_query .= " WHERE id='$search' OR name LIKE '%$search%' OR phone LIKE '%$search%' OR address LIKE '%$search%'";
}
$staff_query .= " ORDER BY id DESC";
$staff_result = $conn->query($staff_query);

// FETCH ATTENDANCE
$attendance_result=$conn->query("SELECT s.name,s.role,sa.time_in,sa.time_out
                                 FROM staff_attendance sa
                                 JOIN staff s ON sa.staff_id=s.id
                                 ORDER BY sa.time_in DESC");

// FETCH SALES
$sales_query = "SELECT * FROM orders";
if (!empty($_GET['sales_search_input'])) {
    $search = $conn->real_escape_string($_GET['sales_search_input']);
    $sales_query .= " WHERE id='$search' OR user_id='$search' OR product_id='$search'";
}
$sales_query .= " ORDER BY created_at DESC";
$sales_result = $conn->query($sales_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard - CREATECH</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
/* ============================= */
/* RESET */
/* ============================= */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', sans-serif;
}

body {
    background: transparent;
    color: #f8fafc;
    padding-bottom: 50px;
}

/* ============================= */
/* VIDEO BACKGROUND */
/* ============================= */
#bg-video {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: -2;
    pointer-events: none;
}

.video-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.65);
    z-index: -1;
}

/* ============================= */
/* HEADER */
/* ============================= */
header {
    background: rgba(15,23,42,0.9);
    color: white;
    padding: 15px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 999;
    backdrop-filter: blur(10px);
}

/* LOGO */
.logo-gradient {
    font-size: 26px;
    font-weight: 900;
    letter-spacing: 2px;
    background: linear-gradient(90deg, #2e7738, #2cbb43, #00ff73);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

/* NAV */
header nav {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

/* Welcome text */
header nav span {
    margin-right: 15px;
    font-weight: 500;
    color: #cbd5e1;
}

/* TAB BUTTONS */
.tab-btn {
    padding: 8px 14px;
    border-radius: 6px;
    border: none;
    background: #1e293b;
    color: white;
    cursor: pointer;
    font-size: 14px;
    transition: 0.3s ease;
}

.tab-btn:hover {
    background: #334155;
}

.tab-btn.active {
    background: #22c55e;
    color: #0f172a;
}

/* STORE & LOGOUT LINKS */
header nav a {
    text-decoration: none;
    padding: 8px 14px;
    border-radius: 6px;
    font-weight: 500;
    transition: 0.3s ease;
}

header nav a[href="store.php"] {
    background: #3b82f6;
    color: white;
}

header nav a[href="logout.php"] {
    background: #ef4444;
    color: white;
}

header nav a:hover {
    opacity: 0.85;
}

/* ============================= */
/* TAB CONTAINER */
/* ============================= */
.tab-container {
    max-width: 1100px;
    margin: 30px auto;
    background: linear-gradient(#212121, #212121) padding-box,
                linear-gradient(145deg, transparent 35%, #e81cff, #40c9ff) border-box;
    border: 2px solid transparent;
    padding: 32px 24px;
    color: white;
    display: flex;
    flex-direction: column;
    gap: 20px;
    border-radius: 16px;
    transition: all 0.3s ease;
}

.tab-container:hover {
    transform: translateY(-6px);
    border: 2px solid #13b61b;
    box-shadow: 0 8px 25px rgba(0,0,0,0.35);
}

/* ============================= */
/* FORMS */
/* ============================= */
form {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 40px;
}

.full-width {
    grid-column: span 2;
}

input, select, textarea {
    width: 100%;
    padding: 12px 16px;
    border-radius: 8px;
    border: 1px solid rgba(65, 65, 65, 0.8);
    background-color: rgba(255, 255, 255, 0.12);
    color: #fff;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

input:focus, select:focus, textarea:focus {
    border-color: #13b61b;
    background-color: rgba(255, 255, 255, 0.18);
}

textarea {
    resize: vertical;
    min-height: 96px;
}

/* ============================= */
/* BUTTONS */
/* ============================= */
button {
    padding: 12px 20px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    background: #22c55e;
    color: #0f172a;
    font-weight: 600;
    transition: 0.3s ease;
}

button:hover {
    background: #16a34a;
}

/* ============================= */
/* TABLES */
/* ============================= */
table {
    width: 100%;
    border-collapse: collapse;
    background: rgba(255,255,255,0.06);
    backdrop-filter: blur(10px);
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 30px;
}

th, td {
    border: 1px solid rgba(255,255,255,0.12);
    padding: 12px;
    text-align: left;
    font-size: 0.9rem;
}

th {
    background: rgba(255,255,255,0.10);
    color: #fff;
}

td {
    background: rgba(255,255,255,0.03);
    color: #f1f5f9;
}

td img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 4px;
}

/* ACTION LINKS */
.btn-delete {
    color: #ef4444;
    text-decoration: none;
    font-weight: bold;
}

.btn-delete:hover {
    opacity: 0.7;
}

.btn-edit {
    color: #3b82f6;
    text-decoration: none;
    font-weight: bold;
}

.btn-edit:hover {
    opacity: 0.7;
}

/* ============================= */
/* TAB CONTENT */
/* ============================= */
.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

/* ============================= */
/* FILE UPLOAD */
/* ============================= */
.file-upload {
    display: flex;
    justify-content: center;
}

.file-label {
    padding: 12px 25px;
    border: 2px solid mediumspringgreen;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    color: mediumspringgreen;
    transition: all 0.3s ease;
}

.file-label:hover {
    background: mediumspringgreen;
    color: #212121;
    box-shadow: 0 0 15px mediumspringgreen;
}

/* ============================= */
/* RESPONSIVE */
/* ============================= */
@media (max-width: 900px) {

    header {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
        padding: 20px;
    }

    header nav {
        width: 100%;
        justify-content: flex-start;
    }

    form {
        grid-template-columns: 1fr;
    }

    .full-width {
        grid-column: span 1;
    }

    table {
        font-size: 0.8rem;
    }
}
.upload-box {
    position: relative;
    border: 2px dashed #22c55e;
    border-radius: 15px;
    padding: 25px;
    text-align: center;
    cursor: pointer;
    transition: 0.3s ease;
    background: rgba(255,255,255,0.05);
}

.upload-box:hover {
    background: rgba(34,197,94,0.08);
    border-color: #16a34a;
}

.upload-box.dragover {
    background: rgba(34,197,94,0.15);
    border-color: #16a34a;
}

.upload-preview {
    width: 120px;
    height: 120px;
    object-fit: contain;
    margin-bottom: 10px;
    transition: 0.3s ease;
}

.upload-text {
    font-size: 14px;
    color: #cbd5e1;
}
select {
    width: 100%;
    padding: 12px 16px;
    border-radius: 8px;
    border: 1px solid rgba(65, 65, 65, 0.8);
    background-color: rgba(255, 255, 255, 0.12);
    color: #ffffff;       /* Selected text color */
    font-size: 0.95rem;
    transition: all 0.3s ease;
    appearance: none;     /* Removes default arrow styling for better consistency */
}

/* Options inside select */
select option {
    color: #000000;       /* Text color of options */
    background-color: #ffffff; /* Background of options */
}

/* On focus / hover for better visibility */
select:focus {
    border-color: #13b61b;
    background-color: rgba(255, 255, 255, 0.18);
}
</style>
</head>
<body>

<video autoplay muted loop playsinline id="bg-video">
    <source src="assets/adbg.mp4" type="video/mp4">
</video>
<div class="video-overlay"></div>

<header>
    <h1 class="logo-gradient">CREATECH</h1>
    <nav>
        <span>Hello, <?= htmlspecialchars($_SESSION['username']); ?></span>
        <button class="tab-btn active" data-tab="products">Products</button>
        <button class="tab-btn" data-tab="staff">Staff</button>
        <button class="tab-btn" data-tab="attendance">Attendance</button>
        <button class="tab-btn" data-tab="sales">Sales</button>
        <a href="store.php">Visit Store</a>
        <a href="logout.php" style="color:#ffffff;">Logout</a>
    </nav>
</header>

<?php
// FUNCTION TO WRAP CONTENT IN TAB-CONTAINER
function wrap_container($content){ echo '<div class="tab-container">'.$content.'</div>'; }
?>

<!-- PRODUCTS -->
<section id="products" class="tab-content active">
<?php ob_start(); ?>
<h2>Add Product</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="text" name="name" placeholder="Product Name" required>
    <select name="category_id" required>
        <option value="">-- Select Category --</option>
        <?php foreach($categories as $cat): ?>
        <option value="<?= $cat['id']; ?>"><?= htmlspecialchars($cat['name']); ?></option>
        <?php endforeach; ?>
    </select>
    <input type="number" step="0.01" name="price" placeholder="Price" required>
    <input type="number" name="stock_quantity" placeholder="Stock Quantity" required>
    <textarea name="description" placeholder="Product Description" required></textarea>
    <div class="file-upload full-width">

<div class="upload-box full-width" id="uploadBox">
    <img src="assets/product_img.png" id="previewImage" class="upload-preview">
    <p class="upload-text">Drag & Drop Image Here<br>or Click Image</p>
    <input type="file" id="image" name="image" accept="image/*" hidden>
</div>
</div>
    <button type="submit" name="add_product" class="button">Upload Product</button>
</form>

<form method="GET" style="margin-top:20px;">
    <input type="text" name="product_search_input" placeholder="Search by ID or Name" value="<?= htmlspecialchars($_GET['product_search_input'] ?? ''); ?>">
    <button type="submit" class="button">Search</button>
</form>

<table>
<thead><tr><th>Image</th><th>Name</th><th>Category</th><th>Stock & Price</th><th>Actions</th></tr></thead>
<tbody>
<?php while($product=$products_result->fetch_assoc()): ?>
<tr>
<td><img src="<?= $product['image_url']?:'assets/placeholder.jpg'; ?>"></td>
<td><?= htmlspecialchars($product['name']); ?></td>
<td><?= htmlspecialchars($product['cat_name']?:'Uncategorized'); ?></td>
<td>$<?= number_format($product['price'],2); ?><br>In Stock: <?= $product['stock_quantity']; ?></td>
<td>
    <a href="edit_product.php?id=<?= $product['id']; ?>" class="btn-edit">Edit</a> |
    <a href="?delete_product=<?= $product['id']; ?>" class="btn-delete" onclick="return confirm('Delete this product?')">Delete</a>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
<?php wrap_container(ob_get_clean()); ?>
</section>

<!-- STAFF -->
<section id="staff" class="tab-content">
<?php ob_start(); ?>
<h2>Add Staff</h2>
<form method="POST">
    <input type="text" name="staff_name" placeholder="Full Name" required>
    <select name="staff_role" required>
        <option value="">-- Select Role --</option>
        <option value="Delivery">Delivery</option>
        <option value="Support">Support</option>
    </select>
    <input type="email" name="staff_email" placeholder="Email">
    <input type="text" name="staff_phone" placeholder="Phone">
    <input type="text" name="staff_address" placeholder="Address">
    <input type="text" name="staff_area" placeholder="Delivery Area">
    <input type="password" name="staff_password" placeholder="Password" required>
    <button type="submit" name="add_staff" class="button full-width">
    <p>Add Staff</p>
</button>
</form>

<form method="GET" style="margin-top:20px;">
    <input type="text" name="staff_search_input" placeholder="Search by ID, Name, Phone or Address" value="<?= htmlspecialchars($_GET['staff_search_input'] ?? ''); ?>">
    <button type="submit" class="button">
    <p>Search</p>
</button>
</form>

<table>
<thead><tr><th>Name</th><th>Role</th><th>Email</th><th>Phone</th><th>Address</th><th>Area</th><th>Actions</th></tr></thead>
<tbody>
<?php while($staff=$staff_result->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($staff['name']); ?></td>
<td><?= htmlspecialchars($staff['role']); ?></td>
<td><?= htmlspecialchars($staff['email']); ?></td>
<td><?= htmlspecialchars($staff['phone']); ?></td>
<td><?= htmlspecialchars($staff['address']); ?></td>
<td><?= htmlspecialchars($staff['delivery_area']); ?></td>
<td>
    <a href="edit_staff.php?id=<?= $staff['id']; ?>" class="btn-edit">Edit</a> |
    <a href="?delete_staff=<?= $staff['id']; ?>" class="btn-delete" onclick="return confirm('Delete this staff?')">Delete</a>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
<?php wrap_container(ob_get_clean()); ?>
</section>

<!-- ATTENDANCE -->
<section id="attendance" class="tab-content">
<?php ob_start(); ?>
<h2>Staff Attendance</h2>
<table>
<thead><tr><th>Staff</th><th>Role</th><th>Time In</th><th>Time Out</th></tr></thead>
<tbody>
<?php while($row=$attendance_result->fetch_assoc()): ?>
<tr>
<td><?= htmlspecialchars($row['name']); ?></td>
<td><?= htmlspecialchars($row['role']); ?></td>
<td><?= $row['time_in']; ?></td>
<td><?= $row['time_out']; ?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
<?php wrap_container(ob_get_clean()); ?>
</section>

<!-- SALES -->
<section id="sales" class="tab-content">
<?php ob_start(); ?>
<h2>Sales Tracking</h2>
<form method="GET" style="margin-top:20px;">
    <input type="text" name="sales_search_input" placeholder="Search by Order ID, User ID, Product ID" value="<?= htmlspecialchars($_GET['sales_search_input'] ?? ''); ?>">
    <button type="submit" class="button">
    <p>Search</p>
</button>
</form>

<table>
<thead>
<tr>
<th>Order ID</th>
<th>User ID</th>
<th>Product ID</th>
<th>Price</th>
<th>Payment Method</th>
<th>Status</th>
<th>Date</th>
<th>Delivery</th>
</tr>
</thead>
<tbody>
<?php while($sale = $sales_result->fetch_assoc()): ?>
<tr>
<td><?= $sale['id']; ?></td>
<td><?= $sale['user_id']; ?></td>
<td><?= $sale['product_id']; ?></td>
<td>$<?= number_format($sale['price'], 2); ?></td>
<td><?= htmlspecialchars($sale['payment_method']); ?></td>
<td><?= htmlspecialchars($sale['status']); ?></td>
<td><?= $sale['created_at']; ?></td>
<td><?= htmlspecialchars($sale['delivery_street'] . ', ' . $sale['delivery_city'] . ', ' . $sale['delivery_province'] . ' ' . $sale['delivery_zip']); ?></td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
<?php wrap_container(ob_get_clean()); ?>
</section>

<script>
// TAB SWITCHING
const tabs=document.querySelectorAll('.tab-btn');
const contents=document.querySelectorAll('.tab-content');
tabs.forEach(btn=>{
    btn.addEventListener('click',()=>{
        tabs.forEach(b=>b.classList.remove('active'));
        contents.forEach(c=>c.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(btn.dataset.tab).classList.add('active');
    });
});
const uploadBox = document.getElementById("uploadBox");
const fileInput = document.getElementById("image");
const previewImage = document.getElementById("previewImage");

/* Click to open file dialog */
uploadBox.addEventListener("click", () => fileInput.click());

/* File selected */
fileInput.addEventListener("change", function () {
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = e => previewImage.src = e.target.result;
        reader.readAsDataURL(this.files[0]);
    }
});

/* Drag & Drop */
uploadBox.addEventListener("dragover", e => {
    e.preventDefault();
    uploadBox.classList.add("dragover");
});

uploadBox.addEventListener("dragleave", () => {
    uploadBox.classList.remove("dragover");
});

uploadBox.addEventListener("drop", e => {
    e.preventDefault();
    uploadBox.classList.remove("dragover");

    const files = e.dataTransfer.files;
    if (files.length > 0) {
        fileInput.files = files;

        const reader = new FileReader();
        reader.onload = ev => previewImage.src = ev.target.result;
        reader.readAsDataURL(files[0]);
    }
});
</script>
</body>
</html>

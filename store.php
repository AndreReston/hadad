<?php
session_start();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// Redirect if not logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

// User info
$user_role = $_SESSION['role'] ?? 'user';
$username = $_SESSION['username'] ?? 'User';

include("computer.php");

// Fetch all products
$products_result = $conn->query("SELECT * FROM products ORDER BY id DESC");
$products_array = [];
while($row = $products_result->fetch_assoc()){
    $products_array[] = $row;
}

// Pagination setup
$per_page = 12;
$page = isset($_GET['page']) ? max(1,(int)$_GET['page']) : 1;
$total_products = count($products_array);
$total_pages = ceil($total_products / $per_page);
$start_index = ($page - 1) * $per_page;
$products_page = array_slice($products_array, $start_index, $per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CREATECH Store</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body {margin:0;font-family:'Segoe UI',sans-serif;color:#fff;background:#0f172a;}
#bg-video {position:fixed;top:0;left:0;width:100%;height:100%;object-fit:cover;z-index:-2;filter:brightness(0.35);}
.video-overlay {position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.55);z-index:-1;}

header {
    background: rgba(15,23,42,0.75);
    padding: 15px 30px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    backdrop-filter: blur(10px);
    z-index: 999;
}

.logo {
    font-size: 24px;
    font-weight: 900;
    letter-spacing: 2px;
    background: linear-gradient(90deg, #2e7738, #2cbb43, #00ff73);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.center-nav {position: absolute; left: 50%; transform: translateX(-50%);}
.center-nav nav {display: flex; gap: 20px;}
nav a {color: #22c55e; text-decoration: none; font-weight: bold;}
nav a:hover {opacity:0.8;}

.section{
    max-width:1200px;
    margin:0 auto;
    padding:40px 20px 80px;
    display:flex;
    justify-content:center;
    flex-wrap:wrap;
    gap:25px;
}

.search-container {
    position: absolute;
    right: 30px;
    top: 50%;
    transform: translateY(-50%);
    display: flex;
    align-items: center;
    gap: 8px;
    color: #22c55e;
    z-index: 1000;
}

.search-icon {
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  flex-shrink: 0;
  z-index: 1001;
}

.search-icon svg {width: 60%; height: 60%; stroke: currentColor; fill: none;}

.search-input {
  width: 0;
  height: 40px;
  padding: 0 12px;
  border-radius: 50px;
  border: none;
  outline: none;
  background-color: #0f172a;
  color: #ffffff;
  box-shadow: 1.5px 1.5px 3px #0e0e0e,
              -1.5px -1.5px 3px rgb(95 94 94 / 25%),
              inset 0 0 0 #0e0e0e,
              inset 0 -0 0 #5f5e5e;
  transition: width 0.3s ease, margin-left 0.3s ease;
  cursor: pointer;
}

.search-container.active .search-input,
.search-input:focus {
  width: 200px;
  margin-left: 8px;
  cursor: text;
}

.product.form-container {
    width: 300px;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    padding: 32px 24px;
    background: linear-gradient(#212121, #212121) padding-box,
                linear-gradient(145deg, transparent 35%,#e81cff, #1ba82e) border-box;
    border: 2px solid transparent;
    border-radius: 16px;
    gap: 20px;
    box-sizing: border-box;
    color: #fff;
    cursor: pointer;
    transition: transform 0.25s, box-shadow 0.25s, border 0.25s;
    overflow:hidden;
    backdrop-filter:blur(10px);
    text-decoration: none;
}

.product.form-container:hover{
    transform: translateY(-6px);
    border: 2px solid #13b61b;
    box-shadow:0 8px 25px rgba(0,0,0,0.35);
}

.product.form-container img {
    width:100%;
    height:190px;
    object-fit:contain;
    margin-bottom:15px;
    border-radius:8px;
}

.product.form-container h4 {
    margin:10px 0 8px;
    font-size:18px;
    color:#22c55e;
    font-weight:800;
}

.product.form-container p {
    color:#e5e7eb;
    font-size:13px;
    margin-bottom:12px;
    line-height:1.4;
    min-height:38px;
}

.product.form-container strong {
    font-size:20px;
    color:#ffcc00;
    font-weight:900;
}

.pagination {
    display:flex;
    justify-content:center;
    flex-wrap:wrap;
    gap:8px;
    margin:20px 0;
}

.pagination button {
    padding:8px 12px;
    border:none;
    border-radius:6px;
    cursor:pointer;
    background:#1e293b;
    color:#fff;
    font-weight:600;
    transition:0.3s;
}

.pagination button.active {
    background:#22c55e;
    color:#0f172a;
}

.pagination button:hover:not(.active) {
    background:#13b61b;
    color:#fff;
}

@media(max-width:600px){
    header{padding:12px 15px;}
    .logo{font-size:22px;}
    .product.form-container{width:90%;}
}
</style>
</head>
<body>

<video autoplay muted loop id="bg-video">
    <source src="assets/redbg.mp4" type="video/mp4">
</video>
<div class="video-overlay"></div>

<header>
    <div class="logo">CREATECH</div>

    <div class="center-nav">
        <?php if($user_role !== 'admin'): ?>
        <nav>
            <a href="cart.php">Cart 🛒</a>
            <a href="create_ticket.php">Support</a>
            <a href="purchases.php">Purchases</a>
            <a href="logout.php">Logout</a>
        </nav>
        <?php endif; ?>
    </div>

    <div class="search-container">
        <div class="search-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                <title>Search</title>
                <path d="M221.09 64a157.09 157.09 0 10157.09 157.09A157.1 157.1 0 00221.09 64z"
                      fill="none" stroke="currentColor" stroke-miterlimit="10" stroke-width="32"/>
                <path fill="none" stroke="currentColor" stroke-linecap="round"
                      stroke-miterlimit="10" stroke-width="32" d="M338.29 338.29L448 448"/>
            </svg>
        </div>
        <input type="text" class="search-input" placeholder="Search by name or category...">
    </div>
</header>

<!-- Pagination Top -->
<div class="pagination" id="pagination-top"></div>

<!-- Products Section -->
<div class="section" id="product-section"></div>

<!-- Pagination Bottom -->
<div class="pagination" id="pagination-bottom"></div>

<script>
// Reload if cached
window.onpageshow = function(event) {
    if (event.persisted || window.performance && window.performance.navigation.type === 2) {
        window.location.reload();
    }
};

const searchContainer = document.querySelector('.search-container');
const searchInput = document.querySelector('.search-input');
const searchIcon = document.querySelector('.search-icon');

const allProductsRaw = <?= json_encode($products_array) ?>;
const perPage = <?= $per_page ?>;
let currentPage = <?= $page ?>;

const productSection = document.getElementById('product-section');
const paginationTop = document.getElementById('pagination-top');
const paginationBottom = document.getElementById('pagination-bottom');

function getPaginatedProducts(products, page){
    const start = (page-1) * perPage;
    return products.slice(start, start+perPage);
}

function renderProducts(products){
    productSection.innerHTML = '';
    if(products.length===0){
        productSection.innerHTML=`<p style="color:#fff;font-size:18px;">No products found.</p>`;
        paginationTop.innerHTML='';
        paginationBottom.innerHTML='';
        return;
    }
    products.forEach(p=>{
        const a=document.createElement('a');
        a.href=`product.php?id=${p.id}`;
        a.className='product form-container';
        a.innerHTML=`<img src="${p.image_url||'assets/placeholder.jpg'}" alt="Product">
            <h4>${p.name}</h4>
            <p>${p.description.substring(0,70)}...</p>
            <strong>€${Number(p.price).toFixed(2)}</strong>`;
        productSection.appendChild(a);
    });
    renderPagination(allProductsRaw);
}

function renderPagination(products){
    const totalPages = Math.ceil(products.length / perPage);
    const build = (container) => {
        container.innerHTML='';
        for(let i=1;i<=totalPages;i++){
            const btn = document.createElement('button');
            btn.textContent = i;
            if(i===currentPage) btn.classList.add('active');
            btn.addEventListener('click', ()=> {
                currentPage=i;
                renderProducts(getPaginatedProducts(products,i));
            });
            container.appendChild(btn);
        }
    }
    build(paginationTop);
    build(paginationBottom);
}

// Initial render
renderProducts(getPaginatedProducts(allProductsRaw,currentPage));

// Toggle search input
searchIcon.addEventListener('click', () => {
    if(searchContainer.classList.contains('active')){
        searchContainer.classList.remove('active');
        searchInput.value='';
        searchInput.blur();
        currentPage=1;
        renderProducts(getPaginatedProducts(allProductsRaw,currentPage));
    } else {
        searchContainer.classList.add('active');
        searchInput.focus();
    }
});

// Live search
searchInput.addEventListener('input',()=>{
    const query = searchInput.value.trim().toLowerCase();
    let filtered = allProductsRaw.filter(p=> p.name.toLowerCase().includes(query) || p.description.toLowerCase().includes(query));
    currentPage=1;
    renderProducts(getPaginatedProducts(filtered,currentPage));
});
</script>

</body>
</html>
<?php
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

include("computer.php");

$user_id = $_SESSION['user_id'] ?? 0;
$user_role = $_SESSION['role'] ?? 'user';

// Validate product id
$product_id = (int)($_GET['id'] ?? 0);
if(!$product_id){
    header("Location: store.php");
    exit;
}

// Handle review submission
$review_message = "";
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rating'], $_POST['review_text'])){
    $rating = (int)$_POST['rating'];
    $review = trim($_POST['review_text']);

    if($rating >= 1 && $rating <= 5){

        // Optional: stop empty reviews
        if($review === ""){
            $review_message = "Review cannot be empty.";
        } else {
            $stmt = $conn->prepare("INSERT INTO product_reviews (product_id,user_id,rating,review) VALUES (?,?,?,?)");
            $stmt->bind_param("iiis",$product_id,$user_id,$rating,$review);
            $stmt->execute();
            $stmt->close();
            $review_message = "Review submitted successfully!";
        }

    } else {
        $review_message = "Rating must be between 1 and 5";
    }
}

// Fetch product
$stmt = $conn->prepare("SELECT * FROM products WHERE id=?");
$stmt->bind_param("i",$product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if(!$product){
    echo "Product not found";
    exit;
}

// Fetch reviews
$reviews = [];
$stmt = $conn->prepare("
    SELECT r.*, u.username
    FROM product_reviews r
    JOIN users u ON r.user_id=u.id
    WHERE r.product_id=?
    ORDER BY r.created_at DESC
");
$stmt->bind_param("i",$product_id);
$stmt->execute();
$result_r = $stmt->get_result();
while($row = $result_r->fetch_assoc()) $reviews[] = $row;
$stmt->close();


// ==========================
// ⭐ Rating Summary (Average + Bars)
// ==========================
$total_reviews = count($reviews);
$rating_counts = [1=>0,2=>0,3=>0,4=>0,5=>0];
$rating_sum = 0;

foreach($reviews as $r){
    $rating_counts[(int)$r['rating']]++;
    $rating_sum += (int)$r['rating'];
}

$avg_rating = $total_reviews > 0 ? round($rating_sum / $total_reviews, 1) : 0;

function percent($count, $total){
    if($total <= 0) return 0;
    return round(($count / $total) * 100);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($product['name']) ?> | CREATECH</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
:root{--neon-green:#22c55e;--dark-bg:#0f172a;--accent:#3b82f6;}
body{margin:0;font-family:'Segoe UI',sans-serif;background:var(--dark-bg);color:#f8fafc;}
#bg-video{position:fixed;top:0;left:0;width:100%;height:100%;object-fit:cover;z-index:-2;filter:brightness(0.35);pointer-events:none;}
.video-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:-1;}

header{background:rgba(15,23,42,0.85);color:white;padding:20px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:999;backdrop-filter:blur(10px);}
header a{color:var(--neon-green);text-decoration:none;font-weight:bold;margin-left:15px;}
header a:hover{opacity:0.8;}
.logo {
    font-size: 24px;
    font-weight: 900;
    letter-spacing: 2px;
    background: linear-gradient(90deg, #2e7738, #2cbb43,  #00ff73);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

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

img{width:100%;max-height:300px;object-fit:contain;border-radius:12px;margin-bottom:20px;}
h1{color:var(--neon-green);margin-bottom:10px;}
p, .stock, .price{margin-bottom:12px;}
.stock{color:#f59e0b;font-weight:bold;}
.price{color:#ffcc00;font-size:22px;font-weight:bold;}
textarea{width:100%;padding:10px;border-radius:8px;margin-top:5px;margin-bottom:10px;background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.12);color:white;resize:vertical;}
/* From Uiverse.io by mrhyddenn */ 
.shadow__btn {
  padding: 10px 20px;
  border: none;
  font-size: 17px;
  color: #fff;
  border-radius: 7px;
  letter-spacing: 4px;
  font-weight: 700;
  text-transform: uppercase;
  transition: 0.5s;
  transition-property: box-shadow;
}

.shadow__btn {
  background: rgb(22, 131, 40);
  box-shadow: 0 0 25px rgb(9, 197, 18);
}

.shadow__btn:hover {
  box-shadow: 0 0 5px rgb(8, 153, 57),
              0 0 25px rgb(8, 153, 57),
              0 0 50px rgb(8, 153, 57),
              0 0 100px rgb(8, 153, 57);
}


.review{background:rgba(255,255,255,0.08);padding:12px;border-radius:10px;margin-bottom:12px;}
.review strong{color:var(--accent);}
.error-msg{color:#fbbf24;font-weight:bold;margin-bottom:12px;}
.add-cart-btn{  padding: 10px 20px;
width: 700px;
  border: none;
  font-size: 17px;
  color: #fff;
  border-radius: 7px;
  letter-spacing: 4px;
  font-weight: 700;
  text-transform: uppercase;
  transition: 0.5s;
  transition-property: box-shadow;}
.add-cart-btn
    {
  background: rgb(0,140,255);
  box-shadow: 0 0 25px rgb(0,140,255);
}
add-cart-btn:hover{box-shadow: 0 0 5px rgb(0,140,255),
              0 0 25px rgb(0,140,255),
              0 0 50px rgb(0,140,255),
              0 0 100px rgb(0,140,255);
}

/* ==========================
   ⭐ NEW ANIMATED STAR RATING
========================== */
.rating-wrap{
    margin-top: 10px;
    margin-bottom: 14px;
    padding: 14px;
    border-radius: 14px;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.10);
}
.rating-title{
    font-weight: 900;
    color: #e5e7eb;
    margin-bottom: 10px;
    letter-spacing: 0.3px;
}
.star-rating{
    display: inline-flex;
    flex-direction: row-reverse;
    gap: 10px;
    user-select: none;
}
.star-rating input{
    display:none;
}
.star-rating label{
    font-size: 34px;
    cursor: pointer;
    color: rgba(255,255,255,0.18);
    transition: transform 0.18s ease, color 0.18s ease, text-shadow 0.18s ease;
}
.star-rating label:hover,
.star-rating label:hover ~ label{
    color: #facc15;
    text-shadow: 0 0 14px rgba(250,204,21,0.45);
    transform: translateY(-3px) scale(1.08);
}
.star-rating input:checked ~ label{
    color: #facc15;
    text-shadow: 0 0 18px rgba(250,204,21,0.55);
    animation: popStar 0.25s ease;
}
.rating-text{
    margin-top: 10px;
    font-size: 13px;
    font-weight: 800;
    color: #94a3b8;
}
@keyframes popStar{
    0%{transform: scale(0.8);}
    60%{transform: scale(1.15);}
    100%{transform: scale(1);}
}

/* ==========================
   ⭐ RATING SUMMARY (AVG + BARS)
========================== */
.rating-summary{
    margin-top: 22px;
    margin-bottom: 10px;
    padding: 16px;
    border-radius: 16px;
    background: rgba(255,255,255,0.06);
    border: 1px solid rgba(255,255,255,0.10);
    display: grid;
    grid-template-columns: 220px 1fr;
    gap: 18px;
}

.avg-box{
    text-align:center;
    padding: 12px;
    border-radius: 14px;
    background: rgba(0,0,0,0.25);
    border: 1px solid rgba(255,255,255,0.08);
}

.avg-num{
    font-size: 46px;
    font-weight: 1000;
    color: #facc15;
    line-height: 1;
}

.avg-stars{
    font-size: 18px;
    letter-spacing: 2px;
    margin-top: 6px;
    color: #facc15;
}

.avg-total{
    margin-top: 8px;
    font-size: 13px;
    color: #94a3b8;
    font-weight: 800;
}

.bars{
    display:flex;
    flex-direction:column;
    gap: 8px;
    padding-top: 4px;
}

.bar-row{
    display:grid;
    grid-template-columns: 52px 1fr 45px;
    gap: 10px;
    align-items:center;
    font-weight: 800;
    color: #cbd5e1;
    font-size: 13px;
}

.bar{
    height: 10px;
    border-radius: 999px;
    background: rgba(255,255,255,0.10);
    overflow:hidden;
    border: 1px solid rgba(255,255,255,0.08);
}

.fill{
    height:100%;
    width:0%;
    border-radius: 999px;
    background: linear-gradient(90deg, #facc15, #fb923c);
    animation: fillBar 0.8s ease forwards;
}

@keyframes fillBar{
    from{width:0%;}
    to{width:var(--w);}
}

@media(max-width: 720px){
    .rating-summary{
        grid-template-columns: 1fr;
    }
}
</style>
</head>

<body>
<video autoplay muted loop id="bg-video">
    <source src="assets/redbg.mp4" type="video/mp4">
</video>
<div class="video-overlay"></div>

<header>
    <h1 class="logo">CREATECH</h1>
    <nav>
        <a href="store.php" class="logo" style="font-size:18px;">← Back to Store</a>
        <a href="cart.php" class="logo" style="font-size:18px;">Cart 🛒</a>
        <a href="logout.php" class="logo" style="font-size:18px;">Logout</a>
    </nav>
</header>

<div class="container">

    <?php if($review_message): ?>
        <div class="error-msg"><?= htmlspecialchars($review_message); ?></div>
    <?php endif; ?>

    <img src="<?= htmlspecialchars($product['image_url'] ?: 'assets/placeholder.jpg') ?>" alt="Product">

    <h1><?= htmlspecialchars($product['name']) ?></h1>
    <p><?= htmlspecialchars($product['description']) ?></p>
    <p class="price">₱<?= number_format($product['price'],2) ?></p>
    <p class="stock"><?= $product['stock_quantity']>0 ? "In Stock ({$product['stock_quantity']})" : "Out of Stock" ?></p>

    <?php if($user_role !== 'admin'): ?>
        <form method="POST" style="margin-top:15px;">

            <!-- ⭐ NEW STAR RATING -->
            <div class="rating-wrap">
                <div class="rating-title">Rate this product</div>

                <div class="star-rating" id="starRating">
                    <?php for($i=5;$i>=1;$i--): ?>
                        <input type="radio" id="star<?= $i ?>" name="rating" value="<?= $i ?>" required>
                        <label for="star<?= $i ?>" title="<?= $i ?> stars">★</label>
                    <?php endfor; ?>
                </div>

                <div class="rating-text" id="ratingText">Select a rating</div>
            </div>

            <textarea name="review_text" rows="3" placeholder="Write your review..." required></textarea>
            <button type="submit" class="shadow__btn">Submit Review</button>
        </form>

        <?php if($product['stock_quantity']>0): ?>
            <form method="POST" action="add_to_cart.php" style="margin-top:10px;">
                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                <label>Quantity:
                    <input type="number" name="quantity" value="1" min="1" max="<?= $product['stock_quantity'] ?>" style="width:70px;margin-left:5px;">
                </label>
                <button type="submit" class="add-cart-btn">Add to Cart</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>


    <!-- ⭐ RATING SUMMARY -->
    <div class="rating-summary">
        <div class="avg-box">
            <div class="avg-num"><?= number_format($avg_rating, 1) ?></div>
            <div class="avg-stars">
                <?php
                $rounded = (int)round($avg_rating);
                echo str_repeat("★", $rounded) . str_repeat("☆", 5 - $rounded);
                ?>
            </div>
            <div class="avg-total"><?= $total_reviews ?> review(s)</div>
        </div>

        <div class="bars">
            <?php for($s=5;$s>=1;$s--): 
                $p = percent($rating_counts[$s], $total_reviews);
            ?>
                <div class="bar-row">
                    <div><?= $s ?>★</div>
                    <div class="bar">
                        <div class="fill" style="--w: <?= $p ?>%;"></div>
                    </div>
                    <div><?= $p ?>%</div>
                </div>
            <?php endfor; ?>
        </div>
    </div>


    <h3 style="margin-top:25px;">Reviews:</h3>

    <?php if(!$reviews): ?>
        <p>No reviews yet.</p>
    <?php endif; ?>

    <?php foreach($reviews as $r): ?>
        <div class="review">
            <strong><?= htmlspecialchars($r['username']) ?></strong>
            - <?= str_repeat("★", (int)$r['rating']) . str_repeat("☆", 5 - (int)$r['rating']) ?>
            <p><?= htmlspecialchars($r['review']) ?></p>
            <small><?= htmlspecialchars($r['created_at']) ?></small>
        </div>
    <?php endforeach; ?>

</div>


<script>
// prevent resubmit
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
}

// ⭐ Rating text update
const ratingText = document.getElementById("ratingText");
const ratingInputs = document.querySelectorAll("#starRating input");

const ratingWords = {
    1: "⭐ 1 - Bad",
    2: "⭐⭐ 2 - Not Good",
    3: "⭐⭐⭐ 3 - Okay",
    4: "⭐⭐⭐⭐ 4 - Good",
    5: "⭐⭐⭐⭐⭐ 5 - Excellent"
};

ratingInputs.forEach(input => {
    input.addEventListener("change", () => {
        ratingText.innerText = ratingWords[input.value] || "Rating selected";
    });
});

// back cache reload fix
window.addEventListener("pageshow", function(event) {
    if (event.persisted) {
        window.location.reload();
    }
});
</script>

</body>
</html>

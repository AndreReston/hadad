<?php
session_start();
require "computer.php";

// ✅ Prevent caching (VERY IMPORTANT for back button)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");

// ✅ must be logged in
if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? "User";

$msg = "";

// ---------------------------
// Submit ticket
// ---------------------------
if($_SERVER["REQUEST_METHOD"] === "POST"){
    $issue = trim($_POST["issue"] ?? "");

    if(empty($issue)){
        $msg = "Please describe your issue.";
    } else {
        $stmt = $conn->prepare("INSERT INTO support_tickets (user_id, user_name, issue, status) VALUES (?, ?, ?, 'Pending')");
        $stmt->bind_param("iss", $user_id, $username, $issue);

        if($stmt->execute()){
            $stmt->close();
            header("Location: create_ticket.php?success=1");
            exit;
        } else {
            $msg = "❌ Error: " . $stmt->error;
            $stmt->close();
        }
    }
}

// Show success message if redirected
if(isset($_GET['success'])){
    $msg = "✅ Ticket submitted successfully! Support will reply soon.";
}

// ---------------------------
// Fetch user tickets
// ---------------------------
$tickets = [];
$stmt = $conn->prepare("SELECT * FROM support_tickets WHERE user_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
while($row = $res->fetch_assoc()){
    $tickets[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Support Ticket</title>

<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{
    font-family:'Segoe UI',sans-serif;
    min-height:100vh;
    color:#e5e7eb;
    background:#0f172a;
    overflow-x:hidden;
}

#bg-video{
    position:fixed;top:0;left:0;
    width:100%;height:100%;
    object-fit:cover;
    z-index:-2;
    filter:brightness(0.35);
}
.overlay{
    position:fixed;top:0;left:0;
    width:100%;height:100%;
    background:rgba(15,23,42,0.65);
    z-index:-1;
}

header{
    background-color: rgba(15,23,42,0.55);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(255,255,255,0.08);
    padding: 16px 40px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    position:sticky;
    top:0;
    z-index:10;
}

.logo {
    font-size: 24px;
    font-weight: 900;
    letter-spacing: 2px;
    background: linear-gradient(90deg, #2e7738, #2cbb43,  #00ff73);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}



nav a{
    color:#94a3b8;
    text-decoration:none;
    margin-left:18px;
    font-weight:600;
}
nav a:hover{color:white;}

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

.card{
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.10);
    backdrop-filter: blur(14px);
    padding: 20px;
    border-radius: 18px;
    box-shadow: 0 18px 40px rgba(0,0,0,0.35);
    margin-bottom:30px;
}

h1{
    font-size:26px;
    margin-bottom:10px;
    font-weight:900;
    color:white;
}

p{
    color:#cbd5e1;
    margin-bottom:14px;
}

textarea{
    width:100%;
    padding:12px;
    border-radius:14px;
    border:1px solid rgba(255,255,255,0.15);
    background:rgba(255,255,255,0.08);
    color:white;
    outline:none;
    resize:none;
}

button{
    width:100%;
    margin-top:12px;
    padding:14px;
    border:none;
    border-radius:16px;
    background: rgba(34,197,94,0.85);
    color:white;
    font-size:16px;
    font-weight:900;
    cursor:pointer;
    transition:0.2s ease;
}
button:hover{
    background: rgba(34,197,94,1);
    transform: translateY(-2px);
}

.msg{
    margin-bottom:12px;
    padding:12px;
    border-radius:14px;
    font-weight:900;
    background:rgba(59,130,246,0.18);
    border:1px solid rgba(59,130,246,0.25);
}

.ticket{
    margin-top:14px;
    padding:14px;
    border-radius:16px;
    background:rgba(255,255,255,0.06);
    border:1px solid rgba(255,255,255,0.10);
}

.ticket b{color:white;}
.ticket small{color:#94a3b8;}
.reply{
    margin-top:10px;
    padding:12px;
    border-radius:14px;
    background:rgba(34,197,94,0.12);
    border:1px solid rgba(34,197,94,0.18);
}
.status{
    font-weight:900;
    color:#facc15;
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
        <a href="cart.php" class="logo" style="font-size:18px;">Cart 🛒</a>
        <a href="create_ticket.php" class="logo" style="font-size:18px;">Support</a>
        <a href="purchases.php" class="logo" style="font-size:18px;">Purchases</a>
        <a href="logout.php" class="logo" style="font-size:18px;">Logout</a>
    </nav>
</header>

<div class="container">

    <div class="card">
        <h1>📩 Contact Support</h1>
        <p>Submit your issue (order problem, damaged item, refund request, wrong product, etc.)</p>

        <?php if($msg): ?>
            <div class="msg"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form method="POST">
            <textarea name="issue" rows="5" placeholder="Describe your issue here..." required></textarea>
            <button type="submit">Submit Ticket</button>
        </form>
    </div>

    <div class="card">
        <h1>📌 Your Tickets</h1>

        <?php if(empty($tickets)): ?>
            <p>You have no support tickets yet.</p>
        <?php else: ?>
            <?php foreach($tickets as $t): ?>
                <div class="ticket">
                    <b>Ticket #<?= $t['id'] ?></b>
                    <br>
                    <small><?= $t['created_at'] ?></small>
                    <br><br>

                    <div><b>Issue:</b> <?= nl2br(htmlspecialchars($t['issue'])) ?></div>
                    <br>
                    <div class="status">Status: <?= htmlspecialchars($t['status']) ?></div>

                    <?php if(!empty($t['reply'])): ?>
                        <div class="reply">
                            <b>Support Reply:</b><br>
                            <?= nl2br(htmlspecialchars($t['reply'])) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>

</div>

<!-- ✅ Back button cache killer -->
<script>
window.addEventListener("pageshow", function(event) {
    if (event.persisted || window.performance.getEntriesByType("navigation")[0]?.type === "back_forward") {
        window.location.reload();
    }
});
</script>

</body>
</html>

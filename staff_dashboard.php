<?php
session_start();
// Force browser not to cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect if not logged in
if(!isset($_SESSION['user_id'])){
    header("Location: staff_login.php");
    exit;
}
require 'computer.php';

if(!isset($_SESSION['staff_id'])){
    header("Location: staff_login.php");
    exit;
}

$staff_id   = $_SESSION['staff_id'];
$staff_name = $_SESSION['staff_name'] ?? 'Staff';
$staff_role = $_SESSION['staff_role'] ?? 'Support';
$message = "";

/* ============================
   ATTENDANCE
============================ */
if(isset($_POST['time_in'])){
    $conn->query("INSERT INTO staff_attendance (staff_id,time_in) VALUES ($staff_id,NOW())");
    $message = "Time In recorded!";
}

if(isset($_POST['time_out'])){
    $conn->query("UPDATE staff_attendance SET time_out=NOW() 
                 WHERE staff_id=$staff_id AND time_out IS NULL 
                 ORDER BY time_in DESC LIMIT 1");
    $message = "Time Out recorded!";
}

// Fetch last attendance
$attendance = $conn->query("SELECT * FROM staff_attendance 
                            WHERE staff_id=$staff_id 
                            ORDER BY time_in DESC LIMIT 1")->fetch_assoc();

/* ============================
   ROLE-BASED TASKS
============================ */
$tasks = [];

if($staff_role === 'Delivery'){

    $staff_area = $_SESSION['staff_area'] ?? "";

    $stmt = $conn->prepare("SELECT o.*, u.username, p.name as product_name 
                            FROM orders o
                            JOIN users u ON o.user_id = u.id
                            JOIN products p ON o.product_id = p.id
                            WHERE u.address LIKE ? 
                            ORDER BY o.order_date DESC");

    $like_area = "%".$staff_area."%";
    $stmt->bind_param("s", $like_area);
    $stmt->execute();
    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()){
        $tasks[] = $row;
    }

} elseif($staff_role === 'Support'){

    // Fetch tickets assigned to this staff OR unassigned tickets
    $stmt = $conn->prepare("
        SELECT s.*, u.username AS user_name
        FROM support_tickets s
        JOIN users u ON s.user_id = u.id
        WHERE (s.assigned_to = ? OR s.assigned_to IS NULL)
        AND s.status != 'Resolved'
        ORDER BY s.created_at DESC
    ");
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while($row = $result->fetch_assoc()){
        $tasks[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Staff Dashboard - CREATECH</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
:root { --neon-green:#22c55e; --dark-bg:#020617; }
body { margin:0; font-family:'Segoe UI',sans-serif; background:var(--dark-bg); color:#f8fafc; }

#bg-video { position:fixed; top:0; left:0; width:100%; height:100%; object-fit:cover; z-index:-2; filter: brightness(0.35); pointer-events:none;}
.video-overlay{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.6);z-index:-1;}

header{
    background:rgba(15,23,42,0.85);
    color:white;
    padding:20px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    position:sticky;
    top:0;
    z-index:999;
    backdrop-filter:blur(10px);
}
header a{color:#22c55e;text-decoration:none;font-weight:bold;margin-left:15px;}
header a:hover{opacity:0.8;}
.logo {
    font-size: 24px;
    font-weight: 900;
    letter-spacing: 2px;
    background: linear-gradient(90deg, #2e7738, #2cbb43,  #00ff73);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
main{
    max-width:1100px;
    margin:30px auto;
    padding:20px;
    backdrop-filter:blur(12px);
    border-radius:12px;
    background:rgba(255,255,255,0.05);
    border:1px solid rgba(255,255,255,0.15);
}

h2{margin-bottom:20px;color:#22c55e;}
.message{color:#22c55e;margin-bottom:15px;font-weight:bold;}

button{padding:12px 20px;margin:5px;background:#22c55e;border:none;border-radius:6px;cursor:pointer;color:#fff;font-weight:bold;}
button:hover{background:#16a34a;box-shadow:0 4px 15px rgba(34,197,94,0.5);}

table{width:100%;border-collapse:collapse;margin-top:20px;backdrop-filter:blur(10px);}
th,td{padding:12px;border:1px solid rgba(255,255,255,0.1);text-align:left;vertical-align:top;}
th{background:rgba(255,255,255,0.1);color:#fff;}
td{background:rgba(255,255,255,0.05);}

textarea{width:100%;padding:10px;border-radius:8px;border:none;outline:none;resize:none;background:rgba(255,255,255,0.12);color:white;}
select{padding:8px;border-radius:8px;border:none;outline:none;background:rgba(255,255,255,0.12);color:white;}
small{color:#cbd5e1;}
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
        <span class="logo">Hello, <?= htmlspecialchars($staff_name); ?> (<?= htmlspecialchars($staff_role); ?>)</span>
        <a href="logout.php" class="logo">Logout</a>
    </nav>
</header>

<main>

    <?php if($message): ?>
        <p class="message"><?= htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <!-- Attendance -->
    <h2>Attendance</h2>
    <form method="POST">
        <button name="time_in">Time In</button>
        <button name="time_out">Time Out</button>
    </form>

    <?php if($attendance): ?>
        <p>Last Time In: <b><?= $attendance['time_in'] ?? 'N/A'; ?></b></p>
        <p>Last Time Out: <b><?= $attendance['time_out'] ?? 'N/A'; ?></b></p>
    <?php endif; ?>

    <!-- Delivery Staff Tasks -->
    <?php if($staff_role === 'Delivery'): ?>
        <h2>Assigned Orders</h2>
        <?php if($tasks): ?>
            <table>
                <tr><th>Order ID</th><th>User</th><th>Product</th><th>Quantity</th><th>Status</th><th>Date</th></tr>
                <?php foreach($tasks as $o): ?>
                    <tr>
                        <td><?= $o['id']; ?></td>
                        <td><?= htmlspecialchars($o['username']); ?></td>
                        <td><?= htmlspecialchars($o['product_name']); ?></td>
                        <td><?= (int)$o['quantity']; ?></td>
                        <td><?= htmlspecialchars($o['status']); ?></td>
                        <td><?= htmlspecialchars($o['order_date']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No assigned orders yet.</p>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Support Staff Tasks -->
    <?php if($staff_role === 'Support'): ?>
        <h2>Support Tickets</h2>
        <?php if($tasks): ?>
            <table>
                <tr><th>Ticket ID</th><th>User</th><th>Issue</th><th>Status</th><th>Created At</th></tr>
                <?php foreach($tasks as $t): ?>
                    <tr>
                        <td><?= $t['id']; ?></td>
                        <td><?= htmlspecialchars($t['user_name']); ?></td>
                        <td><?= htmlspecialchars($t['issue']); ?></td>
                        <td><?= htmlspecialchars($t['status']); ?></td>
                        <td><?= htmlspecialchars($t['created_at']); ?></td>
                    </tr>
                    <tr>
                        <td colspan="5">
                            <form method="POST" action="update_ticket.php">
                                <input type="hidden" name="ticket_id" value="<?= $t['id']; ?>">
                                <label><b>Reply:</b></label><br>
                                <textarea name="reply" rows="3"><?= htmlspecialchars($t['reply'] ?? "") ?></textarea><br><br>
                                <label><b>Status:</b></label>
                                <select name="status">
                                    <option value="Pending" <?= $t['status']=="Pending" ? "selected" : "" ?>>Pending</option>
                                    <option value="In Progress" <?= $t['status']=="In Progress" ? "selected" : "" ?>>In Progress</option>
                                    <option value="Resolved" <?= $t['status']=="Resolved" ? "selected" : "" ?>>Resolved</option>
                                </select>
                                <button type="submit">Update Ticket</button>
                                <br><small>After updating, refresh the page to see the latest changes.</small>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No pending support tickets.</p>
        <?php endif; ?>
    <?php endif; ?>
<script>
if (window.history.replaceState) {
    window.history.replaceState(null, null, window.location.href);
    window.history.forward();
}
</script>
</main>

</body>
</html>

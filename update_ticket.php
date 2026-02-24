<?php
session_start();
require "computer.php";

if(!isset($_SESSION['staff_id']) || ($_SESSION['staff_role'] ?? '') !== "Support"){
    header("Location: staff_login.php");
    exit;
}

$staff_id = $_SESSION['staff_id'];

$ticket_id = (int)($_POST['ticket_id'] ?? 0);
$reply = trim($_POST['reply'] ?? "");
$status = $_POST['status'] ?? "Pending";

if($ticket_id <= 0){
    header("Location: staff_dashboard.php");
    exit;
}

$stmt = $conn->prepare("UPDATE support_tickets SET reply=?, status=?, assigned_to=? WHERE id=?");
$stmt->bind_param("ssii", $reply, $status, $staff_id, $ticket_id);
$stmt->execute();
$stmt->close();

header("Location: staff_dashboard.php");
exit;

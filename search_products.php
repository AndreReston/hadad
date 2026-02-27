<?php
include("computer.php");

$search = $_GET['q'] ?? '';
$search_safe = $conn->real_escape_string($search);

$sql = "SELECT * FROM products 
        WHERE name LIKE '%$search_safe%' OR category LIKE '%$search_safe%' 
        ORDER BY id DESC";
$result = $conn->query($sql);

$products = [];
while($row = $result->fetch_assoc()) {
    $products[] = $row;
}

header('Content-Type: application/json');
echo json_encode($products);
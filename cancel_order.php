<?php
require_once 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in."]);
    exit;
}

$order_id = intval($_POST['order_id']);
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE order_id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$success = $stmt->execute();

echo json_encode(["success" => $success]);
?>

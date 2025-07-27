<?php
require_once 'includes/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$order_id = intval($_POST['order_id'] ?? 0);

// Update order status to completed
$stmt = $conn->prepare("UPDATE orders SET status = 'completed' WHERE order_id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();

// Fetch updated wallet balance
$wallet_stmt = $conn->prepare("SELECT wallet FROM users WHERE user_id = ?");
$wallet_stmt->bind_param("i", $user_id);
$wallet_stmt->execute();
$wallet_stmt->bind_result($new_wallet);
$wallet_stmt->fetch();
$wallet_stmt->close();

$_SESSION['wallet'] = $new_wallet;

echo json_encode([
    'success' => true,
    'new_wallet' => number_format($new_wallet, 2)
]);

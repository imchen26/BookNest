<?php
require_once '/BookNest/includes/db.php';
$page_css = '/BookNest/css/order_history.css'; 
include '/BookNest/includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /BookNest/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$result = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC"); 
?>

<div class="container">
    <div class="orderhistory-container">
        <h2>📦 Your Orders</h2>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="order-block">
                <strong>Order #<?php echo $row['order_id']; ?></strong> - ₱<?php echo number_format($row['total_amount'], 2); ?>
                <br>Status: <?php echo ucfirst($row['status']); ?>
                <br><small>Placed on: <?php echo $row['created_at']; ?></small>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include '/BookNest/includes/footer.php'; ?>


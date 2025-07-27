<?php  
require_once 'includes/db.php';
$page_css = '/BookNest/css/order_history.css';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "
    SELECT o.order_id, o.order_date, o.status, 
           COALESCE(SUM(b.price * oi.quantity), 0) AS total_price
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    LEFT JOIN books b ON oi.book_id = b.book_id
    WHERE o.user_id = ?
    GROUP BY o.order_id, o.order_date, o.status
    ORDER BY o.order_date DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container">
    <div class="orderhistory-container">
        <h2>📦 Your Orders</h2>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="order-block <?php echo strtolower($row['status']); ?>">
                <div class="order-header">
                    <span class="order-id">Order #<?php echo $row['order_id']; ?></span>
                    <span class="order-status-text"><?php echo ucfirst($row['status']); ?></span>
                </div>
                <p class="order-price"><?php echo display_price($row['total_price']); ?></p>
                <small class="order-date">Placed on: <?php echo date("M d, Y", strtotime($row['order_date'])); ?></small>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

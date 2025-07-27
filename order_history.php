<?php
require_once 'includes/db.php';
$page_css = '/BookNest/css/order_history.css';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['receive_order'])) {
        $order_id = $_POST['order_id'];
        $stmt = $conn->prepare("UPDATE orders SET status = 'completed' WHERE order_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();
    }

    if (isset($_POST['cancel_order'])) {
        $order_id = $_POST['order_id'];
        $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE order_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $order_id, $user_id);
        $stmt->execute();
    }
}

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

                <div class="order-items">
                    <ul>
                        <?php
                        $items_stmt = $conn->prepare("
                            SELECT b.title, oi.quantity, oi.subtotal
                            FROM order_items oi
                            JOIN books b ON oi.book_id = b.book_id
                            WHERE oi.order_id = ?
                        ");
                        $items_stmt->bind_param("i", $row['order_id']);
                        $items_stmt->execute();
                        $items = $items_stmt->get_result();

                        while ($item = $items->fetch_assoc()) {
                            echo "<li>" . htmlspecialchars($item['title']) .
                                 " (x" . $item['quantity'] . ") - ₱" . number_format($item['subtotal'], 2) . "</li>";
                        }
                        ?>
                    </ul>
                </div>

                <?php if (in_array($row['status'], ['pending', 'shipped', 'processing'])): ?>
                    <form method="post" action="order_history.php" class="order-actions">
                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                        <?php if ($row['status'] == 'shipped'): ?>
                            <button type="submit" name="receive_order" class="receive-btn">Receive Order</button>
                        <?php endif; ?>
                        <?php if ($row['status'] == 'pending' || $row['status'] == 'processing'): ?>
                            <button type="submit" name="cancel_order" class="cancel-btn">Cancel Order</button>
                        <?php endif; ?>
                    </form>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

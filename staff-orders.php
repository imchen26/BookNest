<?php 
require_once 'includes/db.php';
$page_css = '/BookNest/css/stafforders.css';
include 'includes/header.php';

if ($_SESSION['role'] != 'staff') {
    header("Location: login.php");
    exit;
}

// Update order status
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
}

// Fetch all orders with totals
$result = $conn->query("
    SELECT o.order_id, o.user_id, o.status, o.order_date,
           IFNULL(SUM(b.price * oi.quantity), 0) AS total_amount
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    LEFT JOIN books b ON oi.book_id = b.book_id
    GROUP BY o.order_id
    ORDER BY o.order_date DESC
");
?>

<div class="container">
    <h2>📦 Customer Orders</h2>
    <div class="orders-container">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="order-card">
                <div class="order-header">
                    <h3>Order #<?php echo $row['order_id']; ?></h3>
                    <span><?php echo ucfirst($row['status']); ?></span>
                </div>
                <div class="order-details">
                    <p><strong>User ID:</strong> <?php echo $row['user_id']; ?></p>
                    <p><strong>Date:</strong> <?php echo date("M d, Y h:i A", strtotime($row['order_date'])); ?></p>
                    <p><strong>Total Amount:</strong> <?php echo display_price($row['total_amount']); ?></p>
                </div>
                <div class="items-list">
                    <strong>Items:</strong>
                    <?php
                    $items_result = $conn->prepare("
                        SELECT b.title, oi.quantity 
                        FROM order_items oi
                        JOIN books b ON oi.book_id = b.book_id
                        WHERE oi.order_id = ?
                    ");
                    $items_result->bind_param("i", $row['order_id']);
                    $items_result->execute();
                    $items = $items_result->get_result();
                    if ($items->num_rows > 0) {
                        while ($item = $items->fetch_assoc()) {
                            echo "<p>" . htmlspecialchars($item['title']) . " (x" . $item['quantity'] . ")</p>";
                        }
                    } else {
                        echo "<p>No items found.</p>";
                    }
                    ?>
                </div>
                <form method="post" class="status-update">
                    <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                    <select name="status">
                        <option value="pending" <?php if ($row['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                        <option value="processing" <?php if ($row['status'] == 'processing') echo 'selected'; ?>>Processing</option>
                        <option value="shipped" <?php if ($row['status'] == 'shipped') echo 'selected'; ?>>Shipped</option>
                        <option value="completed" <?php if ($row['status'] == 'completed') echo 'selected'; ?>>Completed</option>
                        <option value="cancelled" <?php if ($row['status'] == 'cancelled') echo 'selected'; ?>>Cancelled</option>
                    </select>
                    <button type="submit" name="update_status">Update</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

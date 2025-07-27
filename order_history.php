<?php  
require_once 'includes/db.php';
$page_css = '/BookNest/css/order_history.css';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch orders for this user
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
            <div class="order-block <?php echo strtolower($row['status']); ?>" data-order-id="<?php echo $row['order_id']; ?>">
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
                    <div class="order-actions">
                        <?php if ($row['status'] == 'shipped'): ?>
                            <button type="button" class="receive-btn" data-order-id="<?php echo $row['order_id']; ?>">
                                Receive Order
                            </button>
                        <?php endif; ?>
                        <?php if ($row['status'] == 'pending' || $row['status'] == 'processing'): ?>
                            <button type="button" class="cancel-btn" data-order-id="<?php echo $row['order_id']; ?>">
                                Cancel Order
                            </button>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Handle "Receive Order"
    document.querySelectorAll(".receive-btn").forEach(button => {
        button.addEventListener("click", function() {
            const orderId = this.getAttribute("data-order-id");
            fetch("receive_order.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "order_id=" + orderId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const orderBlock = this.closest(".order-block");
                    orderBlock.querySelector(".order-status-text").textContent = "Completed";
                    orderBlock.classList.remove("pending", "shipped", "processing", "cancelled");
                    orderBlock.classList.add("completed");
                    this.remove();
                } else {
                    alert(data.message || "Failed to update order.");
                }
            })
            .catch(() => alert("Error communicating with server."));
        });
    });

    // Handle "Cancel Order"
    document.querySelectorAll(".cancel-btn").forEach(button => {
        button.addEventListener("click", function() {
            const orderId = this.getAttribute("data-order-id");
            if (!confirm("Are you sure you want to cancel this order?")) return;

            fetch("cancel_order.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "order_id=" + orderId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const orderBlock = this.closest(".order-block");
                    orderBlock.querySelector(".order-status-text").textContent = "Cancelled";
                    orderBlock.classList.remove("pending", "shipped", "processing", "completed");
                    orderBlock.classList.add("cancelled");
                    this.remove();
                } else {
                    alert(data.message || "Failed to cancel order.");
                }
            })
            .catch(() => alert("Error communicating with server."));
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>

<?php
require_once '../includes/db.php';
$page_css = '/BookNest/css/orders.css';

if ($_SESSION['role'] != 'staff') {
    header("Location: ../login.php");
    exit;
}

if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $status = $_POST['status'];
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();
}

$result = $conn->query("SELECT * FROM orders ORDER BY created_at DESC"); 
include '../includes/header.php';
?>

<div class="container">
    <div class="orders-container">
        <h2>📦 Customer Orders</h2>
        <table>
            <tr><th>Order ID</th><th>User ID</th><th>Total</th><th>Status</th><th>Update</th></tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $row['order_id']; ?></td>
                    <td><?php echo $row['user_id']; ?></td>
                    <td>₱<?php echo number_format($row['total_amount'], 2); ?></td>
                    <td><?php echo ucfirst($row['status']); ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                            <select name="status">
                                <option value="pending" <?php if ($row['status'] == 'pending') echo 'selected'; ?>>Pending</option>
                                <option value="processing" <?php if ($row['status'] == 'processing') echo 'selected'; ?>>Processing</option>
                                <option value="completed" <?php if ($row['status'] == 'completed') echo 'selected'; ?>>Completed</option>
                            </select>
                            <button type="submit" name="update_status">Update</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

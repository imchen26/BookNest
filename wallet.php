<?php
require_once 'includes/db.php';
$page_css = '/BookNest/css/wallet.css';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Handle top-up (customers only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['top_up']) && $role === 'customer') {
    $amount = floatval($_POST['amount']);
    if ($amount > 0) {
        $stmt = $conn->prepare("UPDATE users SET wallet = wallet + ? WHERE user_id = ? AND role = 'customer'");
        $stmt->bind_param("di", $amount, $user_id);
        $stmt->execute();
    }
}

// Get wallet balance
$stmt = $conn->prepare("SELECT wallet FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($wallet_balance);
$stmt->fetch();
$stmt->close();

// For admin, fetch total earnings
$admin_total = 0.00;
if ($role === 'admin') {
    $earnings_query = $conn->query("SELECT IFNULL(SUM(total_amount), 0) AS total FROM orders WHERE status = 'completed'");
    $admin_total = $earnings_query->fetch_assoc()['total'];
}
?>

<div class="wallet-page">
    <div class="wallet-container">
        <h2>💰 Wallet</h2>
        <div class="wallet-balance">
            <p>Your Role: <strong><?php echo ucfirst($role); ?></strong></p>
            <?php if ($role === 'customer'): ?>
                <p>Current Balance: <span class="balance">₱<?php echo number_format($wallet_balance, 2); ?></span></p>

                <form method="post" class="top-up-form">
                    <input type="number" step="0.01" name="amount" placeholder="Enter amount to top up" required>
                    <button type="submit" name="top_up">Top Up</button>
                </form>

            <?php elseif ($role === 'admin'): ?>
                <p>Total Earnings from Completed Orders: <span class="balance">₱<?php echo number_format($admin_total, 2); ?></span></p>
            <?php else: ?>
                <p>Staff Wallet: <span class="balance">₱0.00</span></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

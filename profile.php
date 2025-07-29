<?php
require_once 'includes/db.php';
$page_css = '/BookNest/css/profile.css';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Handle account deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    session_destroy();
    header("Location: register.php?deleted=1");
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username, email, role, wallet FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username, $email, $role, $wallet);
$stmt->fetch();
$stmt->close();

// Get currency preference
$currency_id = $_SESSION['currency_id'] ?? 1;
$currency_stmt = $conn->prepare("SELECT currency_code FROM currencies WHERE currency_id = ?");
$currency_stmt->bind_param("i", $currency_id);
$currency_stmt->execute();
$currency_stmt->bind_result($currency_code);
$currency_stmt->fetch();
$currency_stmt->close();
?>

<div class="container">
    <div class="profile-container">
        <h2>👤 My Profile</h2>
        <ul class="profile-details">
            <li><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></li>
            <li><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></li>
            <li><strong>Role:</strong> <?php echo ucfirst($role); ?></li>
            <li><strong>Wallet Balance:</strong> <?php echo $currency_code . " " . number_format($wallet, 2); ?></li>
            <li><strong>Currency Preference:</strong> <?php echo $currency_code; ?></li>
        </ul>
        <a href="wallet.php" class="wallet-link">Manage Wallet</a>
        <a href="order_history.php" class="orders-link">View Order History</a>
        <form method="post" onsubmit="return confirm('Are you sure you want to delete your account? This cannot be undone.');" style="margin-top:20px;">
            <button type="submit" name="delete_account" class="delete-btn">Delete Account</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
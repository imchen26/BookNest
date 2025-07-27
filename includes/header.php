<?php
session_start();
require_once 'includes/db.php';
require_once __DIR__ . '/currency_helper.php';

$currencies = [];
$currencyQuery = $conn->query("SELECT currency_id, currency_code, exchange_rate FROM currencies");
if ($currencyQuery) {
    $currencies = $currencyQuery->fetch_all(MYSQLI_ASSOC);
}

if (isset($_POST['currency_id'])) {
    $_SESSION['currency_id'] = (int)$_POST['currency_id'];
    $selectedCurrencyId = $_SESSION['currency_id'];

    if (isset($_SESSION['user_id'])) {
        $uid = $_SESSION['user_id'];
        $conn->query("INSERT INTO user_currency_preference (user_id, currency_id)
                      VALUES ($uid, $selectedCurrencyId)
                      ON DUPLICATE KEY UPDATE currency_id = $selectedCurrencyId");
    }

    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}

if (isset($_SESSION['user_id']) && !isset($_SESSION['currency_id'])) {
    $uid = $_SESSION['user_id'];
    $prefQuery = $conn->query("SELECT currency_id FROM user_currency_preference WHERE user_id = $uid");
    if ($prefQuery && $prefQuery->num_rows > 0) {
        $row = $prefQuery->fetch_assoc();
        $_SESSION['currency_id'] = $row['currency_id'];
    }
}

$currentCurrencyId = $_SESSION['currency_id'] ?? 1;

$wallet_display = null;
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $role = $_SESSION['role'];

    if ($role === 'customer') {
        $stmt = $conn->prepare("SELECT wallet FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $stmt->bind_result($wallet);
        $stmt->fetch();
        $stmt->close();
        $wallet_display = "₱" . number_format($wallet, 2);
    } elseif ($role === 'admin') {
        $res = $conn->query("SELECT IFNULL(SUM(total_amount), 0) AS total FROM orders WHERE status = 'completed'");
        $total = $res->fetch_assoc()['total'];
        $wallet_display = "Earnings: ₱" . number_format($total, 2);
    } else {
        $wallet_display = "₱0.00";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BookNest</title>
    <link rel="stylesheet" href="/BookNest/css/header.css">
    <link rel="stylesheet" href="/BookNest/css/footer.css">
    <?php if (isset($page_css)): ?>
        <link rel="stylesheet" href="<?php echo $page_css; ?>">
    <?php endif; ?>
</head>
<body>
<?php if (empty($hideLayout)): ?>
<header>
    <div class="container">
        <h1>📚 BookNest</h1>

        <?php if ($wallet_display !== null): ?>
        <div class="wallet-header">
            <a href="/BookNest/wallet.php" class="wallet-link wallet-balance">
                💰 <?php echo $wallet_display; ?>
            </a>
        </div>
        <?php endif; ?>

        <form method="post" class="currency-form">
            <label for="currency_id">Currency:</label>
            <select name="currency_id" id="currency_id" onchange="this.form.submit()">
                <?php foreach ($currencies as $cur): ?>
                    <option value="<?php echo $cur['currency_id']; ?>" <?php echo ($cur['currency_id'] == $currentCurrencyId) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cur['currency_code']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <nav>
            <ul>
                <li><a href="/BookNest/index.php">Home</a></li>
                <li><a href="/BookNest/browse.php">Browse</a></li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li><a href="/BookNest/admin-dashboard.php">Admin Panel</a></li>
                <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'staff'): ?>
                    <li><a href="/BookNest/staff-orders.php">Staff Panel</a></li>
                <?php endif; ?>
                <?php if (!isset($_SESSION['role']) || $_SESSION['role'] === 'customer'): ?>
                    <li><a href="/BookNest/cart.php">Cart</a></li>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'customer'): ?>
                    <li><a href="/BookNest/order_history.php">My Orders</a></li>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="/BookNest/logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="/BookNest/login.php">Login</a></li>
                    <li><a href="/BookNest/register.php">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>
<main>
<?php endif; ?>

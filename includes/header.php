<?php session_start(); ?>
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
        <nav>
            <ul>
                <li><a href="BookNest/index.php">Home</a></li>
                <li><a href="/BookNest/browse.php">Browse</a></li>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Admin'): ?>
                    <li><a href="/BookNest/admin/dashboard.php">Admin Panel</a></li>
                <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'Staff'): ?>
                    <li><a href="/BookNest/staff/orders.php">Staff Panel</a></li>
                <?php endif; ?>
                <li><a href="/BookNest/cart.php">Cart</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="/BookNest/order_history.php">My Orders</a></li>
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
<?php 
require_once 'includes/db.php';
$page_css = '/BookNest/css/order.css'; 
include 'includes/header.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'customer') {
    header("Location: login.php");
    exit;
}

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
} 

$user_id = $_SESSION['user_id'];
$total = 0;

foreach ($_SESSION['cart'] as $book_id => $qty) {
    $stmt = $conn->prepare("SELECT price FROM books WHERE book_id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $stmt->bind_result($price);
    $stmt->fetch();
    $total += $price * $qty;
    $stmt->close();
}

$stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount) VALUES (?, ?)");
$stmt->bind_param("id", $user_id, $total);
$stmt->execute();
$order_id = $stmt->insert_id;

foreach ($_SESSION['cart'] as $book_id => $qty) {
    $stmt = $conn->prepare("SELECT price FROM books WHERE book_id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $stmt->bind_result($price);
    $stmt->fetch();
    $subtotal = $price * $qty;
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO order_items (order_id, book_id, quantity, subtotal) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $order_id, $book_id, $qty, $subtotal);
    $stmt->execute();
}

$_SESSION['cart'] = []; 
?>

<div class="container">
    <div class="order-container">
        <h2>✅ Order Placed!</h2>
        <p>Your order ID is <strong>#<?php echo $order_id; ?></strong></p>
        <p>Total Amount: <strong><?php echo display_price($total); ?></strong></p>
        <a href="order_history.php">View Order History</a>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

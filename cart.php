<?php  
require_once 'includes/db.php';
$page_css = '/BookNest/css/cart.css';
include 'includes/header.php';

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

if (isset($_POST['add_to_cart'])) {
    $book_id = intval($_POST['book_id']);
    $quantity = intval($_POST['quantity']);
    if ($quantity > 0) {
        $_SESSION['cart'][$book_id] = ($_SESSION['cart'][$book_id] ?? 0) + $quantity;
    }
}

if (isset($_GET['remove'])) {
    $remove_id = intval($_GET['remove']);
    unset($_SESSION['cart'][$remove_id]);
}

$total = 0;
?>

<div class="container">
    <div class="cart-container">
        <h2>🛒 Your Cart</h2>
        
        <?php if (empty($_SESSION['cart'])): ?>
            <p class="empty-message">Your cart is empty.</p>
        <?php else: ?>
            <table class="cart-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                        <th>Action</th>
                    </tr>
                </thead>
                
                <tbody>
                    <?php foreach ($_SESSION['cart'] as $book_id => $qty): ?>
                        <?php 
                        $stmt = $conn->prepare("SELECT title, price FROM books WHERE book_id = ?");
                        $stmt->bind_param("i", $book_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $book = $result->fetch_assoc();
                        $stmt->close();

                        if (!$book) continue; // skip if book not found

                        $subtotal = $book['price'] * $qty;
                        $total += $subtotal;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($book['title']); ?></td>
                            <td><?php echo $qty; ?></td>
                            <td><?php echo display_price($subtotal); ?></td>
                            <td>
                                <a href="?remove=<?php echo $book_id; ?>" class="remove-btn" onclick="return confirm('Remove this item from cart?');">Remove</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>

            <div class="cart-summary">
                <p><strong>Total: <?php echo display_price($total); ?></strong></p>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="order.php" class="checkout-btn">Proceed to Checkout</a>
                <?php else: ?>
                    <a href="login.php" class="checkout-btn" onclick="alert('Please log in to proceed to checkout.');">Login to Checkout</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

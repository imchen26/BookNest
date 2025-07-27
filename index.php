<?php 
require_once 'includes/db.php';
$page_css = '/BookNest/css/index.css'; 
include 'includes/header.php';

$result = $conn->query("SELECT * FROM books ORDER BY RAND() LIMIT 8");
?>

<div class="container">
    <h2>📚 Featured Books</h2>
    <div class="grid">
        <?php while ($book = $result->fetch_assoc()): ?>
            <div class="book-card">
                <h4><?php echo htmlspecialchars($book['title']); ?></h4>
                <p>by <?php echo htmlspecialchars($book['author']); ?></p>

                <p class="price"><?php echo display_price($book['price']); ?></p>

                <?php if ($book['stock'] > 0): ?>
                    <p class="stock-status in-stock">In Stock</p>
                <?php else: ?>
                    <p class="stock-status out-of-stock">Out of Stock</p>
                <?php endif; ?>

                <form method="post" action="cart.php">
                    <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                    <input type="number" name="quantity" value="1" min="1" required>
                    <button type="submit" name="add_to_cart">Add to Cart</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

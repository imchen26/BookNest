<?php
require_once 'includes/db.php';
$page_css = 'css/index.css'; 
include 'includes/header.php';

$result = $conn->query("SELECT * FROM books ORDER BY RAND() LIMIT 6");
?>

<div class="container">
    <h2>📚 Featured Books</h2>
    <div class="grid">
        <?php while ($book = $result->fetch_assoc()): ?>
            <div class="book-card">
                <h4><?php echo $book['title']; ?></h4>
                <p>by <?php echo $book['author']; ?></p>
                <p>₱<?php echo number_format($book['price'], 2); ?></p>
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

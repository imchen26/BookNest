<?php
require_once 'includes/db.php';
$page_css = '/BookNest/css/browse.css';
include 'includes/header.php';

$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;

$cat_query = $conn->query("SELECT * FROM categories");
$books_query = $category_id > 0 ?
    $conn->prepare("SELECT * FROM books WHERE category_id = ?") :
    $conn->prepare("SELECT * FROM books");
if ($category_id > 0) $books_query->bind_param("i", $category_id);
$books_query->execute();
$books_result = $books_query->get_result();
?>

<div class="container">
    <h2>📖 Browse Books</h2>
    <form method="get" action="">
        <select name="category" onchange="this.form.submit()">
            <option value="0">All Categories</option>
            <?php while ($cat = $cat_query->fetch_assoc()): ?>
                <option value="<?php echo $cat['category_id']; ?>" 
                    <?php if ($cat['category_id'] == $category_id) echo "selected"; ?>>
                    <?php echo $cat['category_name']; ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <div class="grid">
        <?php while ($book = $books_result->fetch_assoc()): ?>
            <div class="book-card">
                <h4><?php echo $book['title']; ?></h4>
                <p>by <?php echo $book['author']; ?></p>、、、、7
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

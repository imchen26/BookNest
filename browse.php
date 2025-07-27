<?php 
require_once 'includes/db.php';
$page_css = '/BookNest/css/browse.css';
include 'includes/header.php';

$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'title';
$valid_sorts = ['title', 'price', 'author'];
if (!in_array($sort, $valid_sorts)) $sort = 'title';

$limit = 8;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

$where = [];
$params = [];
$types = '';

if ($category_id > 0) { $where[] = "category_id = ?"; $params[] = $category_id; $types .= 'i'; }
if ($search !== '') { $where[] = "(title LIKE ? OR author LIKE ?)"; $like = "%$search%"; $params[] = $like; $params[] = $like; $types .= 'ss'; }

$where_sql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

$count_query = $conn->prepare("SELECT COUNT(*) AS total FROM books $where_sql");
if ($types) $count_query->bind_param($types, ...$params);
$count_query->execute();
$total = $count_query->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total / $limit);

$query = $conn->prepare("SELECT * FROM books $where_sql ORDER BY $sort ASC LIMIT ? OFFSET ?");
if ($types) {
    $types_with_limit = $types . "ii";
    $params_with_limit = array_merge($params, [$limit, $offset]);
    $query->bind_param($types_with_limit, ...$params_with_limit);
} else {
    $query->bind_param("ii", $limit, $offset);
}
$query->execute();
$books_result = $query->get_result();

$cat_query = $conn->query("SELECT * FROM categories");
?>

<div class="container">
    <h2>📖 Browse Books</h2>
    <form method="get" action="" class="browse-filter-form">
        <input type="text" name="search" placeholder="Search by title or author" value="<?php echo htmlspecialchars($search); ?>">
        <select name="category">
            <option value="0">All Categories</option>
            <?php while ($cat = $cat_query->fetch_assoc()): ?>
                <option value="<?php echo $cat['category_id']; ?>" <?php if ($cat['category_id'] == $category_id) echo "selected"; ?>>
                    <?php echo $cat['name']; ?>
                </option>
            <?php endwhile; ?>
        </select>
        <select name="sort">
            <option value="title" <?php if ($sort == 'title') echo 'selected'; ?>>Sort by Title</option>
            <option value="price" <?php if ($sort == 'price') echo 'selected'; ?>>Sort by Price</option>
            <option value="author" <?php if ($sort == 'author') echo 'selected'; ?>>Sort by Author</option>
        </select>
        <button type="submit">Apply</button>
    </form>

    <div class="grid">
        <?php while ($book = $books_result->fetch_assoc()): ?>
            <div class="book-card">
                <h4><?php echo htmlspecialchars($book['title']); ?></h4>
                <p>by <?php echo htmlspecialchars($book['author']); ?></p>
                <p><?php echo display_price($book['price']); ?></p>
                <?php
                $rating_stmt = $conn->prepare("SELECT AVG(rating) as avg_rating FROM reviews WHERE book_id = ?");
                $rating_stmt->bind_param("i", $book['book_id']);
                $rating_stmt->execute();
                $avg_rating = $rating_stmt->get_result()->fetch_assoc()['avg_rating'];
                $rating_stmt->close();
                $stars = $avg_rating ? round($avg_rating, 1) : 0;
                ?>
                <p class="rating">Rating: 
                    <?php 
                    for ($i = 1; $i <= 5; $i++) {
                        echo $i <= $stars ? "⭐" : "☆";
                    }
                    echo " ($stars)";
                    ?>
                </p>
                <form method="post" action="cart.php">
                    <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                    <input type="number" name="quantity" value="1" min="1" required>
                    <button type="submit" name="add_to_cart">Add to Cart</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>

    <?php if ($total_pages > 1): ?>
        <div class="pagination">
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $category_id; ?>&sort=<?php echo $sort; ?>" 
                   class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>

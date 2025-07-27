<?php 
require_once 'includes/db.php';
$page_css = '/BookNest/css/adminbooks.css';
include 'includes/header.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_POST['add'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $price = $_POST['price'];
    $category_id = $_POST['category'];
    $stock = $_POST['stock'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_digital = isset($_POST['is_digital']) ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO books (title, author, price, category_id, stock, is_featured, is_digital) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdiiii", $title, $author, $price, $category_id, $stock, $is_featured, $is_digital);
    $stmt->execute();
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM books WHERE book_id = $id");
}

$categories = $conn->query("SELECT * FROM categories");
$books = $conn->query("SELECT * FROM books"); 
?>

<div class="container">
    <div class="books-container">
        <h2>📚 Manage Books</h2>
        <form method="post" class="book-form">
            <input type="text" name="title" placeholder="Title" required>
            <input type="text" name="author" placeholder="Author">
            <input type="number" step="0.01" name="price" placeholder="Price" required>
            <select name="category" required>
                <option value="">Select Category</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $cat['category_id']; ?>"><?php echo $cat['name']; ?></option>
                <?php endwhile; ?>
            </select>
            <input type="number" name="stock" placeholder="Stock" min="0">

            <div class="checkbox-group">
                <label><input type="checkbox" name="is_featured"> Featured Book</label>
                <label><input type="checkbox" name="is_digital"> Digital Book</label>
            </div>

            <button type="submit" name="add">Add Book</button>
        </form>

        <table>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Featured</th>
                <th>Digital</th>
                <th>Action</th>
            </tr>
            <?php while ($book = $books->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($book['title']); ?></td>
                    <td><?php echo htmlspecialchars($book['author']); ?></td>
                    <td>₱<?php echo number_format($book['price'], 2); ?></td>
                    <td><?php echo $book['stock']; ?></td>
                    <td><?php echo $book['is_featured'] ? '✅' : '❌'; ?></td>
                    <td><?php echo $book['is_digital'] ? '📱' : '📕'; ?></td>
                    <td><a href="?delete=<?php echo $book['book_id']; ?>">Delete</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

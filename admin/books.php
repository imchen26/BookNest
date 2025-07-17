<?php
require_once '/BookNest/includes/db.php';
$page_css = '/BookNest/css/books.css';

if ($_SESSION['role'] != 'admin') {
    header("Location: /BookNest/login.php");
    exit;
}

// Add/Edit/Delete
if (isset($_POST['add'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $price = $_POST['price'];
    $category_id = $_POST['category'];
    $stock = $_POST['stock'];
    $is_digital = isset($_POST['is_digital']) ? 1 : 0;

    $stmt = $conn->prepare("INSERT INTO books (title, author, price, category_id, stock, is_digital) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdiii", $title, $author, $price, $category_id, $stock, $is_digital);
    $stmt->execute();
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM books WHERE book_id = $id");
}

$categories = $conn->query("SELECT * FROM categories");
$books = $conn->query("SELECT * FROM books"); 
include '/BookNest/includes/header.php';
?>

<div class="container">
    <div class="books-container">
        <h2>📚 Manage Books</h2>
        <form method="post">
            <input type="text" name="title" placeholder="Title" required>
            <input type="text" name="author" placeholder="Author">
            <input type="number" step="0.01" name="price" placeholder="Price" required>
            <select name="category" required>
                <option value="">Select Category</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $cat['category_id']; ?>"><?php echo $cat['category_name']; ?></option>
                <?php endwhile; ?>
            </select>
            <input type="number" name="stock" placeholder="Stock" min="0">
            <label><input type="checkbox" name="is_digital"> Digital Book</label>
            <button type="submit" name="add">Add Book</button>
        </form>

        <table>
            <tr><th>Title</th><th>Author</th><th>Price</th><th>Stock</th><th>Action</th></tr>
            <?php while ($book = $books->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $book['title']; ?></td>
                    <td><?php echo $book['author']; ?></td>
                    <td>₱<?php echo $book['price']; ?></td>
                    <td><?php echo $book['stock']; ?></td>
                    <td><a href="?delete=<?php echo $book['book_id']; ?>">Delete</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<?php include '/BookNest/includes/footer.php'; ?>

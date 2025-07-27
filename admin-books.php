<?php  
require_once 'includes/db.php';
$page_css = '/BookNest/css/adminbooks.css';
include 'includes/header.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit;
}

// Add book
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

// Update book
if (isset($_POST['update'])) {
    $book_id = $_POST['book_id'];
    $title = $_POST['title'];
    $author = $_POST['author'];
    $price = $_POST['price'];
    $category_id = $_POST['category'];
    $stock = $_POST['stock'];
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $is_digital = isset($_POST['is_digital']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE books SET title=?, author=?, price=?, category_id=?, stock=?, is_featured=?, is_digital=? WHERE book_id=?");
    $stmt->bind_param("ssdiiiii", $title, $author, $price, $category_id, $stock, $is_featured, $is_digital, $book_id);
    $stmt->execute();
}

// Delete book
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM books WHERE book_id = $id");
}

// Edit mode (fetch book details)
$edit_book = null;
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];
    $result = $conn->query("SELECT * FROM books WHERE book_id = $id");
    $edit_book = $result->fetch_assoc();
}

$categories = $conn->query("SELECT * FROM categories");
$books = $conn->query("SELECT * FROM books"); 
?>

<div class="container">
    <div class="books-container">
        <h2>📚 Manage Books</h2>
        <form method="post" class="book-form">
            <input type="hidden" name="book_id" value="<?php echo $edit_book['book_id'] ?? ''; ?>">
            <input type="text" name="title" placeholder="Title" required value="<?php echo htmlspecialchars($edit_book['title'] ?? ''); ?>">
            <input type="text" name="author" placeholder="Author" value="<?php echo htmlspecialchars($edit_book['author'] ?? ''); ?>">
            <input type="number" step="0.01" name="price" placeholder="Price" required value="<?php echo $edit_book['price'] ?? ''; ?>">
            <select name="category" required>
                <option value="">Select Category</option>
                <?php 
                $categories->data_seek(0); 
                while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $cat['category_id']; ?>" <?php if (($edit_book['category_id'] ?? '') == $cat['category_id']) echo 'selected'; ?>>
                        <?php echo $cat['name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <input type="number" name="stock" placeholder="Stock" min="0" value="<?php echo $edit_book['stock'] ?? ''; ?>">

            <div class="checkbox-group">
                <label><input type="checkbox" name="is_featured" <?php if (($edit_book['is_featured'] ?? 0) == 1) echo 'checked'; ?>> Featured Book</label>
                <label><input type="checkbox" name="is_digital" <?php if (($edit_book['is_digital'] ?? 0) == 1) echo 'checked'; ?>> Digital Book</label>
            </div>

            <button type="submit" name="<?php echo $edit_book ? 'update' : 'add'; ?>">
                <?php echo $edit_book ? 'Update Book' : 'Add Book'; ?>
            </button>
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
                    <td><?php echo display_price($book['price']); ?></td>
                    <td><?php echo $book['stock']; ?></td>
                    <td><?php echo $book['is_featured'] ? '✅' : '❌'; ?></td>
                    <td><?php echo $book['is_digital'] ? '📱' : '📕'; ?></td>
                    <td>
                        <a href="?edit=<?php echo $book['book_id']; ?>">Edit</a> |
                        <a href="?delete=<?php echo $book['book_id']; ?>" onclick="return confirm('Are you sure you want to delete this book?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

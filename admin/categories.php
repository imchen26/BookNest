<?php
require_once '../includes/db.php';
$page_css = '/BookNest/css/categories.css';

if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_POST['add_category'])) {
    $name = $_POST['category_name'];
    $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $conn->query("DELETE FROM categories WHERE category_id = $id");
}

$result = $conn->query("SELECT * FROM categories"); 
include '../includes/header.php';
?>

<div class="container">
    <div class="categories-container">
        <h2>🛠️ Manage Categories</h2>
        <form method="post">
            <input type="text" name="category_name" placeholder="New Category" required>
            <button type="submit" name="add_category">Add</button>
        </form>

        <table>
            <tr><th>Category</th><th>Action</th></tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['category_name']; ?></td>
                    <td><a href="?delete=<?php echo $row['category_id']; ?>">Delete</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

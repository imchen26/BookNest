<?php
require_once '../includes/db.php';
$page_css = '../css/dashboard.css';
include '../includes/header.php';

if ($_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit;
} 
?>

<div class="container">
    <div class="dashboard-container">
        <h2>📊 Admin Dashboard</h2>
        <ul>
            <li><a href="books.php">Manage Books</a></li>
            <li><a href="categories.php">Manage Categories</a></li>
        </ul>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

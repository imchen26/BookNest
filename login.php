<?php
require_once '/BookNest/includes/db.php';
$page_css = '/BookNest/css/login.css'; 
$hideLayout = true; 
include '/BookNest/includes/header.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] == 'admin') {
                header("Location: /BookNest/admin/dashboard.php");
            } elseif ($row['role'] == 'staff') {
                header("Location: /BookNest/staff/orders.php");
            } else {
                header("Location: /BookNest/index.php");
            }
            exit;
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "User not found.";
    }
}
?>

<div class="login-wrapper">
    <div class="login-container">
        <h2>Login</h2>
        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" required>

            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>

            <button type="submit">Login</button>
        </form>
        <p class="register-link">Don't have an account? <a href="/BookNest/register.php">Sign up here</a>.</p>
    </div>
</div>

<?php include '/BookNest/includes/footer.php'; ?>

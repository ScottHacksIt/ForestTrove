<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $db  = get_db();
        $stmt = $db->prepare('SELECT ID, FirstName, Password, RoleID FROM User WHERE Username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['Password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id']    = $user['ID'];
            $_SESSION['first_name'] = $user['FirstName'];
            $_SESSION['role_id']    = $user['RoleID'];
            header('Location: admin/index.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    } else {
        $error = 'Please enter your username and password.';
    }
}

$page_title = 'Login';
require_once __DIR__ . '/includes/header.php';
?>

<main class="container narrow">
    <div class="auth-box">
        <div class="auth-icon">🌲</div>
        <h1>Admin Login</h1>
        <p class="auth-sub">Forest Trove caretakers only</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                       autocomplete="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       autocomplete="current-password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Enter the Forest</button>
        </form>
    </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

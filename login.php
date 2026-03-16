<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/connect.php';

startAppSession();
redirectIfAuthenticated();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $password = trim((string)filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW));

    if ($username === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $db->prepare('SELECT user_id AS id, username, password FROM users WHERE username = :username');
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            loginUser($user);
            $_SESSION['flash_success'] = 'Login successful. Welcome back!';
            header('Location: dashboard.php');
            exit();
        }

        $error = 'Invalid username or password.';
    }
}

include 'includes/header.php';
?>
<main class="catalog-page">
    <section class="catalog-shell auth-shell">
        <div class="card auth-card">
            <div class="card-body p-4 p-md-5">
                <p class="catalog-eyebrow mb-2">Account access</p>
                <h1 class="h3 mb-3">Login</h1>
                <p class="text-muted mb-4">Sign in to manage equipment, categories, and comments.</p>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post" class="auth-form" novalidate>
                    <div>
                        <label class="form-label" for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($username) ?>" required autocomplete="username">
                    </div>
                    <div>
                        <label class="form-label" for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>

                <p class="text-muted small mt-4 mb-0">
                    Need an account?
                    <a href="register.php">Register here</a>
                </p>
            </div>
        </div>
    </section>
</main>
<?php include 'includes/footer.php'; ?>

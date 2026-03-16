<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/connect.php';

startAppSession();
redirectIfAuthenticated();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$form = [
    'username' => '',
    'email' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form['username'] = trim((string)filter_input(INPUT_POST, 'username', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    $form['email'] = trim((string)filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $password = trim((string)filter_input(INPUT_POST, 'password', FILTER_UNSAFE_RAW));
    $confirmPassword = trim((string)filter_input(INPUT_POST, 'confirm_password', FILTER_UNSAFE_RAW));

    if ($form['username'] === '') {
        $error = 'Username is required.';
    } elseif (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    }

    if (!isset($error)) {
        $stmt = $db->prepare('SELECT username, email FROM users WHERE username = :username OR email = :email');
        $stmt->execute([
            ':username' => $form['username'],
            ':email' => $form['email'],
        ]);
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingUser) {
            if (($existingUser['username'] ?? '') === $form['username']) {
                $error = 'Username already exists.';
            } elseif (($existingUser['email'] ?? '') === $form['email']) {
                $error = 'Email already exists.';
            }
        }
    }

    if (!isset($error)) {
        $statement = $db->prepare('INSERT INTO users (username, email, password) VALUES (:username, :email, :password)');
        $created = $statement->execute([
            ':username' => $form['username'],
            ':email' => $form['email'],
            ':password' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        if ($created) {
            $_SESSION['flash_success'] = 'Account created successfully. Please log in.';
            header('Location: login.php');
            exit();
        }

        $error = 'An error occurred while registering the user.';
    }
}

include 'includes/header.php';
?>
<main class="catalog-page">
    <section class="catalog-shell auth-shell">
        <div class="card auth-card">
            <div class="card-body p-4 p-md-5">
                <p class="catalog-eyebrow mb-2">Create account</p>
                <h1 class="h3 mb-3">Register</h1>
                <p class="text-muted mb-4">Create an admin account to manage your catalog content.</p>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="post" class="auth-form" novalidate>
                    <div>
                        <label class="form-label" for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($form['username']) ?>" required autocomplete="username">
                    </div>
                    <div>
                        <label class="form-label" for="email">Email</label>
                        <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($form['email']) ?>" required autocomplete="email">
                    </div>
                    <div>
                        <label class="form-label" for="password">Password</label>
                        <input type="password" id="password" name="password" class="form-control" required autocomplete="new-password">
                    </div>
                    <div>
                        <label class="form-label" for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" required autocomplete="new-password">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Create account</button>
                </form>

                <p class="text-muted small mt-4 mb-0">
                    Already have an account?
                    <a href="login.php">Login here</a>
                </p>
            </div>
        </div>
    </section>
</main>
<?php include 'includes/footer.php'; ?>

<?php
session_start();
// Use public/read-only DB connection for viewing items
include __DIR__ . '/includes/db_public.php';
include('includes/header.php');

$equipment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch the equipment details
$stmt = $db_public->prepare("SELECT e.*, c.name AS category_name FROM equipment e LEFT JOIN categories c ON e.category_id = c.id WHERE e.equipment_id = :id");
$stmt->execute([':id' => $equipment_id]);
$equipment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$equipment) {
    echo "Item not found.";
    include('includes/footer.php');
    exit();
}

// Fetch comments for the equipment
$stmt = $db_public->prepare("SELECT * FROM comments WHERE equipment_id = :equipment_id ORDER BY comment_id DESC");
$stmt->execute([':equipment_id' => $equipment_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle comment submission with session-backed CAPTCHA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userCaptcha = trim($_POST['captcha'] ?? '');
    $stored = $_SESSION['captcha_code'] ?? '';

    if ($stored === '' || strcasecmp($userCaptcha, $stored) !== 0) {
        $error = "CAPTCHA verification failed. Please try again.";
    } else {
        $comment_text = filter_input(INPUT_POST, 'comment_text', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (!empty($comment_text)) {
            // Try to include the admin/write connection only when a write is required.
            $connectPath = __DIR__ . '/includes/connect.php';
            if (file_exists($connectPath)) {
                require_once $connectPath; // creates $db
            }

            // Use the write-enabled connection when available (connect.php provides $db). Fall back to public PDO.
            $writeDb = (isset($db) && $db instanceof PDO) ? $db : $db_public;
            $stmt = $writeDb->prepare("INSERT INTO comments (equipment_id, comment_text) VALUES (:equipment_id, :comment_text)");
            $stmt->execute([':equipment_id' => $equipment_id, ':comment_text' => $comment_text]);
            // clear captcha so it can't be reused
            unset($_SESSION['captcha_code']);
            header("Location: view_equipment.php?id=$equipment_id");
            exit();
        } else {
            $error = "Please enter a comment.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($equipment['name']) ?> - Details</title>
</head>
<body>
    <h1><?= htmlspecialchars($equipment['name']) ?></h1>
    <p><?= nl2br(htmlspecialchars($equipment['description'])) ?></p>
    <p>Price: $<?= htmlspecialchars($equipment['price']) ?></p>
    <?php if (!empty($equipment['category_name'])): ?>
        <p>Category: <?= htmlspecialchars($equipment['category_name']) ?></p>
    <?php endif; ?>

    <!-- Display Comments -->
    <h2>Comments</h2>
    <ul>
        <?php foreach ($comments as $comment): ?>
            <li><?= htmlspecialchars($comment['comment_text']) ?></li>
        <?php endforeach; ?>
    </ul>

    <!-- Comment Form -->
    <h3>Leave a Comment</h3>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <textarea name="comment_text" class="form-control" rows="4" required></textarea>
        </div>
        <div class="mb-3">
            <label for="captcha">Enter the text shown below</label>
            <br>
            <img src="captcha.php?rand=<?= rand(1000,9999) ?>" alt="CAPTCHA image">
        </div>
        <div class="mb-3">
            <input type="text" name="captcha" id="captcha" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>

    <a href="index.php">Back to Equipment List</a>
<?php include('includes/footer.php'); ?>
</body>
</html>
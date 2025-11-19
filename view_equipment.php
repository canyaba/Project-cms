<?php
session_start();
// Use public/read-only DB connection for viewing items
include __DIR__ . '/includes/db_public.php';
include('includes/header.php');

if (!function_exists('disemvowel_text')) {
    function disemvowel_text(string $text): string
    {
        return preg_replace('/[aeiou]/i', '', $text);
    }
}

$equipment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch the equipment details
$stmt = $db_public->prepare("SELECT e.*, c.name AS category_name FROM equipment e LEFT JOIN categories c ON e.category_id = c.category_id WHERE e.equipment_id = :id");
$stmt->execute([':id' => $equipment_id]);
$equipment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$equipment) {
    echo "Item not found.";
    include('includes/footer.php');
    exit();
}

// Fetch comments for the equipment (current schema has: comment_id, equipment_id, comment_text, user_name, created_at)
$stmt = $db_public->prepare("SELECT comment_id, comment_text, user_name, created_at FROM comments WHERE equipment_id = :equipment_id ORDER BY created_at DESC");
$stmt->execute([':equipment_id' => $equipment_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle comment submission with session-backed CAPTCHA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userCaptcha = trim($_POST['captcha'] ?? '');
    $stored = $_SESSION['captcha_code'] ?? '';

    if ($stored === '' || strcasecmp($userCaptcha, $stored) !== 0) {
        $error = "CAPTCHA verification failed. Please try again.";
    } else {
        $comment_text = trim($_POST['comment_text'] ?? '');
        $comment_text = filter_var($comment_text, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $commenter_name = trim($_POST['commenter_name'] ?? '');
        $commenter_name = filter_var($commenter_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if ($comment_text !== '' && $commenter_name !== '') {
            // Try to include the admin/write connection only when a write is required.
            $connectPath = __DIR__ . '/includes/connect.php';
            if (file_exists($connectPath)) {
                require_once $connectPath; // creates $db
            }

            // Use the write-enabled connection when available. Fall back to public PDO.
            $writeDb = (isset($db) && $db instanceof PDO) ? $db : $db_public;
            $stmt = $writeDb->prepare("INSERT INTO comments (equipment_id, comment_text, user_name) VALUES (:equipment_id, :comment_text, :user_name)");
            $stmt->execute([
                ':equipment_id' => $equipment_id,
                ':comment_text' => $comment_text,
                ':user_name' => $commenter_name
            ]);
            // clear captcha so it can't be reused
            unset($_SESSION['captcha_code']);
            header("Location: view_equipment.php?id=$equipment_id");
            exit();
        } else {
            $error = "Please provide both your name and a comment.";
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
    <div class="mb-3"><?= $equipment['description'] ?></div>
    <p>Price: $<?= htmlspecialchars($equipment['price']) ?></p>
    <?php if (!empty($equipment['category_name'])): ?>
        <p>Category: <?= htmlspecialchars($equipment['category_name']) ?></p>
    <?php endif; ?>

    <!-- Display Comments -->
    <h2>Comments</h2>
    <?php if ($comments): ?>
        <ul class="list-unstyled">
            <?php foreach ($comments as $comment): ?>
                <li class="mb-3">
                    <div><strong><?= htmlspecialchars($comment['user_name'] ?: 'Anonymous') ?>:</strong> <?= nl2br(htmlspecialchars($comment['comment_text'])) ?></div>
                    <small class="text-muted">Posted on <?= htmlspecialchars(date('Y-m-d H:i', strtotime($comment['created_at']))) ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p><em>No comments yet. Be the first to share your thoughts.</em></p>
    <?php endif; ?>

    <!-- Comment Form -->
    <h3>Leave a Comment</h3>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label for="commenter_name" class="form-label">Your name</label>
            <input type="text" name="commenter_name" id="commenter_name" class="form-control" maxlength="100" required>
        </div>
        <div class="mb-3">
            <label for="comment_text" class="form-label">Your comment</label>
            <textarea name="comment_text" id="comment_text" class="form-control" rows="4" required></textarea>
        </div>
        <div class="mb-3">
            <label for="captcha">Enter the text shown below</label>
            <br>
            <img
                src="captcha.php?rand=<?= rand(1000,9999) ?>"
                alt="CAPTCHA image"
                id="captcha-image-detail"
                class="mb-2"
            >
            <div>
                <button type="button" class="btn btn-link p-0 captcha-refresh" data-target="captcha-image-detail">Refresh CAPTCHA</button>
            </div>
        </div>
        <div class="mb-3">
            <input type="text" name="captcha" id="captcha" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>

    <a href="index.php">Back to Equipment List</a>
<?php include('includes/footer.php'); ?>
<script>
document.addEventListener('click', function (event) {
    if (!event.target.classList.contains('captcha-refresh')) {
        return;
    }
    event.preventDefault();
    var targetId = event.target.getAttribute('data-target');
    if (!targetId) {
        return;
    }
    var img = document.getElementById(targetId);
    if (img) {
        var base = img.getAttribute('data-base-src') || img.src.split('?')[0];
        img.setAttribute('data-base-src', base);
        img.src = base + '?rand=' + Date.now();
    }
});
</script>
</body>
</html>
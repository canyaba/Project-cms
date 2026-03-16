<?php
session_start();

include __DIR__ . '/includes/db_public.php';

$equipment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $db_public->prepare("SELECT e.*, c.name AS category_name FROM equipment e LEFT JOIN categories c ON e.category_id = c.category_id WHERE e.equipment_id = :id");
$stmt->execute([':id' => $equipment_id]);
$equipment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$equipment) {
    include 'includes/header.php';
    echo '<main class="catalog-page"><section class="catalog-shell"><div class="alert alert-info">Item not found.</div></section></main>';
    include 'includes/footer.php';
    exit();
}

$stmt = $db_public->prepare("SELECT comment_id, comment_text, created_at, user_name FROM comments WHERE equipment_id = :equipment_id ORDER BY created_at DESC");
$stmt->execute([':equipment_id' => $equipment_id]);
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userCaptcha = trim($_POST['captcha'] ?? '');
    $stored = $_SESSION['captcha_code'] ?? '';

    if ($stored === '' || strcasecmp($userCaptcha, $stored) !== 0) {
        $error = 'CAPTCHA verification failed. Please try again.';
    } else {
        $comment_text = trim($_POST['comment_text'] ?? '');
        $comment_text = filter_var($comment_text, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $commenter_name = trim($_POST['commenter_name'] ?? '');
        $commenter_name = filter_var($commenter_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if ($commenter_name === '') {
            $commenter_name = 'Guest';
        }

        if ($comment_text !== '') {
            $connectPath = __DIR__ . '/includes/connect.php';
            if (file_exists($connectPath)) {
                require_once $connectPath;
            }

            $writeDb = (isset($db) && $db instanceof PDO) ? $db : $db_public;
            $stmt = $writeDb->prepare("INSERT INTO comments (equipment_id, comment_text, user_name) VALUES (:equipment_id, :comment_text, :user_name)");
            $stmt->execute([
                ':equipment_id' => $equipment_id,
                ':comment_text' => $comment_text,
                ':user_name' => $commenter_name
            ]);
            unset($_SESSION['captcha_code']);
            header("Location: view_equipment.php?id=$equipment_id");
            exit();
        } else {
            $error = 'Please enter a comment.';
        }
    }
}

include 'includes/header.php';
?>
<main class="catalog-page">
    <section class="catalog-shell">
        <div class="row g-4 align-items-start">
            <div class="col-lg-5">
                <div class="card equipment-detail-card">
                    <div class="equipment-card__media equipment-detail__media">
                        <?php if (!empty($equipment['image_path'])): ?>
                            <img
                                src="<?= htmlspecialchars($equipment['image_path']) ?>"
                                alt="<?= htmlspecialchars($equipment['name']) ?> image"
                                class="equipment-card__image"
                            >
                        <?php else: ?>
                            <div class="equipment-card__placeholder" aria-hidden="true">No image</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-body">
                        <p class="catalog-eyebrow mb-2">Equipment details</p>
                        <h1 class="h3 mb-3"><?= htmlspecialchars($equipment['name']) ?></h1>
                        <p class="equipment-card__price mb-3">$<?= htmlspecialchars(number_format((float)$equipment['price'], 2)) ?></p>
                        <?php if (!empty($equipment['category_name'])): ?>
                            <p class="text-muted mb-3">Category: <?= htmlspecialchars($equipment['category_name']) ?></p>
                        <?php endif; ?>
                        <div class="mb-4"><?= $equipment['description'] ?></div>
                        <a href="index.php" class="btn btn-outline-light">Back to Equipment List</a>
                    </div>
                </div>
            </div>
        </div>

        <section class="mt-5">
            <h2 class="h4 mb-3">Comments</h2>
            <?php if ($comments): ?>
                <div class="card mb-4">
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <?php foreach ($comments as $comment): ?>
                                <?php $displayName = trim($comment['user_name'] ?? ''); ?>
                                <li class="mb-3">
                                    <div><strong><?= htmlspecialchars($displayName !== '' ? $displayName : 'Guest') ?>:</strong> <?= nl2br(htmlspecialchars($comment['comment_text'])) ?></div>
                                    <small class="text-muted">Posted on <?= htmlspecialchars(date('Y-m-d H:i', strtotime($comment['created_at']))) ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <p><em>No comments yet. Be the first to share your thoughts.</em></p>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <h3 class="h5 mb-3">Leave a Comment</h3>
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="commenter_name" class="form-label">Your name <span class="text-muted small">(optional)</span></label>
                            <input type="text" name="commenter_name" id="commenter_name" class="form-control" placeholder="e.g., Alex" maxlength="80">
                        </div>
                        <div class="mb-3">
                            <label for="comment_text" class="form-label">Your comment</label>
                            <textarea name="comment_text" id="comment_text" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="captcha" class="form-label">Enter the text shown below</label>
                            <div class="mt-2">
                                <img
                                    src="captcha.php?rand=<?= rand(1000, 9999) ?>"
                                    alt="CAPTCHA image"
                                    id="captcha-image-detail"
                                    class="mb-2"
                                >
                            </div>
                            <button type="button" class="btn btn-link p-0 captcha-refresh" data-target="captcha-image-detail">Refresh CAPTCHA</button>
                        </div>
                        <div class="mb-3">
                            <input type="text" name="captcha" id="captcha" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </section>
    </section>
</main>
<?php include 'includes/footer.php'; ?>
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

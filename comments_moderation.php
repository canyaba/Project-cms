<?php
session_start();
require 'includes/header.php';
require_once __DIR__ . '/includes/connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Note: current comments schema has no moderation flags/author_name.

$actionMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_id'], $_POST['moderation_action'])) {
    $commentId = (int)$_POST['comment_id'];
    $moderationAction = $_POST['moderation_action'];

    if ($commentId > 0) {
        try {
            if ($moderationAction === 'delete') {
                $stmt = $db->prepare('DELETE FROM comments WHERE comment_id = :id');
                $stmt->execute([':id' => $commentId]);
                $actionMessage = 'Comment deleted.';
            }
        } catch (PDOException $e) {
            $actionMessage = 'Moderation failed: ' . $e->getMessage();
        }
    }
}

$filterEquipment = isset($_GET['equipment_id']) ? (int)$_GET['equipment_id'] : 0;
$searchTerm = trim($_GET['q'] ?? '');

$query = "SELECT c.comment_id, c.equipment_id, c.comment_text, c.created_at, c.user_name,
                 e.name AS equipment_name
          FROM comments c
          INNER JOIN equipment e ON e.equipment_id = c.equipment_id";
$conditions = [];
$params = [];

if ($filterEquipment) {
    $conditions[] = 'c.equipment_id = :equipment_id';
    $params[':equipment_id'] = $filterEquipment;
}
if ($searchTerm !== '') {
    $conditions[] = '(c.comment_text LIKE :term OR e.name LIKE :term OR c.user_name LIKE :term)';
    $params[':term'] = '%' . $searchTerm . '%';
}

if ($conditions) {
    $query .= ' WHERE ' . implode(' AND ', $conditions);
}

$query .= ' ORDER BY c.created_at DESC LIMIT 100';

$stmt = $db->prepare($query);
foreach ($params as $param => $value) {
    $stmt->bindValue($param, $value, $param === ':equipment_id' ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h1>Moderate Comments</h1>

    <?php if ($actionMessage): ?>
        <div class="alert alert-info"><?= htmlspecialchars($actionMessage) ?></div>
    <?php endif; ?>

    <form class="row g-3 mb-4" method="get" action="">
        <div class="col-md-4">
            <label class="form-label" for="q">Keyword or author</label>
            <input type="text" id="q" name="q" class="form-control" value="<?= htmlspecialchars($searchTerm) ?>">
        </div>
        <div class="col-md-4">
            <label class="form-label" for="equipment_id">Equipment ID</label>
            <input type="number" id="equipment_id" name="equipment_id" class="form-control" value="<?= $filterEquipment ?: '' ?>">
        </div>
        <div class="col-md-4 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-primary">Apply filters</button>
            <a class="btn btn-secondary" href="comments_moderation.php">Reset</a>
        </div>
    </form>

    <?php if (!$comments): ?>
        <div class="alert alert-info">No comments found for the selected filters.</div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($comments as $comment): ?>
                <div class="list-group-item">
                    <div class="d-flex w-100 justify-content-between">
                        <h5 class="mb-1"><?= htmlspecialchars($comment['equipment_name']) ?></h5>
                        <small><?= htmlspecialchars(date('Y-m-d H:i', strtotime($comment['created_at']))) ?></small>
                    </div>
                    <?php $displayName = trim($comment['user_name'] ?? ''); ?>
                    <p class="mb-1"><strong><?= htmlspecialchars($displayName !== '' ? $displayName : 'Guest') ?>:</strong> <?= nl2br(htmlspecialchars($comment['comment_text'])) ?></p>
                    <form method="post" class="d-flex gap-2">
                        <input type="hidden" name="comment_id" value="<?= $comment['comment_id'] ?>">
                        <button type="submit" name="moderation_action" value="delete" class="btn btn-sm btn-danger" onclick="return confirm('Delete this comment?')">Delete</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require 'includes/footer.php';

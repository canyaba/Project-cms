<?php
session_start();

require __DIR__ . '/includes/db_public.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$reviewError = null;
$reviewsError = null;
$reviewForm = [
    'reviewer_name' => '',
    'rating' => 0,
    'review_text' => ''
];
$ratingLabels = [
    5 => 'Excellent',
    4 => 'Great',
    3 => 'Good',
    2 => 'Fair',
    1 => 'Poor'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_submit'])) {
    $reviewForm['reviewer_name'] = filter_var(trim($_POST['reviewer_name'] ?? ''), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $reviewForm['review_text'] = filter_var(trim($_POST['review_text'] ?? ''), FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $reviewForm['rating'] = (int)($_POST['rating'] ?? 0);

    $reviewerName = $reviewForm['reviewer_name'] !== '' ? $reviewForm['reviewer_name'] : 'Guest';
    $reviewText = $reviewForm['review_text'];

    if ($reviewForm['rating'] < 1 || $reviewForm['rating'] > 5) {
        $reviewError = 'Please select a rating between 1 and 5.';
    } elseif ($reviewText === '') {
        $reviewError = 'Please enter a review.';
    } else {
        $connectPath = __DIR__ . '/includes/connect.php';
        if (file_exists($connectPath)) {
            require_once $connectPath;
        }

        $writeDb = (isset($db) && $db instanceof PDO) ? $db : $db_public;

        try {
            $reviewStmt = $writeDb->prepare('INSERT INTO reviews (reviewer_name, rating, review_text) VALUES (:reviewer_name, :rating, :review_text)');
            $reviewStmt->execute([
                ':reviewer_name' => $reviewerName,
                ':rating' => $reviewForm['rating'],
                ':review_text' => $reviewText
            ]);
            $_SESSION['flash_success'] = 'Thanks for your review!';
            header('Location: index.php#reviews');
            exit();
        } catch (PDOException $e) {
            $reviewError = 'Unable to save your review right now.';
        }
    }
}

// Fetch categories for navigation and filtering
$catStmt = $db_public->query('SELECT category_id, name FROM categories ORDER BY name');
$categories = $catStmt->fetchAll(PDO::FETCH_ASSOC);

$sort_by = $_GET['sort'] ?? 'name';
$order = strtoupper($_GET['order'] ?? 'ASC');
$allowed_sorts = ['name', 'price', 'created_at', 'updated_at'];
if (!in_array($sort_by, $allowed_sorts, true)) {
    $sort_by = 'name';
}
if (!in_array($order, ['ASC', 'DESC'], true)) {
    $order = 'ASC';
}

$filterCategory = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$searchTerm = trim($_GET['q'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 6;

$conditions = [];
$params = [];
if ($filterCategory) {
    $conditions[] = 'e.category_id = :category_id';
    $params[':category_id'] = $filterCategory;
}
if ($searchTerm !== '') {
    $conditions[] = 'e.name LIKE :name_prefix';
    $params[':name_prefix'] = $searchTerm . '%';
}

$whereSql = $conditions ? ' WHERE ' . implode(' AND ', $conditions) : '';

$countSql = 'SELECT COUNT(*) FROM equipment e' . $whereSql;
$countStmt = $db_public->prepare($countSql);
foreach ($params as $param => $value) {
    $countStmt->bindValue($param, $value, $param === ':category_id' ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$countStmt->execute();
$totalRecords = (int)$countStmt->fetchColumn();

$totalPages = max(1, (int)ceil($totalRecords / $perPage));
if ($page > $totalPages) {
    $page = $totalPages;
}
$offset = ($page - 1) * $perPage;

$dataSql = "SELECT e.*, c.name AS category_name, COALESCE(cm.comment_count, 0) AS comment_count
            FROM equipment e
            LEFT JOIN categories c ON e.category_id = c.category_id
            LEFT JOIN (
                SELECT equipment_id, COUNT(*) AS comment_count
                FROM comments
                GROUP BY equipment_id
            ) cm ON cm.equipment_id = e.equipment_id"
            . $whereSql .
            " ORDER BY e." . $sort_by . ' ' . $order . "
              LIMIT :limit OFFSET :offset";

$stmt = $db_public->prepare($dataSql);
foreach ($params as $param => $value) {
    $stmt->bindValue($param, $value, $param === ':category_id' ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch recent comments for displayed equipment (up to 2 per equipment)
$recentCommentsByEq = [];
if ($equipment) {
    $ids = array_column($equipment, 'equipment_id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT equipment_id, comment_id, comment_text, created_at, user_name
            FROM comments
            WHERE equipment_id IN ($placeholders)
            ORDER BY equipment_id ASC, created_at DESC";
    $rcStmt = $db_public->prepare($sql);
    foreach ($ids as $i => $val) {
        $rcStmt->bindValue($i + 1, (int)$val, PDO::PARAM_INT);
    }
    $rcStmt->execute();
    while ($row = $rcStmt->fetch(PDO::FETCH_ASSOC)) {
        $eqId = (int)$row['equipment_id'];
        if (!isset($recentCommentsByEq[$eqId])) {
            $recentCommentsByEq[$eqId] = [];
        }
        if (count($recentCommentsByEq[$eqId]) < 2) {
            $recentCommentsByEq[$eqId][] = $row;
        }
    }
}

$reviews = [];
try {
    $reviewsStmt = $db_public->query('SELECT review_id, reviewer_name, rating, review_text, created_at FROM reviews ORDER BY created_at DESC LIMIT 6');
    $reviews = $reviewsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $reviewsError = 'Reviews are not available yet.';
}

function buildQueryString(array $overrides = []): string
{
    $params = array_merge($_GET, $overrides);
    foreach ($params as $key => $value) {
        if ($value === null || $value === '') {
            unset($params[$key]);
        }
    }
    return $params ? '?' . http_build_query($params) : '';
}

require 'includes/header.php';
?>
    <main class="col-lg-9" id="equipment">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-3 gap-3">
                <h1 class="h3 m-0">Equipment</h1>
                <form class="d-flex gap-2" method="get" action="" id="equipment-filter-form">
                    <input type="text" name="q" class="form-control" placeholder="Search equipment" value="<?= htmlspecialchars($searchTerm) ?>" autocomplete="off">
                    <select name="category" class="form-select" style="min-width: 180px;">
                        <option value="">All categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['category_id'] ?>" <?= $filterCategory === (int)$cat['category_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <select name="sort" class="form-select" style="min-width: 160px;">
                        <option value="name" <?= $sort_by === 'name' ? 'selected' : '' ?>>Sort by name</option>
                        <option value="price" <?= $sort_by === 'price' ? 'selected' : '' ?>>Sort by price</option>
                        <option value="created_at" <?= $sort_by === 'created_at' ? 'selected' : '' ?>>Newest first</option>
                        <option value="updated_at" <?= $sort_by === 'updated_at' ? 'selected' : '' ?>>Recently updated</option>
                    </select>
                    <input type="hidden" name="order" value="<?= $order ?>">
                </form>
            </div>

            <?php if ($totalRecords === 0): ?>
                <div class="alert alert-info">No equipment matched your filters.</div>
            <?php else: ?>
                <p class="text-muted">Showing <?= count($equipment) ?> of <?= $totalRecords ?> item(s).</p>
                <div class="row g-4">
                    <?php foreach ($equipment as $item): ?>
                        <div class="col-md-6">
                            <div class="card h-100 shadow-sm">
                                <?php if (!empty($item['image_path'])): ?>
                                    <img src="<?= htmlspecialchars($item['image_path']) ?>" class="card-img-top" alt="<?= htmlspecialchars($item['name']) ?> image">
                                <?php endif; ?>
                                <div class="card-body d-flex flex-column">
                                    <h2 class="h5"><?= htmlspecialchars($item['name']) ?></h2>
                                    <p class="text-muted mb-1"><strong>Category:</strong> <?= htmlspecialchars($item['category_name'] ?? 'Uncategorized') ?></p>
                                    <p class="text-muted mb-1"><strong>Price:</strong> $<?= htmlspecialchars(number_format((float)$item['price'], 2)) ?></p>
                                    <?php if (!empty($item['updated_at'])): ?>
                                        <p class="text-muted mb-3"><strong>Updated:</strong> <?= htmlspecialchars(date('Y-m-d', strtotime($item['updated_at']))) ?></p>
                                    <?php endif; ?>
                                    <p class="flex-grow-1"><?= htmlspecialchars(mb_strimwidth(strip_tags($item['description']), 0, 160, '…')) ?></p>
                                    <div class="mt-3 d-flex justify-content-between align-items-center">
                                        <a class="btn btn-sm btn-info" href="view_equipment.php?id=<?= $item['equipment_id'] ?>">View details</a>
                                        <span class="text-muted small">Comments: <?= (int)$item['comment_count'] ?></span>
                                    </div>
                                    <div class="mt-3">
                                        <?php $eqId = (int)$item['equipment_id']; $preview = $recentCommentsByEq[$eqId] ?? []; ?>
                                        <?php if ($preview): ?>
                                            <ul class="list-unstyled small mb-0">
                                                <?php foreach ($preview as $c): ?>
                                                    <?php $previewName = trim($c['user_name'] ?? ''); ?>
                                                    <li class="border-start ps-2 mb-1">
                                                        <span class="text-muted me-1">[<?= htmlspecialchars(date('Y-m-d', strtotime($c['created_at']))) ?>]</span>
                                                        <strong><?= htmlspecialchars($previewName !== '' ? $previewName : 'Guest') ?>:</strong>
                                                        <?= nl2br(htmlspecialchars(mb_strimwidth($c['comment_text'], 0, 80, '…'))) ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <p class="text-muted small mb-0"><em>No comments yet.</em></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Equipment pagination" class="mt-4">
                        <ul class="pagination">
                            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= htmlspecialchars(buildQueryString(['page' => $p])) ?>"><?= $p ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>

            <section class="mt-5" id="reviews">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-3 gap-2">
                    <h2 class="h4 m-0">Reviews</h2>
                    <span class="text-muted">Share your experience with FitGear.</span>
                </div>

                <?php if ($reviewError): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($reviewError) ?></div>
                <?php endif; ?>

                <div class="row g-4">
                    <div class="col-lg-7">
                        <?php if ($reviewsError): ?>
                            <div class="alert alert-info"><?= htmlspecialchars($reviewsError) ?></div>
                        <?php elseif ($reviews): ?>
                            <?php foreach ($reviews as $review): ?>
                                <?php $reviewName = trim($review['reviewer_name'] ?? ''); ?>
                                <?php $reviewRating = (int)($review['rating'] ?? 0); ?>
                                <div class="card shadow-sm mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong><?= htmlspecialchars($reviewName !== '' ? $reviewName : 'Guest') ?></strong>
                                            <small class="text-muted"><?= htmlspecialchars(date('Y-m-d', strtotime($review['created_at']))) ?></small>
                                        </div>
                                        <div class="text-warning mb-2">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="<?= $i <= $reviewRating ? 'fa-solid' : 'fa-regular' ?> fa-star"></i>
                                            <?php endfor; ?>
                                            <span class="text-muted ms-2"><?= $reviewRating ?>/5</span>
                                        </div>
                                        <p class="mb-0"><?= nl2br(htmlspecialchars($review['review_text'])) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted"><em>No reviews yet. Be the first to leave one.</em></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-lg-5">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h3 class="h5">Leave a review</h3>
                                <form method="post" action="index.php#reviews">
                                    <input type="hidden" name="review_submit" value="1">
                                    <div class="mb-3">
                                        <label class="form-label" for="reviewer_name">Your name <span class="text-muted small">(optional)</span></label>
                                        <input type="text" name="reviewer_name" id="reviewer_name" class="form-control" maxlength="80" value="<?= htmlspecialchars($reviewForm['reviewer_name']) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="rating">Rating</label>
                                        <select name="rating" id="rating" class="form-select" required>
                                            <option value="">Select a rating</option>
                                            <?php foreach ($ratingLabels as $value => $label): ?>
                                                <option value="<?= $value ?>" <?= (int)$reviewForm['rating'] === $value ? 'selected' : '' ?>><?= $value ?> - <?= htmlspecialchars($label) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" for="review_text">Review</label>
                                        <textarea name="review_text" id="review_text" class="form-control" rows="4" maxlength="500" required><?= htmlspecialchars($reviewForm['review_text']) ?></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Submit review</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>

<script src="js/mdb.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const filterForm = document.getElementById('equipment-filter-form');
    if (!filterForm) {
        return;
    }

    const searchInput = filterForm.querySelector('input[name="q"]');
    const selects = filterForm.querySelectorAll('select');
    let debounceTimer = null;

    const submitForm = () => {
        if (typeof filterForm.requestSubmit === 'function') {
            filterForm.requestSubmit();
        } else {
            filterForm.submit();
        }
    };

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(submitForm, 300);
        });
    }

    selects.forEach(function (select) {
        select.addEventListener('change', submitForm);
    });
});
</script>
<?php include 'includes/footer.php'; ?>

<?php
session_start();

require 'includes/header.php';
require __DIR__ . '/includes/db_public.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
    $conditions[] = '(e.name LIKE :term OR e.description LIKE :term)';
    $params[':term'] = '%' . $searchTerm . '%';
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
    $sql = "SELECT equipment_id, comment_id, comment_text, created_at
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
?>

<div class="container mt-4">
    <div class="row">
        <aside class="col-lg-3 mb-4">
            <h5 class="mb-3">Categories</h5>
            <ul class="list-group mb-4">
                <li class="list-group-item <?= $filterCategory ? '' : 'active' ?>">
                    <a class="text-decoration-none" href="<?= htmlspecialchars(buildQueryString(['category' => null, 'page' => 1])) ?>">All</a>
                </li>
                <?php foreach ($categories as $cat): ?>
                    <li class="list-group-item <?= ($filterCategory === (int)$cat['category_id']) ? 'active' : '' ?>">
                        <a class="text-decoration-none" href="<?= htmlspecialchars(buildQueryString(['category' => $cat['category_id'], 'page' => 1])) ?>">
                            <?= htmlspecialchars($cat['name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </aside>

    <main class="col-lg-9" id="equipment">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-3 gap-3">
                <h1 class="h3 m-0">Equipment</h1>
                <form class="d-flex gap-2" method="get" action="">
                    <input type="text" name="q" class="form-control" placeholder="Search equipment" value="<?= htmlspecialchars($searchTerm) ?>">
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
                    <button type="submit" class="btn btn-primary">Search</button>
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
                                                    <li class="border-start ps-2 mb-1">
                                                        <span class="text-muted me-1">[<?= htmlspecialchars(date('Y-m-d', strtotime($c['created_at']))) ?>]</span>
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
        </main>
    </div>
</div>

<script src="js/mdb.umd.min.js"></script>
<?php include 'includes/footer.php'; ?>
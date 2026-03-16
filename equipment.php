<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/connect.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

requireAuth();

$sort_by = $_GET['sort'] ?? 'name';
$order = strtoupper($_GET['order'] ?? 'ASC');
$allowed_sorts = ['name', 'price', 'created_at', 'updated_at'];
if (!in_array($sort_by, $allowed_sorts, true)) {
    $sort_by = 'name';
}
if (!in_array($order, ['ASC', 'DESC'], true)) {
    $order = 'ASC';
}

$query = "SELECT e.*, c.name AS category_name
          FROM equipment e
          LEFT JOIN categories c ON e.category_id = c.category_id
          ORDER BY e." . $sort_by . ' ' . $order;
$stmt = $db->prepare($query);
$stmt->execute();
$equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>
<main class="catalog-page" id="manage-equipment">
    <section class="catalog-shell">
        <div class="equipment-toolbar">
            <div>
                <p class="catalog-eyebrow mb-2">Equipment inventory</p>
                <h1 class="h3 m-0">Manage equipment</h1>
            </div>
            <div class="equipment-toolbar__actions">
                <form class="equipment-sort-form" method="get" action="">
                    <select name="sort" class="form-select">
                        <option value="name" <?= $sort_by === 'name' ? 'selected' : '' ?>>Sort by name</option>
                        <option value="price" <?= $sort_by === 'price' ? 'selected' : '' ?>>Sort by price</option>
                        <option value="created_at" <?= $sort_by === 'created_at' ? 'selected' : '' ?>>Sort by created date</option>
                        <option value="updated_at" <?= $sort_by === 'updated_at' ? 'selected' : '' ?>>Sort by updated date</option>
                    </select>
                    <select name="order" class="form-select">
                        <option value="ASC" <?= $order === 'ASC' ? 'selected' : '' ?>>Ascending</option>
                        <option value="DESC" <?= $order === 'DESC' ? 'selected' : '' ?>>Descending</option>
                    </select>
                </form>
                <a class="btn btn-primary" href="insert_equipment.php">Add New Equipment</a>
            </div>
        </div>

        <?php if (isset($_GET['created'])): ?>
            <div class="alert alert-success">Equipment created successfully.</div>
        <?php elseif (isset($_GET['updated'])): ?>
            <div class="alert alert-success">Equipment updated successfully.</div>
        <?php elseif (isset($_GET['deleted'])): ?>
            <div class="alert alert-info">Equipment deleted.</div>
        <?php endif; ?>

        <?php if (!$equipment): ?>
            <div class="alert alert-info">No equipment has been added yet.</div>
        <?php else: ?>
            <div class="catalog-results">
                <p class="text-muted mb-0">Showing <?= count($equipment) ?> item(s).</p>
            </div>

            <div class="equipment-grid" aria-label="Manage equipment listing">
                <?php foreach ($equipment as $item): ?>
                    <article class="equipment-card card shadow-sm">
                        <a class="equipment-card__link" href="view_equipment.php?id=<?= $item['equipment_id'] ?>" aria-label="View <?= htmlspecialchars($item['name']) ?>">
                            <div class="equipment-card__media">
                                <?php if (!empty($item['image_path'])): ?>
                                    <img
                                        src="<?= htmlspecialchars($item['image_path']) ?>"
                                        class="equipment-card__image"
                                        alt="<?= htmlspecialchars($item['name']) ?> image"
                                        loading="lazy"
                                    >
                                <?php else: ?>
                                    <div class="equipment-card__placeholder" aria-hidden="true">No image</div>
                                <?php endif; ?>
                            </div>
                            <div class="card-body equipment-card__body">
                                <h2 class="equipment-card__title"><?= htmlspecialchars($item['name']) ?></h2>
                                <p class="equipment-card__price">$<?= htmlspecialchars(number_format((float)$item['price'], 2)) ?></p>
                            </div>
                        </a>
                        <div class="equipment-card__actions">
                            <a class="btn btn-sm btn-primary" href="edit_equipment.php?id=<?= $item['equipment_id'] ?>">Edit</a>
                            <a class="btn btn-sm btn-info" href="view_equipment.php?id=<?= $item['equipment_id'] ?>">View</a>
                            <a class="btn btn-sm btn-danger" href="delete_equipment.php?id=<?= $item['equipment_id'] ?>" onclick="return confirm('Delete this equipment?')">Delete</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const sortForm = document.querySelector('.equipment-sort-form');
    if (!sortForm) {
        return;
    }

    sortForm.querySelectorAll('select').forEach(function (select) {
        select.addEventListener('change', function () {
            if (typeof sortForm.requestSubmit === 'function') {
                sortForm.requestSubmit();
            } else {
                sortForm.submit();
            }
        });
    });
});
</script>
<?php include 'includes/footer.php'; ?>

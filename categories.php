<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/connect.php';

requireAuth();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $name = trim((string)filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));

    if ($_POST['action'] === 'create' && $name !== '') {
        $stmt = $db->prepare('INSERT INTO categories (name) VALUES (?)');
        $stmt->execute([$name]);
        header('Location: categories.php?success=create');
        exit();
    }

    if ($_POST['action'] === 'update') {
        $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        if ($categoryId && $categoryId > 0 && $name !== '') {
            $stmt = $db->prepare('UPDATE categories SET name = ? WHERE category_id = ?');
            $stmt->execute([$name, $categoryId]);
            header('Location: categories.php?success=update');
            exit();
        }
    }
}

$stmt = $db->query('SELECT category_id, name FROM categories ORDER BY name');
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>
<main class="catalog-page">
    <section class="catalog-shell">
        <div class="equipment-toolbar">
            <div>
                <p class="catalog-eyebrow mb-2">Catalog structure</p>
                <h1 class="h3 m-0">Manage Categories</h1>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <?php if ($_GET['success'] === 'create'): ?>
                    Category created successfully.
                <?php elseif ($_GET['success'] === 'update'): ?>
                    Category updated successfully.
                <?php elseif ($_GET['success'] === 'delete'): ?>
                    Category deleted successfully.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Add New Category</h2>
                        <form method="post" class="auth-form">
                            <input type="hidden" name="action" value="create">
                            <div>
                                <label class="form-label" for="name">Category Name</label>
                                <input type="text" id="name" name="name" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Create Category</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h5 mb-3">Existing Categories</h2>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th class="w-50">Rename</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($category['name']) ?></td>
                                            <td>
                                                <form method="post" class="d-flex gap-2 align-items-center">
                                                    <input type="hidden" name="action" value="update">
                                                    <input type="hidden" name="category_id" value="<?= $category['category_id'] ?>">
                                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($category['name']) ?>" required>
                                                    <button type="submit" class="btn btn-sm btn-secondary">Save</button>
                                                </form>
                                            </td>
                                            <td>
                                                <a
                                                    href="delete_category.php?id=<?= $category['category_id'] ?>"
                                                    class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Delete this category? Pages assigned to it will lose the association.')"
                                                >
                                                    Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<?php include 'includes/footer.php'; ?>

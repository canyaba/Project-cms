<?php
session_start();
include("includes/header.php");
require_once __DIR__ . '/includes/connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) :
    header("Location: login.php");
    exit();
endif;

// Handle category create/update actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
    if ($_POST['action'] === 'create' && $name !== '') {
        $stmt = $db->prepare("INSERT INTO categories (name) VALUES (?)");
        $stmt->execute([$name]);
        header("Location: categories.php?success=create");
        exit();
    }

    if ($_POST['action'] === 'update') {
        $categoryId = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
        if ($categoryId > 0 && $name !== '') {
            $stmt = $db->prepare("UPDATE categories SET name = ? WHERE category_id = ?");
            $stmt->execute([$name, $categoryId]);
            header("Location: categories.php?success=update");
            exit();
        }
    }
}

// Fetch all categories
$stmt = $db->query("SELECT category_id, name FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - CMS</title>
    <link rel="stylesheet" href="css/mdb.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Manage Categories</h1>

        <?php if (isset($_GET['success'])) : ?>
            <div class="alert alert-success">
                <?php if ($_GET['success'] === 'create') : ?>Category created successfully!<?php elseif ($_GET['success'] === 'update') : ?>Category updated successfully!<?php elseif ($_GET['success'] === 'delete') : ?>Category deleted successfully!<?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Add New Category</h5>
                        <form method="POST">
                            <input type="hidden" name="action" value="create">
                            <div class="form-outline mb-3">
                                <input type="text" id="name" name="name" class="form-control" required>
                                <label class="form-label" for="name">Category Name</label>
                            </div>
                            <button type="submit" class="btn btn-primary">Create Category</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Existing Categories</h5>
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
                                    <?php foreach ($categories as $category) : ?>
                                        <tr>
                                            <td><?= htmlspecialchars($category['name']) ?></td>
                                            <td>
                                                <form method="POST" class="d-flex gap-2 align-items-center">
                                                    <input type="hidden" name="action" value="update">
                                                    <input type="hidden" name="category_id" value="<?= $category['category_id'] ?>">
                                                    <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($category['name']) ?>" required>
                                                    <button type="submit" class="btn btn-sm btn-secondary">Save</button>
                                                </form>
                                            </td>
                                            <td>
                                                <a href="delete_category.php?id=<?= $category['category_id'] ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Delete this category? Pages assigned to it will lose the association.')">Delete</a>
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
    </div>
    
    <script src="js/mdb.umd.min.js"></script>
</body>
</html>

<?php include("includes/footer.php"); ?>
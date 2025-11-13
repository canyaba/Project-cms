<?php
session_start();

include("includes/header.php");
require_once __DIR__ . '/includes/connect.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$name = '';
$description = '';
$category_id = null;

// Check if the user is logged in
if (!isset($_SESSION['user_id'])): 
    header("Location: login.php");
    exit();
endif;

if ($_SERVER['REQUEST_METHOD'] === 'POST'): 
    // Sanitize and validate user input
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);

    // Read selected category
    $category_id = isset($_POST['category']) && $_POST['category'] !== '' ? (int)$_POST['category'] : null;

    // Check if all fields are filled
    if ($name === '' || trim(strip_tags($description)) === '' || $price === false || $price <= 0): 
        $error = "Please fill in all fields correctly.";
    else:
        // Insert the new equipment into the database
        try {
            $stmt = $db->prepare("INSERT INTO equipment (name, description, price, category_id) VALUES (:name, :description, :price, :category_id)");
            $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':price' => $price,
                ':category_id' => $category_id
            ]);

            header("Location: equipment.php?created=1");
            exit();
        } catch (PDOException $e) {
            $error = "Error inserting equipment: " . $e->getMessage();
        }
    endif;
endif;
// Fetch categories for the form
$stmt = $db->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        tinymce.init({
            selector: '#description',
            menubar: false,
            plugins: 'link lists code',
            toolbar: 'undo redo | bold italic underline | bullist numlist | link | code',
            height: 300
        });
    });
</script>

<div class="container mt-4">
    <h1>Add New Equipment</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="" class="card p-3">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($name ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-control" required><?= $description ?? '' ?></textarea>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="price" class="form-label">Price</label>
                <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" value="<?= isset($_POST['price']) ? htmlspecialchars($_POST['price']) : '' ?>" required>
            </div>
            <div class="col-md-6">
                <label for="category" class="form-label">Category</label>
                <select name="category" id="category" class="form-select">
                    <option value="">-- Select category --</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['category_id'] ?>" <?= (isset($category_id) && $category_id === (int)$category['category_id']) ? 'selected' : '' ?>><?= htmlspecialchars($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Add Equipment</button>
            <a href="equipment.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include "includes/footer.php"; ?>
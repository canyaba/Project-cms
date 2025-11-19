<?php
session_start();

include("includes/header.php");
require_once __DIR__ . '/includes/connect.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Guarantee the equipment description column can store rich text without truncation errors.
 */
function ensureEquipmentDescriptionCapacity(PDO $db): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    $sql = "SELECT DATA_TYPE, CHARACTER_MAXIMUM_LENGTH
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'equipment'
              AND COLUMN_NAME = 'description'";
    $stmt = $db->query($sql);
    $column = $stmt->fetch();
    if (!$column) {
        return;
    }

    $isLimitedVarchar = strtolower($column['DATA_TYPE'] ?? '') === 'varchar'
        && (int)($column['CHARACTER_MAXIMUM_LENGTH'] ?? 0) < 1000;

    if ($isLimitedVarchar) {
        $db->exec("ALTER TABLE equipment MODIFY description TEXT NOT NULL");
    }
}

ensureEquipmentDescriptionCapacity($db);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])): 
    header("Location: login.php");
    exit();
endif;

// Get the equipment ID from the URL
$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($id === null): 
    header("Location: equipment.php"); // Redirect to the equipment list page
    exit();
endif;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST'): 
    if (isset($_POST['delete'])): 
        // Delete the equipment (category_id will be set to NULL on categories if FK present)
        $stmt = $db->prepare("DELETE FROM equipment WHERE equipment_id = :id");
        $stmt->execute([':id' => $id]);
        header("Location: equipment.php?deleted=1"); // Redirect to the equipment list page
        exit();
    else:
        // Update the equipment
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $category_id = isset($_POST['category']) && $_POST['category'] !== '' ? (int)$_POST['category'] : null;

        // Validate input
        if ($name === '' || trim(strip_tags($description)) === '' || $price === false || $price <= 0): 
            $error = "Please fill in all fields correctly.";
        else:
            try {
                $db->beginTransaction();

                $stmt = $db->prepare("UPDATE equipment SET name = :name, description = :description, price = :price, category_id = :category_id WHERE equipment_id = :id");
                $stmt->execute([
                    ':name' => $name,
                    ':description' => $description,
                    ':price' => $price,
                    ':category_id' => $category_id,
                    ':id' => $id
                ]);

                $db->commit();
                header("Location: equipment.php?updated=1"); // Redirect to the equipment list page
                exit();
            } catch (Exception $e) {
                if ($db->inTransaction()) $db->rollBack();
                $error = "Error updating equipment: " . $e->getMessage();
            }
        endif;
    endif;
endif;

// Fetch the equipment data
$stmt = $db->prepare("SELECT * FROM equipment WHERE equipment_id = :id");
$stmt->execute([':id' => $id]);
$equipment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$equipment): 
    header("Location: equipment.php"); // Redirect if the equipment does not exist
    exit();
endif;
// Fetch all categories and current assignments
$stmt = $db->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
$equipment_category_id = $equipment['category_id'] ?? null;
?>
<script src="https://cdn.tiny.cloud/1/g83w8n82we9iwazh48aifgcsvhacs4cq9060mnvn6ks6dqhn/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        tinymce.init({
            selector: '#description',
            menubar: false,
            plugins: [
                'lists', 'link', 'code', 'autolink', 'wordcount'
            ],
            toolbar: 'undo redo | bold italic underline | bullist numlist | link | code',
            height: 300
        });
    });
</script>

<div class="container mt-4">
    <h1>Edit Equipment</h1>
    <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" class="card p-3">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($equipment['name']) ?>" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea id="description" name="description" class="form-control" required><?= $equipment['description'] ?></textarea>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="price" class="form-label">Price</label>
                    <input type="number" id="price" name="price" step="0.01" value="<?= htmlspecialchars($equipment['price']) ?>" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="category" class="form-label">Category</label>
                    <select name="category" id="category" class="form-select">
                        <option value="">-- Select category --</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['category_id'] ?>" <?= ($equipment_category_id == $category['category_id']) ? 'selected' : '' ?>><?= htmlspecialchars($category['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Update Equipment</button>
                <button type="submit" name="delete" value="1" class="btn btn-danger" onclick="return confirm('Delete this equipment?')">Delete Equipment</button>
                <a href="equipment.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <?php include("includes/footer.php");
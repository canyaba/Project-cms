<?php
session_start();

require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/includes/image_upload.php';

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
        && (int)($column['CHARACTER_MAXIMUM_LENGTH'] ?? 0) < 1000; // Tiny threshold to catch legacy schema.

    if ($isLimitedVarchar) {
        $db->exec("ALTER TABLE equipment MODIFY description TEXT NOT NULL");
    }
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$name = '';
$description = '';
$category_id = null;

// Check if the user is logged in before output
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

ensureEquipmentDescriptionCapacity($db);
ensureEquipmentImageColumn($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST'):
    // Sanitize and validate user input
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);

    // Read selected category
    $category_id = isset($_POST['category']) && $_POST['category'] !== '' ? (int)$_POST['category'] : null;

    // Check if all fields are filled
    if ($name === '' || trim($description) === '' || $price === false || $price <= 0 || $category_id === null): 
        $error = "Please fill in all fields correctly, including selecting a category.";
    else:
        $imagePath = null;
        if (isset($_FILES['image']) && ($_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE && $_FILES['image']['name'] !== '') {
            try {
                $imagePath = processEquipmentImageUpload($_FILES['image']);
            } catch (RuntimeException $uploadException) {
                $error = $uploadException->getMessage();
            }
        }

        if (!isset($error)) {
            // Insert the new equipment into the database
            try {
                $stmt = $db->prepare("INSERT INTO equipment (name, description, price, category_id, comment_text, image_path) VALUES (:name, :description, :price, :category_id, :comment_text, :image_path)");
                $stmt->execute([
                    ':name' => $name,
                    ':description' => $description,
                    ':price' => $price,
                    ':category_id' => $category_id,
                    ':comment_text' => '',
                    ':image_path' => $imagePath
                ]);

                header('Location: equipment.php?created=1');
                exit();
            } catch (PDOException $e) {
                if (str_contains($e->getMessage(), 'Data too long')) {
                    $error = "Description is too long for the current database column. Please shorten it or contact support.";
                } else {
                    $error = "Error inserting equipment: " . $e->getMessage();
                }
            }
        }
    endif;
endif;
// Fetch categories for the form
$stmt = $db->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

include 'includes/header.php';
?>
<script src="https://cdn.tiny.cloud/1/g83w8n82we9iwazh48aifgcsvhacs4cq9060mnvn6ks6dqhn/tinymce/8/tinymce.min.js" referrerpolicy="origin" crossorigin="anonymous"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('equipment-form');
        const descriptionField = document.getElementById('description');
        const descriptionError = document.getElementById('description-error');

        tinymce.init({
            selector: '#description',
            menubar: false,
            plugins: [
                'lists', 'link', 'code', 'autolink', 'wordcount'
            ],
            toolbar: 'undo redo | bold italic underline | bullist numlist | link | code',
            height: 300,
            setup(editor) {
                editor.on('change input keyup', function () {
                    editor.save();
                    if (descriptionError) {
                        const hasContent = editor.getContent({ format: 'text' }).trim().length > 0;
                        descriptionError.classList.toggle('d-none', hasContent);
                    }
                });
            }
        });

        if (form && descriptionField) {
            form.addEventListener('submit', function (event) {
                const editor = tinymce.get('description');
                const content = editor ? editor.getContent({ format: 'text' }).trim() : descriptionField.value.trim();

                if (content === '') {
                    event.preventDefault();
                    if (descriptionError) {
                        descriptionError.classList.remove('d-none');
                        descriptionError.textContent = 'Description is required.';
                    }
                    if (editor) {
                        editor.focus();
                    } else {
                        descriptionField.focus();
                    }
                    return;
                }

                if (editor) {
                    descriptionField.value = editor.getContent();
                }
            });
        }
    });
</script>

<div class="container mt-4">
    <h1>Add New Equipment</h1>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" action="" class="card p-3" id="equipment-form" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="name" class="form-label">Name</label>
            <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($name ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea id="description" name="description" class="form-control"><?= htmlspecialchars($description ?? '') ?></textarea>
            <div id="description-error" class="text-danger small mt-1 d-none">Description is required.</div>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Equipment image <span class="text-muted small">(optional)</span></label>
            <input type="file" class="form-control" id="image" name="image" accept="image/*">
            <div class="form-text">JPEG, PNG, GIF, or WebP up to 5MB. Images are resized on upload.</div>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label for="price" class="form-label">Price</label>
                <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" value="<?= isset($_POST['price']) ? htmlspecialchars($_POST['price']) : '' ?>" required>
            </div>
            <div class="col-md-6">
                <label for="category" class="form-label">Category</label>
                <select name="category" id="category" class="form-select" required>
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
<?php
session_start();

include("includes/header.php");
include('includes/connect.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
        header("Location: equipment.php"); // Redirect to the equipment list page
        exit();
    else:
        // Update the equipment
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $description = $_POST['description']; // WYSIWYG content is not sanitized here
        $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
        $category_id = isset($_POST['category']) && $_POST['category'] !== '' ? (int)$_POST['category'] : null;

        // Validate input
        if (empty($name) || empty($description) || $price === false || $price <= 0): 
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
                header("Location: equipment.php"); // Redirect to the equipment list page
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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Equipment</title>
    <script src="https://cdn.tiny.cloud/1/fgwis09pz9s06ju92wbfo59j7upfo7xmcwjkvx2eoajw5v8e/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#description'
        });
    </script>
</head>
<body>
    <h1>Edit Equipment</h1>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" value="<?= htmlspecialchars($equipment['name']) ?>" required>
        <br>
        <label for="description">Description:</label>
        <textarea id="description" name="description" required><?= htmlspecialchars($equipment['description']) ?></textarea>
        <br>
        <label for="price">Price:</label>
        <input type="number" id="price" name="price" step="0.01" value="<?= htmlspecialchars($equipment['price']) ?>" required>
        <br>
        <div>
            <label for="category">Category</label>
            <select name="category" id="category" class="form-control">
                <option value="">-- Select category --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>" <?= ($equipment_category_id == $category['id']) ? 'selected' : '' ?>><?= htmlspecialchars($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <br>
        <button type="submit">Update Equipment</button>
        <button type="submit" name="delete" value="1">Delete Equipment</button>
    </form>
</body>
</html>

<?php
include("includes/footer.php");
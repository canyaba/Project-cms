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

if ($_SERVER['REQUEST_METHOD'] === 'POST'): 
    // Sanitize and validate user input
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);

    // Read selected category
    $category_id = isset($_POST['category']) && $_POST['category'] !== '' ? (int)$_POST['category'] : null;

    // Check if all fields are filled
    if (empty($name) || empty($description) || $price === false || $price <= 0): 
        $error = "Please fill in all fields correctly.";
    else:
        // Insert the new equipment into the database
        try {
            $db->beginTransaction();
            $stmt = $db->prepare("INSERT INTO equipment (name, description, price, category_id) VALUES (:name, :description, :price, :category_id)");
            $stmt->execute([
                ':name' => $name,
                ':description' => $description,
                ':price' => $price,
                ':category_id' => $category_id
            ]);

            $equipment_id = $db->lastInsertId();

            $db->commit();

            // Redirect to the equipment list page
            header("Location: equipment.php");
            exit();
        } catch (PDOException $e) {
            if ($db->inTransaction()) $db->rollBack();
            $error = "Error inserting equipment: " . $e->getMessage();
        }
    endif;
endif;
// Fetch categories for the form
$stmt = $db->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Equipment</title>
    <script src="https://cdn.tiny.cloud/1/fgwis09pz9s06ju92wbfo59j7upfo7xmcwjkvx2eoajw5v8e/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#description'
        });
    </script>
</head>
<body>
    <h1>Add New Equipment</h1>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST" action="">
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
        <br>
        <label for="description">Description:</label>
        <textarea id="description" name="description" required></textarea>
        <br>
        <label for="price">Price:</label>
        <input type="number" id="price" name="price" step="0.01" required>
        <br>
        <div>
            <label for="category">Category</label>
            <select name="category" id="category" class="form-control">
                <option value="">-- Select category --</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <br>
        <button type="submit">Add Equipment</button>
    </form>
</body>
</html>

<?php
include("includes/footer.php");
?>
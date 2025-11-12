<?php
session_start();
include("includes/header.php");
include("includes/connect.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) :
    header("Location: login.php");
    exit();
endif;

// Get page ID
$page_id = $_GET['id'] ?? null;
if (!$page_id) :
    header("Location: pages.php");
    exit();
endif;

// Fetch the page
$stmt = $db->prepare("SELECT * FROM pages WHERE id = ?");
$stmt->execute([$page_id]);
$page = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$page) :
    header("Location: pages.php");
    exit();
endif;

// Fetch all categories
$stmt = $db->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch current page categories
$stmt = $db->prepare("SELECT category_id FROM page_categories WHERE page_id = ?");
$stmt->execute([$page_id]);
$page_categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') :
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $slug = strtolower(str_replace(' ', '-', $title));
    $selected_categories = $_POST['categories'] ?? [];
    
    try {
        $db->beginTransaction();
        
        // Update the page
        $stmt = $db->prepare("UPDATE pages SET title = ?, content = ?, slug = ? WHERE id = ?");
        $stmt->execute([$title, $content, $slug, $page_id]);
        
        // Delete existing category assignments
        $stmt = $db->prepare("DELETE FROM page_categories WHERE page_id = ?");
        $stmt->execute([$page_id]);
        
        // Insert new category assignments
        if (!empty($selected_categories)) :
            $stmt = $db->prepare("INSERT INTO page_categories (page_id, category_id) VALUES (?, ?)");
            foreach ($selected_categories as $category_id) :
                $stmt->execute([$page_id, $category_id]);
            endforeach;
        endif;
        
        $db->commit();
        header("Location: pages.php?success=2");
        exit();
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Error updating page: " . $e->getMessage();
    }
endif;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Page - CMS</title>
    <link rel="stylesheet" href="css/mdb.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Edit Page</h1>
        
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="form-outline mb-4">
                        <input type="text" id="title" name="title" class="form-control" 
                               value="<?= htmlspecialchars($page['title']) ?>" required>
                        <label class="form-label" for="title">Page Title</label>
                    </div>
                    
                    <div class="form-outline mb-4">
                        <textarea id="content" name="content" class="form-control" rows="10" required><?= htmlspecialchars($page['content']) ?></textarea>
                        <label class="form-label" for="content">Page Content</label>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label d-block">Categories</label>
                        <?php foreach ($categories as $category) : ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="categories[]" 
                                       value="<?= $category['id'] ?>" 
                                       id="category<?= $category['id'] ?>"
                                       <?= in_array($category['id'], $page_categories) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="category<?= $category['id'] ?>">
                                    <?= htmlspecialchars($category['name']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Page</button>
                    <a href="pages.php" class="btn btn-link">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    
    <script src="js/mdb.umd.min.js"></script>
</body>
</html>

<?php include("includes/footer.php"); ?>
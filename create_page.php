<?php
session_start();
include("includes/header.php");
include("includes/connect.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) :
    header("Location: login.php");
    exit();
endif;

// Fetch all categories for the form
$stmt = $db->query("SELECT * FROM categories ORDER BY name");
$categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') :
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $slug = strtolower(str_replace(' ', '-', $title));
    $selected_categories = $_POST['categories'] ?? [];
    
    try {
        $db->beginTransaction();
        
        // Insert the page
        $stmt = $db->prepare("INSERT INTO pages (title, content, slug, user_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $content, $slug, $_SESSION['user_id']]);
        $page_id = $db->lastInsertId();
        
        // Insert category assignments
        if (!empty($selected_categories)) :
            $stmt = $db->prepare("INSERT INTO page_categories (page_id, category_id) VALUES (?, ?)");
            foreach ($selected_categories as $category_id) :
                $stmt->execute([$page_id, $category_id]);
            endforeach;
        endif;
        
        $db->commit();
        header("Location: pages.php?success=1");
        exit();
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Error creating page: " . $e->getMessage();
    }
endif;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Page - CMS</title>
    <link rel="stylesheet" href="css/mdb.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Create New Page</h1>
        
        <?php if (isset($error)) : ?>
            <div class="alert alert-danger">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="form-outline mb-4">
                        <input type="text" id="title" name="title" class="form-control" required>
                        <label class="form-label" for="title">Page Title</label>
                    </div>
                    
                    <div class="form-outline mb-4">
                        <textarea id="content" name="content" class="form-control" rows="10" required></textarea>
                        <label class="form-label" for="content">Page Content</label>
                    </div>
                    
                    <div class="mb-4">
                        <label class="form-label d-block">Categories</label>
                        <?php foreach ($categories as $category) : ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" 
                                       name="categories[]" 
                                       value="<?= $category['id'] ?>" 
                                       id="category<?= $category['id'] ?>">
                                <label class="form-check-label" for="category<?= $category['id'] ?>">
                                    <?= htmlspecialchars($category['name']) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Create Page</button>
                    <a href="pages.php" class="btn btn-link">Cancel</a>
                </form>
            </div>
        </div>
    </div>
    
    <script src="js/mdb.umd.min.js"></script>
</body>
</html>

<?php include("includes/footer.php"); ?>
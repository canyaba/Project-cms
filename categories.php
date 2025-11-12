<?php
session_start();
include("includes/header.php");
include("includes/connect.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) :
    header("Location: login.php");
    exit();
endif;

// Handle category creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) :
    if ($_POST['action'] === 'create') :
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        
        if (!empty($name)) :
            $stmt = $db->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
            $stmt->execute([$name, $description]);
            header("Location: categories.php?success=1");
            exit();
        endif;
    endif;
endif;

// Fetch all categories
$stmt = $db->query("SELECT * FROM categories ORDER BY name");
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
                Category has been created successfully!
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
                            <div class="form-outline mb-3">
                                <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                                <label class="form-label" for="description">Description</label>
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
                                        <th>Description</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($categories as $category) : ?>
                                        <tr>
                                            <td><?= htmlspecialchars($category['name']) ?></td>
                                            <td><?= htmlspecialchars($category['description']) ?></td>
                                            <td><?= date('Y-m-d', strtotime($category['created_at'])) ?></td>
                                            <td>
                                                <a href="edit_category.php?id=<?= $category['id'] ?>" 
                                                   class="btn btn-sm btn-primary">Edit</a>
                                                <a href="delete_category.php?id=<?= $category['id'] ?>" 
                                                   class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Are you sure? This will remove the category from all pages.')">Delete</a>
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
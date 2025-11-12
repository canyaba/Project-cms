<?php
session_start();
include("includes/header.php");
include("includes/connect.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) :
    header("Location: login.php");
    exit();
endif;

// Get sort parameters
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'title';
$order = isset($_GET['order']) ? $_GET['order'] : 'ASC';

// Validate sort parameters
$allowed_sorts = ['title', 'created_at', 'updated_at'];
if (!in_array($sort_by, $allowed_sorts)) :
    $sort_by = 'title';
endif;

// Prepare and execute query
$query = "SELECT p.*, GROUP_CONCAT(c.name) as categories 
          FROM pages p 
          LEFT JOIN page_categories pc ON p.id = pc.page_id 
          LEFT JOIN categories c ON pc.category_id = c.id 
          GROUP BY p.id 
          ORDER BY p.{$sort_by} {$order}";

$stmt = $db->prepare($query);
$stmt->execute();
$pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Pages - CMS</title>
    <link rel="stylesheet" href="css/mdb.min.css">
</head>
<body>
    <div class="container mt-4">
        <h1>Manage Pages</h1>
        
        <div class="mb-3">
            <a href="create_page.php" class="btn btn-primary">Create New Page</a>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>
                                    <a href="?sort=title&order=<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>">
                                        Title <?= $sort_by === 'title' ? ($order === 'ASC' ? '↑' : '↓') : '' ?>
                                    </a>
                                </th>
                                <th>Categories</th>
                                <th>
                                    <a href="?sort=created_at&order=<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>">
                                        Created <?= $sort_by === 'created_at' ? ($order === 'ASC' ? '↑' : '↓') : '' ?>
                                    </a>
                                </th>
                                <th>
                                    <a href="?sort=updated_at&order=<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>">
                                        Updated <?= $sort_by === 'updated_at' ? ($order === 'ASC' ? '↑' : '↓') : '' ?>
                                    </a>
                                </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pages as $page) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($page['title']) ?></td>
                                    <td><?= htmlspecialchars($page['categories'] ?? '') ?></td>
                                    <td><?= date('Y-m-d H:i', strtotime($page['created_at'])) ?></td>
                                    <td><?= date('Y-m-d H:i', strtotime($page['updated_at'])) ?></td>
                                    <td>
                                        <a href="edit_page.php?id=<?= $page['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <a href="view_page.php?id=<?= $page['id'] ?>" class="btn btn-sm btn-info">View</a>
                                        <a href="delete_page.php?id=<?= $page['id'] ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure you want to delete this page?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="js/mdb.umd.min.js"></script>
</body>
</html>

<?php include("includes/footer.php"); ?>
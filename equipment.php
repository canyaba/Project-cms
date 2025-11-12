<?php
session_start();

include("includes/header.php");
include('includes/connect.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle sorting with order direction
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'name';
$order = isset($_GET['order']) ? strtoupper($_GET['order']) : 'ASC';
$allowed_sorts = ['name', 'price', 'created_at', 'updated_at'];
if (!in_array($sort_by, $allowed_sorts)) {
    $sort_by = 'name';
}
if ($order !== 'ASC' && $order !== 'DESC') {
    $order = 'ASC';
}

// Fetch all equipment with categories (if any)
$query = "SELECT e.*, c.name AS category_name
          FROM equipment e
          LEFT JOIN categories c ON e.category_id = c.id
          ORDER BY e." . $sort_by . " " . $order;
$stmt = $db->prepare($query);
$stmt->execute();
$equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);

// (Optional) Handle comment submission — preserve existing behavior if used elsewhere
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['equipment_id'])) {
    $equipment_id = $_POST['equipment_id'];
    $comment_text = filter_input(INPUT_POST, 'comment_text', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (!empty($comment_text)) {
        $stmt = $db->prepare("INSERT INTO comments (equipment_id, comment_text) VALUES (:equipment_id, :comment_text)");
        $stmt->execute([':equipment_id' => $equipment_id, ':comment_text' => $comment_text]);
        header("Location: equipment.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment List</title>
</head>
<body>
    <h1>Equipment List</h1>
    <a class="btn btn-primary mb-3" href="insert_equipment.php">Add New Equipment</a>
    <div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>
                    <a href="?sort=name&order=<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>">Name <?= $sort_by === 'name' ? ($order === 'ASC' ? '↑' : '↓') : '' ?></a>
                </th>
                <th>
                    <a href="?sort=price&order=<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>">Price <?= $sort_by === 'price' ? ($order === 'ASC' ? '↑' : '↓') : '' ?></a>
                </th>
                <th>Categories</th>
                <th>
                    <a href="?sort=created_at&order=<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>">Created <?= $sort_by === 'created_at' ? ($order === 'ASC' ? '↑' : '↓') : '' ?></a>
                </th>
                <th>
                    <a href="?sort=updated_at&order=<?= $order === 'ASC' ? 'DESC' : 'ASC' ?>">Updated <?= $sort_by === 'updated_at' ? ($order === 'ASC' ? '↑' : '↓') : '' ?></a>
                </th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($equipment as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name']) ?></td>
                    <td><?= htmlspecialchars($item['price']) ?></td>
                    <td><?= htmlspecialchars($item['categories'] ?? '') ?></td>
                    <td><?= htmlspecialchars($item['created_at']) ?></td>
                    <td><?= htmlspecialchars($item['updated_at'] ?? '') ?></td>
                    <td>
                        <a class="btn btn-sm btn-primary" href="edit_equipment.php?id=<?= $item['equipment_id'] ?>">Edit</a>
                        <a class="btn btn-sm btn-info" href="view_equipment.php?id=<?= $item['equipment_id'] ?>">View</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</body>
</html>

<?php
include("includes/footer.php");
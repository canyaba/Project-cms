<?php 

// include DB connection first
include_once 'includes/connect.php';
include_once 'includes/header.php';

// Ensure $db exists and is a PDO instance
if (!isset($db) || !($db instanceof PDO)) {
    die('Database connection not found. Check includes/connect.php');
}

try {
    $stmt = $db->prepare("SELECT equipment_id, name, price FROM equipment ORDER BY name");
    $stmt->execute();
    $equipment = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Query error: ' . $e->getMessage());
}
?>


<h1>Welcome to the Equipment Store</h1>
<h2>Equipment List</h2>
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Price</th>
            <th>Details</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($equipment as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['name']) ?></td>
                <td><?= htmlspecialchars($item['price']) ?></td>
                <td><a href="view_equipment.php?id=<?= $item['equipment_id'] ?>">View Details</a></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include('includes/footer.php'); ?>

<a href="login.php" class="btn btn-primary btn-block">Sign In</a>
<a href="register.php" class="btn btn-primary btn-block">Register</a>

<?php 
include('includes/footer.php');

?>
<?php
session_start();

include("includes/header.php");
include("includes/connect.php");

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) :
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
endif;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <header>
        <h1>Dashboard</h1>
        <p>Here you can manage your content, users, and settings.</p>
        <nav>
            <ul>
                <li><a href="equipment.php">Manage Equipment</a></li>
                <li><a href="insert_equipment.php">New Equipment</a></li>
                <li><a href="edit_del_equipment.php">Update Equipment</a></li>
            </ul>
        </nav>
    </header>
    <main>
        <p>Welcome, <?= htmlspecialchars($_SESSION['username']) ?>!</p>
    </main>
    <footer>
        <p>&copy; <?= date("Y") ?> CMS. All rights reserved.</p>
    </footer>
</body>
</html>
<?php include("includes/footer.php"); ?>
<?php
session_start();
include("includes/connect.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) :
    header("Location: login.php");
    exit();
endif;

$category_id = $_GET['id'] ?? null;
if (!$category_id) :
    header("Location: categories.php");
    exit();
endif;

try {
    $db->beginTransaction();
    
    // Delete category associations first
    $stmt = $db->prepare("DELETE FROM page_categories WHERE category_id = ?");
    $stmt->execute([$category_id]);
    
    // Delete the category
    $stmt = $db->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$category_id]);
    
    $db->commit();
    header("Location: categories.php?success=2");
} catch (Exception $e) {
    $db->rollBack();
    header("Location: categories.php?error=" . urlencode($e->getMessage()));
}
?>
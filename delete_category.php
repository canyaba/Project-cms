<?php
session_start();
require_once __DIR__ . '/includes/connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) :
    header("Location: login.php");
    exit();
endif;

$category_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$category_id) :
    header("Location: categories.php");
    exit();
endif;

try {
    $db->beginTransaction();
    
    // Remove the category reference from equipment items before deleting it
    $stmt = $db->prepare("UPDATE equipment SET category_id = NULL WHERE category_id = ?");
    $stmt->execute([$category_id]);

    // Delete the category
    $stmt = $db->prepare("DELETE FROM categories WHERE category_id = ?");
    $stmt->execute([$category_id]);
    
    $db->commit();
        header("Location: categories.php?success=delete");
} catch (Exception $e) {
    $db->rollBack();
    header("Location: categories.php?error=" . urlencode($e->getMessage()));
}
?>
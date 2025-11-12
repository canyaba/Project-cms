<?php
session_start();
include("includes/connect.php");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) :
    header("Location: login.php");
    exit();
endif;

$page_id = $_GET['id'] ?? null;
if (!$page_id) :
    header("Location: pages.php");
    exit();
endif;

try {
    $db->beginTransaction();
    
    // Delete category associations first
    $stmt = $db->prepare("DELETE FROM page_categories WHERE page_id = ?");
    $stmt->execute([$page_id]);
    
    // Delete the page
    $stmt = $db->prepare("DELETE FROM pages WHERE id = ?");
    $stmt->execute([$page_id]);
    
    $db->commit();
    header("Location: pages.php?success=3");
} catch (Exception $e) {
    $db->rollBack();
    header("Location: pages.php?error=" . urlencode($e->getMessage()));
}
?>
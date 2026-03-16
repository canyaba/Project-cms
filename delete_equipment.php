<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/connect.php';
require_once __DIR__ . '/includes/image_upload.php';

requireAuth();

$equipmentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($equipmentId <= 0) {
    header('Location: equipment.php');
    exit();
}

try {
    $stmt = $db->prepare('SELECT image_path FROM equipment WHERE equipment_id = :id');
    $stmt->execute([':id' => $equipmentId]);
    $imagePath = $stmt->fetchColumn();

    $stmt = $db->prepare('DELETE FROM equipment WHERE equipment_id = :id');
    $stmt->execute([':id' => $equipmentId]);

    if ($imagePath) {
        deleteEquipmentImage($imagePath);
    }
} catch (PDOException $e) {
    error_log('Failed to delete equipment: ' . $e->getMessage());
}

header('Location: equipment.php?deleted=1');
exit();

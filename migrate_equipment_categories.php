<?php
// One-time migration helper: migrate many-to-many assignments from equipment_categories
// into a single category_id column on equipment.
// Run this once from the command line or via the browser (when trusted) and delete it afterwards.

require_once __DIR__ . '/includes/connect.php';

try {
    // Check if equipment_categories table exists
    $stmt = $db->prepare("SELECT COUNT(*) AS cnt FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'equipment_categories'");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row || $row['cnt'] == 0) {
        echo "No equipment_categories table found; nothing to migrate.";
        exit();
    }

    // For each equipment, pick the smallest category_id (or any) and assign it to equipment.category_id
    $sql = "UPDATE equipment e
            JOIN (
                SELECT equipment_id, MIN(category_id) AS category_id
                FROM equipment_categories
                GROUP BY equipment_id
            ) ec ON e.equipment_id = ec.equipment_id
            SET e.category_id = ec.category_id";
    $affected = $db->exec($sql);

    echo "Migration complete. Rows updated: " . $affected . "\n";

    // Optionally drop the equipment_categories table
    // $db->exec("DROP TABLE IF EXISTS equipment_categories");
    // echo "Dropped equipment_categories table.\n";

} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
}

?>
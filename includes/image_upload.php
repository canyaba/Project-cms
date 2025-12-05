<?php

/**
 * Ensure the equipment table has an image_path column available.
 */
function ensureEquipmentImageColumn(PDO $db): void
{
    static $checked = false;
    if ($checked) {
        return;
    }
    $checked = true;

    $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'equipment'
              AND COLUMN_NAME = 'image_path'";
    $stmt = $db->query($sql);
    if ($stmt->fetch()) {
        return;
    }

    $db->exec("ALTER TABLE equipment ADD COLUMN image_path VARCHAR(255) NULL AFTER comment_text");
}

function getUploadsDirectory(): string
{
    $uploadsDir = dirname(__DIR__) . '/uploads';
    if (!is_dir($uploadsDir)) {
        mkdir($uploadsDir, 0775, true);
    }
    return $uploadsDir;
}

/**
 * Handle equipment image upload, validate, resize, and save.
 *
 * @return string Relative file path (e.g., 'uploads/foo.jpg').
 * @throws RuntimeException if validation fails.
 */
function processEquipmentImageUpload(array $file): string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE || empty($file['name'])) {
        throw new RuntimeException('No image uploaded.');
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed with error code ' . $file['error']);
    }

    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('Image must be 5MB or smaller.');
    }

    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        throw new RuntimeException('Please upload a valid image.');
    }

    $mime = $imageInfo['mime'];
    $supported = [
        'image/jpeg' => 'jpeg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    if (!isset($supported[$mime])) {
        throw new RuntimeException('Unsupported image type. Use JPG, PNG, GIF, or WebP.');
    }

    [$width, $height] = $imageInfo;
    $maxDimension = 1200;
    $scale = min($maxDimension / $width, $maxDimension / $height, 1);
    $newWidth = (int) max(1, $width * $scale);
    $newHeight = (int) max(1, $height * $scale);

    switch ($mime) {
        case 'image/jpeg':
            $src = imagecreatefromjpeg($file['tmp_name']);
            break;
        case 'image/png':
            $src = imagecreatefrompng($file['tmp_name']);
            break;
        case 'image/gif':
            $src = imagecreatefromgif($file['tmp_name']);
            break;
        case 'image/webp':
            $src = imagecreatefromwebp($file['tmp_name']);
            break;
        default:
            throw new RuntimeException('Unsupported image type.');
    }

    if (!$src) {
        throw new RuntimeException('Failed to read uploaded image.');
    }

    $dst = imagecreatetruecolor($newWidth, $newHeight);

    if (in_array($mime, ['image/png', 'image/gif', 'image/webp'], true)) {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        $transparent = imagecolorallocatealpha($dst, 0, 0, 0, 127);
        imagefilledrectangle($dst, 0, 0, $newWidth, $newHeight, $transparent);
    }

    imagecopyresampled($dst, $src, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

    $uploadsDir = getUploadsDirectory();
    $filename = 'equipment_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $supported[$mime];
    $targetPath = $uploadsDir . '/' . $filename;

    switch ($mime) {
        case 'image/jpeg':
            imagejpeg($dst, $targetPath, 85);
            break;
        case 'image/png':
            imagepng($dst, $targetPath, 7);
            break;
        case 'image/gif':
            imagegif($dst, $targetPath);
            break;
        case 'image/webp':
            imagewebp($dst, $targetPath, 85);
            break;
    }

    imagedestroy($src);
    imagedestroy($dst);

    return 'uploads/' . $filename;
}

function deleteEquipmentImage(?string $relativePath): void
{
    if (!$relativePath) {
        return;
    }
    $fullPath = dirname(__DIR__) . '/' . ltrim($relativePath, '/');
    if (is_file($fullPath)) {
        @unlink($fullPath);
    }
}

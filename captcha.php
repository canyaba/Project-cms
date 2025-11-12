<?php
session_start();

// CAPTCHA settings
$width = 140;
$height = 45;
$length = 5;
$font_size = 20;

// Generate random code (exclude easily confused characters)
$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$code = '';
for ($i = 0; $i < $length; $i++) {
    $code .= $chars[random_int(0, strlen($chars) - 1)];
}

// Store in session
$_SESSION['captcha_code'] = $code;

// Create image
$image = imagecreatetruecolor($width, $height);
$bg_color = imagecolorallocate($image, 255, 255, 255);
$text_color = imagecolorallocate($image, 0, 0, 0);
$noise_color = imagecolorallocate($image, 120, 120, 120);

imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

// Add noise - lines
for ($i = 0; $i < 6; $i++) {
    $x1 = random_int(0, $width);
    $y1 = random_int(0, $height);
    $x2 = random_int(0, $width);
    $y2 = random_int(0, $height);
    imageline($image, $x1, $y1, $x2, $y2, $noise_color);
}

// Add noise - dots
for ($i = 0; $i < 80; $i++) {
    imagesetpixel($image, random_int(0, $width), random_int(0, $height), $noise_color);
}

// Try to use a TTF font if available
$fontFile = __DIR__ . '/fonts/arial.ttf';
if (!file_exists($fontFile)) {
    // Try common Windows font path
    $winFont = 'C:\\Windows\\Fonts\\arial.ttf';
    if (file_exists($winFont)) $fontFile = $winFont; else $fontFile = null;
}

// Write the characters with slight random rotation and position
$spacing = ($width - 20) / $length;
for ($i = 0; $i < strlen($code); $i++) {
    $char = $code[$i];
    $angle = random_int(-20, 20);
    $x = 10 + $i * $spacing + random_int(-2, 4);
    $y = ($height / 2) + ($font_size / 2) + random_int(-4, 4);
    if ($fontFile) {
        imagettftext($image, $font_size, $angle, (int)$x, (int)$y, $text_color, $fontFile, $char);
    } else {
        // Fallback: use built-in font (no rotation)
        imagestring($image, 5, (int)$x, (int)($height/4), $char, $text_color);
    }
}

// Output image
header('Content-Type: image/png');
// Disable caching so captcha refreshes
header('Cache-Control: no-cache, must-revalidate');
imagepng($image);
imagedestroy($image);

// Note: captcha code is stored in session as 'captcha_code' for verification

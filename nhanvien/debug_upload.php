<?php
// File debug - Hiển thị lỗi
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== DEBUG UPLOAD ===<br><br>";

echo "<strong>1. POST Data:</strong><br>";
echo "Has FILES: " . (isset($_FILES) ? 'YES' : 'NO') . "<br>";
if (isset($_FILES['file'])) {
    echo "File name: " . $_FILES['file']['name'] . "<br>";
    echo "File size: " . $_FILES['file']['size'] . "<br>";
    echo "File error: " . $_FILES['file']['error'] . "<br>";
    echo "File tmp: " . $_FILES['file']['tmp_name'] . "<br>";
}
echo "<br>";

echo "<strong>2. Upload Dir:</strong><br>";
$upload_dir = __DIR__ . '/../img/blog/';
echo "Path: " . $upload_dir . "<br>";
echo "Exists: " . (is_dir($upload_dir) ? 'YES' : 'NO') . "<br>";
echo "Writable: " . (is_writable($upload_dir) ? 'YES' : 'NO') . "<br>";
echo "<br>";

// Include file chính để test
echo "<strong>3. Testing main upload file:</strong><br>";
ob_start();
include(__DIR__ . '/upload_image.php');
$output = ob_get_clean();
echo "Output: <pre>" . htmlspecialchars($output) . "</pre>";

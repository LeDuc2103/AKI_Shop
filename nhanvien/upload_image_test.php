<?php
// File test upload - KHÔNG KIỂM TRA SESSION
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

ob_clean();
header('Content-Type: application/json; charset=utf-8');

error_log('=== UPLOAD TEST START ===');

// Thư mục lưu ảnh
$upload_dir = dirname(__FILE__) . '/../img/blog/';

// Tạo thư mục nếu chưa có
if (!is_dir($upload_dir)) {
    @mkdir($upload_dir, 0755, true);
}

// Kiểm tra có file upload không
if (!isset($_FILES['file'])) {
    ob_clean();
    echo json_encode(array('error' => 'Không có file được gửi lên'));
    exit();
}

if ($_FILES['file']['error'] != UPLOAD_ERR_OK) {
    ob_clean();
    echo json_encode(array('error' => 'Lỗi upload file: ' . $_FILES['file']['error']));
    exit();
}

$file_tmp = $_FILES['file']['tmp_name'];
$file_name = $_FILES['file']['name'];
$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
$allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'webp');

// Kiểm tra định dạng file
if (!in_array($file_ext, $allowed_ext)) {
    ob_clean();
    echo json_encode(array('error' => 'Định dạng file không hợp lệ'));
    exit();
}

// Tạo tên file unique
$new_file_name = 'test_' . time() . '_' . uniqid() . '.' . $file_ext;
$upload_path = $upload_dir . $new_file_name;

error_log('Trying to upload to: ' . $upload_path);

// Upload file
if (move_uploaded_file($file_tmp, $upload_path)) {
    $file_url = 'img/blog/' . $new_file_name;
    
    error_log('Upload SUCCESS: ' . $file_url);
    
    ob_clean();
    echo json_encode(array('location' => $file_url));
} else {
    error_log('Upload FAILED');
    error_log('Dir exists: ' . (is_dir($upload_dir) ? 'YES' : 'NO'));
    error_log('Dir writable: ' . (is_writable($upload_dir) ? 'YES' : 'NO'));
    
    ob_clean();
    echo json_encode(array('error' => 'Không thể lưu file'));
}

ob_end_flush();

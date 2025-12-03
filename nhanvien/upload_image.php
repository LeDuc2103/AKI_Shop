<?php
// File: nhanvien/upload_image.php
// Xử lý upload hình ảnh cho TinyMCE editor

session_start();

// Đặt header JSON
header('Content-Type: application/json');

// Kiểm tra quyền
if (!isset($_SESSION['nhanvien_logged_in']) || $_SESSION['nhanvien_logged_in'] != true) {
    http_response_code(403);
    echo json_encode(array('error' => 'Bạn chưa đăng nhập'));
    exit();
}

// Thư mục lưu ảnh
$upload_dir = '../img/blog/';

// Tạo thư mục nếu chưa có
if (!is_dir($upload_dir)) {
    @mkdir($upload_dir, 0755, true);
}

// Kiểm tra có file upload không
if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(array('error' => 'Không có file được gửi lên'));
    exit();
}

if ($_FILES['file']['error'] != UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(array('error' => 'Lỗi upload file: ' . $_FILES['file']['error']));
    exit();
}

$file_tmp = $_FILES['file']['tmp_name'];
$file_name = $_FILES['file']['name'];
$file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
$allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'webp');

// Kiểm tra định dạng file
if (!in_array($file_ext, $allowed_ext)) {
    http_response_code(400);
    echo json_encode(array('error' => 'Định dạng file không hợp lệ. Chỉ chấp nhận: jpg, jpeg, png, gif, webp'));
    exit();
}

// Kiểm tra kích thước file (max 5MB)
$max_size = 5 * 1024 * 1024; // 5MB
if ($_FILES['file']['size'] > $max_size) {
    http_response_code(400);
    echo json_encode(array('error' => 'File quá lớn. Kích thước tối đa: 5MB'));
    exit();
}

// Tạo tên file unique
$new_file_name = 'content_' . time() . '_' . uniqid() . '.' . $file_ext;
$upload_path = $upload_dir . $new_file_name;

// Upload file
if (move_uploaded_file($file_tmp, $upload_path)) {
    // Trả về đường dẫn file
    $file_url = 'img/blog/' . $new_file_name;
    
    http_response_code(200);
    echo json_encode(array('location' => $file_url));
} else {
    http_response_code(500);
    echo json_encode(array('error' => 'Không thể lưu file lên server'));
}
?>

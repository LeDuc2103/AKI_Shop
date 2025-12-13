<?php
// File: nhanvien/upload_image.php
// Xử lý upload hình ảnh cho TinyMCE editor

// Bắt mọi lỗi
try {
    // Tắt output buffering và error display để tránh làm hỏng JSON
    ob_start();
    error_reporting(0);
    ini_set('display_errors', 0);
    
    // Xóa bất kỳ output nào trước đó
    ob_clean();
    
    // Đặt header JSON
    header('Content-Type: application/json; charset=utf-8');
    
    // Log để debug (vào file log, không output ra màn hình)
    error_log('Upload image request received');
    
    // Thư mục lưu ảnh - sử dụng đường dẫn tuyệt đối
    $upload_dir = dirname(__FILE__) . '/../img/blog/';
    
    // Tạo thư mục nếu chưa có
    if (!is_dir($upload_dir)) {
        if (!@mkdir($upload_dir, 0777, true)) {
            throw new Exception('Không thể tạo thư mục upload');
        }
    }
    
    // Kiểm tra có file upload không
    if (!isset($_FILES['file'])) {
        throw new Exception('Không có file được gửi lên');
    }
    
    if ($_FILES['file']['error'] != UPLOAD_ERR_OK) {
        throw new Exception('Lỗi upload file: ' . $_FILES['file']['error']);
    }
    
    $file_tmp = $_FILES['file']['tmp_name'];
    $file_name = $_FILES['file']['name'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    
    // Kiểm tra định dạng file
    if (!in_array($file_ext, $allowed_ext)) {
        throw new Exception('Định dạng file không hợp lệ');
    }
    
    // Kiểm tra kích thước file (max 5MB)
    $max_size = 5 * 1024 * 1024;
    if ($_FILES['file']['size'] > $max_size) {
        throw new Exception('File quá lớn');
    }
    
    // Tạo tên file unique
    $new_file_name = 'content_' . time() . '_' . uniqid() . '.' . $file_ext;
    $upload_path = $upload_dir . $new_file_name;
    
    error_log('Trying to upload to: ' . $upload_path);
    
    // Upload file
    if (!move_uploaded_file($file_tmp, $upload_path)) {
        error_log('Upload failed - Source: ' . $file_tmp . ', Dest: ' . $upload_path);
        error_log('Upload dir exists: ' . (is_dir($upload_dir) ? 'yes' : 'no'));
        error_log('Upload dir writable: ' . (is_writable($upload_dir) ? 'yes' : 'no'));
        throw new Exception('Không thể lưu file lên server');
    }
    
    // Trả về đường dẫn file
    $file_url = 'img/blog/' . $new_file_name;
    
    error_log('Upload SUCCESS: ' . $file_url);
    
    ob_clean();
    echo json_encode(array('location' => $file_url));
    
} catch (Exception $e) {
    error_log('Upload ERROR: ' . $e->getMessage());
    ob_clean();
    http_response_code(500);
    echo json_encode(array('error' => $e->getMessage()));
}

ob_end_flush();


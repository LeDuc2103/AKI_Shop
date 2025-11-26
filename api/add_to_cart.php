<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// Import database connection
require_once '../config/database.php';

// Khởi tạo response
$response = array('status' => 'error', 'message' => 'Có lỗi xảy ra');

try {
    // Kết nối database
    $db = new Database();
    $conn = $db->getConnection();
    
    // Kiểm tra method POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response['message'] = 'Phương thức không hợp lệ';
        echo json_encode($response);
        exit;
    }
    
    // Lấy dữ liệu từ POST
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
    $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
    $color_id = isset($_POST['color_id']) ? (int)$_POST['color_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    // Validation
    if ($user_id <= 0) {
        $response['message'] = 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng';
        echo json_encode($response);
        exit;
    }
    
    if ($product_id <= 0) {
        $response['message'] = 'Sản phẩm không hợp lệ';
        echo json_encode($response);
        exit;
    }
    
    if ($quantity <= 0) {
        $response['message'] = 'Số lượng không hợp lệ';
        echo json_encode($response);
        exit;
    }
    
    // 1. Kiểm tra sản phẩm tồn tại
    $sql_check_product = "SELECT id_sanpham, ten_sanpham, gia, gia_khuyen_mai FROM san_pham WHERE id_sanpham = ?";
    $stmt = $conn->prepare($sql_check_product);
    $stmt->execute(array($product_id));
    $product = $stmt->fetch();
    
    if (!$product) {
        $response['message'] = 'Sản phẩm không tồn tại';
        echo json_encode($response);
        exit;
    }
    
    // 2. Lấy giá sản phẩm
    // Ưu tiên: giá từ bảng san_pham_mau_sac (nếu có) > gia_khuyen_mai > gia gốc
    $final_price = $product['gia']; // Giá mặc định
    
    if ($color_id > 0) {
        // Kiểm tra xem có giá riêng cho màu sắc này không
        $sql_color_price = "SELECT gia FROM san_pham_mau_sac WHERE id_sanpham = ? AND mau_sac_id = ?";
        $stmt_color = $conn->prepare($sql_color_price);
        $stmt_color->execute(array($product_id, $color_id));
        $color_variant = $stmt_color->fetch();
        
        if ($color_variant && $color_variant['gia'] > 0) {
            $final_price = $color_variant['gia']; // Ưu tiên giá theo màu
        } else if ($product['gia_khuyen_mai'] > 0) {
            $final_price = $product['gia_khuyen_mai']; // Giá khuyến mãi
        }
    } else {
        // Không có màu sắc, kiểm tra giá khuyến mãi
        if ($product['gia_khuyen_mai'] > 0) {
            $final_price = $product['gia_khuyen_mai'];
        }
    }
    
    // 3. Tính thành tiền
    $thanh_tien = $final_price * $quantity;
    
    // 4. Kiểm tra giỏ hàng hiện tại
    // LƯU Ý: Bảng gio_hang hiện tại chưa hỗ trợ lưu mau_sac_id
    // TODO: Cần cập nhật cấu trúc bảng gio_hang để thêm cột mau_sac_id
    // để hỗ trợ biến thể màu sắc đầy đủ
    
    $sql_check_cart = "SELECT id_giohang, so_luong FROM gio_hang WHERE ma_user = ? AND id_sanpham = ?";
    $stmt_check = $conn->prepare($sql_check_cart);
    $stmt_check->execute(array($user_id, $product_id));
    $existing_cart = $stmt_check->fetch();
    
    if ($existing_cart) {
        // Đã có sản phẩm trong giỏ -> Cập nhật
        $new_quantity = $existing_cart['so_luong'] + $quantity;
        $new_thanh_tien = $final_price * $new_quantity;
        
        $sql_update = "UPDATE gio_hang SET so_luong = ?, thanh_tien = ?, update_at = NOW() WHERE id_giohang = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->execute(array($new_quantity, $new_thanh_tien, $existing_cart['id_giohang']));
        
        $response['status'] = 'success';
        $response['message'] = 'Đã cập nhật số lượng trong giỏ hàng';
        $response['action'] = 'updated';
    } else {
        // Chưa có sản phẩm trong giỏ -> Thêm mới
        // Không chỉ định id_giohang, để AUTO_INCREMENT tự động tạo
        $sql_insert = "INSERT INTO gio_hang (ma_user, id_sanpham, so_luong, thanh_tien, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->execute(array($user_id, $product_id, $quantity, $thanh_tien));
        
        $response['status'] = 'success';
        $response['message'] = 'Đã thêm vào giỏ hàng';
        $response['action'] = 'added';
    }
    
    // Lấy tổng số sản phẩm trong giỏ hàng
    $sql_count = "SELECT SUM(so_luong) as total_items FROM gio_hang WHERE ma_user = ?";
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->execute(array($user_id));
    $count_result = $stmt_count->fetch();
    
    $response['cart_count'] = $count_result['total_items'] ? (int)$count_result['total_items'] : 0;
    
} catch (PDOException $e) {
    $response['message'] = 'Lỗi database: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = 'Lỗi: ' . $e->getMessage();
}

echo json_encode($response);
?>

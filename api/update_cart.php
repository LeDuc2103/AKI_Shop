<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

require_once '../config/database.php';

$response = array('status' => 'error', 'message' => 'Có lỗi xảy ra');

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response['message'] = 'Phương thức không hợp lệ';
        echo json_encode($response);
        exit;
    }
    
    $cart_id = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    
    if ($cart_id <= 0) {
        $response['message'] = 'ID giỏ hàng không hợp lệ';
        echo json_encode($response);
        exit;
    }
    
    if ($quantity <= 0) {
        $response['message'] = 'Số lượng phải lớn hơn 0';
        echo json_encode($response);
        exit;
    }
    
    // Kiểm tra user_id từ session
    if (!isset($_SESSION['user_id'])) {
        $response['message'] = 'Vui lòng đăng nhập';
        echo json_encode($response);
        exit;
    }
    
    $user_id = $_SESSION['user_id'];
    
    // Lấy thông tin giỏ hàng
    $sql_get = "SELECT g.*, s.gia, s.gia_khuyen_mai 
                FROM gio_hang g 
                INNER JOIN san_pham s ON g.id_sanpham = s.id_sanpham
                WHERE g.id_giohang = ? AND g.ma_user = ?";
    $stmt = $conn->prepare($sql_get);
    $stmt->execute(array($cart_id, $user_id));
    $cart_item = $stmt->fetch();
    
    if (!$cart_item) {
        $response['message'] = 'Không tìm thấy sản phẩm trong giỏ hàng';
        echo json_encode($response);
        exit;
    }
    
    // Tính đơn giá (từ thành tiền cũ / số lượng cũ)
    $unit_price = $cart_item['thanh_tien'] / $cart_item['so_luong'];
    $new_thanh_tien = $unit_price * $quantity;
    
    // Cập nhật số lượng và thành tiền
    $sql_update = "UPDATE gio_hang SET so_luong = ?, thanh_tien = ? WHERE id_giohang = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->execute(array($quantity, $new_thanh_tien, $cart_id));
    
    $response['status'] = 'success';
    $response['message'] = 'Đã cập nhật giỏ hàng';
    $response['new_total'] = $new_thanh_tien;
    
} catch (PDOException $e) {
    $response['message'] = 'Lỗi database: ' . $e->getMessage();
} catch (Exception $e) {
    $response['message'] = 'Lỗi: ' . $e->getMessage();
}

echo json_encode($response);
?>
